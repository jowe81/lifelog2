<?php 
//Default Timezone GMT minus:
DEFINE("TIMEZONE","-8");

//Where are the library scripts?
DEFINE("PATH_TO_LIBRARY","../lib/");
//Where are the page content scripts?
DEFINE("PATH_TO_PAGES","pages/");
//Generic title
DEFINE("PAGE_TITLE","jowe.de - LifeLog");
//Stylesheet
DEFINE("DEFAULT_CSS","styles.css");

//==================CLASSES======================
//The auth class
require_once PATH_TO_LIBRARY."class_jowe_auth.php";
//The website class
require_once PATH_TO_LIBRARY."class_jowe_site.php";
//The lifelog class
require_once PATH_TO_LIBRARY."class_jowe_lifelog.php";
//The day view class
require_once PATH_TO_LIBRARY."class_jowe_lifelog_calendar_views.php";
//=================FUNCTIONS===================
//Functions around calculating dates and times
require_once PATH_TO_LIBRARY."date_tools.php";
//Functions for email parsing etc.
require_once PATH_TO_LIBRARY."email_tools.php";
//Functions for strings
//require_once PATH_TO_LIBRARY."string_tools.php";
//Basic processing for lifelog
require_once "ll2_basicfns.php"; //This is in the same dir as index.php

//Create the website object $s
$s=new jowe_site();
//Create the auth object $auth
$auth=new jowe_auth();
//Create the lifelog database interaction object $ll
$ll=new jowe_lifelog();


$a=$_GET["a"]; //action
$p=$_GET["p"]; //parameter

$result="{}";

function isIntranetRequest(){
	return (substr($_SERVER["REMOTE_ADDR"],0,8)=="192.168.");
}

//Guaranteed zero (make sure there's a 0 returned if no value)
function gz($p){
	if ($p==0){
		$p=0;
	}
	return $p;
}

function getOkay(){
	return array('status'=>200);
}

function getFailed($msg){
	return array('status'=>400,'message'=>$msg);
}


