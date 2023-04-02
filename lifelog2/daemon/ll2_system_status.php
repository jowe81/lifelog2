<?php
/*
	LifeLog Daemon - System status (diagnostics)
	
	
	

*/
			date_default_timezone_set("America/Vancouver");

			define ("INTERVAL",60); //The shortest interval on which the deamon can act

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

lg("Starting up LifeLog2 SYSTEM_STATUS deamon...");
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");
req("./../../lib/string_tools.php");
lg("All libraries found.");

//Create the lifelog object
if ($ll=new jowe_lifelog())
{
	lg("Created jowe_lifelog object");
} else {
	lg("Could not create jowe_lifelog object, terminating");
	die;
}

lg("Entering daemon loop...system diagnostics");
//The daemon loop
while (true){
	lg("Reloading parameters.");
	$ll->load_params();
	lg("Diagnosing...");
	$ll->diagnoseSystem();
	lg("Complete. GOING TO SLEEP.");
	sleep(INTERVAL);
}
?>