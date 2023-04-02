<?php
	include "lib/class_jowe_auth.php";
	echo "Creating AUTH for LifeLog...object creation";
	if ($jowe_auth = new jowe_auth()) {
		echo "...SUCCESS";
	} else {
		echo "...ERROR";	
	}
	if ($jowe_auth->drop_tables()) {
	} else {	
	}
	if ($jowe_auth->create_tables()) {
	} else {	
	}
	
	
	if ($jowe_auth->create_user("johannes","cs81","3","4","5","6","7","8","9","10","11")) {
		echo "<br>CREATED USER johannes";
	} else {
		echo "<br>ERROR CREATING USER johannes";	
	}
	
	if ($jowe_auth->create_service("jowe.de",0)) {
		echo "<br>CREATED SERVICE jowe.de";
	} else {
		echo "<br>ERROR CREATING SERVICE jowe.de";
	}
	if ($jowe_auth->create_service("admin",1)) {
		echo "<br>CREATED SERVICE admin";
	} else {
		echo "<br>ERROR CREATING SERVICE admin";
	}
	if ($jowe_auth->create_service("lifelog",1)) {
		echo "<br>CREATED SERVICE lifelog";
	} else {
		echo "<br>ERROR CREATING SERVICE lifelog";
	}
	if ($jowe_auth->create_service("billboard",3)) {
		echo "<br>CREATED SERVICE billboard";
	} else {
		echo "<br>ERROR CREATING SERVICE billboard";
	}
	if ($jowe_auth->create_service("record",3)) {
		echo "<br>CREATED SERVICE record";
	} else {
		echo "<br>ERROR CREATING SERVICE record";
	}
	if ($jowe_auth->create_service("today",3)) {
		echo "<br>CREATED SERVICE today";
	} else {
		echo "<br>ERROR CREATING SERVICE today";
	}
	if ($jowe_auth->create_service("addperson",3)) {
		echo "<br>CREATED SERVICE addperson";
	} else {
		echo "<br>ERROR CREATING SERVICE addperson";
	}
	if ($jowe_auth->create_service("managepeople",3)) {
		echo "<br>CREATED SERVICE managepeople";
	} else {
		echo "<br>ERROR CREATING SERVICE managepeople";
	}
	if ($jowe_auth->create_service("email",3)) {
		echo "<br>CREATED SERVICE email";
	} else {
		echo "<br>ERROR CREATING SERVICE email";
	}
	if ($jowe_auth->create_service("logout",3)) {
		echo "<br>CREATED SERVICE logout";
	} else {
		echo "<br>ERROR CREATING SERVICE logout";
	}
	if ($jowe_auth->create_service("settings",3)) {
		echo "<br>CREATED SERVICE settings";
	} else {
		echo "<br>ERROR CREATING SERVICE settings";
	}

	
	$jowe_auth->display_tree();
	
	echo "<br>ADDING PERMISSION for johannes TO everything";
	if ($jowe_auth->add_permission(1,"johannes")) {
		echo "...OK";
	} else {
		echo "...ERROR";
	}
	
	echo "<br>DOES johannes HAVE PERMISSION TO LifeLog?";
	if ($jowe_auth->check_user_permission(3,"johannes")) {
		echo "...YES";
	} else {
		echo "...NO";
	}
?>