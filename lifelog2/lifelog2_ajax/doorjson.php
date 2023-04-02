<?php 
	date_default_timezone_set("America/Vancouver");
	
	include '../../lib/class_jowe_lifelog.php';
	include '../../lib/date_tools.php';
	
	$ll=new jowe_lifelog();
		
	if (isset($_GET["a"])) { $a=$_GET["a"]; } //action
	if (isset($_GET["p"])) { $p=$_GET["p"]; } //param

	//Guaranteed zero (make sure there's a 0 returned if no value)
	function gz($p){
		if ($p==0){
			$p=0;
		}		
		return $p;
	}
	
	if ($a=="get_status"){
		$i=$ll->get_status("out_in");
		if ($i==""){ $i=0; }
		$j=$ll->get_status("sleep_wake");
		if ($j==""){ $j=0; }
		$k=$ll->get_status("busy_available");
		if ($k==""){ $k=0; }
		$data=["flag"=>["out_in"=>$i,"sleep_wake"=>$j,"busy_available"=>$k],"bathroom_heat"=>($ll->get_channel_status(8)>0)];
		
		$now=time();
		$event=$ll->get_next_hosting_event();
		if ($event){
			$timestamp = $event["timestamp"];
			if (($now>$timestamp-60*30) && ($now<$timestamp+$event["duration"])){
				if (($event["cat1"]=="social") && ($event["cat2"]=="hosting visitors")){
					$data["person"]=$ll->get_person_firstname($event["person"]);
					$a=($now-$timestamp);
					$b=getHumanReadableLengthOfTime(abs($a));
					if ($a>180){
						$b=$b." late";
					} else if (($a<=180) && ($a>=-60)){
						$b="on time:)";
					} else {
						$b=$b." early";
					}
					$data["punctuality"]=$b;
					if ($event["value3"]==1){
						$data["hasarrived"]=true;
					}
					$data["eventid"]=$event["id"];
				}
			}
		}
		
		//How many unread emails?
		$s=sizeof($ll->get_unread_emails());
		if ($s>0){
			$data["unread_emails"]=$s;
		}
		
		//Timer due in 3 minutes or less?
		$s=$ll->get_scheduled_alarms();
		if (sizeof($s)>0){
		    $data["alarm_in"]=$s[0]["timestamp"]-time();
		}
		
	} else if ($a=="flag_arrival"){
		//Flag arrival for event with ID $p by setting value3=1
		$e=$ll->retrieve($p);
		if (!$p==false){
			$e2["id"]=$e["id"];
			$e2["value3"]=1;
			$e2["notes"]=$e["notes"]." - ARRIVED ".date("H:i",time());
			//$e2["notes"]="ARRIVED";
			$e2["active"]=1;
			var_dump($ll->update_event($e2));
			echo "Flagged";
		} else {
			echo "Event $p not found";
		}
	} else if ($a=="report_deposit"){
	    if ($p==true){
	        //Got deposit
	       $ll->param_store("HAVE_DEPOSIT",1,"MAILBOX");
	       $ll->param_store("DEPOSIT_COUNTER",$ll->param_retrieve_value("DEPOSIT_COUNTER","MAILBOX",true,"Total no deposits processed")+1,"MAILBOX");
	       if ($ll->param_retrieve_value("SEND_EMAIL_ON_DEPOSIT","MAILBOX",true,"Notification on deposit?")){
	           if ($ll->param_retrieve_value("LAST_EMAIL_NOTIFICATION_SENT","MAILBOX",true) < time()-10*60){
	               //Send email only if none has been sent the last 10 minutes
	               $email_address=$ll->param_retrieve_value("RCPT_EMAIL_ADDRESS","MAILBOX",true,"Email address for notification");
	               if ($email_address!=""){
	                   mail($email_address,"You've got mail!","Hi Johannes,\nYou have a deposit in your mailbox.\nReceived at ".date("Y/m/d H:i:s",time()).".\n--\nSent from my LifeLog system\r","From: Johannes Weber <johannes@drweber.de>\r\n"."Bcc: johannes@drweber.de");
	                   $ll->param_store("LAST_EMAIL_NOTIFICATION_SENT",time(),"MAILBOX");
	               }	               
	           }
	       }
	    } else {
	        //Items retrieved
	       $ll->param_store("HAVE_DEPOSIT",0,"MAILBOX");	        
	    }
	    $ll->param_store("DEPOSIT_UPDATED",time(),"MAILBOX");
	} else if ($a=="report_lights"){
	    if ($p==true){
	        $ll->param_store("LIGHTS",1,"MAILBOX");
	    } else {
	        $ll->param_store("LIGHTS",0,"MAILBOX");
	    }
	    $ll->param_store("LIGHTS_SWITCHED",time(),"MAILBOX");
	}
	
	/*	
	echo date("H:i:s",($timestamp-60*30))." ".($timestamp-60*30)."\r\n";
	echo date("H:i:s",$timestamp)." ".$timestamp."\r\n";
	echo date("H:i:s",($timestamp+60*30))." ".($timestamp+60*30)."\r\n";
	echo $now;*/
	header('Content-Type: application/json');
	echo json_encode($data);
	
	
	
	
?>
