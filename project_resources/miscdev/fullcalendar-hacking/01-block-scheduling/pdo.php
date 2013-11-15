<?php

$dbh = new PDO('mysql:host=localhost;dbname=ebt_dev;charset=utf8', 'root', '');

$query = "SELECT * FROM schedule_day_meta WHERE store_id = 301 AND date = '2013-11-05'";

$sth = $dbh->prepare($query);

$sth->execute();

if ($res = $sth->fetch()) {
    var_dump($res);
} else {
    var_dump("no");
}

if ($res = $sth->fetch()) {
    var_dump($res);
} else {
    var_dump("no");
}