if ($a=="playaudio"){
	if ($_GET["delay"]==0){
		if (!($ll->get_status("sleep_wake") || $ll->get_status("busy_available"))){
			$ll->add_default_alert($_GET["file"]);
			$result=getOkay();
		} else {
			$result=getFailed("Johannes doesn't want to be disturbed right now");
		}
	}
} elseif ($a=="getstatus"){
	$s=gz($ll->get_status($p));
	$t=gz($ll->get_latest_status_change($p));
	$result=array('flag'=>$p,'status'=>$s,'changed'=>$t,'elapsed'=>time()-$t,'elapsed_h'=>getHumanReadableLengthOfTime(time()-$t));
} elseif ($a=="getwebstatus"){
	if((gz($ll->get_status("sleep_wake"))==0) && (gz($ll->get_status("busy_available"))==0)){
		$s=gz($ll->get_status("out_in"));
		$t=gz($ll->get_latest_status_change("out_in"));
		if ($s==0){
			$result=array('status'=>'Johannes came home '.getHumanReadableLengthOfTime(time()-$t).' ago.');				
		} else {
			$result=array('status'=>'Johannes is away.');				
		}
	} else {
		//Sleep or busy
		$result=array('status'=>'Please do not disturb.');
	}
} elseif ($a=="pct_toggle_channel"){
	if (($p>0) && ($p<17)){
		$l=$ll->param_retrieve_value("PCT_CH".$p."_NAME","POWERCONTROL");
		$old_status=$ll->get_channel_status($p);
		$ll->pct_toggle_channel($p);
		$new_status=$ll->get_channel_status($p);
		if ($new_status==0){
			$lampstatus="off";
		} else {
			$lampstatus="on";
		}
		if ($new_status!=$old_status){
			$result=array('status'=>200,'lamp'=>$l,'lampstatus'=>$lampstatus,'text'=>"You turned the $l light $lampstatus");
		} else {
			$result=array('status'=>400,'lamp'=>$l,'lampstatus'=>$lampstatus,'text'=>"Could not switch the $l light");
		}
	} else {
		$result=array('status'=>400,'text'=>"Invalid channel");
	}
} elseif ($a=="web_togglelight"){
	$p=6;
	$l="piano";
	if ($ll->get_status("sleep_wake")==0){
		$x=rand(0,2);
		switch ($x){
			case 0:
				$p=9;
				$l="desk";
				break;
			case 1:
				$p=16;
				$l="kitchen";
				break;
		}
	}
	$old_status=$ll->get_channel_status($p);
	$ll->pct_toggle_channel($p);
	$new_status=$ll->get_channel_status($p);
	if ($new_status==0){
		$lampstatus="off";
	} else {
		$lampstatus="on";
	}
	if ($new_status!=$old_status){
		$result=array('status'=>200,'lamp'=>$l,'lampstatus'=>$lampstatus,'text'=>"You turned the $l light $lampstatus");
	} else {
		$result=array('status'=>400,'lamp'=>$l,'lampstatus'=>$lampstatus,'text'=>"Could not switch the $l light");
	}
} elseif ($a=="web_postbillboardmessage"){
	if ($ll->add_bb_message_through_webinterface($_POST["message"],$_POST["clientip"],$sender=$_POST["nickname"])){
		$result=array('status'=>200,'message'=>$_POST["message"]);
	} else {
		//Spam
		$result=array('status'=>400,'text'=>'LifeLog thinks your message might be spam');		
	}	
} elseif (isIntranetRequest() && ($a=="intranet_togglelight")){
	$ll->pct_toggle_channel($p);
} elseif (isIntranetRequest() && ($a=="intranet_togglestatus")){
	$ll->toggle_statusflag($p);
} elseif (isIntranetRequest() && ($a=="intranet_recallpreset")){
	$ll->pct_recall_preset($p);
} elseif (isIntranetRequest() && ($a=="intranet_deletealarms")){
	$ll->cancel_scheduled_alarms();
	$ll->add_default_alert();
} elseif (isIntranetRequest() && ($a=="intranet_add_alert")){
    ($p==0) ? $n=$ll->param_retrieve_value("TIMER_PRESET_1","ALERTS") : $n=$p; //default to timer 1 if no $p given
	if ($ll->add_alert(time()+$n,
			$ll->param_retrieve_value("ALARM_TTL","ALERTS"),
			5,
			$ll->param_retrieve_value("ALARM_INTERVAL","ALERTS"),
			$ll->param_retrieve_value("FILE_ALARM","ALERTS"),"ALARM")
			){
				$ll->add_default_alert();
				return true;
	} else {
		return false;
	}
} elseif (isIntranetRequest() && ($a=="remoteBtn")){
    (isset($_GET["remoteID"])) ? $remoteID=$_GET["remoteID"] : $remoteID="";
    //The one behind the bed
    if ($remoteID=="bed"){
        if ($p==1){
            $ll->pct_recall_preset(1); //All Bedroom lights
        }
        if ($p==2){
            $ll->pct_recall_preset(2); //All kitchen lights
        }
        if ($p==3){
            $ll->toggle_statusflag("sleep_wake");
        }
        if ($p==4){
            $ll->pct_toggle_channel(5); //piano light
        }
        if ($p==5){
            $ll->pct_recall_preset(4); //Movie
        }        
    } else {      
        //The 9-button square one
        // if ($p==1){
        //     $ll->pct_recall_preset(6); //ALEDS
        // }
				// if ($p==2){
        //     $a=$ll->get_scheduled_alarms();
        //     //If there's alarms, clear
        //     if (is_array($a)){
        //         $ll->cancel_scheduled_alarms();
        //     } else {
        //         //Set timer 1
        //         if ($ll->add_alert(time()+$ll->param_retrieve_value("TIMER_PRESET_1","ALERTS"),
        //             $ll->param_retrieve_value("ALARM_TTL","ALERTS"),
        //             5,
        //             $ll->param_retrieve_value("ALARM_INTERVAL","ALERTS"),
        //             $ll->param_retrieve_value("FILE_ALARM","ALERTS"),"ALARM")
        //             ){
        //                 $ll->add_default_alert();
        //         }
        //     }
        // }
        // if ($p==3){
        //     $ll->toggle_statusflag("out_in");
        // }
        // if ($p==4){
        //     $ll->pct_recall_preset(1); //bedrm
        // }
        // if ($p==5){
        //     $ll->pct_recall_preset(2); //ktchn
        // }
        // if ($p==6){
        //     $ll->pct_recall_preset(3); //living rm
        // }
        // if ($p==7){
        //     $ll->pct_recall_preset(5); //desk work
        // }
        // if ($p==8){
        //     $ll->pct_recall_preset(7); //all off
        // }
        // if ($p==9){
        //     $ll->pct_recall_preset(8); //all on
        // }
        
			//New mapping March 2023
			if ($p>=1 && $p<=3){
				$a=$ll->get_scheduled_alarms();
				//If there's alarms, clear
				if (is_array($a)){
						$ll->cancel_scheduled_alarms();
				} else {
					//Set timer 1, 2, 3
					if ($ll->add_alert(time()+$ll->param_retrieve_value("TIMER_PRESET_$p","ALERTS"),
						$ll->param_retrieve_value("ALARM_TTL","ALERTS"),
						5,
						$ll->param_retrieve_value("ALARM_INTERVAL","ALERTS"),
						$ll->param_retrieve_value("FILE_ALARM","ALERTS"),"ALARM")
						){
							$ll->add_alert(time(), 0, 5, 0, $ll->param_retrieve_value("TIMER_FILE_$p","ALERTS"));
							$ll->add_default_alert();
					}
				}
			}
			if ($p==4){	
				$ll->pct_recall_preset(2); //ktchn
			}
			if ($p==5){
				$ll->pct_recall_preset(3); //living rm
			}
			if ($p==6){
				$ll->pct_recall_preset(8); //all on
			}
			if ($p==7){
				$ll->toggle_statusflag("out_in"); //away
			}
			if ($p==8){
				$ll->pct_recall_preset(6); //ALEDS
			}
			if ($p==9){
				$ll->pct_recall_preset(7); //all off
			}

			if ($ll->get_status("out_in")) {
				//If 'away', set back to home on any button except 7 (or else we'd toggle twice)
				if ($p!=7) {
					$ll->toggle_statusflag("out_in");
				}				
			}
			


    }
} elseif (isIntranetRequest() && ($a=="reportRemoteVoltage")){
    $last_voltage=$ll->param_retrieve("REMOTE_VOLTAGE","REMOTE");
    $highest_voltage=$ll->param_retrieve("REMOTE_VOLTAGE_HIGHEST","REMOTE");
    //Store highest and lowest; clear both markers if no voltage has been reported for more than a few minutes 
    if (($highest_voltage["pvalue"]<$p) || ($highest_voltage["pvalue"]==0) || (time()-$last_voltage["updated"]>130) ){
        $ll->param_store("REMOTE_VOLTAGE_HIGHEST",$p,"REMOTE","Highest reported voltage");
    }
    $lowest_voltage=$ll->param_retrieve("REMOTE_VOLTAGE_LOWEST","REMOTE");
    if (($lowest_voltage["pvalue"]>$p) || ($lowest_voltage["pvalue"]==0) || (time()-$last_voltage["updated"]>130) ){
        $ll->param_store("REMOTE_VOLTAGE_LOWEST",$p,"REMOTE","Lowest reported voltage");        
    }
    $ll->param_store("REMOTE_VOLTAGE",$p,"REMOTE","Last: ".$last_voltage["pvalue"]);
} elseif (isIntranetRequest() && ($a=="setBathroomLED")){
	$ll->setBathroomLED($p); //Test only; not being used
}


header('Content-Type: application/json');
echo json_encode($result);


?>