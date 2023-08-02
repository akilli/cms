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

The database password can be adjusted with the environment variable `APP_DB_PASSWORD`.

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
[db](db) directory to `/init/postgres` in the `akilli/postgres` image.
