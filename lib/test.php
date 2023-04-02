<!DOCTYPE html>
<html>
<head></head>
<body>
pre php<br>
<?php 
$s=$_GET["s"];
$s="1";
shell_exec("curl 192.168.1.107/switchled.php?s=$s");
echo "executed!<br>";
?>
post php
</body>
</html>