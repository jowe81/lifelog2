<?php
include '../../lib/class_jowe_lifelog.php';
include '../../lib/date_tools.php';

$ll=new jowe_lifelog();

date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));

$a="";
$p="";
if (isset($_GET["a"])){
    $a=$_GET["a"]; //action    
}
if (isset($_GET["p"])){
    $p=$_GET["p"]; //param    
}

if ($a=="toggle_status"){
	$ll->toggle_statusflag($p);
}

if ($a=="heat_on"){
	$ll->set_channel_status(8,true);
}

if ($a=="heat_off"){
	$ll->set_channel_status(8,false);
}

if ($a=="recall_preset"){
	$ll->pct_recall_preset($p);
}

if ($a=="toggle_channel"){
	$ll->pct_toggle_channel($p);
}

if ($a=="set_timer"){
	if ($p>0){
		$alarmtime=time()+($p*60);
		if ($ll->add_alert($alarmtime,
				$ll->param_retrieve_value("ALARM_TTL","ALERTS"),
				5,
				$ll->param_retrieve_value("ALARM_INTERVAL","ALERTS"),
				$ll->param_retrieve_value("FILE_ALARM","ALERTS"),"ALARM")
				){
					$ll->add_default_alert();
		}
	} else {
		$ll->cancel_scheduled_alarms();
		$ll->add_default_alert();
	}
}

if ($a=="set_alarm"){
	if ($p>0){
		$presettime=$ll->param_retrieve_value("ALARM_PRESET_".$p,"ALERTS");
		$h=substr($presettime,0,2); //HH:mm
		$m=substr($presettime,3,2);//hh:MM
		//Create timestamp for $h:$m today. But if that's in the past, push it to tomorrow.
		$alarmtime=mktime($h,$m,0);
		if (isPast($alarmtime)){
			$alarmtime+=DAY;
		}
		echo $alarmtime;
		if ($ll->add_alert($alarmtime,
				$ll->param_retrieve_value("ALARM_TTL","ALERTS"),
				5,
				$ll->param_retrieve_value("ALARM_INTERVAL","ALERTS"),
				$ll->param_retrieve_value("FILE_ALARM","ALERTS"),"ALARM")
				){
					$ll->add_default_alert();
		}
	}
}

if ($a=="unflag_emails"){
	$ll->unflag_unread_emails();
}

if ($a=="cancel_alarms"){
	$ll->cancel_scheduled_alarms();
}

if ($a=="nudge"){
	if ($p=="down"){
		$down=true;
	} else {
		$down=false;
	}
	//To the next due alarm, add or reduce time
	if ($a=$ll->get_scheduled_alarms()){
		//Are any alarms scheduled?
		if (count($a)>0){
			//Yes (but we'll only deal with the first one even there are more)
			//Determine time to be added (subtracted): the closer the alarm, the smaller the adjustment
			if ($a[0]["timestamp"]-time()>(HOUR*2)){
				$factor=MINUTE*15;//More than 5 hours: adjust by 15 minutes
			} elseif ($a[0]["timestamp"]-time()>(MINUTE*30)){
				$factor=MINUTE*5; 	//More than half an hour: adjust by 5 minutes
			} elseif ($a[0]["timestamp"]-time()>(MINUTE*10)){
				$factor=MINUTE*2; 	//More than ten minutes: adjust by 2 minutes
			} elseif ($a[0]["timestamp"]-time()>(MINUTE*5)){
				$factor=MINUTE;		// More than five minutes: adjust by a minute
			} elseif ($a[0]["timestamp"]-time()>0){
				$factor=30;		// Less than a minute: adjust by 30 seconds
			}
			//Check if we are supposed to substract
			if (isset($down)){
				if ($down=="true"){
					//For substracting we will not perform the operation if the alarm is closer than a minute
					if ($a[0]["timestamp"]-time()<MINUTE){
						$factor=0; //Cancel operation because alarm is almost due
					} else {
						$factor=$factor*(-1);
					}
				}
			}
			if ($factor!=0){
				//A factor was correctly determined -> perform db-update
				$a[0]["timestamp"]+=$factor;
				$ll->update_record($a[0],"ll2_alerts");
				$ll->add_default_alert();
				
			}
		}
	}
	$a="";
	
}

