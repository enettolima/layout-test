#!/bin/bash

mysqldump -v --skip-extended-insert -udbuser -p'this is literally the password for dbuser' passport_live -hebtpassport.com | mysql -udbuser -pdbpass dbname
