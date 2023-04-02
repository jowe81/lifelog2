<?php
/*
	LifeLog Daemon - Alerts
	
	
	

*/
			date_default_timezone_set("America/Vancouver");

			define ("INTERVAL",1800); //The shortest interval on which the deamon can act

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


lg("Starting up LifeLog2 WEATHER deamon...");
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");
req("./../ll2_basicfns.php");
lg("All libraries found.");

//Create the lifelog object
if ($ll=new jowe_lifelog())
{
	lg("Created jowe_lifelog object");
} else {
	lg("Could not create jowe_lifelog object, terminating");
	die;
}

lg("Entering daemon loop...");
//The daemon loop
while (true){
	if ($ll->fetch_weather_to_db()){
		//Log this as latest successful weather poll
		$ll->param_store("LATEST_WEATHER_POLL",time(),"SYSTEM_STATUS");
		lg("rss fetch successful");
	} else {
		lg("ERROR: rss fetch failed");	
	}
	lg("Going to sleep for ".INTERVAL." seconds");
	sleep(INTERVAL);
}
?>