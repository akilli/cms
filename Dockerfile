FROM akilli/php

LABEL maintainer="Ayhan Akilli"

COPY . /srv/
COPY init/ /init/

ONBUILD COPY . /opt/
