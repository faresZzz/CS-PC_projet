<?php
function errorMsg($code,$status,$text,$trace) {
    global $f3;
?>
  <html>
  <head>
    <meta charset="UTF-8">
    <title>Error</title>
  </head>
  <body>
<?php
  print '<h1>Error '.$code.'</h1>';
  print '<h3>'.$status.'</h3>';
  print '<p>'.$text.'</p>';
  print '<div></i>Trace</div><div><pre>'.$trace.'</pre></div></div>';
}
?>
</body>
</html>