if ($a=="answer_door"){
	$ll->answer_door();
}



//Build status object

$x=new class{};
$x->channel_meta=new class{};
$x->flags=new class{};
$x->unread_emails=new class{};
$x->scheduled_alarms=new class{};
$x->doorbell=new class{};


//Lights, Air, Other
$lights=array(6,9,11,12,13,14,15,16,17,19);
$le[6]=2.5;
$le[9]=15;
$le[11]=2.5;
$le[12]=15;
$le[13]=10;
$le[14]=10;
$le[15]=5+10;
$le[16]=20;
$le[17]=6;
$le[19]=12;

$air=[5,8,18,20];
$ae[5]=25;
$ae[8]=1500;
$ae[18]=1500;
$ae[20]=40;

$other=[3,4,7,10];
$oe[3]=40;
$oe[4]=135;
$oe[7]=60;
$oe[10]=30;

$x->channel_meta->lights_on=0;
$x->channel_meta->air_on=0;
$x->channel_meta->other_on=0;
$x->channel_meta->lights_energy=0;
$x->channel_meta->air_energy=0;
$x->channel_meta->other_energy=0;

//PCT Channels
for($i=1;$i<25;$i++){
	if ($ll->get_channel_status($i)==1){
		$x->channels[]=true;
	} else {
		$x->channels[]=false;
	}
	if ($x->channels[$i-1]==1){
		if (in_array($i,$lights)){
			$x->channel_meta->lights_on++;
			$x->channel_meta->lights_energy+=$le[$i];
		}
		if (in_array($i,$air)){
			$x->channel_meta->air_on++;
			$x->channel_meta->air_energy+=$ae[$i];
		}
		if (in_array($i,$other)){
			$x->channel_meta->other_on++;
			$x->channel_meta->other_energy+=$oe[$i];
		}
	}
}

//Statusflags
if ($ll->get_status("sleep_wake")){
	$x->flags->sleep_wake=true;
} else {
	$x->flags->sleep_wake=false;
}
if ($ll->get_status("out_in")){
	$x->flags->out_in=true;
} else {
	$x->flags->out_in=false;
}
if ($ll->get_status("busy_available")){
	$x->flags->busy_available=true;
} else {
	$x->flags->busy_available=false;
}

//Nightview
if ($ll->is_nightview()){
	$x->nightview=true;
} else {
	$x->nightview=false;
}

//Emails
$emails=$ll->get_unread_emails(10);
$x->unread_emails->count=count($emails);
foreach ($emails as $e){
	$t=new stdClass();
	$t->sender=$ll->get_person_displayname($e["person"]);
	$t->subject=$e["subject"];
	$t->timestamp=$e["timestamp"];
	$t->age=getHumanReadableLengthOfTime(time()-$e["timestamp"]);
	$x->unread_emails->headers[]=$t;
}

//Alarms
$alarms=$ll->get_scheduled_alarms();
if ($alarms){
	$x->scheduled_alarms->count=count($alarms);
	foreach($alarms as $a){
		$t=new stdClass();
		$t->timestamp=$a["timestamp"];
		$t->time=date("H:i:s",$a["timestamp"]);
		$t->remaining=($a["timestamp"]-time());
		$t->remainingH=getHumanReadableLengthOfTime(abs($a["timestamp"]-time()),"s");
		$t->ttl=($a["duration"]);
		$x->scheduled_alarms->alarms[]=$t;
	}
} else {
	$x->scheduled_alarms->count=0;
}

//Doorbell last ring
$lastring=$ll->param_retrieve_value("doorbell_last_ring","DOORBELL");
$x->doorbell->last_ring=$lastring;//["pvalue"];
$x->doorbell->since_last_ring=time()-$lastring;
$x->doorbell->since_last_ringH=getHumanReadableLengthOfTime($x->doorbell->since_last_ring,"s");
$x->doorbell->answered=$ll->param_retrieve_value("doorbell_answered","DOORBELL");


//Weather info from db
$x->weather=$ll->get_weather_info(time());



header('Content-Type: application/json');
echo json_encode($x);