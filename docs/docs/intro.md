---
sidebar_position: 1
---

# Getting Started

Eloquent Tables is a Laravel package that makes building fully-featured, accessible HTML tables as simple as writing PHP.

No front-end setup. No JavaScript boilerplate. No custom markup. Just expressive, elegant code: the Laravel way.

With Eloquent Tables, you can define sorting, searching, filtering, pagination, row actions, styling, and more using clean PHP classes. The package handles generating the correct HTML structure, accessibility attributes, and integrations for you. Whether you're building admin dashboards, data-heavy applications, or CRUD interfaces, Eloquent Tables gives backend developers everything they need to create powerful table components without touching the front end.

## Requirements

- PHP `^8.4|^8.5`
- Laravel `^12.0`
- Bootstrap 5

## Installation

Install the package via composer:

```bash
composer require bricknpc/eloquent-tables
```

## Publishing assets

The Eloquent Tables package has several different assets that can be published for you to customize.

### Config

If you want to edit the config, you should publish the config file for Eloquent Tables:

```bash
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="config"
```

### Blade views

If you want to edit the blade files, you should publish the config file for Eloquent Tables:

```bash
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="views"
```

### Translations

If you want to edit the translation file, you should publish the config file for Eloquent Tables:

```bash
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="lang"
```

### All assets

If you want to publish all assets for the Eloquent Tables package at once, use this command:

```bash
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider"
```
