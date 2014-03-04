<?php
    $weekOf = $_GET['weekOf'];
    $targetDay = date('Y-m-d', strtotime($_GET['weekOf']) + ($_GET['dayOffset'] * 86400));
    $selectorDateFormat = 'D, M jS, Y';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Scheduler - Day Detail</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="css/local.css" rel="stylesheet">

        <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.10.3.custom.css" />

        <link rel="stylesheet" href='fullcalendar/fullcalendar.css' />

        <link rel="stylesheet" href='fullcalendar/fullcalendar.print.css' media='print' />

        <!-- <script>document.write('<script src="http://192.168.1.52:35729/livereload.js?snipver=1"></' + 'script>')</script> -->

    </head>

    <body>

        <?php include('inc-header.php'); ?>

        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <div role="form" class="form-horizontal">
                        <form action="" class="form-horizontal"> 
                                <select disabled id="rangeSelector" class="form-control input">
                                    <?php
                                        $range['start'] = strtotime($_GET['weekOf']);
                                        $range['end'] = strtotime($_GET['weekOf']) + (86400 * 6) ;
                                        echo "<option $selected value=\"".date('Y-m-d', $range['start'])."\">".date($selectorDateFormat, $range['start'])." &mdash; ".date($selectorDateFormat, $range['end'])."</option>\n";
                                    ?>
                                </select>
                        </form>
                    </div>
                </div>

            </div>

                <h4>Modifying Schedule for <?php echo date($selectorDateFormat, strtotime($targetDay)); ?> [<a class="" href="overview.php?weekOf=<?php echo $_GET['weekOf'] ?>">Back to Overview</a>] </h4>


            <div class="row" style="padding-top:10px;">
                <div id='calendar'></div>
            </div>

        </div>


        <script src="js/jquery-git.js"></script>

        <script src="js/jquery-ui-1.10.3.custom.js"></script>

        <script src="fullcalendar/fullcalendar.js"></script>

        <script src="employee-database.js" type="text/javascript" charset="utf-8"></script>
        <script src="day.js"></script>

        <input type="hidden" name="targetDate" id="targetDate" value="<?php echo $targetDay ?>" />
        <input type="hidden" name="weekOf" id="weekOf" value="<?php echo $weekOf ?>" />
    </body>
</html>
