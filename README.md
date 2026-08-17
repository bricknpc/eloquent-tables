<p align="center">

# BrickNPC Eloquent Tables

![BrickNPC Eloquent Table Logo](eloquent-tables.png "Eloquent Tables")

![Static Badge](https://img.shields.io/badge/bricknpc-eloquent--tables-blue)
![GitHub License](https://img.shields.io/github/license/bricknpc/eloquent-tables)
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/bricknpc/eloquent-tables/ci.yml)
![Codecov](https://img.shields.io/codecov/c/github/bricknpc/eloquent-tables)
![GitHub commit activity](https://img.shields.io/github/commit-activity/t/bricknpc/eloquent-tables)
![GitHub Release](https://img.shields.io/github/v/release/bricknpc/eloquent-tables)

</p>

## Installation

Install the package using composer.

```bash
composer require bricknpc/eloquent-tables 
```

### Requirements

- PHP `^8.4|^8.5`
- Laravel `^12.0|^13.0`

This package builds eloquent tables for you with the frontend framework of your choice. You need to install the 
frontend framework yourself in your Laravel project.

Supported frontend frameworks:

- Bootstrap 5

Roadmap:

- Tailwind 4
- Bulma
- BlazeUI

### Supported versions

| Eloquent Tables | PHP      | Laravel | Status      |
|-----------------|----------|---------|-------------|
| 2.0             | 8.4, 8.5 | 12, 13  | Active      |
| 1.2             | 8.4, 8.5 | 12      | End of life |
| 1.1             | 8.4      | 12      | End of life |
| 1.0             | 8.4      | 12      | End of life |

Only the active major version receives new features. At most one older major is supported alongside it, and it
receives bug fixes only. Version 1.x was not carried forward, so 2.0 is currently the only supported version. See
[supported versions](https://bricknpc.github.io/eloquent-tables/docs/supported-versions) for the full policy.

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

See the [documentation](https://bricknpc.github.io/eloquent-tables/docs/intro).

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

### Documentation

When starting the docker container, the documentation site will automatically be started as well and will be available 
on http://localhost:3000/eloquent-tables. The documentation is built using [Docusaurus](https://docusaurus.io/). When 
adding new features or making changes, please also update the documentation.

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

## Community showcase

Are you using Eloquent Tables in your project? Let us know by opening a pull request to add your project to the 
[community showcase](https://github.com/bricknpc/eloquent-tables/blob/HEAD/docs/src/pages/showcase.js). We love seeing 
what people are building with Eloquent Tables.

## Contributing

Pull requests are welcome. When creating a pull request, please include what you changed and why in the description of 
the pull request. When fixing a bug, please include a test that reproduces the bug and describe how to test the bug 
manually.

Before creating a pull request, please run the tests and code quality tools locally.

We only accept pull requests when PHPStan reports no errors and the test coverage hasn't gone down.

### Which branch to target

Branches are named after versions. The default branch is the current major, `2.x` today, and it tracks what is
released. Work does not go there directly.

Features and bug fixes target the **next release branch** instead, named after the version it will become, so `2.1`
for the next minor or `3.x` for the next major. Branch from it as
`feature/<issue-number>-descriptive-name` or `bugfix/<issue-number>-descriptive-name`, leaving the issue number out
if there is no issue, and open your pull request back against it. If no next release branch exists yet, mention it
on your issue or pull request and one will be created.

The exception is an urgent fix that cannot wait for the next release. Those branch as `hotfix/<name>` from the
current major branch, and are tagged as a patch release as soon as they merge. See
[supported versions](https://bricknpc.github.io/eloquent-tables/docs/supported-versions) for the full policy.
