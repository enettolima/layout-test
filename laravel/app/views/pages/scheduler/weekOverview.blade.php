@extends('layouts.default')

@section('content')

    <!-- Week Selector -->
    <div class="row" style="">
        <div class="col-xs-5">
            <div role="form" class="form-horizontal">
                <form action="" class="form-horizontal"> 
                    <select id="rangeSelector" class="form-control input">
                        <?php

                            /*
                            if (isset($_GET['weekOf']) ) {
                                $currentWeekOf = $_GET['weekOf'];
                                // $logger->addInfo($currentWeekOf);
                            } else {
                                $currentWeekOf = '2014-02-23';
                            }
                            */

                            if (Session::has('schedulerCurrentWeekOf')) {
                                $currentWeekOf = Session::get('schedulerCurrentWeekOf');
                            } else { 
                                $currentWeekOf = date('Y-m-d', strtotime('last sunday'));
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
                                // $logger->addInfo("currentWeekOf:$currentWeekOf");
                                // $logger->addInfo("weekOf:$weekOf");
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
    </div>
    <!-- /Week Selector -->


    <!-- Staff Selector Modal -->
    <div class="row" style="padding-top:10px; margin-left:4px;">
        <div id="emplist-container">
            <strong>Staff:</strong>
            <!-- <img id="emplist&#45;loading&#45;image" src="/images/ajax&#45;loader&#45;arrows.gif"> -->
            <ul id="empList"></ul>
            <button id="emplist-add-button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#staffPickerModal">+</button>
        </div>
    </div>
    <!-- /Staff Selector -->

    <!-- New Employee Selector -->
    <div class="modal fade" id="staffPickerModal" tabindex="-1" role="dialog" aria-labelledby="staffPickerModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="staffPickerModalLabel">Click on staff member to add to schedule</h4>
                </div>
                <div class="modal-body">

                    <div class="input-group"> <span class="input-group-addon">Filter</span>

                        <input id="filter" type="text" class="form-control" placeholder="Type here to filter staff list...">
                    </div>
                    <div style="height:300px; overflow:auto">
                        <table id="staff-picker" class="table">
                            <thead>
                                <tr><th>ID</th><th>Name</th></tr>
                            </thead>
                            <tbody class="searchable">
                            </tbody>
                        </table>
                    </div>

                    <script type="text/javascript" charset="utf-8">
                    $(document).ready(function () {
                        (function ($) {
                            $('#filter').keyup(function () {
                                var rex = new RegExp($(this).val(), 'i');
                                $('.searchable tr').hide();
                                $('.searchable tr').filter(function () {
                                    return rex.test($(this).text());
                                }).show();
                            })
                        }(jQuery));
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Scheduling Grid Section -->
    <div class="row" style="margin-top:20px;">
        <div class="col-xs-12">
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
    <!-- /Main Scheduling Grid Section -->

    <!-- Summary Section -->
    <div class="row">

        <div class="col-xs-5">
            <h4>Schedule Summary by Day</h4>
            <table id="day-summary" class="summary-table table table-striped table-condensed"></table>
        </div>

        <div class="col-xs-5">
            <h4>Week Summary by Staff</h4>
            <table id="week-summary" class="summary-table table table-striped table-condensed"></table>
        </div>

    </div>
    <!-- Summary Section -->

    <input type="hidden" id="currentWeekOf" name="currentWeekOf" value="<?php echo Session::has('schedulerCurrentWeekOf') ? Session::get('schedulerCurrentWeekOf') : ''; ?>">

    <script src="/js/jquery-git.js"></script>
    <script src="/js/jquery-ui-1.10.3.custom.js"></script>
    <!-- <script src="http://bseth99.github.io/jquery&#45;ui&#45;extensions/ui/jquery.ui.combobox.js"></script> -->
    <!-- employees needs to be first! -->
    <!-- <script src="/js/scheduler/employee&#45;database.js" type="text/javascript"></script> -->
    <script src="/js/scheduler/employees.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/scheduler/overview.js" type="text/javascript" charset="utf-8"></script>

@stop
