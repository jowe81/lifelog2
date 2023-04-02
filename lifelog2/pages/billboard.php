<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Get refresh interval in seconds
	$refresh=$ll->param_retrieve_value("REFRESH_INTERVAL","BILLBOARD");

	//Default 
	if ($_GET["bb_width"]==0) { $_GET["bb_width"]=1250; }
	if ($_GET["bb_height"]==0) { $_GET["bb_height"]=760; }
	
	//What screen?
	$view="standard";
	if (isset($_GET["view"])){
		$view=$_GET["view"];
	}
	//Produce
	$s->p($ll->produce_billboard($_GET["bb_width"],$_GET["bb_height"],time(),$view));



	//-------------------------- CREATE HIDDEN INPUT FOR CAPTURING KEYSTROKES WITH JS
	//We need to prepare an array of an array with the following elements:
	//  $k[i][keycode] and $k[i][url]
	$staticparams=me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=$view";
	
	$k=array();

	//These key associations will only work if the controls are not locked
	if (!$ll->isLocked()){
		//Toggle In-Out 
		$k[1]["keycode"]=16; //Shift
		$k[1]["url"]=$staticparams."&processform=toggle_status&flagname=out_in";
		//Flag all unread emails as read
		$k[2]["keycode"]=82; //r
		$k[2]["url"]=$staticparams."&processform=unflag_unread_emails";
		//Toggle busy/available
		$k[3]["keycode"]=17; //ctrl
		$k[3]["url"]=$staticparams."&processform=toggle_status&flagname=busy_available";
		//Toggle sleep/wake
		$k[4]["keycode"]=9; //tab
		$k[4]["url"]=$staticparams."&processform=toggle_status&flagname=sleep_wake";
		//Delete latest screen message
		$k[5]["keycode"]=83; //s
		$k[5]["url"]=$staticparams."&processform=delete_latest_screenmessage";
		//Set alarm (timer preset 1)
		$k[6]["keycode"]=97; //1 (on keypad)
		$k[6]["url"]=$staticparams."&processform=set_alarm&timer_preset_id=1";
		//Set alarm (timer preset 2)
		$k[7]["keycode"]=98; //2 (on keypad)
		$k[7]["url"]=$staticparams."&processform=set_alarm&timer_preset_id=2";
		//Set alarm (timer preset 3)
		$k[8]["keycode"]=99; //3 (on keypad)
		$k[8]["url"]=$staticparams."&processform=set_alarm&timer_preset_id=3";
		//Set alarm (alarm preset 1)
		$k[9]["keycode"]=100; //4 (on keypad)
		$k[9]["url"]=$staticparams."&processform=set_alarm&alarm_preset_id=1";
		//Set alarm (alarm preset 2)
		$k[10]["keycode"]=101; //5 (on keypad)
		$k[10]["url"]=$staticparams."&processform=set_alarm&alarm_preset_id=2";
		//Set alarm (alarm preset 3)
		$k[11]["keycode"]=102; //6 (on keypad)
		$k[11]["url"]=$staticparams."&processform=set_alarm&alarm_preset_id=3";
		//Cancel all alarms
		$k[12]["keycode"]=96; //0 (on keypad)
		$k[12]["url"]=$staticparams."&processform=cancel_alarms";
		//Add time to (top) alarm
		$k[21]["keycode"]=107; //+ (on keypad)
		$k[21]["url"]=$staticparams."&processform=alarm_add";
		//Delete time from (top) alarm
		$k[22]["keycode"]=109; //- (on keypad)
		$k[22]["url"]=$staticparams."&processform=alarm_add&mode=subtract";

		//Create new postit
		$k[13]["keycode"]=80; //p
		$k[13]["url"]=$staticparams."&page=record&form=add_postit&goto=billboard";
		//Delete top postit (the "link_id" refers to the respective clickable link in the billboard view)
		$k[14]["keycode"]=8; //backspace
		$k[14]["link_id"]="postit_1_delete";
		//Delete 2nd postit (the "link_id" refers to the respective clickable link in the billboard view)
		$k[15]["keycode"]=107; //+ (next to backspace)
		$k[15]["link_id"]="postit_2_delete";
		//Delete 3rd postit (the "link_id" refers to the respective clickable link in the billboard view)
		$k[16]["keycode"]=109; //- (next to +,backspace)
		$k[16]["link_id"]="postit_3_delete";
		//Edit top postit (the "link_id" refers to the respective clickable link in the billboard view)
		$k[17]["keycode"]=220; //the tilde, unter backspace
		$k[17]["link_id"]="postit_1_edit";
		//Edit 2nd postit (the "link_id" refers to the respective clickable link in the billboard view)
		$k[18]["keycode"]=221; //]
		$k[18]["link_id"]="postit_2_edit";
		//Edit 3rd postit (the "link_id" refers to the respective clickable link in the billboard view)
		$k[19]["keycode"]=219; //[
		$k[19]["link_id"]="postit_3_edit";


		//Toggle SHOW_LIVESTATS
		$k[20]["keycode"]=76; //l
		$k[20]["url"]=$staticparams."&processform=toggle_livestats";
		//Toggle SHOW_WEATHER_FORECAST
		$k[23]["keycode"]=87; //w
		$k[23]["url"]=$staticparams."&processform=toggle_weather_forecast";
		//Toggle SHOW_SYSTEM_STATUS
		$k[25]["keycode"]=81; //q
		$k[25]["url"]=$staticparams."&processform=toggle_system_status";
		//Toggle MOVIEMODE
		$k[26]["keycode"]=77; //m
		$k[26]["url"]=$staticparams."&processform=toggle_moviemode";
		//Toggle CURRENT_STILL_TO_BB
		$k[30]["keycode"]=90; //z
		$k[30]["url"]=$staticparams."&processform=toggle_stillimagetobb";
		//Toggle CURRENT_STILL_TO_NIBB
		$k[31]["keycode"]=88; //x
		$k[31]["url"]=$staticparams."&processform=toggle_stillimagetonibb";
		//Cycle CURRENT_STILL_CAM_NR
		$k[32]["keycode"]=67; //c
		$k[32]["url"]=$staticparams."&processform=cycle_stillimagecamnr";



		///////////////// POWERCONTROL 
		$k[33]["keycode"]=49; //1
		$k[33]["url"]=$staticparams."&processform=pct_recall_preset&preset=1";		
		$k[34]["keycode"]=50; //2
		$k[34]["url"]=$staticparams."&processform=pct_recall_preset&preset=2";		
		$k[35]["keycode"]=51; //3
		$k[35]["url"]=$staticparams."&processform=pct_recall_preset&preset=3";		
		$k[36]["keycode"]=52; //4
		$k[36]["url"]=$staticparams."&processform=pct_recall_preset&preset=4";		
		$k[37]["keycode"]=53; //5
		$k[37]["url"]=$staticparams."&processform=pct_recall_preset&preset=5";		
		$k[38]["keycode"]=54; //6
		$k[38]["url"]=$staticparams."&processform=pct_recall_preset&preset=6";		
		$k[39]["keycode"]=55; //7
		$k[39]["url"]=$staticparams."&processform=pct_recall_preset&preset=7";		
		$k[40]["keycode"]=56; //8
		$k[40]["url"]=$staticparams."&processform=pct_recall_preset&preset=8";		



		//LOCK
		$k[24]["keycode"]=192; //~
		$k[24]["url"]=$staticparams."&processform=lock_controls";
	} else {
		//When locked, we need the keypad for unlocking.
		for ($i=0;$i<10;$i++){
			$k[$i]["keycode"]=96+$i; //0-9 on keypad
			$k[$i]["url"]=$staticparams."&processform=pin_digit&digit=$i";
		}
		$k[10]["keycode"]=110; //. on keypad, to reset input
		$k[10]["url"]=$staticparams."&processform=pin_clear";
		$k[11]["keycode"]=13; //Enter on keypad, to send pin and unlock/disarm
		$k[11]["url"]=$staticparams."&processform=unlock_controls";
	}
	//--The input
	$s->p("<input id='inputfield' style='position:absolute; top:-1000px; left:-1000px;' type='text' onkeydown='return CheckKeyCode(event)'/>");
	//--The interpreting JS
	$s->p("
		<script type='text/javascript'>
		function CheckKeyCode(event)
		{");
	//Create JS code for each key association	
	foreach ($k as $key=>$value){
		if (isset($value["url"])){
			//A specific URL link has been provided, follow on keypress
			$s->p("
				 if (event.keyCode==".$value["keycode"]."){
					window.location='".$value["url"]."';
				 }
				 ");
		} elseif (isset($value["link_id"])) {
			//No specific URL has been provided here, but we use a link that exists in the billboard view
			$s->p("
				 if (event.keyCode==".$value["keycode"]."){
					window.location=document.getElementById('".$value["link_id"]."').href;
				 }
				 ");			
		}
	}
	$s->p("}
		</script>
		");


	$s->set_refresh($refresh,me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=$view"); 
	
	//Set the focus to the hidden input, for keystroke capturing
	$s->set_initial_focus('inputfield');
	
	//If nightview and or moviemode, change page background
	if (!($ll->param_retrieve_value("MOVIEMODE","BILLBOARD")==0)){
		$s->h("
				<style type='text/css'>
				body {background-image:url('');background:black;}
				</style>
			");
	} else {
		if ($ll->is_nightview()){
			$s->h("
					<style type='text/css'>
					body {background-image:url('img/gradient_nightview.jpg');}
					</style>
				");
		}
	}
	
	//Footer
	$s->p("<div style='color:gray; font-size:7pt; position:relative; float:left; width:600px;'>[<a href='".sid()."&page=today'>Exit</a>] 
			[<a href=".me()."&bb_width=1870&bb_height=1160&view=$view>1870/1160</a>
			- <a href=".me()."&bb_width=1650&bb_height=1010&view=$view>1650/1010</a>
			- <a href=".me()."&bb_width=1250&bb_height=760&view=$view>1250/760</a>
			- <a href=".me()."&bb_width=1330&bb_height=745&view=$view>1330/745</a>
			- <a href=".me()."&bb_width=974&bb_height=728&view=$view>974/728</a>]
			[Refresh: ".$refresh."s] [UTStp: ".time()."] [GTime: ".number_format($s->elapsed_time(),4)."] [%NVw: ".number_format($ll->nightview_percent()*100,2)."]
			&nbsp;
			</div>
			<div style='color:gray; font-size:7pt; position:relative; float:right; text-align:right; width:200px;'>LifeLog Billboard, (C) 2010-".date("Y")." by jowe.de</div>
			");
	
	
?>