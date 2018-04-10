# `akilli/cms`

![akıllı CMS](https://raw.githubusercontent.com/akilli/cms/master/gui/logo.jpg)

A quick'n'dirty non-OOP-experiment... or something completely different.

## Setup

Check the docker-compose.yml file as one possible solution. It is using an external network `proxy` and [traefik](https://traefik.io/) as reverse proxy. Both is not needed, so adjust to your needs.

Run the docker containers and access with configured domain. You can log in via URL `/account/login` with name `admin` and password `password`. 

**!!! NOTE !!!**

The docker image is based on the `akilli/php` image and uses its default configuration. This might work for you out of the box or not.

## Abbreviations

abbr | full
---- | -------
app  | application
arr  | array
attr | attribute
cfg  | config
crit | criteria
db   | database
ent  | entity
ext  | extension
i18n | internationalization
msg  | message
opt  | option
priv | privilege
reg  | registry
req  | request
rte  | rich text editor
tpl  | template
val  | value
