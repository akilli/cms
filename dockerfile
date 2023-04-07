FROM akilli/php
LABEL maintainer="Ayhan Akilli"

ENV APP_DB_PASSWORD=app

COPY . /app/
ONBUILD COPY . /opt/
