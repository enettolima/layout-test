<title>EBT Passport</title>

<!--meta name="viewport" content="width=device-width, initial-scale=1.0"-->

<!-- Bootstrap core CSS -->

<link rel="stylesheet" href="/css/bootstrap.css">

<link rel="stylesheet" href="/css/local.css">

<link rel="stylesheet" href="/css/ui-lightness/jquery-ui-1.10.3.custom.css" />

<script src="/js/jquery.js"></script>
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
