<?php
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('my_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/log.txt', Logger::DEBUG));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <script src="js/bonsai-0.4.1.min.js" type="text/javascript" charset="utf-8"></script>

        <script>document.write('<script src="http://192.168.1.52:35729/livereload.js?snipver=1"></' + 'script>')</script>

        <title>Scheduler - Overview</title>

        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.10.3.custom.css" />

        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="css/local.css" rel="stylesheet">
    </head>

    <body>

        <!-- Fixed navbar -->
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand passport-brand" href="#"><img alt="Passport Logo" src="logo.png"></a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="overview.php">Scheduler</a></li>
                        <li><a href="#about">Thing</a></li>
                        <li><a href="#contact">Foo</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">STORE 301 <b class="caret" /></b></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Choose store:</li>
                                <li><a href="#">301</a></li>
                                <li><a href="#">302</a></li>
                                <li><a href="#">303</a></li>
                                <li><a href="#">304</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <div role="form" class="form-horizontal">
                        <form action="" class="form-horizontal"> 
                                <select id="rangeSelector" class="form-control input">
                                    <?php

                                        if ($_GET['weekOf']) {
                                            $currentWeekOf = $_GET['weekOf'];
                                            $logger->addInfo($currentWeekOf);
                                        }
                                        $futureWeekCount = 10;
                                        // Get the Sunday for this week
                                        $lastSunday = strtotime('last sunday');
                                        $futureWeekCount = 10;
                                        $selectorDateFormat = 'D, M jS, Y';
                                        $ranges = array();

                                        // Get the Sunday for this week
                                        $lastSunday = strtotime('last sunday');

                                        for ($i=0; $i<$futureWeekCount; $i++) {
                                            $ranges[] = array('start' => $lastSunday, 'end' => $lastSunday + (86400 * 6));
                                            $lastSunday = $lastSunday + (86400 * 7);
                                        }

                                        foreach ($ranges as $range) {
                                            $weekOf = date('Y-m-d', $range['start']);
                                            $logger->addInfo("currentWeekOf:$currentWeekOf");
                                            $logger->addInfo("weekOf:$weekOf");
                                            if ($currentWeekOf == $weekOf) {
                                                $selected = 'selected';
                                            } else {
                                                $selected = '';
                                            }
                                            echo "<option $selected value=\"".date('Y-m-d', $range['start'])."\">".date($selectorDateFormat, $range['start'])." &mdash; ".date($selectorDateFormat, $range['end'])."</option>\n";
                                        }
                                    ?>
                                </select>
                        </form>
                    </div>
                </div>
                <div class="col-md-7">
                    <strong>Employees: <a href="#" class="adder" role="button">(click here to add)</a></strong>
                    <ul id="empList">
                    </ul>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-md-12">
        <!-- width = blockwidth * 11 * 7 -->
        <!-- height = blockheight * 4 * 24 -->
        <div id="schedule-graphic-scale">
            <div class="scale-time">12:00am</div>
            <div class="scale-time">12:30am</div>

            <div class="scale-time">1:00am</div>
            <div class="scale-time">1:30am</div>

            <div class="scale-time">2:00am</div>
            <div class="scale-time">2:30am</div>

            <div class="scale-time">3:00am</div>
            <div class="scale-time">3:30am</div>

            <div class="scale-time">4:00am</div>
            <div class="scale-time">4:30am</div>

            <div class="scale-time">5:00am</div>
            <div class="scale-time">5:30am</div>

            <div class="scale-time">6:00am</div>
            <div class="scale-time">6:30am</div>

            <div class="scale-time">7:00am</div>
            <div class="scale-time">7:30am</div>

            <div class="scale-time">8:00am</div>
            <div class="scale-time">8:30am</div>

            <div class="scale-time">9:00am</div>
            <div class="scale-time">9:30am</div>

            <div class="scale-time">10:00am</div>
            <div class="scale-time">10:30am</div>

            <div class="scale-time">11:00am</div>
            <div class="scale-time">11:30am</div>

            <div class="scale-time">12:00pm</div>
            <div class="scale-time">12:30pm</div>

            <div class="scale-time">1:00pm</div>
            <div class="scale-time">1:30pm</div>

            <div class="scale-time">2:00pm</div>
            <div class="scale-time">2:30pm</div>

            <div class="scale-time">3:00pm</div>
            <div class="scale-time">3:30pm</div>

            <div class="scale-time">4:00pm</div>
            <div class="scale-time">4:30pm</div>

            <div class="scale-time">5:00pm</div>
            <div class="scale-time">5:30pm</div>

            <div class="scale-time">6:00pm</div>
            <div class="scale-time">6:30pm</div>

            <div class="scale-time">7:00pm</div>
            <div class="scale-time">7:30pm</div>

            <div class="scale-time">8:00pm</div>
            <div class="scale-time">8:30pm</div>

            <div class="scale-time">9:00pm</div>
            <div class="scale-time">9:30pm</div>

            <div class="scale-time">10:00pm</div>
            <div class="scale-time">10:30pm</div>

            <div class="scale-time">11:00pm</div>
            <div class="scale-time">11:30pm</div>
        </div>

        <div id="schedule-graphic-container">
            <div id="schedule-graphic-headers">
                <button type="button" data-day-number="0" class="day-button btn btn-primary btn-sm">Sunday</button>
                <button type="button" data-day-number="1" class="day-button btn btn-primary btn-sm">Monday</button>
                <button type="button" data-day-number="2" class="day-button btn btn-primary btn-sm">Tuesday</button>
                <button type="button" data-day-number="3" class="day-button btn btn-primary btn-sm">Wednesday</button>
                <button type="button" data-day-number="4" class="day-button btn btn-primary btn-sm">Thursday</button>
                <button type="button" data-day-number="5" class="day-button btn btn-primary btn-sm">Friday</button>
                <button type="button" data-day-number="6" class="day-button btn btn-primary btn-sm">Saturday</button>
            </div>
            <div id="schedule-graphic-graph" style=""></div>
        </div>

                </div>
            </div>

        </div>
            

        </div> <!-- /container -->

        <div id="dialog" title="Select User" style="display:none;">
            <label for="users">Users:</label>
            <input id="user" />
            <select id="newUser" name="newUser"></select>
        </div>

        <script src="js/jquery-git.js"></script>
        <script src="js/jquery-ui-1.10.3.custom.js"></script>
        <script src="http://bseth99.github.io/jquery-ui-extensions/ui/jquery.ui.combobox.js"></script>
        <!-- employees needs to be first! -->
        <script src="employee-database.js" type="text/javascript" charset="utf-8"></script>
        <script src="employees.js" type="text/javascript" charset="utf-8"></script>
        <script src="overview.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/bootstrap.js"></script>
    </body>
</html>
