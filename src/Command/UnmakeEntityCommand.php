<?php

namespace Hexis\UnmakeBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UnmakeEntityCommand extends Command
{
    protected static $defaultName = 'unmake:entity';
    protected static $defaultDescription = 'Delete entities based on provided filters.';
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $entitySelected = $this->selectEntity();

        if (!$entitySelected) {
            $this->io->error('No valid entity selected.');
            return Command::FAILURE;
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')->from($entitySelected, 'a');

        $filterCounter = 0;
        do {
            $filterCounter = $this->addFilter($qb, $filterCounter);
        } while ($this->askForAnotherProperty());

        $result = $qb->getQuery()->getResult();
        $this->io->info(sprintf('Number of rows affected: %s', count($result)));

        if ($this->confirmDeletion($result)) {
            $deletedRows = $this->deleteRows($qb, $entitySelected);
            $this->io->success(sprintf('Number of rows deleted: %s', $deletedRows));
        } else {
            $this->io->warning('Deletion canceled or no rows to delete.');
        }

        return Command::SUCCESS;
    }

    private function getEntityNames(): array
    {
        $entityList = $this->entityManager->getMetadataFactory()->getAllMetadata();
        return array_map(static function ($metadata) {
            return $metadata->getName();
        }, $entityList);
    }

    private function selectEntity(): ?string
    {
        $entityNames = $this->getEntityNames();
        $question = new Question('What entity do you want to delete?');
        $question->setAutocompleterValues($entityNames);

        return $this->io->askQuestion($question);
    }

    private function addFilter($qb, int $filterCounter): int
    {
        $entitySelected = $qb->getRootEntities()[0];
        $columnList = $this->entityManager->getClassMetadata($entitySelected)->getFieldNames();

        $columnSelected = $this->io->askQuestion(new ChoiceQuestion('Select property', $columnList));
        $operatorList = ['=', '>', '<', '>=', '<='];
        $operatorSelected = $this->io->askQuestion(new ChoiceQuestion('Select operator', $operatorList));
        $columnValue = $this->io->askQuestion(new Question(sprintf('Enter value of %s', $columnSelected)));

        $qb->andWhere('a.' . $columnSelected . ' ' . $operatorSelected . ' :parameter' . $filterCounter);
        $qb->setParameter('parameter' . $filterCounter, $columnValue);

        return ++$filterCounter;
    }

    private function askForAnotherProperty(): bool
    {
        return $this->io->confirm('Do you want to add another property?', false);
    }

    private function confirmDeletion(array $result): bool
    {
        $confirmation = $this->io->confirm(sprintf('Do you want to continue and delete %s rows?', count($result)), false);
        return $confirmation && count($result) > 0;
    }

    private function deleteRows($qb, $entitySelected): int
    {
        $deletedQuery = $qb->delete($entitySelected, 'a');
        return $deletedQuery->getQuery()->execute();
    }
}
