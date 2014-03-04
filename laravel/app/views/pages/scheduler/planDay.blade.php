@extends('layouts.default')

@section('content')

    <div class="row">
        <div class="col-md-5">
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

    <h4>Modifying Schedule for <?php echo date($selectorDateFormat, strtotime($targetDay)); ?> [<a class="" href="weekOverview?weekOf=<?php echo $_GET['weekOf'] ?>">Back to Overview</a>] </h4>

    <div class="row" style="padding-top:10px;">
        <div id='calendar'></div>
    </div>

    <script src="/js/jquery-git.js"></script>

    <script src="/js/jquery-ui-1.10.3.custom.js"></script>

    <script src="/js/scheduler/fullcalendar.js"></script>
    <script src="/js/scheduler/employee-database.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/scheduler/day.js"></script>

    <input type="hidden" name="targetDate" id="targetDate" value="<?php echo $targetDay ?>" />
    <input type="hidden" name="weekOf" id="weekOf" value="<?php echo $weekOf ?>" />

@stop
