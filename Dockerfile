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
