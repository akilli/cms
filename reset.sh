#!/bin/bash

set -e

CONT=eqmh_postgres_1
DB=qnd
DEST=/app/pg.sql
DIR=/docker/app/qnd
REPO=https://ci:8htCH3ss3w@gogs.test.eqmh.de/eqmh/qnd.git
SRC=$DIR/data/pg.sql

if [ -z $DIR ] || [ ! -d $DIR ]; then
    echo "Invalid directory $DIR"
    exit 1
fi

rm -rf $DIR/* $DIR/.[^.]*
ls -al $DIR

git clone $REPO $DIR
ls -al $DIR

docker cp $SRC $CONT:$DEST
docker exec -u postgres $CONT psql -c "DROP DATABASE IF EXISTS $DB;"
docker exec -u postgres $CONT psql -c "CREATE DATABASE $DB;"
docker exec -u postgres $CONT psql -f $DEST $DB
_pwd='$2y$10$gBt7TQ4aEfsl25PPzZK1U.mrppMMtJlHT8hA2wTX.o2c5EKwz75FO'
docker exec -u postgres $CONT psql -c "UPDATE account SET password = '$_pwd' WHERE name = 'admin';" $DB
_pwd='$2y$10$zebAk/rfLIbM/n/zf/.N8e2aJ5hfQJhViYrrYfTV6aM2mknG2SxMK'
docker exec -u postgres $CONT psql -c "INSERT INTO account (name, password, role_id, active, project_id) VALUES ('kpi', '$_pwd', 1, TRUE, 1)" $DB
