# Unmake Bundle

## Features

- **The Unmake Entity Command**: is a Symfony Console command designed to facilitate the deletion of entities from a
  database based on provided filters. This command is particularly useful during development and testing phases where 
  data cleanup or selective deletion of entities is necessary.

## Installation

Install the `UnmakeBundle` via Composer:

```bash
composer require hexis-hr/unmake-bundle
```

---

## Unmake Entity Command

### Command Usage:

Run the following command from your terminal:

```bash
php bin/console unmake:entity
```

### Entity Selection:

You will be prompted to select the entity you wish to delete. Provide the name of the entity exactly as it appears in
the application's entity definitions.

### Filtering:

Optionally add filters to narrow down the entities to be deleted. 
Filters include selecting a property, an operator, and providing a value for comparison.

### Confirmation:

Confirm the deletion operation and review the number of rows that will be affected.

### Deletion:

If confirmed, the command will proceed to delete the selected entities from the database.

---

## Contributing

Contributions are welcome! Please feel free to submit issues, feature requests, or pull requests.

## License

This bundle is open-sourced software licensed under the [Apache License 2.0](LICENSE).

---

Feel free to customize the README with additional information such as usage examples, troubleshooting tips, or any other
relevant details specific to your bundle and its functionality.
