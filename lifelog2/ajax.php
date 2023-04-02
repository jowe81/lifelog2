<?php
/*
	This file responds to dynamic requests
*/

require "../lib/class_jowe_lifelog.php";

$ll=new jowe_lifelog();
$catid=$_GET["catid"];

////////////////////////////////
//Added after migration to msqli
$mysqli = new mysqli("mariadb", "jowede", "33189", "lifelog");

/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}
////////////////////////////////


$other_criteria="";
for ($i=1;$i<$catid;$i++) {
	if (isset($_GET["x".$i])){
		$other_criteria.=" AND cat".$i."='".$mysqli->real_escape_string($_GET["x".$i])."'";
	}
}

//echo $other_criteria;

$pvs=$ll->get_popular_values("cat".$_GET["catid"],$other_criteria);


$r="";
foreach ($pvs as $key=>$value){
	$r.=$key."|";
}

echo $r;

?> 