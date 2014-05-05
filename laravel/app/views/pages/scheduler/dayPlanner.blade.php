@extends('layouts.default')

@section('content')

    <div class="row">
        <div class="col-xs-5">
            <div role="form" class="form-horizontal">
                <form action="" class="form-horizontal"> 
                        <select disabled id="rangeSelector" class="form-control input">
                            <?php
                                $range['start'] = strtotime($_GET['weekOf']);
                                $range['end'] = strtotime($_GET['weekOf']) + (86400 * 6) ;
                                echo "<option value=\"".date('Y-m-d', $range['start'])."\">".date($selectorDateFormat, $range['start'])." &mdash; ".date($selectorDateFormat, $range['end'])."</option>\n";
                            ?>
                        </select>
                </form>
            </div>
        </div>

    </div>

    <h4>Modifying Schedule for <?php echo date($selectorDateFormat, strtotime($targetDay)); ?> [<a class="" href="week-overview?weekOf=<?php echo $_GET['weekOf'] ?>">Back to Overview</a>] </h4>


    <div class="row" style="padding-top:10px;">
        <div id='calendar'></div>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <div id="targets">
                <h4>Day Summary</h4>
                <table class="table table-striped">
                    <tr><td>Day Target:</td><td id="day-target"></td></tr>
                    <tr><td>Store Hours from DB:</td><td id="day-hours"></td></tr>
                </table>
            </div>
        </div>
        <div class="col-xs-6">
            <div id="targets">
                <h4>Hours Status</h4>
                <table id="day-hours-detail" class="table table-striped">
                    <thead>
                        <th class="text-center">Hour</th>
                        <th class="text-right">Target</th>
                        <th class="text-center">Staff Count</th>
                        <th class="text-right">Distrib Goal</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="block-remove-modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Block Deletion</h4>
                </div>
                <div id="block-remove-modal-content" class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button id="block-remove-modal-confirm" type="button" data-event-id="" class="btn btn-danger">Confirm Deletion</button> 
                </div>
            </div>
        </div>
    </div>

	<script type="text/javascript" charset="utf-8">
		var slimServiceURL = "<?php echo $_ENV['slim_service_url']?>"
	</script>

    <script src="/js/jquery-git.js"></script>

    <script src="/js/jquery-ui-1.10.3.custom.js"></script>

    <script src="/js/scheduler/employees.js" type="text/javascript" charset="utf-8"></script>

    <script src="/js/scheduler/fullcalendar.js"></script>

    <script src="/js/scheduler/day.js"></script>

    <input type="hidden" name="targetDate" id="targetDate" value="<?php echo $targetDay ?>" />
    <input type="hidden" name="weekOf" id="weekOf" value="<?php echo $weekOf ?>" />
    <input type="hidden" name="dayOffset" id="dayOffset" value="<?php echo Input::get('dayOffset') ?>" />

@stop
