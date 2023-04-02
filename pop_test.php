<?php
	echo "<br/>Attemting to require class";
	if (require_once("lib/class_jowe_pop_client.php")){
		echo "...success";
	} else {
		echo "...failed";
	}
	
	$p = new jowe_pop_client();
	
	echo $p->connect();
	if ($p->login()) {
		echo "<br>LOGGED IN";
	} else {
		echo "<br>Login failed";
	}
	

	
	echo "<br>Number of messages: ".$p->get_number_of_messages();
	
	echo "<br>READING MESSAGE 1";
	
	$msg=$p->get_message(3);
	
	$headers=nl2br($msg["headers"]);
	echo "<p style='background:black; color:yellow;'>$headers</p>";
	
	$body=nl2br($msg["body"]);
	echo "<p style='background:red; color:white;'>$body</p>";

	foreach ($msg["headers"] as $key=>$value){
		echo "<br>HD: $key ='$value'";
	}

	if ($p->logout()) {
		echo "<br>LOGGED OUT";
	} else {
		echo "<br>Logout failed";
	}

?>