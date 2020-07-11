ARG TAG=latest
FROM akilli/php:${TAG}
LABEL maintainer="Ayhan Akilli"

COPY . /app/
ONBUILD COPY . /opt/
