FROM akilli/php

LABEL maintainer="Ayhan Akilli"

COPY . /app/

ONBUILD COPY . /data/ext/
ONBUILD RUN su-exec app php /app/preload.php
