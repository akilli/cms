#!/bin/sh

set -e

DB=$(realpath "$(dirname "$0")")
CMS=$(realpath "$(dirname "$0")/..")

#
# Directories
#
rm -rf $DB/app $DB/data || true
mkdir $DB/app $DB/data

#
# Dump
#
CONT=$(docker run -d -v $CMS/data/pg.sql:/pg.sql -v $DB/data:/data akilli/postgres)
sleep 10
docker exec -u app $CONT psql -f /pg.sql app
docker stop $CONT
docker rm $CONT

#
# Build
#
docker build -t registry.test.eqmh.de/cms-data $DB

#
# Clean
#
rm -rf $DB/app $DB/data
