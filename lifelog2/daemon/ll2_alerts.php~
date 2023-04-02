<?php
/*
	LifeLog Daemon - Alerts
	
	
	

*/
			date_default_timezone_set("America/Vancouver");

			define ("INTERVAL",1); //The shortest interval on which the deamon can act

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
			function executeAlert($ll,$id){
					$x=$ll->retrieve($id,"ll2_alerts");
					$ll->flag_alert_execution($id); //Log this execution
					if (file_exists($x["filename"])){
						$filename=$x["filename"];
					} else {
						$filename="alert_default.ogg";
					}
					passthru("ogg123 -d alsa ".$filename);
			}
			
			//Execute an array $a of alerts (contains just filenames)
			function executeAlerts($ll,$a){
				foreach ($a as $value){
					lg("Playing alert #$value");
					executeAlert($ll,$value);
				}
				//Clean up
				$ll->delete_expired_alerts();
			}

lg("Starting up LifeLog2 ALERT deamon...");
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");
lg("All libraries found.");

//Create the lifelog object
if ($ll=new jowe_lifelog())
{
	lg("Created jowe_lifelog object");
} else {
	lg("Could not create jowe_lifelog object, terminating");
	die;
}

lg("Entering daemon loop...waiting for alerts.");
//The daemon loop
while (true){
	//Retrieve array of current alert-ids and excute them
	executeAlerts($ll,$ll->get_current_alerts());
	sleep(INTERVAL);
}
?>