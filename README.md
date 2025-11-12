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

# Only the views or only the config
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="views"
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="config"
```

## Local development

### Clone and install the project

This project has a simple docker setup for local development. TO start local development, download the project 
and start the docker container. You need to have Docker installed on your lo

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