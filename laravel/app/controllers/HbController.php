<?php

class HbController extends BaseController
{
    public function getIndex()
    {
        $errors = FALSE;

        $results = array();

        $results['db'] = array();

        // TEST DB CONNECTIONS
        foreach (Config::get('database')['connections'] as $cname=>$cspec) {
            try {
                DB::connection($cname)->getDatabaseName();
                $results['db'][] = array ('string' => $cname, 'ok' => TRUE);
            } catch(PDOException $e) {
                $errors = TRUE;
                $results['db'][] = array ('string' => $cname, 'ok' => FALSE);
            }
        }

        // TEST API
        $results['api'] = array();

        try {
            $api = new EBTAPI(FALSE);
            $results['api'][] = array('string' => 'primary', 'ok' => TRUE);
        } catch(Exception $e) {
            $errors = TRUE;
            $results['api'][] = array('string' => 'primary', 'ok' => FALSE);
        }

        // TEST FOR SETTINGS THAT SHOULDN'T BE
        $results['set'] = array();

        // Mock RPA Allows users to log in with a valid RP User + any password for troubleshooting
        // purposes.
        if (array_key_exists('mock_rpro_auth', $_ENV) && $_ENV['mock_rpro_auth'] === TRUE) {
            $errors = TRUE;
            $results['set'][] = array('string' => 'mockrpa', 'ok' => FALSE);
        } else {
            $results['set'][] = array('string' => 'mockrpa', 'ok' => TRUE);
        }

        // Make sure Debug mode isn't on, which will spit out all of our settings with any error
        if (Config::get('app.debug')) {
            $errors = TRUE;
            $results['set'][] = array('string' => 'debug', 'ok' => FALSE);
        } else {
            $results['set'][] = array('string' => 'debug', 'ok' => TRUE);
        }

        if ($errors) {
            echo "<p>HBNOTOK</p>";
        } else {
            echo "<p>HBALLOK</p>";
        }

        echo "<ul>";

        foreach ($results as $category=>$categoryResults) {
            echo "<li>$category<ul>";
            foreach ($categoryResults as $test) {
                echo "<li>{$test['string']}: " . ($test['ok'] ? 'OK' : 'HB_FAIL') . "</li>";
            }
            echo "</ul></li>";
        }

        echo "</ul>";

    }
}
