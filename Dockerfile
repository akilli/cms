FROM akilli/php

LABEL maintainer "Ayhan Akilli"

#
# Setup
#
COPY . /app/

RUN chown -R app:app /app
