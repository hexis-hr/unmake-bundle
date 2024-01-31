<?php

namespace Hexis\UnmakeBundle\Tests;

use Hexis\UnmakeBundle\DependencyInjection\UnmakeExtension;
use Hexis\UnmakeBundle\UnmakeBundle;
use PHPUnit\Framework\TestCase;

class UnmakeTest extends TestCase
{
    public function testGetContainerExtension(): void
    {
        $bundle = new UnmakeBundle();
        $this->assertInstanceOf(UnmakeExtension::class, $bundle->getContainerExtension());
    }
}
