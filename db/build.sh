#!/bin/sh

set -e

#
# Backup & Build
#
#DB=$(realpath "$(dirname "$0")")
#docker run --rm -v $DB:/backup -v cms_data:/app -v cms_db:/data akilli/base tar cvf /backup/db.tar /app /data
#docker build -t registry.test.eqmh.de/cms-data $DB

#
# Commit
#
CONT=$(docker run -d -it -v cms_data:/cms-data -v cms_db:/cms-db --entrypoint ash akilli/postgres)
docker exec $CONT ash -c 'rm -rf /app /data && cp -R /cms-data /app && cp -R /cms-db /data && chown -R app:app /app /data'
docker commit $CONT registry.test.eqmh.de/cms-data
docker rm -f $CONT
