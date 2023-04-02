<?php
/*
	Deamon for pop3 integration
*/
date_default_timezone_set("America/Vancouver");

define ("INTERVAL",600); //POP interval in seconds

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
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");


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
						$sender_name=$ll->get_person_displayname($sender_pid);
						$y.=" (IDENTIFIED: $sender_name)";
						//That means we can insert the message into ll (if its not in there yet)
						//A message should be unique by timestamp and sender - check if in db already
						$q="SELECT id FROM ll2_emails WHERE person=$sender_pid AND email_timestamp=".$value["__timestamp"].";";
						if ($res=$ll->db($q)){
							if ($r=mysql_fetch_array($res)){
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
								
								/*$sline="\n---------------------------------------------------";
								
								//Retrieve full message now
								$full_msg=$pop->get_message($key);
								if ($value["__message_type"]=="plain"){
									//If this is plaintext, we simple put the body in the notes
									$e["notes"].="\nContent-type: text/plain$sline\n".$full_msg["body"];
								} elseif ($value["__message_type"]=="html"){
									//If this is html, we  strip the tags and put the body in the notes								
									$e["notes"].="\nContent-type: text/html$sline\n".trim(strip_tags($full_msg["body"]));
								} elseif ($value["__message_type"]=="multipart"){
									//If this is html, we  strip the tags and put the body in the notes								
									//For the ll notes we want to find text/plain or text/html parts to go in
									$e["notes"].="\nContent-type: text/multipart ".$full_msg["body"].$sline;
									foreach ($full_msg["parts"] as $key2=>$value2){
										//See for each part what kind it is, and add to $e["Notes"] if wanted
										if ($value2["headers"]["__message_type"]=="plain"){
											if ($value2["headers"]["content-transfer-encoding"]!="base64"){
												$e["notes"].="$sline\nPART $key2 content-type: text/plain ".$value2["headers"]["content-transfer-encoding"]."$sline\n".$value2["body"];
											} else {
												$e["notes"].="$sline\nPART $key2 content-type: text/plain ".$value2["headers"]["content-transfer-encoding"]." (not displayed)$sline\n";											
											}
										} elseif ($value2["headers"]["__message_type"]=="html"){
											$e["notes"].="$sline\nPART $key2 content-type: text/html, ".$value2["headers"]["content-transfer-encoding"]."$sline\n".trim(strip_tags($value2["body"]));
										} else {
											//Probably a file
											$e["notes"].="$sline\nPART $key2 content-type: ".$value2["headers"]["content-type"].$sline;
										}
									}
								}*/
								//Try to insert
								echo "MESSAGE: ".substr($e["message"],0,1000);
								if ($ll->add_email($e)){
									$y.=" INSERTED in LL";
								} else {
									$y.=" ERROR INSERTING INTO LL";
								}
							}
						} else {
							$y.=" LL DATABASE ISSUE ($q)";
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
	//Sleep until next time
	lg("Destroying objects...");
	unset($pop);
	unset($ll);
	lg("Going to sleep for the next ".INTERVAL." seconds.");
	sleep(INTERVAL);
}
?>