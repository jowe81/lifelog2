<?php
/*
	LifeLog Daemon - Image Grab
	
	
	

*/
			date_default_timezone_set("America/Vancouver");

			define ("INTERVAL",10); //The shortest interval on which the deamon can act

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


lg("Starting up LifeLog2 Image Grab deamon...");
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");
req("./../ll2_basicfns.php");
lg("All libraries found.");


lg("Entering daemon loop...");
//The daemon loop
while (true){
	//Cam
	$url = 'http://192.168.1.219:45892/snapshot.cgi?user=admin&pwd=us1800KR';
	//Dest
	$img = '/home/johannes/public_html/webservices/live/cam1.jpg';
	if (file_put_contents($img, file_get_contents($url))){
		lg("Grabbed frame successfully");
	} else {
		lg("Grabbing frame failed");
	}
	sleep(INTERVAL);
}
?>
