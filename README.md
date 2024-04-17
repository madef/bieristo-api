# Introduction

This project use docker and traefik. You can run this project without docker but the documentation is focused on docker installation.

# Installation with docker

## Requirements

You need to install docker and have a traefik installed as reverse proxy.
First install docker:
```
yay docker
```

You can use this container if you dont have traefik : https://github.com/madef/traefik

## Get the projet and set configurations files

Get the projet:
```
mkdir bieristo
cd bieristo
git clone git@github.com:madef/bieristo-api.git
cd bieristo-api
```

Copy sample files:
```
cp docker/docker-compose.override.yml.sample docker/docker-compose.override.yml
cp docker/env/development.sample docker/env/development
```

Edit environments files:
```
vim docker/docker-compose.override.yml
vim docker/env/development
```

## Setup libs and mongo database
Start the containers:
```
make start
```

From the console, run composer install:
```
make bash
./composer.phar install
```

Create mongo collection and set indexes:
```
php script/mongodb/createIndex.php
```

## Check installation

Check installation is ok, by loading the url https://api.bieristo.local/check-db.php:
```
{"success":true}
```
