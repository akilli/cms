![akıllı CMS](https://raw.githubusercontent.com/akilli/cms/master/gui/cms.svg?sanitize=true)

# akıllı CMS

A quick'n'dirty non-OOP-experiment... or something completely different.

## `akilli/cms` Docker image

[akilli/php](https://github.com/akilli/php) based PHP image including the cms source files and defining an `ONBUILD` 
command

```
ONBUILD COPY . /opt/
```

so that you can easily extend it by creating a custom `Dockerfile` with just the following line in it

```
FROM akilli/cms
```

## Usage

Start the docker containers with

```
docker-compose up -d
```

and access the cms with http://localhost. You can log into the admin area via http://localhost/account/login with 
username `admin` and password `password`.

The provided [docker-compose.yml](docker-compose.yml) is meant for development and testing purposes only as it sets the 
environment variable `DEV=1` for the php container which effectively disables the `session.cookie_secure` restriction, 
opcache and preloading. It also mounts the source code into all three containers and uses the `akilli/php` image 
instead of the `akilli/cms` image.
