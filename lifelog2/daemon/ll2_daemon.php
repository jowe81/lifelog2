<?php
/*
	LifeLog Daemon
	v. 1.0
	
	

*/
date_default_timezone_set("America/Vancouver");

define ("INTERVAL",400); //The shortest interval on which the deamon can act

//Output a log-line
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

//Execute an alert
function executeAlert(){
		//passthru("ogg123 tada.ogg");
}

lg("Starting up...");
req("./../../lib/class_jowe_pop_client.php");
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");
lg("All libraries found.");


//The daemon loop
while (true){
	//We create the objects each time and destroy them at the end
	//Create the pop client object
	if ($pop=new jowe_pop_client())
	{
		lg("Created jowe_pop_client object");
	} else {
		lg("Could not create jowe_pop_client object, terminating");
		die;
	}
	//Create the lifelog object
	if ($ll=new jowe_lifelog())
	{
		lg("Created jowe_lifelog object");
	} else {
		lg("Could not create jowe_lifelog object, terminating");
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
			//Get all headers for all messages
			if ($all_headers=$pop->get_all_headers()){
				//Success
				$y="";
				//Now go through each message, see if the sender can be identified and if its already in LL. Insert if necessary.
				//$key is simply the POP3 message number
				foreach ($all_headers as $key=>$value){
					$y.="\nMsg $key is from: ".$value["__sender_address"]."(".$value["__message_type"].")";
					if ($sender_pid=$ll->get_personid_by_email($value["__sender_address"])){
						//LifeLog has identified the sender
						if ($sender_pid!=26){ //my own mail - probably spam
							$sender_name=$ll->get_person_displayname($sender_pid);
							$y.=" (IDENTIFIED: $sender_name)";
							//That means we can insert the message into ll (if its not in there yet)
							//A message should be unique by timestamp and sender - check if in db already
							$q="SELECT id FROM ll2_emails WHERE person=$sender_pid AND email_timestamp=".$value["__timestamp"].";";
							if ($res=$ll->db($q)){
								if ($r=mysqli_fetch_array($res)){
									//Email is in db already.
									$y.=" (already in LL)";
								} else {
									//Email is not yet in db, insert.								
									$e=array(); //New event
									$e["email_timestamp"]=$value["__timestamp"];
									$e["timestamp"]=time();
									$e["subject"]=$value["subject"];
									$e["person"]=$sender_pid;
									$e["sender_address"]=$value["__sender_address"];
									$e["message_type"]=$value["__message_type"];
									$e["message"]=$pop->get_raw_message($key); //Save the full raw message here
									
									//Try to insert
									echo "MESSAGE: ".substr($e["message"],0,1000);
									if ($ll->add_email($e)){
										$y.=" INSERTED in LL";
										//Alert?
										if (($ll->get_status("out_in")==0) && ($ll->get_status("sleep_wake")==0)){
											$ll->add_default_alert("tada.ogg");
										}
									} else {
										$y.=" ERROR INSERTING INTO LL";
									}
								}
							} else {
								$y.=" LL DATABASE ISSUE ($q)";
							}
						} else {
							$y.=" SENDER IS johannes@drweber.de - SPAM.";
						}
					}
				}
				lg("Header-info: $y");
			} else {
				lg("Headers could not be retrieved.");
			}
			//Logout/Disconnect
			if ($pop->logout()){
				lg("Disconnected from server.");
				//Log this as latest successful email poll
				$ll->param_store("LATEST_EMAIL_POLL",time(),"SYSTEM_STATUS");				
			} else {
				lg("Something went wrong at the attempt to disconnect.");
			}
		} else {
			//Could not login. Sleep until next time
			lg("Login failed.");
		}
	} else {
		//Could not connect, sleep until next time
		lg("Could not connect this time.");
	}


	//Check for alerts
	lg("Checking for alerts...");
	$alert=false;
	//Get all unread emails...
	if ($unreadmails=$ll->get_unread_emails()){
		//And check for each one whether or not it needs to be alerted now. Break on the first positive result.
		foreach ($unreadmails as $key=>$value){
			lg("Checking person #".$value["person"]."...");
			if ($ll->alertPerson($value["person"])){
				$alert=true;
				lg("...ALERT FOUND.");
				break;
			} else {
				lg("...no alert.");
			}
		}
	}
	//Screen messages?
	if (is_Array($ll->get_top_screenmessage())){
		if (($ll->get_status("out_in")==0) && ($ll->get_status("sleep_wake")==0)){
			$alert=true;
		}
	}
	if ($alert){
		lg("ALERT IS DUE\n");
		//Alert is due, so execute it
		//executeAlert();
		$ll->add_default_alert();
	} else {
		lg("No alerts are currently due.");
	}

	//Sleep until next time
	lg("Destroying objects...");
	unset($pop);
	unset($ll);
	lg("Going to sleep for the next ".INTERVAL." seconds.");
	sleep(INTERVAL);
}
?>