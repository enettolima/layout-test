@extends('layouts.default')

@section('content')

    <div id="page-cover"></div>

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

    <style type="text/css" media="all">
        .tab-content img {width:100%;}
    </style>
    
    <div class="row">
        <div class="col-xs-4" style=""><h4>Staff</h4></div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <ul role="tablist" id="staff">
                <li><a href="#overview" data-toggle="tab">Overview</a></li>
            </ul>
        </div>
        <div class="col-md-6">
            <!-- Tab panes -->
            <div class="tab-content" id="staff-inouts">
                <div class="tab-pane active" id="overview">
                    <div class="jumbotron">
                        <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
                        Click on a name to enter In/Outs.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <div id="targets">
                <h4>Day Summary</h4>
                <table class="table table-striped">
                    <tr><td>Day Target:</td><td id="day-target"></td></tr>
                    <tr><td>Store Hours from DB:</td><td id="day-hours"></td></tr>
                </table>
                <h4>Day Goals Per Employee</h4>
                <table id="emp-hours-summary" class="table table-striped">
                    <thead>
                        <th class="text-left">Emp Code</th>
                        <th class="text-left">Name</th>
                        <th class="text-right">Day Goal</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-6">
            <div id="targets">
                <h4>Schedule Coverage</h4>
                <table id="new-day-hours-detail" class="table table-striped">
                    <thead>
                        <th class="text-left">Hour</th>
                        <th class="text-right">Goal</th>
                        <th class="text-center">Associate<br />Minutes<br />Scheduled</th>
                        <th class="text-right">Associate<br/>Goal</br>Per Minute</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="inout-delete-modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Delete In/Out</h4>
                </div>
                <div id="inout-delete-modal-content" class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button id="inout-delete-modal-confirm" class="btn btn-danger" type="button" data-event-id="">Confirm Deletion</button> 
                </div>
            </div>
        </div>
    </div>


    {{-- TODO: Hack: this is a terrible way to let Javascript know what rights the user has --}}
    @if ($userCanManage)
    <script type="text/javascript" charset="utf-8">var userCanManage = true;</script>  
    @endif

    <script src="/js/jquery-git.js"></script>

    <script src="/js/jquery-ui-1.10.3.custom.js"></script>

    <script src="/js/scheduler/summary-functions.js" type="text/javascript" charset="utf-8"></script>

    <script src="/js/scheduler/employees.js" type="text/javascript" charset="utf-8"></script>

    <script src="/js/scheduler/newday.js"></script>

    <input type="hidden" name="targetDate" id="targetDate" value="<?php echo $targetDay ?>" />
    <input type="hidden" name="weekOf" id="weekOf" value="<?php echo $weekOf ?>" />
    <input type="hidden" name="dayOffset" id="dayOffset" value="<?php echo Input::get('dayOffset') ?>" />

@stop
