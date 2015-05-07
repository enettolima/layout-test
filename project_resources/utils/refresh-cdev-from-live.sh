#!/bin/bash

#mysqldump -v --skip-extended-insert -udbuser -p'this is literally the password for dbuser' passport_live -hebtpassport.com | mysql -udbuser -pdbpass dbname

SRC_SSHHOST="ebtpassport.com"
SRC_SSHUSER="chad"

SRC_DBHOST="localhost"
SRC_DBUSER="dbuser"
SRC_DBPASS="this is literally the password for dbuser"
SRC_DBNAME="passport_live"

DEST_DBHOST="localhost"
DEST_DBUSER="dbuser"
DEST_DBPASS="dbpass"
DEST_DBNAME="dbname"

STAMP="$(date +%Y%m%d%H%M%S)"

# Protect against running this from the wrong spot...
#if [ -d ../../mageroot ]
if [ 1 ]
then

    echo "Dropping & recreating DEST DB..."
    mysql -u${DEST_DBUSER} -p"${DEST_DBPASS}" -h${DEST_DBHOST} -e "drop database if exists ${DEST_DBNAME}; create database ${DEST_DBNAME}"

    #echo "Dumping SRC db into DEST"
    #mysqldump --verbose -u${SRC_DBUSER} -p"${SRC_DBPASS}" -h${SRC_DBHOST} ${SRC_DBNAME} | mysql -u${DEST_DBUSER} -p"${DEST_DBPASS}" -h${DEST_DBHOST} ${DEST_DBNAME}

    echo "Creating DB Dump on SRC..."
    ssh ${SRC_SSHUSER}@${SRC_SSHHOST} "mysqldump -u${SRC_DBUSER} -p'${SRC_DBPASS}' -h${SRC_DBHOST} ${SRC_DBNAME} > dbdump.${STAMP}.sql && gzip --fast dbdump.${STAMP}.sql"

    echo "Retrieving DB Dump from SRC..."
    scp ${SRC_SSHUSER}@${SRC_SSHHOST}:dbdump.${STAMP}.sql.gz .

    echo "Removing DB Dump on SRC..."
    ssh ${SRC_SSHUSER}@${SRC_SSHHOST} "rm dbdump.${STAMP}.sql.gz"

    echo "Uncompressing DB Dump..."
    gunzip -vv dbdump.${STAMP}.sql

    echo "Loading DEST DB from Dump..."
    mysql -u${DEST_DBUSER} -p"${DEST_DBPASS}" -h${DEST_DBHOST} ${DEST_DBNAME} < dbdump.${STAMP}.sql

    echo "Removing DB Dump..."
    rm dbdump.${STAMP}.sql

else
    echo "Not running... this needs to be run from inside project_resources/cli"
fi

