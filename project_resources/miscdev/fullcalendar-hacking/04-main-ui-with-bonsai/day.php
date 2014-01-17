<?php
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
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">STORE 301 <b class="caret"></b></a>
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

            <h3>Modifying Schedule for <?php echo date($selectorDateFormat, strtotime($targetDay)); ?></h3>

            <a class="btn btn-primary btn-sm" href="overview.php?weekOf=<?php echo $_GET['weekOf'] ?>">Back to Overview</a>

            <div id='calendar'></div>

            <div id="dialog" title="Select User" style="display:none;">
                <label for="users">Users:</label>
                <input id="user" />
                <select id="newUser" name="newUser"></select>
            </div>
        </div>


        <script src="js/jquery-git.js"></script>

        <script src="js/jquery-ui-1.10.3.custom.js"></script>

        <script src="fullcalendar/fullcalendar.js"></script>

        <script src="http://bseth99.github.io/jquery-ui-extensions/ui/jquery.ui.combobox.js"></script>

        <script src="day.js"></script>

        <input type="hidden" name="targetDate" id="targetDate" value="<?php echo $targetDay ?>" />
    </body>
</html>
