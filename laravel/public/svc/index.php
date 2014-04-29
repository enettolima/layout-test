<?php

require ('empInfoHelper.class.php');

$e = new empInfoHelper();

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->response->headers->set('Content-Type', 'application/json');

if ($_SERVER['HTTP_HOST'] == 'cdev.newpassport.com') {
	$db = new PDO('mysql:host=localhost;dbname=dbname;charset=utf8', 'dbuser', 'dbpass');
} elseif ($_SERVER['HTTP_HOST'] == 'ppdev.earthboundtrading.com') {
	$db = new PDO('mysql:host=localhost;dbname=dev_passport;charset=utf8', 'dev_dbuser', 'dev_dbpass');
} else {
	die ("need db specifics");
}

$logger = true;

$app->post(
    '/inOut/:storeNumber/:userId/:inString/:outString/:date', 
    function($storeNumber, $userId, $inString, $outString, $date) use ($logger, $app, $db)
    {
        $in  = date('Y-m-d H:i:s', strtotime($inString));
        $out = date('Y-m-d H:i:s', strtotime($outString));

        if ($userId != "undefined") {

            $query = "
                INSERT INTO scheduled_inout (
                    associate_id, 
                    store_id, 
                    date_in, 
                    date_out
                ) VALUES (
                    '$userId', 
                    $storeNumber, 
                    '$in', 
                    '$out'
                )
            ";
            
            if ($db->exec($query)) {
                $id = $db->lastInsertId();

                $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
                $scheduleHalfHourLookupSTMT = $db->query($scheduleHalfHourLookupSQL);
                $scheduleHalfHourLookupRES  = $scheduleHalfHourLookupSTMT->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(array(
                    'status' => 1,
                    'id' => $id, 
                    'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
                ));

            } else {
                echo json_encode(array('id' => null));
            }
        } else {
            echo json_encode(array('id' => null));
        }
    }
);

