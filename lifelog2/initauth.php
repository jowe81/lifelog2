<?php
	include "../lib/class_jowe_auth.php";

	echo "INIT AUTH FOR LL2";
	if ($jowe_auth = new jowe_auth()) {
		echo "...SUCCESS";
	} else {
		echo "...ERROR";	
	}
	if ($jowe_auth->create_tables()){
		echo "CREATED TABLES";
	}
	
	if ($jowe_auth->create_user("johannes","pass","3","4","5","6","7","8","9","10","11")) {
		echo "<br>CREATED USER johannes";
	} else {
		echo "<br>ERROR CREATING USER johannes";	
	}
	if ($jowe_auth->create_user("test","pass","3","4","5","6","7","8","9","10","11")) {
		echo "<br>CREATED USER test";
	} else {
		echo "<br>ERROR CREATING USER test";	
	}
	
	echo "<br>Service ID of today: ".$jowe_auth->get_service_id("today");
	
	if ($jowe_auth->create_service("today",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE today";
	} else {
		echo "<br>ERROR CREATING SERVICE today";
	}

	if ($jowe_auth->create_service("email_compose",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE email_compose";
	} else {
		echo "<br>ERROR CREATING SERVICE email_compose";
	}
	
	if ($jowe_auth->create_service("mobile",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE mobile";
	} else {
		echo "<br>ERROR CREATING SERVICE mobile";
	}
	
	if ($jowe_auth->create_service("thisweek",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE thisweek";
	} else {
		echo "<br>ERROR CREATING SERVICE thisweek";
	}

	if ($jowe_auth->create_service("thismonth",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE thismonth";
	} else {
		echo "<br>ERROR CREATING SERVICE thismonth";
	}
	
	if ($jowe_auth->create_service("record",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE record";
	} else {
		echo "<br>ERROR CREATING SERVICE record";
	}

	if ($jowe_auth->create_service("addfilter",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE addfilter";
	} else {
		echo "<br>ERROR CREATING SERVICE addfilter";
	}

	if ($jowe_auth->create_service("managefilters",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE managefilters";
	} else {
		echo "<br>ERROR CREATING SERVICE managefilters";
	}


	if ($jowe_auth->create_service("addperson",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE addperson";
	} else {
		echo "<br>ERROR CREATING SERVICE addperson";
	}

	if ($jowe_auth->create_service("managepeople",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE managepeople";
	} else {
		echo "<br>ERROR CREATING SERVICE managepeople";
	}

	if ($jowe_auth->create_service("addtemplate",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE addtempate";
	} else {
		echo "<br>ERROR CREATING SERVICE addtemplate";
	}

	if ($jowe_auth->create_service("managetemplates",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE managetemplates";
	} else {
		echo "<br>ERROR CREATING SERVICE managetemplates";
	}


	if ($jowe_auth->create_service("logout",$jowe_auth->get_service_id("lifelog"))) {
		echo "<br>CREATED SERVICE logout";
	} else {
		echo "<br>ERROR CREATING SERVICE logout";
	}

	$jowe_auth->display_tree();
	
	echo "<br>ADDING PERMISSION for johannes TO lifelog";
	if ($jowe_auth->add_permission($jowe_auth->get_service_id("lifelog"),"johannes")) {
		echo "...OK";
	} else {
		echo "...ERROR";
	}
	echo "<br>ADDING PERMISSION for test TO today";
	if ($jowe_auth->add_permission($jowe_auth->get_service_id("today"),"test")) {
		echo "...OK";
	} else {
		echo "...ERROR";
	}
	echo "<br>ADDING PERMISSION for test TO logout";
	if ($jowe_auth->add_permission($jowe_auth->get_service_id("logout"),"test")) {
		echo "...OK";
	} else {
		echo "...ERROR";
	}
	
	echo "<br>DOES markus HAVE PERMISSION TO LifeLog?";
	if ($jowe_auth->check_user_permission($jowe_auth->get_service_id("lifelog"),"johannes")) {
		echo "...YES";
	} else {
		echo "...NO";
	}
	echo "<br>DOES johannes HAVE PERMISSION TO today?";
	if ($jowe_auth->check_user_permission($jowe_auth->get_service_id("today"),"johannes")) {
		echo "...YES";
	} else {
		echo "...NO";
	}

?>