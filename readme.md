![akıllı cms](https://raw.githubusercontent.com/akilli/cms/master/gui/cms.svg?sanitize=true)

A quick'n'dirty non-OOP-experiment... or something completely different.

# `akilli/cms` Docker image

[akilli/php](https://github.com/akilli/php) based PHP image including the cms source files and defining an `ONBUILD`
command

```
ONBUILD COPY . /opt/
```

so that you can easily extend it by creating a custom `Dockerfile` with just the following line in it

```
FROM akilli/cms
```

# Usage

Start the docker containers with

```
docker compose up --detach
```

and access the cms with http://localhost. You can log in via http://localhost/account:login with
username `admin` and password `password`.

The provided [compose.yml](compose.yml) is meant for development and testing purposes only as it sets the
environment variable `DEV=1` for the php container which effectively disables the `session.cookie_secure` restriction,
opcache and preloading. It also mounts the source code into all three containers and uses the `akilli/php` image
instead of the `akilli/cms` image. On top of that it also automatically initializes the database by mounting 
[db/init](db/init) directory to `/init/postgres` in the `akilli/postgres` image.

# Manual database initialisation

It is also possible to initialize the database manually, p. e. by executing the `*.sql` files in [db/init](db/init) with
`psql` or by executing the following command with the cms cli tool:

```
docker compose exec --user app php php /app/bin/cli.php app:init
```

# Database schema version

The core cms tables are created in the *public* schema per default. The curreent schema version is stored as a comment
on the *public* schema.

## Current version

To show the current schema version either use the means provided by PostgreSQL directly or the user defined function 
`public.app_version_get()`:

```
SELECT public.app_version_get('public');
```

## Upgrade version

After upgrading the cms to a new version please execute the following command with the cms cli tool to also upgrade the
schema version on the pre-existing database:

```
docker compose exec --user app php php /app/bin/cli.php app:upgrade
```
