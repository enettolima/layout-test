@extends('layouts.default')

@section('content')

    <!-- Week Selector -->
    <div class="row" style="">
        <div class="col-xs-5">
            <div role="form" class="form-horizontal">
                <form action="" class="form-horizontal"> 
                    <select id="rangeSelector" class="form-control input">
                        <?php
                            if (! Session::has('schedulerCurrentWeekOf')) {
                                $schedulerCurrentWeekOf = date('Y-m-d', strtotime('last sunday'));
                                Session::set('schedulerCurrentWeekOf', $schedulerCurrentWeekOf);
                            } else {
                                $schedulerCurrentWeekOf = Session::get('schedulerCurrentWeekOf');
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
                                if ($schedulerCurrentWeekOf == $weekOf) {
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

    <!-- Staff Row -->
    <div class="row" style="padding-top:10px; margin-left:4px;">
        <div id="emplist-container">
            <strong>Staff:</strong>
            <!-- <img id="emplist&#45;loading&#45;image" src="/images/ajax&#45;loader&#45;arrows.gif"> -->
            <ul id="empList"><li></li></ul>
            @if ($userCanManage)
                <button id="emplist-add-button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#staffPickerModal">+</button>
            @endif
        </div>
    </div>
    <!-- /Staff Row -->

    <!-- Staff Selector Modal -->
    <div class="modal " id="staffPickerModal" tabindex="-1" role="dialog" aria-labelledby="staffPickerModalLabel" aria-hidden="true">
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
    <!-- /Staff Selector Modal -->

    <div id="staff-remove-modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Confirm Staff Deletion</h4>
                </div>
                <div id="staff-remove-modal-content" class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button id="staff-remove-modal-confirm" type="button" class="btn btn-danger">Confirm Deletion</button> 
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
                <!-- <div class="scale&#45;time">12:00am</div> -->
                <!-- <div class="scale&#45;time">12:30am</div> -->
                <!--  -->
                <!-- <div class="scale&#45;time">1:00am</div> -->
                <!-- <div class="scale&#45;time">1:30am</div> -->
                <!--  -->
                <!-- <div class="scale&#45;time">2:00am</div> -->
                <!-- <div class="scale&#45;time">2:30am</div> -->
                <!--  -->
                <!-- <div class="scale&#45;time">3:00am</div> -->
                <!-- <div class="scale&#45;time">3:30am</div> -->
                <!--  -->
                <!-- <div class="scale&#45;time">4:00am</div> -->
                <!-- <div class="scale&#45;time">4:30am</div> -->
                <!--  -->
                <!-- <div class="scale&#45;time">5:00am</div> -->
                <!-- <div class="scale&#45;time">5:30am</div> -->
                <!--  -->
                <!-- <div class="scale&#45;time">6:00am</div> -->
                <!-- <div class="scale&#45;time">6:30am</div> -->

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

    <input type="hidden" id="schedulerCurrentWeekOf" name="schedulerCurrentWeekOf" value="<?php echo Session::has('schedulerCurrentWeekOf') ? Session::get('schedulerCurrentWeekOf') : ''; ?>">

    {{-- TODO: Hack: this is a terrible way to let Javascript know what rights the user has --}}
    @if ($userCanManage)
    <script type="text/javascript" charset="utf-8">var userCanManage = true;</script>  
    @endif

    <script src="/js/jquery-git.js"></script>
    <script src="/js/jquery-ui-1.10.3.custom.js"></script>
    <script src="/js/scheduler/employees.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/scheduler/overview.js" type="text/javascript" charset="utf-8"></script>

@stop
