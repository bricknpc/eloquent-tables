# BrickNPC Eloquent Tables

## Local development

### Clone and install the project

This project has a simple docker setup for local development. TO start local development, download the project 
and start the docker container. You need to have Docker installed on your local system to use this setup.

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