<?php 

$pin=$_GET["pin"];
$msg=$_SERVER["REMOTE_ADDR"];
if ($pin){
	$msg.=" / PIN present";
}
$msg.="<div style='float:right'>Real-time automation services brought to you by <a style='color:yellow' href='http://www.jowe.ca'>jowe.ca</a> and LifeLog</div>";

//Security: only allow intranet and pin protected external requests
if ((substr($_SERVER["REMOTE_ADDR"],0,7)!="192.168") && ($pin!="1969")){
	echo "<html><head><title>Not allowed</title></head><body><h1>This interface is accessible in the local network only.</h1></body></html>";
	die;
}

//Default Timezone GMT minus:
DEFINE("TIMEZONE","-8");

//Where are the library scripts?
DEFINE("PATH_TO_LIBRARY","../../lib/");
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
require_once "../ll2_basicfns.php"; //This is in the same dir as index.php

//Create the website object $s
$s=new jowe_site();
//Create the auth object $auth
$auth=new jowe_auth();
//Create the lifelog database interaction object $ll
$ll=new jowe_lifelog();


//Get timezone from settings/params table
date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));

//skip all overhead for tablet control in intranet
if (true){

	$pct_btns="";
	$top_btns="";
	
	$actions=array("pct_recall_preset","toggle_status","pct_toggle_channel","get_channel_status","get_sunsettime","get_tempdata","set_timer","set_alarm_preset","nudge_alarm","unflag_email","answer_door");

	if (!in_array($_GET["processform"],$actions)){
		
		$top_btns.="<div style=\"display:none\" class=\"button fullsize_button off\" id=\"nightmodeoff\"onclick=\"toggle_status('sleep_wake')\">Nachtmodus ausschalten</div>";
		$top_btns.="<div style=\"display:none\" class=\"button fullsize_button off\" id=\"awaymodeoff\"onclick=\"toggle_status('out_in')\">Awaymodus ausschalten</div>";
		$top_btns.="<div style=\"display:none\" class=\"button fullsize_button off\" id=\"busymodeoff\"onclick=\"toggle_status('busy_available')\">No longer busy</div>";
		$top_btns.="<div style=\"display:none\" class=\"button fullsize_button off\" id=\"heateroff\"onclick=\"pct_channel(8)\">Bad-Heizung ausschalten</div>";
		$top_btns.="<div style=\"display:none\" class=\"button fullsize_button off\" id=\"acoff\"onclick=\"pct_channel(18)\">AC ausschalten</div>";
		$top_btns.="<div style=\"display:none\" class=\"button fullsize_button off\" id=\"fanoff\"onclick=\"pct_channel(20)\">Bad-Fan ausschalten</div>";
		
		
		$pct_btns.="<div id=\"sunset_info\" class=\"bigger spacer\"></div>";
		
		$pct_btns.="<div class=\"biggerfont spacer\">Lighting Main</div>";
		
		$pct_btns.="<div id=\"ch12\" class=\"light_button button noselect\" onclick=\"pct_channel(12)\">Tisch</div>";
		$pct_btns.="<div id=\"ch13\" class=\"light_button button noselect\" onclick=\"pct_channel(13)\">Eingang</div>";
		$pct_btns.="<div id=\"ch11\" class=\"light_button button noselect\" onclick=\"pct_channel(11)\">Couchtisch</div>";
		$pct_btns.="<div id=\"ch16\" class=\"light_button button noselect\" onclick=\"pct_channel(16)\">K&uuml;che</div>";
		$pct_btns.="<div id=\"ch9\" class=\"light_button button noselect\" onclick=\"pct_channel(9)\">Schreibtisch</div>";
		$pct_btns.="<div id=\"ch6\" class=\"light_button button noselect\" onclick=\"pct_channel(6)\">Klavier</div>";
		$pct_btns.="<div class=\"biggerfont spacer\">Lighting Bedroom</div>";
		$pct_btns.="<div id=\"ch14\" class=\"light_button button noselect\" onclick=\"pct_channel(14)\">Schlafzimmer</div>";
		$pct_btns.="<div id=\"ch17\" class=\"light_button button noselect\" onclick=\"pct_channel(17)\">Nachttisch</div>";
		$pct_btns.="<div class=\"biggerfont spacer\">Lighting Bathroom</div>";
		$pct_btns.="<div id=\"ch19\" class=\"light_button button noselect\" onclick=\"pct_channel(19)\">Badezimmer</div>";
		
		$pct_btns.="<div class=\"biggerfont spacer\">Air</div>";
		
		$pct_btns.="<div id=\"ch20\" class=\"heating light_button button noselect\" onclick=\"pct_channel(20)\">Bad Fan</div>";
		$pct_btns.="<div id=\"ch5\" class=\"heating light_button button noselect\" onclick=\"pct_channel(5)\">K&uuml;che Fan</div>";
		$pct_btns.="<div id=\"ch8\" class=\"heating light_button button noselect\" onclick=\"pct_channel(8)\">Bad Heizung</div>";
		$pct_btns.="<div id=\"ch18\" class=\"heating light_button button noselect\" onclick=\"pct_channel(18)\">AC</div>";

		
		$pct_btns.="<div class=\"biggerfont spacer\">Miscellaneous</div>";
		
		$pct_btns.="<div id=\"ch4\" class=\"misc light_button button noselect\" onclick=\"pct_channel(4)\">Monitors</div>";
		$pct_btns.="<div id=\"ch3\" class=\"misc light_button button noselect\" onclick=\"pct_channel(3)\">Heizdecke</div>";
		$pct_btns.="<div id=\"ch7\" class=\"misc light_button button noselect\" onclick=\"pct_channel(7)\">Home Theater</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"doorbell\">Klingel</div>";

		$pct_btns.="<div class=\"biggerfont spacer\">Status</div>";
		$pct_btns.="<div class=\"button big_button off\" id=\"toggle_sleeping\"onclick=\"toggle_status('sleep_wake')\">Toggle Sleep</div>";
		$pct_btns.="<div class=\"button big_button off\" id=\"toggle_away\"onclick=\"toggle_status('out_in')\">Toggle Away</div>";
		$pct_btns.="<div class=\"button big_button off\" id=\"toggle_busy\"onclick=\"toggle_status('busy_available')\">Toggle Busy</div>";
		$pct_btns.="<div class=\"button big_button off\" id=\"answerdoor\"onclick=\"answerdoor()\">Answer door</div>";
		
		$pct_btns.="<div class=\"biggerfont spacer\">Alarms, timers, email notification</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"alarm_preset_2\"onclick=\"set_alarm_preset(2)\">Alarm 8:00</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"alarm_preset_3\"onclick=\"set_alarm_preset(3)\">Alarm 9:00</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"timer_3\"onclick=\"set_timer(180)\">Timer 3 min</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"timer_10\"onclick=\"set_timer(600)\">Timer 10 min</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"nudge_true\"onclick=\"nudge_alarm(false)\">Nudge up</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"nudge_false\"onclick=\"nudge_alarm(true)\">Nudge down</div>";
		$pct_btns.="<div class=\"misc light_button button noselect off\" id=\"clear_alarms\"onclick=\"set_timer(0)\">Clear all</div>";
		$pct_btns.="<div class=\"misc email_button light_button button noselect off\" id=\"unflag_emails\"onclick=\"unflag_email()\">Unflag email</div>";
		
		$pct_btns.="<div class=\"biggerfont spacer\">Presets</div>";
		for($i=1;$i<2;$i++){
			$pct_btns.="<div class=\"button normal_button noselect off\" onclick=\"pct_preset($i)\">".$ll->param_retrieve_value("PCT_PRESET".$i."_NAME","POWERCONTROL")."</div>";
		}
		for($i=4;$i<9;$i++){
			$pct_btns.="<div class=\"button normal_button noselect off\" onclick=\"pct_preset($i)\">".$ll->param_retrieve_value("PCT_PRESET".$i."_NAME","POWERCONTROL")."</div>";
		}
		
		echo "
		<!DOCTYPE html>
		<head>
		<title>Guest Home Automation Control</title>
		<link rel=\"icon\" href=\"./img/lights_icon.png\">
		<script src=\"jquery-1.7.1.min.js\"></script>
		<style>
		body{
		
		background:black;
		font-family:Arial,sans-serif;
		padding:0px;
		margin:0px;
	}

	.spacer {
		width:94%;
		height:50px;
		float:left;
		border-radius:5px;
		margin:10px;
		padding:5px;
		color:gray;
		text-align:center;
		font-size:25px;
		
	}
	
	.on {
		border-color:red;
	}
	
	.off {
		border-color:rgba(0,0,0,0);
	}

	.biggerfont {
		font-size:330%;
		border-bottom:3px 	solid #888;
		border-radius:0px;
		text-align:left;
	}
	
	.bigger {
		font-size:330%;
		color:#CCC;
		background:#444;
		padding:20px;
		margin:20px;
		margin-top:40px;
		box-sizing:border-box;
		height:230px;
		border-radius:10px;
		border:3px dashed #888;
	}
	
	.button_container{
	overflow:auto;
	color:white;
	}

	
	
	.button{
	border-style:solid;
	border-width:10px;
	border-radius:10px;
	background:gray;
	float:left;
	margin:20px;
	padding:5px;
	padding-top:30px;
	color:white;
	font-size:60px;
	font-family:arial;
	text-align:center;
	cursor:pointer;
	box-sizing:border-box;
	}

	.noselect {
	-webkit-touch-callout: none; /* iOS Safari */
	-webkit-user-select: none;   /* Chrome/Safari/Opera */
	-khtml-user-select: none;    /* Konqueror */
	-moz-user-select: none;      /* Firefox */
	-ms-user-select: none;       /* Internet Explorer/Edge */
	user-select: none;           /* Non-prefixed version, currently
	not supported by any browser */
	}


	.normal_button{
	background:#A05;
	width:45%;
	height:190px;
	padding-top:45px;
	}
	
	.light_button{
	background:#282;
	width:45%;
	height:190px;
	padding-top:45px;
	}

	.heating{
		background:#822;
	}
	
	.misc{
		background:#288;
	}
	
	.email_button {
		background:#B00;
	}
	
	.big_button{
	background:navy;
	width:45%;
	height:190px;
	padding-top:45px;
	}
	
	.fullsize_button{
	background:orange;
	width:94%;
	height:300px;
	padding-top:70px;
	border:10px solid red;
	box-sizing:border-box;
	}
	

	.button:active{
	color:red;
	background:#900;
	}
	</style>
	
		<meta http-equiv=\"refresh\"
        content=\"36000;url='http://192.168.1.200/~johannes/webservices/lifelog2/lights.php'\">
	</head>

	<body>
	<div id='top_buttons' class=\"button_container\">
	$top_btns
	</div>
	<div class=\"button_container\">
	$msg
	</div>
	<div class=\"button_container\">
	$pct_btns
	</div>

	<script>

	var pct_status='0000000000000000';
	
	function toggle_status(flagname){
	$.get(\"?page=itc&processform=toggle_status&pin=$pin&flagname=\"+flagname,function(result){
		update_status();
		//$('#nightmodeoff').hide(500);
		//alert (result);
	});
	}

	function pct_preset(preset_id){
	$.get(\"?page=itc&processform=pct_recall_preset&pin=$pin&preset=\"+preset_id,function(result){
	//alert (result);
	});
	}
	
	function pct_channel(channel_id){
		$.get(\"?page=itc&processform=pct_toggle_channel&pin=$pin&channel=\"+channel_id,function(result){
		 update_status();
		 //alert(result);
		});
	}
	
	
	function answerdoor(line1,line2){
		//$.get(\"http://192.168.1.200:8100/open\",function(result){
		//});
		$.get(\"?processform=answer_door&pin=$pin\");
	}
	
	function set_alarm_preset(presetnumber){
		$.get(\"?page=itc&processform=set_alarm_preset&pin=$pin&preset=\"+presetnumber,function(result){
		 //update_status();
		 //alert(result);
		});
	}
	
	function set_timer(s){
		$.get(\"?page=itc&processform=set_timer&pin=$pin&seconds=\"+s,function(result){
		 //update_status();
		});
	}
	
	function nudge_alarm(down){
		if (down){
			down='true';	
		} else {
			down='false';
		}
		$.get(\"?page=itc&processform=nudge_alarm&pin=$pin&down=\"+down,function(result){
		 //update_status();
		});
	}
	
	function unflag_email(){
		$.get(\"?page=itc&processform=unflag_email&pin=$pin\",function(result){
		 //update_status();
		});
	}
	
	$('#doorbell').click(function(){
		$.get(\"../doorbell.php\",function(){});
	});
	
	
	function get_sunsettime(){
		$.get(\"?page=itc&pin=$pin&processform=get_sunsettime\",function(result){
			$('#sunset_info').html(result);	
		});
	}
	
	function update_status(){
		$.get(\"?page=itc&processform=get_channel_status&pin=$pin\",function(result){
		 pct_status=result;
		 var i;
		 for (i=1;i<21;i++){
			 if ((pct_status.substr(i-1,1)=='1')){
			 	//alert('adding class');
				$('#ch'+i).addClass('on').removeClass('off');
				if (i==8){
					if (!$('#heateroff').is(':visible')){
						$('#heateroff').show(500);				
						window.scrollTo(0,0);					
					}
				}
				if (i==18){
					if (!$('#acoff').is(':visible')){
						$('#acoff').show(500);
						window.scrollTo(0,0);
					}
				}
				if (i==20){
					if (!$('#fanoff').is(':visible')){
						$('#fanoff').show(500);
						window.scrollTo(0,0);
					}
				}
			} else {
				$('#ch'+i).removeClass('on').addClass('off');
				if (i==8){
					$('#heateroff').hide(500);				
				}
				if (i==18){
					$('#acoff').hide(500);
				}
				if (i==20){
					$('#fanoff').hide(500);
				}
			 }		 
		 }
		 //sleep
		 if ((pct_status.substr(21,1)=='1')){
			if (!$('#nightmodeoff').is(':visible')){		 
				$('#toggle_sleeping').addClass('on').removeClass('off');
				window.scrollTo(0,0);
				$('#nightmodeoff').show(500);			
			}
		 } else {
			$('#toggle_sleeping').removeClass('on').addClass('off');
			$('#nightmodeoff').hide(500);
		 }
		 //Away
		 if ((pct_status.substr(22,1)=='1')){
			if (!$('#awaymodeoff').is(':visible')){		 
				$('#toggle_away').addClass('on').removeClass('off');
				window.scrollTo(0,0);
				$('#awaymodeoff').show(500);			
			}
		 } else {
			$('#toggle_away').removeClass('on').addClass('off');
			$('#awaymodeoff').hide(500);
		 }
		 //Busy
		 if ((pct_status.substr(23,1)=='1')){
			if (!$('#busymodeoff').is(':visible')){		 
				$('#toggle_busy').addClass('on').removeClass('off');
				window.scrollTo(0,0);
				$('#busymodeoff').show(500);			
			}
		 } else {
			$('#toggle_busy').removeClass('on').addClass('off');
			$('#busymodeoff').hide(500);
		 }
		 //Nightview
		 /*
		 if ((pct_status.substr(24,1)=='1')){
			$('body').css('background-color','black');
		 } else {
			$('body').css('background-color','rgba(0,0,0,0)');
		 }
		 */
		});
	}
	update_status();
	get_sunsettime();
	setInterval(update_status,5000);
	setInterval(get_sunsettime,5000);
	
	</script>

	</body>
	</html>
	";
	die;
	} else {
		if ($_GET["processform"]=="get_channel_status"){
			$result="";
			for($i=1;$i<21;$i++){
				$result.=$ll->get_channel_status($i);
			}
			if ($ll->get_status("sleep_wake")){
				$tmp="1";
			} else {
				$tmp="0";
			}
			$result.="-".$tmp;
			if ($ll->get_status("out_in")){
				$tmp="1";
			} else {
				$tmp="0";
			}
			$result.=$tmp;
			if ($ll->get_status("busy_available")){
				$tmp="1";
			} else {
				$tmp="0";
			}
			$result.=$tmp;
			if ($ll->is_nightview()){
				$tmp="1";
			} else {
				$tmp="0";
			}
			$result.=$tmp;
			echo $result;
		} else	if ($_GET["processform"]=="get_sunsettime"){
			$x= $ll->get_weather_info_with_local_temp();
			//$x=$ll->get_weather_info_with_local_temp();
			echo $x["temperature"]."&deg;C &mdash; ".$ll->extract_sky_condition_from_fulldescr($x["full_description"])."<br>";
			$t=$ll->get_sunset(time())-time();
			if ($t>0){
				echo date("H:i",time())." &mdash; Sunset in ".getHumanReadableLengthOfTime($t);
			} else {
				echo date("H:i",time())." &mdash; The sun has set";
			}
			echo "<br>Bedroom: ".$x["temperature_bedroom"]."&deg;C";
		} else if ($_GET["processform"]=="get_tempdata"){
			//$j=json_decode($ll->get_temp_from_local_sensors(),true);
			//echo $j["Sensoren"][0]["temp"];
			//var_dump ($ll->get_weather_info_with_local_temp());
			//echo "</br>";
			//var_dump ($ll->get_weather_info(time()));
		} else if ($_GET["processform"]=="set_timer"){
			if ($_GET["seconds"]>0){
				$alarmtime=time()+$_GET["seconds"];
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
		} else if ($_GET["processform"]=="set_alarm_preset"){
			var_dump($_GET);
			if ($_GET["preset"]>0){
				$presettime=$ll->param_retrieve_value("ALARM_PRESET_".$_GET["preset"],"ALERTS");
				$h=substr($presettime,0,2); //HH:mm
				$m=substr($presettime,3,2);//hh:MM
				//Create timestamp for $h:$m today. But if that's in the past, push it to tomorrow.
				$alarmtime=mktime($h,$m,0);
				if (isPast($alarmtime)){
					$alarmtime+=DAY;
				}
				if ($ll->add_alert($alarmtime,
						$ll->param_retrieve_value("ALARM_TTL","ALERTS"),
						5,
						$ll->param_retrieve_value("ALARM_INTERVAL","ALERTS"),
						$ll->param_retrieve_value("FILE_ALARM","ALERTS"),"ALARM")
						){
							$ll->add_default_alert();
				}
			}
		} else if ($_GET["processform"]=="nudge_alarm"){
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
					if (isset($_GET["down"])){
						if ($_GET["down"]=="true"){
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
					}
				}
			}
		} else if ($_GET["processform"]=="unflag_email"){
			$ll->unflag_unread_emails();
		} else if ($_GET["processform"]=="answer_door"){
			$ll->answer_door();
		} else {
			echo"processed this";
		}
	}
}


//Evaluate $_GET parameters
$_GET=evaluate_GET($_GET);

//Add the user record to $_GET as $_GET["owner"]
$_GET["owner"]=$auth->get_session_owner($_GET["sid"]);

//Validate session (unless login page is being requested)
if (($auth->validate_session($auth->get_service_id($_GET["page"]),$_GET["sid"])) || ($_GET["page"]=="login") || ($_GET["page"]=="itc")) {
	//Okay, request is authorized
	//Process form if one was submitted
	if ($_GET["processform"]!="") {
		if (processform($_GET["processform"],$ll,$s)) {
			//Form processing successful
		}
	}
}




?>