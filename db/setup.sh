#!/bin/sh

set -e

DB=$(realpath "$(dirname "$0")")

#
# Dump
#
CONT=$(docker run -d -v $DB:/backup akilli/postgres)
sleep 10
docker exec -u app $CONT psql -f /backup/pg.sql app
docker exec $CONT tar cvf /backup/db.tar /app /data
docker rm -f $CONT

#
# Build
#
docker build -t registry.test.eqmh.de/cms-data $DB

#
# Clean
#
rm $DB/db.tar
