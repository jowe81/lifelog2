<?php
/*
	LifeLog Daemon - PowerControl
	
	
	

*/
			date_default_timezone_set("America/Vancouver");

			define ("INTERVAL", 30); //The shortest interval on which the deamon can act (seconds)

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


lg("Starting up LifeLog2 POWERCONTROL deamon...");
req("./../../lib/class_jowe_lifelog.php");
req("./../../lib/date_tools.php");
req("./../ll2_basicfns.php");
lg("All libraries found.");


lg("Entering daemon loop...waiting for switch operations.");


function getMinute() {
	return round(time() / 60);
}

function updateClockBrightness($ll, $previousBrightness, $previousMinute) {
	//------------ Gradually transition the clock brightness ----------------------
	if ($previousMinute != getMinute()) {

		$nightVal = $ll->param_retrieve_value("7SGC_BRIGHTNESS_SLEEP","7SEGCLOCK",true);
		$dayVal = $ll->param_retrieve_value("7SGC_BRIGHTNESS_WAKE","7SEGCLOCK",true);
	
		$difference = abs($dayVal - $nightVal);
		
		$nightviewPercent = $ll->nightview_percent();
		$nightviewPercent = 1;
		$clockBrightness = round($nightVal + (1 - $nightviewPercent) * $difference);

		if ($previousBrightness != $clockBrightness) {
			$ll->set7SegClockBrightness($clockBrightness);
		}
	}	
}


//The daemon loop
ob_implicit_flush();
$previousMinute = 0;
$previousBrightness = 0;

while (true){	
	//Create the lifelog object
	if ($ll=new jowe_lifelog())
	{
		//lg("Created jowe_lifelog object");
	} else {
		lg("Could not create jowe_lifelog object, terminating");
		die;
	}
	$pct_server="192.168.1.222";
	$pct_server_port="3280";
	$pct_server_password="PCT2010";
	
	//------------------------------ LOOK FOR TIMED SWITCH OPERATIONS
	//Sunset-sunrise
	$now=$ll->is_nighttime();
	$earlier=$ll->is_nighttime(time()-30);
	if ($now!=$earlier){
		//A switch has occurred within the last while
		if ($now){
			//The sun just set
			$ll->pct_recall_preset("_NIGHT");
			$ll->setMailboxLights(true);
		} else {
			//The sun just rose
			$ll->pct_recall_preset("_DAY");		
			$ll->setMailboxLights(false);
		}
	}
	
	updateClockBrightness($ll, $previousBrightness, $previousMinute);

	/*
	//------------------------------ WRITING TO PORTS ------------------------------------------------
	//Get the actual physical status of the ports (the status last written)
	$port1_physical_status=$ll->param_retrieve_value("PCT_PORT1_PHYSICAL","POWERCONTROL");
	$port2_physical_status=$ll->param_retrieve_value("PCT_PORT2_PHYSICAL","POWERCONTROL");
	//Get the current/new status (the status to set)
	$port1_new_status=zerofill($ll->param_retrieve_value("PCT_PORT1","POWERCONTROL"),3);
	$port2_new_status=zerofill($ll->param_retrieve_value("PCT_PORT2","POWERCONTROL"),3);
	
	//Update Port 1 if necessary
	if ($port1_new_status!=$port1_physical_status){
		lg("Attempting to create socket...");
		$fp = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($fp === false) {
    		lg("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
		}		
		lg("Attempting to connect...");
		$result = socket_connect($fp, $pct_server, $pct_server_port);
		if ($result === false) {
			//Connection failed
			lg("Connection to PCT server failed.");
		} else {
			lg("Connected to PCT server. Reading...");
			//Connection successful
			$q=socket_read($fp, 2048, PHP_NORMAL_READ);
			$wr="$pct_server_password 1 $port1_new_status\n";
			lg("Writing command: $wr");
			socket_write($fp, $wr,strlen($wr));
			lg("Done writing. Reading response...");
			$a=socket_read($fp, 2048, PHP_NORMAL_READ);
			lg("Done reading response.");
			if (substr($a,0,3)=="+OK"){
				lg("Updated port 1 ($port1_new_status)");
			} else {
				lg("Could not update port 1");
			}
			socket_close($fp);
		}
		$ll->param_store("PCT_PORT1_PHYSICAL",$port1_new_status,"POWERCONTROL");
	}
	//Update Port 2 if necessary
	if ($port2_new_status!=$port2_physical_status){
		lg("Attempting to create socket...");
		$fp = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($fp === false) {
    		lg("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
		}		
		lg("Attempting to connect...");
		$result = socket_connect($fp, $pct_server, $pct_server_port);
		if ($result === false) {
			//Connection failed
			lg("Connection to PCT server failed.");
		} else {
			lg("Connected to PCT server. Reading...");
			//Connection successful
			$q=socket_read($fp, 2048, PHP_NORMAL_READ);
			$wr="$pct_server_password 2 $port2_new_status\n";
			lg("Writing command: $wr");
			socket_write($fp, $wr,strlen($wr));
			lg("Done writing. Reading response...");
			$a=socket_read($fp, 2048, PHP_NORMAL_READ);
			lg("Done reading response.");
			if (substr($a,0,3)=="+OK"){
				lg("Updated port 2 ($port2_new_status)");
			} else {
				lg("Could not update port 2");
			}
			socket_close($fp);
		}
		$ll->param_store("PCT_PORT2_PHYSICAL",$port2_new_status,"POWERCONTROL");
	}
	*/

	$previousMinute = getMinute();

	unset($ll);
	sleep(INTERVAL);
}
?>