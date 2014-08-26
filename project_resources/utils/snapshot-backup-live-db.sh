#!/bin/bash

DBNAME="passport_live"
DBUSER="dbuser"
DBPASS="this is literally the password for dbuser"
DBHOST="dev.ebtpassport.com"

STAMP="$(date +%Y%m%d%H%M%S)"

mysqldump --skip-extended-insert --complete-insert -u${DBUSER} -p"${DBPASS}" -h${DBHOST} ${DBNAME} > dbdump.${STAMP}.sql

bzip2 dbdump.${STAMP}.sql

scp dbdump.${STAMP}.sql.bz2 ebtbackup@web02.earthboundtrading.com:backup/passport_live/

rm dbdump.${STAMP}.sql.bz2
