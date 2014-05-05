#!/bin/bash

mysqldump -v --skip-extended-insert -udbuser -pdbpass dbname | mysql -udbuser -p'this is literally the password for dbuser' ppdev_01 -hdev.ebtpassport.com
