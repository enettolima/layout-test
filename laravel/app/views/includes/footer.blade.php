<?php

// Todo: Make this respect live/dev (which will make more sense when we have a real live site
if (getenv("HTTP_HOST") != 'cdev.ebtpassport.com') {

?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-3079923-2', 'auto');
  ga('send', 'pageview');

</script>
<?php
}
