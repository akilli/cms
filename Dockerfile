FROM akilli/php

LABEL maintainer="Ayhan Akilli"

COPY . /app/

ONBUILD COPY . /data/ext/
