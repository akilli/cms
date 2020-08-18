![akıllı CMS](https://raw.githubusercontent.com/akilli/cms/master/gui/cms.svg?sanitize=true)

# akıllı CMS

A quick'n'dirty non-OOP-experiment... or something completely different.

## `akilli/cms` Docker image

`akilli/php` based PHP 7.4 (`latest`) or 8.0 (`next`) image including the cms source files and defining an `ONBUILD` command

```
ONBUILD COPY . /opt/
```

so that you can easily extend it by creating a custom `Dockerfile` with just the following line in it

```
FROM akilli/cms
```

## `akilli/cms-db` Docker image

`akilli/postgres` based PostgreSQL image including the cms database and defining an `ONBUILD` command

```
ONBUILD COPY . /init/postgres/
```

so that you can easily extend it by creating a custom `Dockerfile` with just the following line in it

```
FROM akilli/cms-db
```

## Usage

Start the docker containers with

```
docker-compose up -d
```

and access the cms with http://localhost. You can log into the admin area via http://localhost/account/login with username `admin` and password `password`.

The provided [docker-compose.yml](docker-compose.yml) is meant for development purposes only as it mounts the [php.dev.ini](php.dev.ini) into the php container which effectively disables the `session.cookie_secure` restriction, opcache and preloading. You surely do not want to do this in production. It also mounts the source code into all three containers and uses the base docker images instead of `akilli/cms` and `akilli/cms-db` for development and testing reasons.
