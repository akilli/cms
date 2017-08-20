#!/bin/sh

set -e

IMG=registry.test.eqmh.de/cms-data
VDA=cms_data
VDB=cms_db

#
# Commit
#
CONT=$(docker run -d -v $VDA:/vol-data -v $VDB:/vol-db akilli/postgres)
sleep 10
docker exec $CONT ash -c 'rm -rf /app /data && cp -R /vol-data /app && cp -R /vol-db /data && chown -R app:app /app /data'
docker commit $CONT $IMG
docker rm -f $CONT
