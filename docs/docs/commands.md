---
sidebar_position: 4
---

# Table Commands

The help you quickly create a new Table, the Eloquent Tables package exposes a few helpful commands.

## Make table command

The make table command creates a new Table class for you. It works the same as all default Laravel `make` commands. By 
default, it places new Tables in the `app\Tables` folder, but you can change the default folder in the config file.

```bash
php artisan et:make:table UserTable --model=User
```

The command requires a name for the new Table. Just like with other `make` commands, you can use a namespaced classname 
to create Tables deeper inside the directory:

```bash
php artisan et:make:table Users\Table --model=User
```

### Options

The `make` command has the following options:

| Option              | Description                                                                  |
|---------------------|------------------------------------------------------------------------------|
| `--model=Model`     | The Model for which to create the table. Can be just the classname or a FQN. |
| `--with-pagination` | If added, adds pagination to the table.                                      |
