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

## Setup

Check the docker-compose.yml file as one possible solution. It is using an external network `proxy` and [traefik](https://traefik.io/) as reverse proxy. Both is not needed, so adjust to your needs.

Run the docker containers and access with configured domain. You can log in via URL path `/account/login` with name `admin` and password `password`. 
