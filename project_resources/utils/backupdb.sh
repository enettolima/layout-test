#!/bin/bash

DBNAME="ppdev_01"
DBUSER="dbuser"
DBPASS="this is literally the password for dbuser"
DBHOST="dev.ebtpassport.com"

STAMP="$(date +%Y%m%d%H%M%S)"

mysqldump --skip-extended-insert --complete-insert -u${DBUSER} -p"${DBPASS}" -h${DBHOST} ${DBNAME} > dbdump.${STAMP}.sql

bzip2 dbdump.${STAMP}.sql

scp dbdump.${STAMP}.sql.bz2 ebtbackup@web02.earthboundtrading.com:backup/ppdev_01_database/

rm dbdump.${STAMP}.sql.bz2
