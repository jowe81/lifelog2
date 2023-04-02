<?php
/*
	Delete pop3 mailbox
*/
date_default_timezone_set("America/Vancouver");

function lg($s){
	echo date("Y/m/d H:i:s",time()).": ".$s."\n";
}

//Require $s and terminate in case of failure
function req($s){
	if (require_once($s)){
		lg("$s");
	} else {
		lg("Failed to access '$s'. Terminating.");
		die;
	}
}

lg("Starting up...");
req("./../../lib/class_jowe_pop_client.php");
req("./../../lib/date_tools.php");


if ($pop=new jowe_pop_client())
{
	lg("Created jowe_pop_client object");
} else {
	lg("Could not create jowe_pop_client object, terminating");
	die;
}
//Attempt to connect
$c=$pop->connect();
if ($c){
	//On success, keep going
	lg("Connected ($c)");
	//Attempt to login
	if ($pop->login()){
		lg("Logged in successfully.");
		//Retrieve the number of messages
		$n=$pop->get_number_of_messages();
		lg("There are $n messages in the mailbox.");
		if ($pop->delete_all_messages()){
			lg("Mailbox cleared.");
		} else {
			lg("Error while deleting the messages.");
		}
		if ($pop->logout()){
			lg("Logged out.");		
		} else {
			lg("Error logging out.");		
		}
	} else {
		lg("Login failed. Terminating.");	
	}
}
?>