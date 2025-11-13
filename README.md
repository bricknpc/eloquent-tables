# BrickNPC Eloquent Tables

## Installation

Install the package using composer.

```bash
composer require bricknpc/eloquent-tables
```

### Requirements

- PHP ^8.4
- Laravel ^12.0

This package builds eloquent tables for you with the frontend framework of your choice. You need to install the 
frontend framework yourself in your Laravel project.

Supported frontend frameworks:

- Bootstrap 5

Frameworks that are planned:

- Tailwind CSS V4
- Bulma
- BlazeUI

### Publishing assets

You can publish the Eloquent Tables config and view files.

```php
# All assets
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider"

# Only one type of asset
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="views"
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="config"
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="lang"
```

## Documentation

See the [docs](docs/index.md).

## Local development

### Clone and install the project

This project has a simple docker setup for local development. To start local development, download the project 
and start the docker container. You need to have Docker installed on your local machine for this.

First, clone the project.

```bash
git clone https://github.com/bricknpc/eloquent-tables.git
cd eloquent-tables
```

Up the docker container and install the dependencies.

```bash
docker-compose up -d
docker-compose exec php composer install
```

### Executing commands in the container

You can execute commands in the container using the exec option.

```bash
docker-compose exec php <your command>
```

If you rather log in to the container and execute commands manually, you can use this:

```bash
docker-compose exec php bash
```

### Stopping the container

```bash
docker-compose down
```

## Running tests

You can run the tests using the following command.

```bash
docker-compose exec php composer test
```

## Code quality tools

Eloquent Tables uses PHP CS Fixer and PHPStan to ensure a high quality code base. You can run the tools locally 
using the following commands.

**PHP CS Fixer:**
```bash
docker-compose exec php composer cs
```

**PHPStan:**
```bash
docker-compose exec php composer ps
```

## Contributing

Pull requests are welcome. When creating a pull request, please include what you changed and why in the description of 
the pull request. When fixing a bug, please include a test that reproduces the bug and describe how to test the bug 
manually.

Before creating a pull request, please run the tests and code quality tools locally.

We only accept pull requests when PHPStan reports no errors and the test coverage hasn't gone down.