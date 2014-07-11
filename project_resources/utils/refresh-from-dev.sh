#!/bin/bash

mysqldump -v --skip-extended-insert -udbuser -p'this is literally the password for dbuser' ppdev_01 -hdev.ebtpassport.com | mysql -udbuser -pdbpass dbname
