<?php

// THIS MUST BE THE FULL PATH
$config = include('/home/wwwgeneral/sites/ebtpassport.com/laravel/.env.local.php');

$STAMP = date("D.Hi");
system("mysqldump --skip-extended-insert --complete-insert -u'{$config['mysql_username']}' -p'{$config['mysql_password']}' -h{$config['mysql_host']} {$config['mysql_database']} > dbdump.{$STAMP}.sql");
system("bzip2 dbdump.{$STAMP}.sql");
system("scp dbdump.{$STAMP}.sql.bz2 ebtbackup@web02.earthboundtrading.com:backup/{$config['mysql_database']}/");
system("rm dbdump.{$STAMP}.sql.bz2");
