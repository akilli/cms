FROM akilli/php

LABEL maintainer="Ayhan Akilli"

#
# Setup
#
COPY . /app/

#
# Onbuild
#
ONBUILD COPY . /data/ext/
ONBUILD RUN su-exec app php /app/preload.php
