<?php

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('my_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/log.txt', Logger::DEBUG));
$logger->addInfo('start');

$app = new \Slim\Slim();

$app->response->headers->set('Content-Type', 'application/json');

$db = new PDO('mysql:host=localhost;dbname=ebt_dev;charset=utf8', 'root', '');

$app->get(
    '/storeDaySchedule/:storeNumber/:date', 
    function($storeNumber, $date) use($logger, $db)
    {

        $date = date('Y-m-d', strtotime($date));

        $returnval = array();

        $querySchedule = "
            SELECT
                s.`id`,
                s.`associate_id`,
                s.`store_id`, 
                s.`date_in`, 
                s.`date_out` 
            FROM 
                scheduled_inout s 
            WHERE
                s.`store_id` = $storeNumber AND
                DATE(date_in) = '$date';
        ";

        $stmtSchedule = $db->query($querySchedule);

        $resultsSchedule = $stmtSchedule->fetchAll(PDO::FETCH_ASSOC);

        $queryMeta = "
            SELECT
                data
            FROM
                schedule_day_meta
            WHERE
                store_id = $storeNumber AND
                date = '$date'
        ";

        $stmtMeta = $db->query($queryMeta);

        $resultsMeta = $stmtMeta->fetchAll(PDO::FETCH_ASSOC);

        if (! isset($resultsMeta[0])) {
            $resultsMeta[0] = array();
        }

        // var_dump($resultsMeta[0]['data']);

        echo json_encode(array('meta' => json_decode($resultsMeta[0]['data']), 'schedule' => $resultsSchedule));
    }
);

$app->post(
    '/inOut/:storeNumber/:userId/:inString/:outString', 
    function($storeNumber, $userId, $inString, $outString) use ($logger, $app, $db)
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
                // $app->response->setStatus(201);
                echo json_encode(array('id' => $db->lastInsertId()));
            } else {
                // $app->response->setStatus(409);
                echo json_encode(array('id' => null));
            }
        } else {
            $logger->addInfo('hey');
            echo json_encode(array('id' => null));
        }
    }
);

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

        $logger->addInfo($query);

        $stmt = $db->query($query);

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $metaArray = json_decode($res[0]['data'], true);

        $currentSequence = $metaArray['sequence'];

        $currentSequence[] = $userId;

        $metaArray['sequence'] = $currentSequence;

        $newData = json_encode($metaArray);

        $update = "UPDATE schedule_day_meta set data = '$newData' where id = {$res[0]['id']}";

        if ($db->exec($update)) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }

        //TODO: Error checking

        // $currentMeta = json_decode($res[0], true);

        // $logger->addInfo($currentMeta);
    }
);


$app->put(
    '/inOutResize/:inOutId/:delta', 
    function($inOutId, $delta) use ($app, $db, $logger)
    {
        $logger->addInfo('hey', array('asdf' => 'foo'));
        $query = "
            UPDATE scheduled_inout
            SET
                date_out = DATE_ADD(date_out, INTERVAL $delta MINUTE)
            WHERE
                id = $inOutId
        ";

        if ($db->exec($query)) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }

    }
);

$app->put(
    '/inOutMove/:userId/:inOutId/:delta', 
    function($userId, $inOutId, $delta) use ($logger, $app, $db)
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
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
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
            // $app->response->setStatus(204);
        } else {
            // $app->response->setStatus(409);
        }
    }
);

$app->delete(
    '/inOut/:inOutId',
    function($inOutId) use($logger, $app, $db){

        $query = "
            DELETE FROM
                scheduled_inout
            WHERE
                id = $inOutId
        ";

        if ($db->exec($query)) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }
);


/*
$app->get('/associateScheduleDay/:associateId', function($associateId){
    echo "Get all the pairs for this associate $associateId";
});
*/

$app->run();