// Add a column
$app->put(
    '/inOutColumn/:storeNumber/:date/:userId',
    function($storeNumber, $date, $userId) use ($logger, $app, $db)
    {
        // Get the metadata for this store/day...

        $query = "
            SELECT
                *
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$date'
        ";

        $sth = $db->prepare($query);

        $sth->execute(); //TODO: Handle errors

        if ($res = $sth->fetch()){
            $metaArray = json_decode($res['data'], true);
            $currentSequence = $metaArray['sequence'];
            if (! in_array($userId, $currentSequence)) {
                $currentSequence[] = $userId;
                $metaArray['sequence'] = $currentSequence;
                $newData = json_encode($metaArray);
                $addQuery = "UPDATE schedule_day_meta set data = '$newData' where id = {$res['id']}";
            } else {
                $addQuery = false;
            }
        } else {
            $metaArray = array();
            $metaArray['sequence'][] = $userId;
            $newData = json_encode($metaArray);
            $addQuery = "INSERT INTO schedule_day_meta (store_id, date, data) VALUES ($storeNumber, '$date', '$newData')";
        }

        if ($addQuery) {
            $sth = $db->prepare($addQuery);
             
            if ($sth->execute()) {
                echo json_encode(array('status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }
        } else {
            echo json_encode(array('status' => 1));
        }
    }
);

$app->delete(
    '/removeUserFromSchedule/:storeNumber/:userId/:weekOf',
    function($storeNumber, $userId, $weekOf) use ($logger, $app, $db)
    {
        $query = "
            SELECT
                *
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$weekOf'
        ";

        $sth = $db->prepare($query);

        $sth->execute(); //TODO: Handle errors

        $performUpdate = false;

        if ($res = $sth->fetch()){

            $metaArray = json_decode($res['data'], true);

            $currentSequence = $metaArray['sequence'];
            if (in_array($userId, $currentSequence)) {
                foreach($currentSequence as $key=>$val) {
                    if ($val == $userId) {
                        unset($currentSequence[$key]);
                        $performUpdate = true;
                    }
                }
            }
        }

        if ($performUpdate) {
            $metaArray['sequence'] = array_values($currentSequence);
            $newData = json_encode($metaArray);
            $query = "UPDATE schedule_day_meta set data = '$newData' where id = {$res['id']}";
            $sth = $db->prepare($query);
             
            if ($sth->execute()) {

                $nextSunday = date("Y-m-d", strtotime('next sunday', strtotime($weekOf)));

                $deleteScheduleQuery = "
                    DELETE FROM
                        scheduled_inout
                    WHERE
                        date_in >= '$weekOf' AND
                        date_in < '$nextSunday' AND
                        associate_id = '$userId' AND
                        store_id = $storeNumber
                ";

                $deleteScheduleHandle = $db->prepare($deleteScheduleQuery);

                $deleteScheduleHandle->execute();

                echo json_encode(array('status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }

        } else {
            echo json_encode(array('status' => 1));
        }
    }
);

$app->delete(
    '/inOutColumn/:storeNumber/:date/:userId',
    function($storeNumber, $date, $userId) use ($logger, $app, $db)
    {
        // Get the metadata for this store/day...

        $query = "
            SELECT
                *
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$date'
        ";

        $sth = $db->prepare($query);

        $sth->execute(); //TODO: Handle errors

        $performUpdate = false;

        if ($res = $sth->fetch()){
            $metaArray = json_decode($res['data'], true);
            $currentSequence = $metaArray['sequence'];
            if (in_array($userId, $currentSequence)) {
                foreach($currentSequence as $key=>$val) {
                    if ($val == $userId) {
                        unset($currentSequence[$key]);
                        $performUpdate = true;
                    }
                }
            }

        }

        if ($performUpdate) {
            $metaArray['sequence'] = $currentSequence;
            $newData = json_encode($metaArray);
            $query = "UPDATE schedule_day_meta set data = '$newData' where id = {$res['id']}";
            $sth = $db->prepare($query);
             
            if ($sth->execute()) {
                echo json_encode(array('status' => 1));
            } else {
                echo json_encode(array('status' => 0));
            }

        } else {
            echo json_encode(array('status' => 1));
        }
    }
);


$app->put(
    '/inOutResize/:inOutId/:delta/:storeNumber/:date', 
    function($inOutId, $delta, $storeNumber, $date) use ($app, $db, $logger)
    {
        $query = "
            UPDATE scheduled_inout
            SET
                date_out = DATE_ADD(date_out, INTERVAL $delta MINUTE)
            WHERE
                id = $inOutId
        ";

        if ($db->exec($query)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupSTMT = $db->query($scheduleHalfHourLookupSQL);
            $scheduleHalfHourLookupRES  = $scheduleHalfHourLookupSTMT->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
            ));

        } else {
            echo json_encode(array('status' => 0));
        }

    }
);

$app->put(
    '/inOutMove/:userId/:inOutId/:delta/:date/:storeNumber', 
    function($userId, $inOutId, $delta, $date, $storeNumber) use ($logger, $app, $db)
    {

        $query = "
            UPDATE scheduled_inout
            SET
                associate_id = '$userId',
                date_in = DATE_ADD(date_in, INTERVAL $delta MINUTE),
                date_out = DATE_ADD(date_out, INTERVAL $delta MINUTE)
            WHERE
                id = $inOutId
        ";

        if ($db->exec($query)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupSTMT = $db->query($scheduleHalfHourLookupSQL);
            $scheduleHalfHourLookupRES  = $scheduleHalfHourLookupSTMT->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
            ));
        } else {
            echo json_encode(
                array(
                    'status' => 0,
                    'error' => $db->errorInfo()
                )
            );
        }

    }
);

$app->put(
    '/inOut/:inOutId/:userId/:inString/:outString', 
    function($inOutId, $userId, $inString, $outString) use ($logger, $app, $db)
    {
        $in  = date('Y-m-d H:i:s', strtotime($inString));
        $out = date('Y-m-d H:i:s', strtotime($outString));

        $query = "
            UPDATE scheduled_inout
            SET
                associate_id = '$userId',
                date_in = '$in',
                date_out = '$out'
            WHERE
                id = $inOutId
        ";

        if ($db->exec($query)) {

            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupSTMT = $db->query($scheduleHalfHourLookupSQL);
            $scheduleHalfHourLookupRES  = $scheduleHalfHourLookupSTMT->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
            ));
        } else {
            echo json_encode(
                array(
                    'status' => 0,
                    'error' => $db->errorInfo()
                )
            );
        }
    }
);

$app->delete(
    '/inOut/:inOutId/:storeNumber/:date',
    function($inOutId, $storeNumber, $date) use($logger, $app, $db){

        $query = "
            DELETE FROM
                scheduled_inout
            WHERE
                id = $inOutId
        ";

        if ($db->exec($query)) {
            $scheduleHalfHourLookupSQL  = "call p2($storeNumber, '$date')";
            $scheduleHalfHourLookupSTMT = $db->query($scheduleHalfHourLookupSQL);
            $scheduleHalfHourLookupRES  = $scheduleHalfHourLookupSTMT->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(array(
                'status' => 1,
                'scheduleHourLookup' => $scheduleHalfHourLookupRES[0]
            ));

        } else {
            echo json_encode(array('status' => 0));
        }
    }
);

$app->run();
