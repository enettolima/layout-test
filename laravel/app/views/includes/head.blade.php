<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EBT Passport</title>

<!--meta name="viewport" content="width=device-width, initial-scale=1.0"-->

<!-- Bootstrap core CSS -->

<!--<link rel="stylesheet" href="/css/bootstrap.css">-->

<!-- CSS -->
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/css/local.css">

<link rel="stylesheet" href="/css/ui-lightness/jquery-ui-1.10.3.custom.css" />

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

<!-- jQuery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
<script src="/js/jquery-git.js"></script>
<script src="/js/jquery.cookie.js"></script>
<script src="/js/main.js" type="text/javascript" charset="utf-8"></script>

<?php 
    if (isset($extraHead)) {
        echo $extraHead;
    }

    if (isset($extraCSS) && count($extraCSS) > 0) {
        foreach ($extraCSS as $stylesheet) {
            echo "<link rel=\"stylesheet\" href=\"$stylesheet\">\n";
        }
    }
?>
