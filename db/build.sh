#!/bin/sh

set -e

IMG=akilli/cms-data
VDA=cms_data
VDB=cms_db

#
# Commit
#
CONT=$(docker run -d -v $VDA:/vol-data -v $VDB:/vol-db akilli/postgres)
sleep 10
docker exec $CONT ash -c 'rm -rf /app && cp -R /vol-data /app && rm -rf /data && cp -R /vol-db /data && chown -R app:app /app /data'
docker commit $CONT $IMG
docker rm -f $CONT
