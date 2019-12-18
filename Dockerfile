FROM akilli/php

LABEL maintainer="Ayhan Akilli"

COPY . /srv/
COPY docker/00-dirs /init/00-dirs

ONBUILD COPY . /opt/
