<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->response->headers->set('Content-Type', 'application/json');

$db = new PDO('mysql:host=localhost;dbname=ebt_dev;charset=utf8', 'root', '');

$app->get(
    '/storeDaySchedule/:storeNumber/:date', 
    function($storeNumber, $date) use($db)
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
    function($storeNumber, $userId, $inString, $outString) use ($app, $db)
    {
        $in  = date('Y-m-d H:i:s', strtotime($inString));
        $out = date('Y-m-d H:i:s', strtotime($outString));

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
        }

    }
);


$app->put(
    '/inOutResize/:inOutId/:delta', 
    function($inOutId, $delta) use ($app, $db)
    {

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
    function($userId, $inOutId, $delta) use ($app, $db)
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
    function($inOutId, $userId, $inString, $outString) use ($app, $db)
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
    function($inOutId) use($app, $db){

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
