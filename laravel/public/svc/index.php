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
