<?php

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('my_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/log.txt', Logger::DEBUG));
$logger->addInfo('start');

$app = new \Slim\Slim();

$app->response->headers->set('Content-Type', 'application/json');

$db = new PDO('mysql:host=localhost;dbname=ebt_dev;charset=utf8', 'root', '123');

$app->get(
    '/storeWeekSchedule/:storeNumber/:sundayDate',
    function($storeNumber, $sundayDate) use ($logger, $db)
    {
        // Normalize supplied 'sundayDate' to YYYY-MM-DD
        $sundayDate = date('Y-m-d', strtotime($sundayDate));

        $returnval = array();

        // So we get 7 total days starting from sunday...
        for ($i=0; $i <=6; $i++) {

            $onDate = date('Y-m-d', strtotime($sundayDate) + ($i * 86400));

            /*
             * Following is a copy from /storeDaySchedule below, which
             * is not the proper way to do this, but Slim's scope is messing with 
             * me. TODO: Refactor this to avoid obvious DRY breakage
             */ 

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
                    DATE(date_in) = '$onDate'
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
                    date = '$onDate'
            ";

            $stmtMeta = $db->query($queryMeta);

            $resultsMeta = $stmtMeta->fetchAll(PDO::FETCH_ASSOC);

            if (! isset($resultsMeta[0])) {
                $resultsMeta[0] = array();
                $resultsMeta[0]['data'] = null;
            }

            $returnval[$onDate] = array('meta' => json_decode($resultsMeta[0]['data']), 'schedule' => $resultsSchedule);
        }

        $nr = array();

        // Here I need to reconstruct the data in a way that makes sense
        foreach ($returnval as $day => $val) {
            $dayArray = array();
            $empArray = array();
            $logger->addInfo($day);
            foreach ($val['schedule'] as $inoutKey => $inoutVal) {
                $empArray[$inoutVal['associate_id']][] = array (
                    'in' => date("H:i", strtotime($inoutVal['date_in'])), 
                    'out' =>date("H:i", strtotime($inoutVal['date_out']))
                );
            }
            foreach ($empArray as $empKey=>$empVal) {
                $dayArray[] = array('eid' => $empKey, 'inouts' => $empVal);
            }

            $nr[] = $dayArray;
        }

        $logger->addInfo('', $nr);

        echo json_encode($nr);
    }
);


// This isn't being used; created as part of sketching out of range
// but decided to just get -week-
$app->get(
    '/storeRangeSchedule/:storeNumber/:dateFrom/:dateTo',
    function($storeNumber, $dateFrom, $dateTo) use ($logger, $db)
    {
        // Normalize supplied 'dateFrom' to YYYY-MM-DD
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        // Normalize supplied 'dateTo' to YYYY-MM-DD
        $dateTo = date('Y-m-d', strtotime($dateTo));

        // Calculate number of days different between these two
        $daysDiff = (strtotime($dateTo) - strtotime($dateFrom)) / 86400;

        $returnval = array();

        for ($i=0; $i <=$daysDiff; $i++) {

            $onDate = date('Y-m-d', strtotime($dateFrom) + ($i * 86400));

            /*
             * Following is a copy from /storeDaySchedule below, which
             * is not the proper way to do this, but Slim's scope is messing with 
             * me. TODO: Refactor this to avoid obvious DRY breakage
             */ 

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
                    DATE(date_in) = '$onDate'
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
                    date = '$onDate'
            ";

            $stmtMeta = $db->query($queryMeta);

            $resultsMeta = $stmtMeta->fetchAll(PDO::FETCH_ASSOC);

            if (! isset($resultsMeta[0])) {
                $resultsMeta[0] = array();
                $resultsMeta[0]['data'] = null;
            }

            $returnval[$onDate] = array('meta' => json_decode($resultsMeta[0]['data']), 'schedule' => $resultsSchedule);
        }

        echo json_encode($returnval);
        
    }
);


// Get store schedule for a day
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
            $resultsMeta[0]['data'] = null;
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
            print_r ($currentSequence);
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
