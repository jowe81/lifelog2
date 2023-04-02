<?php
	include "lib/class_jowe_auth.php";
	echo "Creating AUTH for LifeLog...object creation";
	if ($jowe_auth = new jowe_auth()) {
		echo "...SUCCESS";
	} else {
		echo "...ERROR";	
	}
	
	if ($jowe_auth->create_service("edt_sightread",12)) {
		echo "<br>CREATED SERVICE edutainment";
	} else {
		echo "<br>ERROR CREATING SERVICE edutainment";
	}
	
	
	$jowe_auth->display_tree();
	
?>