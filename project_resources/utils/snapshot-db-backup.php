<?php

// THIS MUST BE THE FULL PATH
$config = include('/home/wwwgeneral/sites/ebtpassport.com/laravel/.env.local.php');

$STAMP = date("D.Hi");
system("mysqldump --single-transaction --quick -u'{$config['mysql_username']}' -p'{$config['mysql_password']}' -h{$config['mysql_host']} {$config['mysql_database']} > dbdump.{$STAMP}.sql");
system("ionice -c3 nice -n19 bzip2 dbdump.{$STAMP}.sql");
system("scp dbdump.{$STAMP}.sql.bz2 ebtbackup@web02.earthboundtrading.com:backup/{$config['mysql_database']}/");
system("rm dbdump.{$STAMP}.sql.bz2");
