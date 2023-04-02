<?php
	//Fuehrende Nullen
	function zerofill($n,$length)
	{
		$p=$n;
		while (strlen($p)<$length)
		{
			$p="0".$p;
		}
		return $p;
	}

	//Multiply string $s times $n
	function strmult($s,$n){
		if ($n==0) { return ""; }
		$r=$s;
		for ($i=1;$i<$n;$i++){
			$r.=$s;
		}
		return $r;
	}

	//Verify the standard GET parameters
	function evaluate_GET($G) {
		//No page requested (not set) go to login
		if (!isset($G["page"])) {
			$G["page"]="login";
		}
		//Or: variable is set but not specified, default to 'today'
		elseif ($G["page"]=="") {
			$G["page"]="today";			
		}
		//Just touch the following parameters
		if (!isset($G["sid"]))  		{ $G["sid"]=""; }
		if (!isset($G["goto"])) 		{ $G["goto"]=""; }		
		if (!isset($G["processform"]))	{ $G["processform"]=""; }
		if (!isset($G["navto"]))		{ $G["navto"]=""; }	//For display navigation
		if (!isset($G["form"]))		{ $G["form"]=""; }	//To identify form mode for "record"
		if (!isset($G["id"]))			{ $G["id"]=""; }		//To identify event to be edited or deleted
		if (!isset($G["dbaction"]))		{ $G["dbaction"]=""; }	//Used to request a database action (usually delete)
		if (!isset($G["table"]))		{ $G["table"]=""; }	//Used for the table name for the requested database action
		if (!isset($G["bb_width"]))	{ $G["bb_width"]=""; }	//Render billboard for width
		if (!isset($G["bb_height"]))	{ $G["bb_height"]=""; }	//Render billboard for height
		if (!isset($G["action"]))		{ $G["action"]=""; }	//E.g. show email message
		
		
		return $G;
	}

	//Return URL appendix with sid only
	function sid() {
		return "?sid=".$_GET["sid"];
	}

	//Return URL appendix with sid and page/service link
	function me() {
		return sid()."&page=".$_GET["page"];
	}
	
	//Return URL appendix with all of GET, except "dbaction" and "navto"
	function refreshme() {
	
	}


	//Build updated parameter record from $_POST, and update
	function process_param_update($ll){
		$id=$_GET["id"];
		if ($e=$ll->retrieve($id,"ll2_params")){
			//Update param
			return $ll->param_store($e["pname"],$_POST["pvalue"],$e["pgroup"]);		
		} else {
			//An error occurred
			return false;
		}	
	}
	
	//Process a login attempt.
	//--$auth is the auth object.
	//--$s is the site object.
	function processlogin($auth,$s) {
		//Attempt to create a session for the user loggin in. 
		//[This does not yet mean that they get access to the requested site!]
		if ($_POST["action_after_login"]!="") {
			//The user selected a post-login action
			$_GET["page"]=$_POST["action_after_login"];
		}
		return ($_GET["sid"]=$auth->create_session($_POST["flogin"],$_POST["fpassword"]));
	}

	//This giant does all form processing for the database.
	//--$form indicates the form (from GET-processform): edit_event, ...
	//--$d is the lifelog database object
	//--$s is the site object, for messages
	function processform($form,$ll,$s) {

		//--------------------------------Build event record from form data. These pertain to all forms-----------------------
		//$e is the event-record array to be prepared
		$e=array();
		$e["created_at"]=time();
		$e["created_by"]=$_GET["owner"]["id"]; 	//This is a reference to the user table, not ll2_people!
		
		//-------------------------------The following pertains only to the event form, not the persons form------------------
		if (strpos($form,"event")) {
			//Checkbox values need to be touched if they are not set
			if (!isset($_POST["untimed"])) { $_POST["untimed"]=false; } else { $_POST["untimed"]=true;}
			if (!isset($_POST["notes_to_billboard"])) { $_POST["notes_to_billboard"]=false; } else { $_POST["notes_to_billboard"]=true; }
			if (!isset($_POST["deposit"])) { $_POST["deposit"]=false; } else { $_POST["deposit"]=true; }
			//Build timestamp
			$e["timestamp"]=mktime($_POST["timestamp_hour"],
						$_POST["timestamp_minute"],
						0,
						$_POST["timestamp_month"],
						$_POST["timestamp_day"],
						$_POST["timestamp_year"]);
			//Build timestamp for ends_xxx for temporary purposes
			$end_ts=mktime($_POST["ends_hour"],
						$_POST["ends_minute"],
						0,
						$_POST["ends_month"],
						$_POST["ends_day"],
						$_POST["ends_year"]);
			
			//Determine duration
			//If ends_xxx = begins_xxx then duration, if present, is in duration_xxx
			if ($e["timestamp"]==$end_ts) {
				//Duration is coded in duration_xxx
				$e["duration"]=($_POST["duration_days"]*DAY)+($_POST["duration_hours"]*HOUR)+($_POST["duration_minutes"]*MINUTE);
			} else {
				//Since ends_xxx and begins_xxx are different, then
				//---check if any of the two were in fact modified. 
				if (($end_ts==$_POST["orig_end"]) && ($e["timestamp"]==$_POST["orig_timestamp"])) {
					//If not: there is either no duration, or it is prexisting and coded in duration (in either case building it from $duration_xxx will be correct)
					$e["duration"]=($_POST["duration_days"]*DAY)+($_POST["duration_hours"]*HOUR)+($_POST["duration_minutes"]*MINUTE);
				} else {
					//One or both of the values were modified. If that was the timestamp, then the duration is
					if ($e["timestamp"]!=$_POST["orig_timestamp"]) {
						//---in duration_xxx if that is not 0
						$d=($_POST["duration_days"]*DAY)+($_POST["duration_hours"]*HOUR)+($_POST["duration_minutes"]*MINUTE);
						if ($d!=0){
							$e["duration"]=$d;
						} else {
						//---or between the two values, if that does not lead to a negative duration (in that case no duration)
							if ($end_ts>$e["timestamp"]){
								$e["duration"]=$end_ts-$e["timestamp"];
							} else {
								$e["duration"]=0;
							}
						}
					} else {
						//If the ends_xxx was modified then the duration is for sure between the two values
						$e["duration"]=$end_ts-$e["timestamp"];
					}
				}
			}
			//One more super smart thing:
			//---if duration is 0 at this time,
			//---but form submission was long after its generation (e.g. more than 5 min)
			//---and this is a add_event call,
			//---then take the time in between as duration!
			if (($e["duration"]==0) && ($_POST["generated"]<(time()-MINUTE*5)) && ($form=="add_event")){
				$e["duration"]=time()-$_POST["orig_timestamp"];
			}
			//Now we know for sure where the event will be saved (timestamp) and how long it will go (duration). Use timestamp to relocate navigation.
			$_GET["navto"]=getBeginningOfDay($e["timestamp"]);
			
			$e["timezone"]=TIMEZONE;
			//Person 
			$e["person"]=$ll->get_personid_by_name($_POST["person"]); //Tries to identify the person through a string like "Lastname, Firstname" 
			//Attachment
			$e["attachment"]="";

			//Expense
			$e["expense"]=(float) $_POST["expense_dollar"].".".$_POST["expense_cent"];
			if ($_POST["deposit"]) { $e["expense"]=$e["expense"]*(-1); }

			//Paramters for direct copy:
			$e["id"]=$_POST["id"]; //Will be 0 in the case of a new event
			$e["untimed"]=$_POST["untimed"];
			$e["notes"]=$_POST["notes"];
			$e["notes_to_billboard"]=$_POST["notes_to_billboard"];
			$e["billboard_text"]=$_POST["billboard_text"];
			$e["priority"]=$_POST["priority"];
			$e["cat1"]=$_POST["cat1"];
			$e["cat2"]=$_POST["cat2"];
			$e["cat3"]=$_POST["cat3"];
			$e["cat4"]=$_POST["cat4"];
			$e["cat5"]=$_POST["cat5"];
			$e["account"]=$_POST["account"];
			$e["privacy"]=$_POST["privacy"];
			$e["value1"]=$_POST["value1"];
			$e["value2"]=$_POST["value2"];
			$e["value3"]=$_POST["value3"];
			$e["active"]=true;
		}
		//-----------------------------------------The following pertains to the persons form---------------------------------------------------------
		elseif (strpos($form,"person")) {
			//Checkbox values need to be touched if they are not set
			if (!isset($_POST["year_of_birth_unknown"])) { $_POST["year_of_birth_unknown"]=false; } else { $_POST["year_of_birth_unknown"]=true;}
			if (!isset($_POST["show_birthday"])) { $_POST["show_birthday"]=false; } else { $_POST["show_birthday"]=true; }
			//Deal with empty birthday info must be touched, too
			if (($_POST["birthday_month"]=="") || ($_POST["birthday_day"]=="")) {
				$e["birthday"]=0; //Birthday not known
			} else {
				//Birth known, but perhaps not the year
				if ($_POST["birthday_year"]=="") { $_POST["year_of_birth_unknown"]=true; } //If year is not provided, then it's not known
				if ($_POST["year_of_birth_unknown"]) {
					$_POST["birthday_year"]=1970; //Doesn't really matter what year is given now.
				}
				//Build birthday
				$e["birthday"]=mktime(0,
						0,
						0,
						$_POST["birthday_month"],
						$_POST["birthday_day"],
						$_POST["birthday_year"]);
				//But for birthday we also save day,month,year seperately (for more effective recall)
				$e["birthday_day"]=$_POST["birthday_day"];
				$e["birthday_month"]=$_POST["birthday_month"];
				$e["birthday_year"]=$_POST["birthday_year"];
			}
			//Attachment
			$e["attachment"]="";
			//Paramters for direct copy:
			$e["id"]=$_POST["id"]; //Will be 0 in the case of a new person
			$e["notes"]=$_POST["notes"];
			$e["main_address_street"]=$_POST["main_address_street"];
			$e["main_address_zip"]=$_POST["main_address_zip"];
			$e["main_address_city"]=$_POST["main_address_city"];
			$e["main_address_province"]=$_POST["main_address_province"];
			$e["main_address_country"]=$_POST["main_address_country"];
			$e["main_address_homephone"]=$_POST["main_address_homephone"];
			$e["second_address_street"]=$_POST["second_address_street"];
			$e["second_address_zip"]=$_POST["second_address_zip"];
			$e["second_address_city"]=$_POST["second_address_city"];
			$e["second_address_province"]=$_POST["second_address_province"];
			$e["second_address_country"]=$_POST["second_address_country"];
			$e["second_address_homephone"]=$_POST["second_address_homephone"];
			$e["work_address_street"]=$_POST["work_address_street"];
			$e["work_address_zip"]=$_POST["work_address_zip"];
			$e["work_address_city"]=$_POST["work_address_city"];
			$e["work_address_province"]=$_POST["work_address_province"];
			$e["work_address_country"]=$_POST["work_address_country"];
			$e["work_address_homephone"]=$_POST["work_address_homephone"];
			$e["cell_personal"]=$_POST["cell_personal"];
			$e["cell_work"]=$_POST["cell_work"];
			$e["email_personal"]=$_POST["email_personal"];
			$e["email_work"]=$_POST["email_work"];
			$e["email_extra"]=$_POST["email_extra"];
			$e["skype"]=$_POST["skype"];
			$e["icq"]=$_POST["icq"];
			$e["website"]=$_POST["website"];
			$e["lastname"]=$_POST["lastname"];
			$e["firstname"]=$_POST["firstname"];
			$e["middlename"]=$_POST["middlename"];
			$e["year_of_birth_unknown"]=$_POST["year_of_birth_unknown"];
			$e["show_birthday"]=$_POST["show_birthday"];
			$e["relationship_type"]=""; //Not using this (yet)
			$e["active"]=true;

		}
		//The following pertains to the postit form
		elseif (strpos($form,"postit")) {
			//Checkbox values need to be touched if they are not set
			if (!isset($_POST["unlimited"])) { $_POST["unlimited"]=false; } else { $_POST["unlimited"]=true;}
			//Build timestamp
			$e["timestamp"]=mktime(0,0,0,$_POST["timestamp_month"],$_POST["timestamp_day"],$_POST["timestamp_year"]);
			//Duration is 0 if unlimited or DAY if unlimited is not checked [no duration stands for indefinite validity of the postit]
			if ($_POST["unlimited"]) { $e["duration"]=0; } else { $e["duration"]=DAY; }
			//Priority can just be copied through
			$e["priority"]=$_POST["priority"];
			//Categories
			$e["cat1"]="notes";
			$e["cat2"]="post-it";
			//Notes
			$e["notes"]=$_POST["notes"];
			//ID (from hidden field)
			$e["id"]=$_POST["id"];
			//Use timestamp to relocate navigation to the day where the postit was saved
			$_GET["navto"]=getBeginningOfDay($e["timestamp"]);

		}
		//Now $e contains the event. 
		switch ($form) {
			//A preexisting event is being updated
			case "edit_event":
				//The id of the event to be updated is in $e["id"] (coming through a hidden form input)
				return $ll->update_event($e);
			break;
			//Event is to be added new
			case "add_event":
				//Ensure we have the first category at the very least
				if ($e["cat1"]!="") {
					//At least cat1 is present -> process
					return $ll->add_event($e);				
				} else {
					//Don't process if not even one category is given
					return false;
				}
			break;
			case "edit_person":
				//Ensure we have a name at the very least
				if (($e["lastname"]!="") || ($e["firstname"]!="")) {
					//Either last name or first name is given -> process
					return $ll->update_person($e);
				} else {
					//Don't process if no name is given
					return false;
				}
			break;
			case "add_person":
				//Ensure we have a name at the very least
				if (($e["lastname"]!="") || ($e["firstname"]!="")) {
					//Either last name or first name is given -> process
					return $ll->add_person($e);
				} else {
					//Don't process if no name is given
					return false;
				}
			break;
			case "add_postit":
				return $ll->add_event($e);
			break;
			case "edit_postit":
				unset($e["created_at"]); //On edit, don't set created_at to time() - this is not ideal here, but a quick fix.
				return $ll->update_event($e);
			break;
			case "bb_in_out":
			//This is strictly speaking not a form, but just a link
				//Create "in" event if user is not home, "out" event if he is
				$e["timestamp"]=time();
				$e["cat1"]="notes";
				$e["cat2"]="in-out";
				$e["cat3"]=$_GET["action"];
				return $ll->add_event($e);
			break;
			//This is again not a form, but the bb shortcut to delete the unread-flag
			case "unflag_unread_emails":
				return $ll->unflag_unread_emails();
			break;
			case "toggle_status":
				return $ll->toggle_statusflag($_GET["flagname"]);
			break;
			case "delete_latest_screenmessage":
				return $ll->delete_latest_screenmessage();
			return;
			case "set_alarm":
				$alarmtime=0;
				if (isset($_GET["timestamp"])){
					//A specific timestamp was provided for the alarm
					$alarmtime=$_GET["timestamp"];
				} else {
					//No timestamp provided. Maybe a preset ID?
					if (isset($_GET["timer_preset_id"])){
						//A preset timer delay was selected (see params). Caculate actual alarm time.
						$alarmtime=(time()+$ll->param_retrieve_value("TIMER_PRESET_".$_GET["timer_preset_id"],"ALERTS"));
					} elseif (isset($_GET["alarm_preset_id"])) {
						//An alarm preset was selected (see params). This is a time in 24hr format, HH:MM. Caculate actual alarm time.
						$presettime=$ll->param_retrieve_value("ALARM_PRESET_".$_GET["alarm_preset_id"],"ALERTS");
						$h=substr($presettime,0,2); //HH:mm
						$m=substr($presettime,3,2);//hh:MM
						//Create timestamp for $h:$m today. But if that's in the past, push it to tomorrow.
						$alarmtime=mktime($h,$m,0);
						if (isPast($alarmtime)){
							$alarmtime+=DAY;
						}
					}
				}
				//Could an alarmtime be determined?
				if ($alarmtime!=0){
					//Yes, set alert.
					if ($ll->add_alert($alarmtime,
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
				} else {
					//No time was specified, request fails
					return false;
				}
			break;
			case "cancel_alarms":
				$ll->cancel_scheduled_alarms();
				return true;
			break;
			case "alarm_add":
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
						if (isset($_GET["mode"])){
							if ($_GET["mode"]=="subtract"){
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
				return true;
			break;
			case "toggle_livestats":
				$c=$ll->param_retrieve_value("SHOW_LIVESTATS","BILLBOARD");
				if ($c==0){
					$c=1;
				} else {
					$c=0;
				}
				$ll->param_store("SHOW_LIVESTATS",$c,"BILLBOARD");
				return true;
			break;
			case "toggle_weather_forecast":
				$c=$ll->param_retrieve_value("SHOW_WEATHER_FORECAST","BILLBOARD");
				if ($c==0){
					$c=1;
				} else {
					$c=0;
				}
				$ll->param_store("SHOW_WEATHER_FORECAST",$c,"BILLBOARD");
				return true;
			break;
			case "toggle_system_status":
				$c=$ll->param_retrieve_value("SHOW_SYSTEM_STATUS","BILLBOARD");
				if ($c==0){
					$c=1;
				} else {
					$c=0;
				}
				$ll->param_store("SHOW_SYSTEM_STATUS",$c,"BILLBOARD");
				return true;			
			break;
			case "toggle_moviemode":
				$c=$ll->param_retrieve_value("MOVIEMODE","BILLBOARD");
				if ($c==0){
					$c=1;
				} else {
					$c=0;
				}
				$ll->param_store("MOVIEMODE",$c,"BILLBOARD");
				return true;			
			break;
			case "toggle_stillimagetobb":
				$c=$ll->param_retrieve_value("STILL_IMAGE_TO_BB","BILLBOARD");
				if ($c==0){
					$c=1;
				} else {
					$c=0;
				}
				$ll->param_store("STILL_IMAGE_TO_BB",$c,"BILLBOARD");
				return true;			
			break;
			case "toggle_stillimagetonibb":
				$c=$ll->param_retrieve_value("STILL_IMAGE_TO_NIBB","BILLBOARD");
				if ($c==0){
					$c=1;
				} else {
					$c=0;
				}
				$ll->param_store("STILL_IMAGE_TO_NIBB",$c,"BILLBOARD");
				return true;			
			break;			
			case "cycle_stillimagecamnr":
				//Cycle the cam# through (4 cams)
				$c=$ll->param_retrieve_value("STILL_IMAGE_CAM_NR","BILLBOARD");
				if ($c<4){
					$c++;
				} else {
					$c=0;
				}
				$ll->param_store("STILL_IMAGE_CAM_NR",$c,"BILLBOARD");
				return true;			
			break;			
			case "lock_controls":
					$ll->lock_controls();
				return true;
			break;
			//Adds a digit to the SYSTEM PIN_INPUT parameter
			case "pin_digit":
					if (isset($_GET["digit"])){
						$pin_input=$ll->param_retrieve_value("PIN_INPUT","SYSTEM"); //Get what we have so far
						$pin_input.=$_GET["digit"]; //Add the new digit
						$ll->param_store("PIN_INPUT",$pin_input,"SYSTEM"); //And store
						//Check whether pin might be complete now: compare length with length of actual length
						if (strlen($pin_input)==strlen($ll->param_retrieve_value("PIN","SYSTEM"))){
							//Pin is complete. Attempt to unlock.
							$ll->unlock_controls();
						}
					}
				return true;
			break;
			case "pin_clear":
					$ll->param_store("PIN_INPUT","","SYSTEM");			
				return true;
			break;
			case "unlock_controls":
					$ll->unlock_controls();
				return true;
			break;
			//Live stats navigation down/up
			case "cat_descend":
				$current=$ll->param_retrieve_value("CURRENT_CATPATH","LIVESTATS"); //Get the current path of categoires, comma sep.
				if ($current==""){
					$new=urldecode($_GET["cat"]);
				} else {
					$new=$current.",".urldecode($_GET["cat"]);
				}
				$ll->param_store("CURRENT_CATPATH",$new,"LIVESTATS"); //And store new path
				return true;
			break;
			case "cat_ascend":
				$current=$ll->param_retrieve_value("CURRENT_CATPATH","LIVESTATS"); //Get the current path of categoires, comma sep.
				if ($p=strrpos($current,",")){
					$new=substr($current,0,$p);
				} else {
					$new="";
				}
				$ll->param_store("CURRENT_CATPATH",$new,"LIVESTATS"); //And store new path
				return true;
			break;
			case "startnow":
				//Modify the event referenced in $_GET["id"] to start now.
				$r=$ll->retrieve($_GET["id"]);
				$timestamp=time();
				return $ll->db("UPDATE ll2_events SET timestamp=$timestamp WHERE id=".$r["id"].";");
			break;
			case "finishnow":
				//Modify the (usually current) event referenced in $_GET["id"] to finish now.
				$r=$ll->retrieve($_GET["id"]);
				return $ll->db("UPDATE ll2_events SET duration=".(time()-$r["timestamp"])." WHERE id=".$r["id"].";");
			break;
			case "contract":
				//Shorten event
				$r=$ll->retrieve($_GET["id"]);				
				//Shorten by how much?
				$change=$ll->param_retrieve_value("CONTRACT_EXTEND_LENGTH","DAYVIEW");
				if ($r["duration"]<$change+(30*MINUTE)){
					//Do not change if it would shorten the event below 30 minutes
					return true;
				} else {
					return $ll->db("UPDATE ll2_events SET duration=".($r["duration"]-$change)." WHERE id=".$r["id"].";");
				}
			break;
			case "extend":
				//Extend event
				$r=$ll->retrieve($_GET["id"]);				
				//Extend by how much?
				$change=$ll->param_retrieve_value("CONTRACT_EXTEND_LENGTH","DAYVIEW");
				return $ll->db("UPDATE ll2_events SET duration=".($r["duration"]+$change)." WHERE id=".$r["id"].";");
			break;
			//POWERCONTROL
			case "pct_toggle_channel":
				$ll->pct_toggle_channel($_GET["channel"]);
				return true;
			break;
			case "pct_recall_preset":
				$ll->pct_recall_preset($_GET["preset"]);
				return true;
			break;
		}
	}
		
	//Output lifelog menu to site object $s. The context info is in $_GET. $auth object also needed.
	function ll_menu($s,$auth) {
		
		function build_item($auth,$service_name,$caption,$serviceparams="",$nobreak=false,$accesskey="") {
			$float="";
			if ($nobreak) {
				$float="float:left; padding-right:5px;";
			}
			if ($auth->check_user_permission($auth->get_service_id($service_name),$_GET["owner"]["login"])) {
				return "<div id='menu_item' style='$float'>
						<a accesskey='$accesskey' class='menu' href='".sid()."&page=$service_name$serviceparams'>$caption</a>
					</div>";
			} else {
				return "<div id='inactive_menu_item' style='$float'>$caption</div>";
			}
		}
	
		$s->p("<div id='menu'>");
		$s->p("<div id='menu_groupheader'>Calendar</div>");	
		
		$s->p(build_item($auth,"today","Day","",true,"A"));
		$s->p(build_item($auth,"","Week","",true));
		$s->p(build_item($auth,"","Month","",true));
		$s->p(build_item($auth,"billboard","Billboard (normal)","",false,"Q"));
		$s->p(build_item($auth,"billboard","Billboard (NI-view)","&view=noninteractive",false,""));
		$s->p(build_item($auth,"mobile","Mobile","",false,""));
					
		$s->p("<div id='menu_groupheader'>Create event</div>");			
		$s->p(build_item($auth,"record","Default template","",false,"D"));

		$s->p("<div id='menu_groupheader'>Create note</div>");			
		$s->p(build_item($auth,"record","Post-it","&form=add_postit&navto=".$_GET["navto"],true));
		$s->p(build_item($auth,"","Journal","&form=add_postit&navto=".$_GET["navto"],false));

		$s->p("<div id='menu_groupheader'>People</div>");			
		$s->p(build_item($auth,"addperson","Add","&goto=".$_GET["page"],true));
		$s->p(build_item($auth,"managepeople","Manage"));

		$s->p("<div id='menu_groupheader'>Email</div>");			
		$s->p(build_item($auth,"email","View","",true));
		$s->p(build_item($auth,"email_compose","Compose",""));

		$s->p("<div id='menu_groupheader'>Templates</div>");			
		$s->p(build_item($auth,"","Add","",true));
		$s->p(build_item($auth,"","Manage"));

		$s->p("<div id='menu_groupheader'>Filters</div>");			
		$s->p(build_item($auth,"","Add","",true));
		$s->p(build_item($auth,"","Manage",""));


		$s->p("<div id='menu_groupheader'>System</div>");			
		$s->p(build_item($auth,"logout","Logout","",true));
		$s->p(build_item($auth,"settings","Settings"));
		$s->p("</div><!--menu-->");
	}
	
	//Return <option value='$n'>$n</option> for $n=$start-$n; $selected will be preselected
	function get_num_options($end,$selected=0,$start=1) {
		$r="";
		for ($i=$start;$i<=$end;$i++) {
			if ($i==$selected) {
				$s=" selected";
			} else {
				$s="";
			}
			//For 1-9, add 0 in front
			if (strlen($i)==1) {
				$p="0$i";
			} else {
				$p=$i;
			}
			$r.="<option  value='$i'$s>$p</option>";
		}
		return $r;
	}
		
	//Create HTML for listbox option with value $value
	function create_option($value,$label="",$selected=false) {
		if ($label=="") { $label=$value; } //If no label provided, the value is the label
		if ($selected) { $s=" selected"; } else { $s=""; } //Selected?
		return "<option value='".htmlentities($value,ENT_QUOTES)."'$s>$label</option>";
	}
	
	//Create a string of options sorted by popular values 
	function create_options_with_popular_values($ll,$field) {
		//Read into the array $pvs a sorted list of the most popular options in field $field
		$pvs=$ll->get_popular_values($field);
		$r="";
		//For each element of pvs the key is the option we are after ($value holds the number of occurences)
		foreach ($pvs as $key=>$value) {
			$r.=create_option($key,$key);
		}
		return $r;
	}
	
	//Create a string of options for the person field. Here, $ll will return ids only which we need to convert to display names
	function create_options_with_popular_ids_to_names($ll) {
		//Get the popular values from field person (these are just ids, pointing to ll2_people)
		$pvs=$ll->get_popular_values("person");
		//For each element of pvs the key is the option we are after ($value holds the number of occurences)
		$r="";
		foreach ($pvs as $key=>$value) {
			//$key now holds the person-id we need to translate to a display name
			$v=$ll->get_person_displayname($key);
			$r.=create_option($v,$v);
		}
		return $r;
	}

	function get_priority_label($i) {
		switch ($i) {
			case 5: return "critical";
			case 4: return "higher";
			case 3: return "normal";
			case 2: return "lower";
			case 1: return "lowest";
			default: return "not assigned";
		}
	}
	
	//Generate the priority listbox with preselected priority
	//$mode=event will show the full select, $mode=postit will show the reduced one for the postit form
	function generate_priority_select($selected=0,$mode='event') {
		if ($mode=="postit") {
			$size=1;	//The postit form needs a one-liner with a limited choice
			$highest=5;
			$lowest=3;
		} else {
			$size=6; //The event form gets the full choice
			$highest=5;
			$lowest=0;
		}
		//Catch off-range values for $selected
		if (($selected<0) || ($selected>5)) { $selected=0; }
		$r="<select class='listbox_priority' id='' name='priority' size='$size'>";
		for ($i=$lowest;$i<($highest+1);$i++) {
			$r.=create_option($i,get_priority_label($i),$i==$selected);
		}
		$r.="</select>";
		return $r;
	}
	
	//Format $v with existing decimals: 1.900 -> 1.9, 2.000->2 etc.
	function format_with_existing_decimals($v){
		//Are there any decimals?
		if ($v==floor($v)) {
			//No. Just return $v
			return (int) $v;
		}
		//Since we now know there are decimals, all we need to do is delete the 0s at the end
		$x = (string) $v;
		while (substr($x,strlen($x)-1,1)=="0") {
			$x=substr($x,0,strlen($x)-1);
		}
		return $x;
	}

	//Form for new event, edit event, filter editing, template editing.
	//$s is the site object
	//$ll is the lifelog object used to interact with the ll database
	//$goto = GET param "page" for referral after processing
	//$navto = GET param "navto" preselected timestamp for mode "add_event". 0=time(); [not for referall after processing, that is included in $goto! ( eg $goto='today&navto=xxx')
	//modes are: edit_event, add_event... , and affect form processing (see form action)
	function display_event_form($s,$ll,$id=0,$mode="edit_event",$goto='today',$navto=0) {
		//Include the JavaScript necessary for AJAX
		$s->h("
				<script type='text/javascript'>
				
				//This is clears the selected input as well as all inputs/sels further down (and unselects the sel below)
				function inputselect(cat_id){
					//The selected input
					o=document.getElementById('ip'+cat_id).value='';
					//The sel below the input (unselected)
					document.getElementById('lb'+cat_id).selectedIndex=-1;
					//The items further down: empty them
					for (i=cat_id+1;i<6;i++){
						document.getElementById('ip'+i).value='';
						document.getElementById('lb'+i).options.length=0;
						document.getElementById('lb'+i).style.backgroundImage='url()';				
					}
				}

				//Replace a with b in string s
				function encquotes(s,a,b){
					while (s.indexOf(a)>(-1)) {
						s=s.replace(a,b);		
					}
					return s;
				}
				
				
				//Ajax replacing of category listboxes
				function dyn_lb_load(selected_option,lb_id) //lb_id is the id-number of the target listbox
				{
				if (window.XMLHttpRequest)
				  {// code for IE7+, Firefox, Chrome, Opera, Safari
				  xmlhttp=new XMLHttpRequest();
				  }
				else
				  {// code for IE6, IE5
				  xmlhttp=new ActiveXObject('Microsoft.XMLHTTP');
				  }
				  
				xmlhttp.onreadystatechange=function()
				  {
				  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				    {
					//Grab the select which is to be modified
					lb=document.getElementById('lb'+lb_id);					
					//Clear it out
					lb.options.length=0;
					
					new_options=xmlhttp.responseText;
					
					//Now we need to chop the string with the options apart, the seperator is |
					while (new_options.indexOf('|')>0) {
						delin_pos=new_options.indexOf('|');
						current=new_options.substr(0,delin_pos);
						lb.options[lb.options.length]=new Option(current,current);
						new_options=new_options.substr(delin_pos+1);
					}
					
					//Now change the background of the newly filled listbox, if it has at least 1 item
					if (lb.options.length>0){
						lb.style.backgroundImage='url(img/select_bg_cat.jpg)';
					}
					
					//If only one option is present, select id and move a category deeper
					if (lb.options.length==1){
						lb.options[0].selected=true;
						nextip=document.getElementById('ip'+lb_id).value=lb.options[0].value;
						dyn_lb_load(lb.options[0].value,(lb_id+1));
					}
				    }
				  }
			
				//Submit all 4 indices in the get string
				getstring='';
				for (i=1;i<5;i++){
					getstring+='&x'+i+'='+document.getElementById('lb'+i).value;
				}

				//Clear out the boxes further down
				for (i=lb_id;i<6;i++){
					document.getElementById('lb'+i).options.length=0;
					document.getElementById('ip'+i).value='';
					document.getElementById('lb'+(i)).style.backgroundImage='url()';				
				}
								
				xmlhttp.open('GET','ajax.php?catid='+lb_id+getstring,true);
				xmlhttp.send();
				}
				</script>
			");
		
	
		//Edit event: Get the preexisting record to prefill form
		if ($mode=="edit_event") {
			$f=$ll->retrieve($id,"ll2_events");
			$id=$f["id"]; //For event editing store $id in hidden input
		}
		//Add event: start with template $id, or no template
		elseif ($mode="add_event") {
			//$navto should contain the initial timestamp for the new event. If it does not, use time()
			if ($navto==0) { $navto=time(); }
			//Start fresh, no template used (i.e. use default template with id=1)
			if ($id<=1) {
				$f=$ll->retrieve(1,"ll2_templates");
				//If timestamp =0 then timestamp in $navto will be used (this should be the default)
				if ($f["timestamp"]==0) {
					$f["timestamp"]=$navto;
				}
			}
			$id=0; //For new event, no id is given
		}
		//*****
			$timestamp_day=date("j",$f["timestamp"]);
			$timestamp_month=date("n",$f["timestamp"]);
			$timestamp_year=date("Y",$f["timestamp"]);			
			
			$begins_hour=date("H",$f["timestamp"]);
			$begins_minute=date("i",$f["timestamp"]);
			
			$untimed="";
			if ($f["untimed"]) { $untimed="checked"; }
			
			$ends_day=date("j",$f["timestamp"]+$f["duration"]);
			$ends_month=date("n",$f["timestamp"]+$f["duration"]);
			$ends_year=date("Y",$f["timestamp"]+$f["duration"]);			

			$ends_hour=date("H",$f["timestamp"]+$f["duration"]);
			$ends_minute=date("i",$f["timestamp"]+$f["duration"]);
									
			$duration_days=days($f["duration"]);
			$duration_hours=hours($f["duration"])-($duration_days*24);
			$duration_minutes=minutes($f["duration"])-(($duration_days*24*60)+($duration_hours*60));
			
			$dollar=floor(abs($f["expense"]));
			$cent=substr($f["expense"],strrpos($f["expense"],".")+1,2);
			if (($cent==0) && ($dollar==0)) { $cent=""; $dollar=""; } //Display nothing if there is nothing

			$deposit="";
			$amount_style="style='color:red;'";
			if ($f["expense"]<0) {
				$deposit="checked";
				$amount_style="style='color:green;'";
			}
			
			$uploadedfile="<br><span id='form_exp'>(nothing uploaded yet)</span>";
			if ($f["attachment"]!="") {
				$uploadedfile="<br><span id='form_exp'>(Uploaded file: <span style='color:green'>".$f["attachment"]."</span>)</span>";
			}
			
			$notestobillboard="";
			if ($f["notes_to_billboard"]) { $notestobillboard="checked"; }
			
			//Only show decimals if there are any
			$value1=format_with_existing_decimals($f["value1"]);
			$value2=format_with_existing_decimals($f["value2"]);
			$value3=format_with_existing_decimals($f["value3"]);
			
			//Which privacy level to check?
			$public=""; $friends=""; $personal=""; $confidential="";
			$$f["privacy"]="checked";
			
			//Which priority level?
			if ($f["priority"]==5) { $critical="checked"; } else { $critical=""; }
			if ($f["priority"]==4) { $higher="checked"; } else { $higher=""; }
			if ($f["priority"]==3) { $normal="checked"; } else { $normal=""; }
			if ($f["priority"]==2) { $lower="checked"; } else { $lower=""; }
			if ($f["priority"]==1) { $lowest="checked"; } else { $lowest=""; }
			if ($f["priority"]==0) { $notassigned="checked"; } else { $notassigned=""; }
			
		//*****
		
		$r="
			<form action='".sid()."&page=$goto&processform=$mode' method='POST' enctype='multipart/form-data'>
			<table>
				<tr>
					<td class='form_hd'>Begins on <span id='form_exp'>(DD-MM-YYYY)</span></td>
					<td class='form_hd'>Category I</td>
					<td class='form_hd'>Category II</td>
					<td class='form_hd'>Category III</td>
					<td class='form_hd'>Category IV</td>
					<td class='form_hd'>Category V</td>
					<td class='form_hd'>Person</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<select class='listbox_2d' id='' name='timestamp_day'>
							".get_num_options(31,$timestamp_day)."
						</select>
						<select class='listbox_2d' id='' name='timestamp_month'>
							".get_num_options(12,$timestamp_month)."
						</select>
						<select class='listbox_4d' id='' name='timestamp_year'>
							".get_num_options(date("Y")+5,$timestamp_year,$timestamp_year-5)."
						</select>
					</td>
					";

		//The 5 category inputs and listboxes - the beauty and pride of the whole thing!
		for ($i=1;$i<6;$i++) {
			if ($i==1) { $class='listbox_first_cat'; } else { $class='listbox_cat'; } //First select gets the "start here" background at the beginning
			$r.=	"
					<td rowspan='7' class='form_dt'>
						<input class='input_cat' id='ip$i' name='cat$i' value='".$f["cat$i"]."' onFocus=\"javascript:inputselect($i)\" onKeyDown=\"document.getElementById('lb$i').selectedIndex=-1\" /><br>
						<div id='lbdiv$i'>
							<select class='$class' id='lb$i' size='10' onClick=\"document.getElementById('ip$i').value=this.value\" onChange='dyn_lb_load(this.value,".($i+1).")'>";
			if ($i==1) { $r.=				create_options_with_popular_values($ll,"cat$i"); } //Initially fill 1st box only
			$r.=				"
							</select>
						</div>
					</td>
				";
		
		
		}
		
		//Person			
		$r.=			"
					<td rowspan='7' class='form_dt'>
						<input class='input_person' name='person' id='ipp' value='".$ll->get_person_displayname($f["person"])."'/><br>
						<select class='listbox_person' id='' name='' size='10' onClick=\"document.getElementById('ipp').value=this.value\">
							".create_options_with_popular_ids_to_names($ll)."
						</select>
					</td>
				</tr>
				";

		//The rest
		$r.=		"
				<tr>
					<td  class='form_hd'>Begins at time <span id='form_exp'>(HH:MM)</span> </td>				
				</tr>
				<tr>	
					<td class='form_dt'>
						<input class='input_2d' name='timestamp_hour' onClick=\"this.value=''\" style='text-align:right;' type='text' value='".$begins_hour."'/> :
						<input class='input_2d' name='timestamp_minute' onClick=\"this.value=''\" type='text' value='".$begins_minute."'/>
						<input type='checkbox' name='untimed' $untimed/>untimed
					</td>
				</tr>
				<tr>
					<td  class='form_hd'>Ends on <span id='form_exp'>(DD-MM-YYYY)</span></td>				
				</tr>
				<tr>
					<td class='form_dt'>
						<select class='listbox_2d' id='' name='ends_day'>
							".get_num_options(31,$ends_day)."
						</select>
						<select class='listbox_2d' id='' name='ends_month'>
							".get_num_options(12,$ends_month)."
						</select>
						<select class='listbox_4d' id='' name='ends_year'>
							".get_num_options(date("Y")+5,$ends_year,$ends_year-5)."
						</select>
					</td>
				</tr>
				<tr>
					<td class='form_hd'>Ends at / after <span id='form_exp'>(time or duration)</span></td>				
				</tr>
				<tr>	
					<td class='form_dt'>
						<input class='input_2d' name='ends_hour' onClick=\"this.value=''\" style='text-align:right;' type='text' value='".$ends_hour."'/> :
						<input class='input_2d' name='ends_minute' onClick=\"this.value=''\" type='text' value='".$ends_minute."'/>
						<span id='form_exp'>(at time, HH:MM)</span>
						<br>
						<input class='input_2d' name='duration_days' onClick=\"this.value=''\" style='text-align:right;' type='text' value='".$duration_days."'/>d, 
						<input class='input_2d' name='duration_hours' onClick=\"this.value=''\" style='text-align:right;' type='text' value='".$duration_hours."'/>h, 
						<input class='input_2d' name='duration_minutes' onClick=\"this.value=''\" style='text-align:right;' type='text' value='".$duration_minutes."'/>m
						<span id='form_exp'>(duration)</span>
					</td>
				</tr>";
		
		//Expense, notes, attachment,...
		$r.=		"
				<tr>
					<td class='form_hd' style='width:165px;'>Expense</td>				
					<td colspan='4'  class='form_hd'>Event notes</td>
					<td colspan='2'  class='form_hd''>Attachment</td>
				</tr>
				<tr>
					<td  class='form_dt'>
						$<input class='input_dollar' name='expense_dollar' type='text' value='".$dollar."' ".$amount_style."/>.<input class='input_cent' name='expense_cent' type='text' value='".$cent."' ".$amount_style."/>
						<input type='checkbox' name='deposit' ".$deposit."/>deposit
					</td>
					<td rowspan='7' colspan='4' class='form_dt'>
						<textarea class='textarea_notes' id='' name='notes'>".$f["notes"]."</textarea>
					</td>
					<td colspan='2' class='form_dt' style='text-align:right;'>
						(select a file) <input class='input_attachment' type='file' id='' name='attachment' value='' />
						".$uploadedfile."
					</td>
				</tr>
				<tr>	
					<td class='form_hd'>Transaction account</td>				
					<td colspan='2' class='form_hd' >Billboard announcement</td>
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input class='input_account' type='text' id='ipacc' name='account' value='".$f["account"]."'/>
						<select class='listbox_account' id='' name='listbox_account' size='4' onClick=\"document.getElementById('ipacc').value=this.value\" >
							".create_options_with_popular_values($ll,"account")."
						</select>
					</td>
					<td colspan='2' class='form_dt'>
						<input class='input_billboard' type='text' id='' name='billboard_text' value='".$f["billboard_text"]."'/>
						<input type='checkbox' id='' name='notes_to_billboard' ".$notestobillboard." /> event notes to billboard
					</td>
				</tr>
				<tr>
					<td class='form_hd'>Generic numerical values</td>				
					<td colspan='2' class='form_hd'>Privacy</td>
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input class='input_gvs' type='text' id='' name='value1' value='".$value1."' /> a						
						<input class='input_gvs' type='text' id='' name='value2' value='".$value2."' /> b						
						<input class='input_gvs' type='text' id='' name='value3' value='".$value3."' /> c						
					</td>
					<td colspan='2' class='form_dt'>					
						<input type='radio' id='' name='privacy' value='public' ".$public."> public 
						<input type='radio' id='' name='privacy' value='friends' ".$friends."> friends 
						<input type='radio' id='' name='privacy' value='personal' ".$personal."> personal 
						<input type='radio' id='' name='privacy' value='confidential' ".$confidential."> confidential 
					</td>
				</tr>
				<tr>
					<td class='form_hd'>Event priority</td>				
					<td colspan='2' class='form_hd'>Form</td>
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						".generate_priority_select($f["priority"])."
					</td>
					<td colspan='2' class='form_dt' style='vertical-align:bottom; text-align:right;'>
						<input type='hidden' name='id' value='$id'>
						<input type='hidden' name='generated' value='".time()."'>
						<input type='hidden' name='orig_timestamp' value='".$f["timestamp"]."'>
						<input type='hidden' name='orig_end' value='".($f["timestamp"]+$f["duration"])."'>
						<input type='submit' value='Save'>
					</td>
				</tr>
			</table>
			</form>
		";
		return $r;
	}
	

	//Form for postit creation
	function display_postit_form($s,$ll,$id=0,$mode='add_postit',$goto='today',$navto=0) {
		//Edit postit: Get the preexisting record to prefill form
		if ($mode=="edit_postit") {
			$f=$ll->retrieve($id,"ll2_events");
			$navto=$f["timestamp"];
			$id=$f["id"]; //For postit editing store $id in hidden input
			if ($f["duration"]==0){ //If the saved post-it has no duration, it is valid indefinitely
				$unlimited="checked";
			} else {
				$unlimited="";
			}
		}
		elseif ($mode="add_postit") {
			//Add postit, initial values
			$id=0; //For new postit, no id is given
			$f["priority"]=3; //Default to normal
			$f["notes"]="";
			$unlimited="";
			if (getBeginningOfDay($navto)<time()) { $unlimited="checked"; } //If the note starts to be relevant today or before, preselect "unlimited"
		}
		$r="";
		$r.="<form action='".sid()."&page=$goto&processform=$mode' method='POST'>
			<div id='create_postit_container'>
			<table style='width:500px;'>
				<tr>
					<td class='form_hd'>Post-it is valid on</td>
					<td class='form_hd'>Post-it priority</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input class='input_2d' style='text-align:right;' onClick=\"this.value=''\" name='timestamp_day' style='text-align:right;' type='text' value='".date("j",$navto)."'/>
						/ <input class='input_2d' style='text-align:right;' onClick=\"this.value=''\" name='timestamp_month' type='text' value='".date("n",$navto)."'/>
						/ <input class='input_4d' style='text-align:right;' onClick=\"this.value=''\" name='timestamp_year' type='text' value='".date("Y",$navto)."'/>
						<span id='form_exp'>(DD/MM/YYYY)</span>
						<input type='checkbox' name='unlimited' $unlimited/>unlimited
					</td>
					<td class='form_dt' style='text-align:right; width:165px;'>
						".generate_priority_select($f["priority"],"postit")."
					</td>
				</tr>
			</table>
			<table style='width:500px;'>
				<tr>
					<td class='form_dt' style='text-align:center; background:#FFFF80;'>
						<span style='color:orange; letter-spacing:5px;'>POST-IT-NOTE</span>
						<textarea id='textarea_notes_postit' name='notes'>".$f["notes"]."</textarea>
					</td>
				</tr>
				<tr>
					<td class='form_dt' style='text-align:right; background:none;'>
						<input type='hidden' name='id' value='$id'>
						<input type='submit' value='Save'>
					</td>
				</tr>
			</table>
			</form>
			</div>";
		return $r;
	}

	//Form for new person, edit person
	//$s is the site object
	//$ll is the lifelog object used to interact with the ll database
	//$goto = GET param for referral after processing
	//modes are: edit_person, add_person... , and affect form processing (see form action)
	function display_person_form($s,$ll,$id=0,$mode="add_person",$goto='today') {
		if ($mode=="add_person") {
			//Add person: start with blank form
			$id=0; //For new person, no id is given
			$color="black";
		} else {
			$color="red;border:none"; //Use red for data in view/edit mode
		}
		$f=$ll->retrieve($id,"ll2_people");
		
		if ($f["year_of_birth_unknown"]) { $yobuk="checked"; } else { $yobuk=""; }
		if ($f["show_birthday"]) { $showbd="checked"; } else { $showbd=""; }
		if ($f["birthday"]==0){
			$bday_day="";
			$bday_month="";
			$bday_year="";
		
		} else {
			$bday_day=date("d",$f["birthday"]);
			$bday_month=date("m",$f["birthday"]);
			if (!$yobuk) {
				$bday_year=date("Y",$f["birthday"]);
			} else {
				$bday_year="";
			}
		}
	
		$r="
			<form action='".sid()."&page=$goto&processform=$mode' method='POST' enctype='multipart/form-data'>
			<table>
				<tr>
					<td class='form_hd_section'>
						Personal information
					</td>
					<td class='form_hd_section'>
						Main postal address
					</td>
					<td class='form_hd_section'>
						Second postal address
					</td>
					<td class='form_hd_section'>
						Work/office postal address
					</td>
				</tr>
				<tr>
					<td class='form_hd'>
						Last name
					</td>
					<td class='form_hd'>
						Main street address
					</td>
					<td class='form_hd'>
						Second street address
					</td>
					<td class='form_hd'>
						Work street address
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='1' style='color:$color;' class='input_personform' id='lastname' name='lastname' type='text' value='".$f["lastname"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='9' style='color:$color;' class='input_personform' name='main_address_street' type='text' value='".$f["main_address_street"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='15' style='color:$color;' class='input_personform' name='second_address_street' type='text' value='".$f["second_address_street"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='21' style='color:$color;' class='input_personform' name='work_address_street' type='text' value='".$f["work_address_street"]."' />
					</td>
				</tr>
				<tr>
					<td class='form_hd'> 
						First name, middle name(s)
					</td>
					<td class='form_hd'>
						Main address city, province and zip code
					</td>
					<td class='form_hd'>
						Second address city, province and zip code
					</td>
					<td class='form_hd'>
						Work address zip city, province and zip code
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='2' class='input_personform' style='width:140px; color:$color;' name='firstname' type='text' value='".$f["firstname"]."' />
						<div style='float:right;'>
							<input tabindex='3' class='input_personform' style='width:100px; color:$color;' name='middlename' type='text' value='".$f["middlename"]."' />
						</div>
					</td>
					<td class='form_dt'>
						<input tabindex='10' class='input_personform' style='width:128px; color:$color;' name='main_address_city' type='text' value='".$f["main_address_city"]."' />,
						<input tabindex='11 'class='input_personform' style='width:36px; color:$color;' name='main_address_province' type='text' value='".$f["main_address_province"]."' />
						<div style='float:right;'>
							<input tabindex='12' class='input_personform' style='width:65px; color:$color;' name='main_address_zip' type='text' value='".$f["main_address_zip"]."' /> 
						</div>
					</td>
					<td class='form_dt'>
						<input tabindex='16' class='input_personform' style='width:128px; color:$color;' name='second_address_city' type='text' value='".$f["second_address_city"]."' />,
						<input tabindex='17' class='input_personform' style='width:36px; color:$color;' name='second_address_province' type='text' value='".$f["second_address_province"]."' />
						<div style='float:right;'>
							<input tabindex='18' class='input_personform' style='width:65px; color:$color;' name='second_address_zip' type='text' value='".$f["second_address_zip"]."' />
						</div>
					</td>
					<td class='form_dt'>
						<input tabindex='22' class='input_personform' style='width:128px; color:$color;' name='work_address_city' type='text' value='".$f["work_address_city"]."' />,
						<input tabindex='23' class='input_personform' style='width:36px; color:$color;' name='work_address_province' type='text' value='".$f["work_address_province"]."' />
						<div style='float:right;'>
							<input tabindex='24' class='input_personform' style='width:65px; color:$color;' name='work_address_zip' type='text' value='".$f["work_address_zip"]."' />
						</div>
					</td>
				</tr>
				<tr>
					<td class='form_hd'> 
						Birthday
					</td>
					<td class='form_hd'>
						Main address country
					</td>
					<td class='form_hd'>
						Second address country
					</td>
					<td class='form_hd'>
						Work address country
					</td>
				</tr>
				<tr>
					<td class='form_dt' rowspan='3'>
						<input tabindex='4' style='color:$color;' class='input_2d' name='birthday_day' type='text' value='".$bday_day."' />/
						<input tabindex='5' style='color:$color;' class='input_2d' name='birthday_month' type='text' value='".$bday_month."' />/
						<input tabindex='6' style='color:$color;' class='input_4d' name='birthday_year' type='text' value='".$bday_year."' />
						<span id='form_exp'>(DD/MM/YYYY)</span>
						<br>
						<input tabindex='7' type='checkbox' name='year_of_birth_unknown' $yobuk />year of birth not known
						<br>
						<input tabindex='8' type='checkbox' name='show_birthday' $showbd />display this birthday in lifelog
					</td>
					<td class='form_dt'>
						<input tabindex='13' style='color:$color;' class='input_personform' name='main_address_country' type='text' value='".$f["main_address_country"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='19' style='color:$color;' class='input_personform' name='second_address_country' type='text' value='".$f["second_address_country"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='25' style='color:$color;' class='input_personform' name='work_address_country' type='text' value='".$f["work_address_country"]."' />
					</td>
				</tr>
				<tr>
					<td class='form_hd'>
						Main address home phone <span id='form_exp'>(use international format)</span>
					</td>
					<td class='form_hd'>
						Second address home phone <span id='form_exp'>(use international format)</span>
					</td>
					<td class='form_hd'>
						Work address home phone <span id='form_exp'>(use international format)</span>
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='14' style='color:$color;' class='input_personform' name='main_address_homephone' type='text' value='".$f["main_address_homephone"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='20' style='color:$color;' class='input_personform' name='second_address_homephone' type='text' value='".$f["second_address_homephone"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='26' style='color:$color;' class='input_personform' name='work_address_homephone' type='text' value='".$f["work_address_homephone"]."' />
					</td>
				</tr>
				<tr>
					<td class='form_hd_section'>
						Email, cell numbers
					</td>
					<td class='form_hd_section'>
						IM, web, attachment
					</td>
					<td class='form_hd_section'>
						Notes
					</td>
					<td class='form_hd_section'>
						
					</td>					
				</tr>
				<tr>
					<td class='form_hd'> 
						Personal email
					</td>
					<td class='form_hd'>
						Skype name
					</td>
					<td class='form_dt' rowspan='10'>
						<textarea tabindex='35' style='color:$color;' class='textarea_notes_person' name='notes'>".$f["notes"]."</textarea>
					</td>
					<td class='form_dt' rowspan='10' style='vertical-align:bottom; text-align:right;' >
						<input tabindex='36' type='submit' name='personform' value='Save'/>
						<input type='hidden' name='id' value='$id'>
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='26' style='color:$color;' class='input_personform' name='email_personal' type='text' value='".$f["email_personal"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='31' style='color:$color;' class='input_personform' name='skype' type='text' value='".$f["skype"]."' />
					</td>
				</tr>
				<tr>
					<td class='form_hd'> 
						Work email
					</td>
					<td class='form_hd'>
						ICQ number
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='27' style='color:$color;' class='input_personform' name='email_work' type='text' value='".$f["email_work"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='32' style='color:$color;' class='input_personform' name='icq' type='text' value='".$f["icq"]."' />					
					</td>
				</tr>
				<tr>
					<td class='form_hd'> 
						Extra email
					</td>
					<td class='form_hd'>
						Website
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='28' style='color:$color;' class='input_personform' name='email_extra' type='text' value='".$f["email_extra"]."' />
					</td>
					<td class='form_dt'>
						<input tabindex='33' style='color:$color;' class='input_personform' name='website' type='text' value='".$f["website"]."' />
					</td>
				</tr>
				<tr>
					<td class='form_hd'> 
						Personal cellphone
					</td>
					<td class='form_hd'>
						Attachment
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='29' style='color:$color;' class='input_personform' name='cell_personal' type='text' value='".$f["cell_personal"]."' />
					</td>
					<td class='form_dt' style='text-align:right;' rowspan='3'>
						<input tabindex='34' class='input_attachment' type='file' id='' name='attachment' value='' />
					</td>
				</tr>
				<tr>
					<td class='form_hd'> 
						Work cellphone
					</td>
				</tr>
				<tr>
					<td class='form_dt'>
						<input tabindex='30' style='color:$color;' class='input_personform' name='cell_work' type='text' value='".$f["cell_work"]."' />
					</td>
				</tr>
			</table>
			</form>
			";
			
		return $r;
	
	}
	
	
	/*********************************************************************************************/
	//Render list of $n people starting at $start
	function show_people($ll,$start=0,$goto="",$filter="",$orderby="lastname,firstname",$n=20,$height=400){
		$query="SELECT * FROM ll2_people $filter ORDER BY $orderby LIMIT $start,$n;";
		$r="";
		$i=0;
		if ($res=$ll->db($query)){
			$r.="<table>";
			$r.="<tr>
				<td>
					<table style='width:1070px; padding:0px; margin:-2px; border-bottom:1px solid gray; font-weight:bold;'>
						<tr>
						<td style='font-weight:normal; text-align:center; font-size:9px; width:60px;'>".mysqli_num_rows($res)." records
						</td>
						<td style='width:120px; '>Last name</td>
						<td style='width:100px; '>First name</td>
						<td style='width:270px;'>Email</a></td>
						<td style='width:172px;'>Phone</td>
						<td style='width:172px;'>Cell</td>
						<td style='width:68px;'>Birthday</td>
						<td>Modified</td>
						</tr>
					</table>
				</td>
			</tr>
			</table>
			<div style='height:".$height."px; width:99%; overflow:auto;'>
			<table>
			<tr>
			";
			while ($q=mysqli_fetch_array($res)){
				$i++;
				if (($i%2)==0){
					$bgcolor="#EEE";
				} else {
					$bgcolor="#FFF";
				}
				
				//Build strings for email, cell numbers, home numbers
				$email="";
				$emailtypes="";
				if ($q["email_personal"]!="") {
					$email.="<a href='".sid()."&page=email_compose&rcpt_addr=".$q["email_personal"]."'>".$q["email_personal"]."</a>";
					$emailtypes.="<span style='font-size:7pt; color:gray;'>(ps:)</span>&nbsp;";
				}
				if ($q["email_work"]!="") {
					if ($email!=""){ $email.="&nbsp;<br/>"; }//Add newline if necessary
					$email.="<a href='mailto:".$q["email_work"]."'>".$q["email_work"]."</a>";
					if ($emailtypes!=""){ $emailtypes.="<br/>"; }//Add newline if necessary
					$emailtypes.="<span style='font-size:7pt; color:gray;'>(wk:)</span>&nbsp;";
				}
				if ($q["email_extra"]!="") {
					if ($email!=""){ $email.="<br/>"; }//Add newline if necessary
					$email.="<a href='mailto:".$q["email_extra"]."'>".$q["email_extra"]."</a>";
					if ($emailtypes!=""){ $emailtypes.="<br/>"; }//Add newline if necessary
					$emailtypes.="<span style='font-size:7pt; color:gray;'>(ex:)</span>&nbsp;";
				}
				//Home numbers
				$hphone="";
				$hphonetypes="";
				if ($q["main_address_homephone"]!="") {
					$hphone.=$q["main_address_homephone"];
					$hphonetypes.="<span style='font-size:7pt; color:gray;'>(hm:)</span>&nbsp;";
				}
				if ($q["second_address_homephone"]!="") {
					if ($hphone!=""){ $hphone.="&nbsp;<br/>"; }//Add newline if necessary
					$hphone.=$q["second_address_homephone"];
					if ($hphonetypes!=""){ $hphonetypes.="<br/>"; }//Add newline if necessary
					$hphonetypes.="<span style='font-size:7pt; color:gray;'>(2d:)</span>&nbsp;";
				}
				if ($q["work_address_homephone"]!="") {
					if ($hphone!=""){ $hphone.="<br/>"; }//Add newline if necessary
					$hphone.=$q["work_address_homephone"];
					if ($hphonetypes!=""){ $hphonetypes.="<br/>"; }//Add newline if necessary
					$hphonetypes.="<span style='font-size:7pt; color:gray;'>(wk:)</span>&nbsp;";
				}
				//Cell numbers
				$cphone="";
				$cphonetypes="";
				if ($q["cell_personal"]!="") {
					$cphone.=$q["cell_personal"];
					$cphonetypes.="<span style='font-size:7pt; color:gray;'>(ps:)</span>&nbsp;";
				}
				if ($q["cell_work"]!="") {
					if ($cphone!=""){ $cphone.="&nbsp;<br/>"; }//Add newline if necessary
					$cphone.=$q["cell_work"];
					if ($cphonetypes!=""){ $cphonetypes.="<br/>"; }//Add newline if necessary
					$cphonetypes.="<span style='font-size:7pt; color:gray;'>(wk:)</span>&nbsp;";
				}
				//Birthday info
				$bdayinfo="";
				if (($q["birthday_day"]!="") && ($q["birthday_month"]!="")) { //Birthday is known, though perhaps not the year
					$bdayinfo=date("d/m",$q["birthday"]);
					if (!$q["year_of_birth_unknown"]){
						$bdayinfo.=date("/Y",$q["birthday"]);
					} else {
						$bdayinfo.="/????";
					}
					if ($q["show_birthday"]){
						$bdayinfo="<span style='font-size:10px; color:#060;'>$bdayinfo</span>";
					} else {
						$bdayinfo="<span style='font-size:10px; color:gray;'>$bdayinfo</span>";					
					}
				}
				$r.="<tr>
					<td>
						<table onMouseOver=\"this.style.background='#FFE'; this.style.color='red';\" onMouseOut=\"this.style.background='$bgcolor'; this.style.color='';\" style='width:1070px; padding:0px; margin:-2px; border-top:1px dotted gray; background:$bgcolor;'>
							<tr>
							<td style='width:60px;  vertical-align:top; text-align:center;'>
								<a href='".sid()."&page=addperson&form=edit_person&id=".$q["id"]."&goto=$goto'>[e]</a> 
								<a href='".me()."&dbaction=delete&table=ll2_people&id=".$q["id"]."'>[x]</a>
							</td>
							<td style='width:120px; vertical-align:top;'>".$q["lastname"]."</td>
							<td style='width:100px; vertical-align:top; '>".$q["firstname"]." ".substr($q["middlename"],0,1)."</td>
							<td style='width:27px;  vertical-align:top;'>$emailtypes</td>
							<td style='width:240px;  vertical-align:top;'>$email</td>
							<td style='width:27px;  vertical-align:top;'>$hphonetypes</td>
							<td style='width:140px;  vertical-align:top;'>$hphone</td>
							<td style='width:27px;  vertical-align:top;'>$cphonetypes</td>
							<td style='width:140px;  vertical-align:top;'>$cphone</td>
							<td style='width:70px;  vertical-align:top;'>$bdayinfo</td>
							<td style='width:auto;  vertical-align:top;'><span style='color:gray; font-size:10px;'>".date("d/m/Y",$q["created_at"])."</span></td>
							</tr>
						</table>
					</td>
				</tr>";
			}
			$r.="</table></div>";
		}
		if ($r!=''){
			$r="<table>$r</table>";
		}
		return $r;
	}
	
	//Produce a table with emails
	function show_emails($ll,$filter){
		$background_1="white";
		$background_0="white";
		$background_unread="#FBB";
		$result="";
		$query="SELECT id,timestamp,person,email_timestamp,subject,sender_address,sender,isread
				FROM ll2_emails
				WHERE active=true
				ORDER BY email_timestamp DESC;";
		if ($res=$ll->db($query)){
			$rows="";
			while ($r=mysqli_fetch_array($res)){
				$i++; //Odd or even for row bg
				if (($i%2)==0){
					$background=$background_0;
				} else {
					$background=$background_1;				
				}
				if (!$r["isread"]){
					//Unread messages overwrite background color
					$background=$background_unread;
				}
				
				$links="<a style='color:brown;' href='".me()."&action=showmail&id=".$r["id"]."'>[v]</a> "
					  ."<a style='color:brown;' href='".me()."&dbaction=toggleread&id=".$r["id"]."'>[r]</a> "
				          ."<a style='color:brown;' href='".me()."&dbaction=delete&table=ll2_emails&id=".$r["id"]."'>[x]</a>";
				
				$rows.=
					"<tr><td>
						<table style='background:$background; width:515px; font-size:8pt;border-bottom:1px dotted gray; margin:-2px;'>
							<tr>
								<td style='width:150px;'>".$ll->get_person_displayname($r["person"])
								."<br/>".date("Y/m/d H:i:s",$r["email_timestamp"])
								."</td><td style='vertical-align:top;'>
								<a style='color:#822;' href='".me()."&action=showmail&id=".$r["id"]."'>".$r["subject"]."</a>
								<div style='float:right;'>$links</div>
								</td>
							</tr>
						</table>
					</td></tr>";
			}
			$table="<table>$rows</table>";
			$result=$table;
		}
		
		return $result;
	}
	
	//display email message
	function show_email($ll,$id){
		$result="";
		if ($r=$ll->retrieve($id,"ll2_emails")){
			//First of all we need to mark this message read if that is not yet so
			if (!$ll->is_marked_read($id)){
				$ll->toggle_read($id);
			}
			//Create an array from the tablefield, but don't destroy
			$raw=explode("\r\n",$r["message"]);
			foreach ($raw as $key=>&$value){
				$value=$value."\n\r"; //Preserve linebreaks
			}
			//Parse the message
			$fullmsg=parse_message($raw);
			//
			if (!$from=$ll->get_person_displayname($ll->get_personid_by_email($fullmsg["headers"]["__sender_address"]))){
				$from="Sender not identified by LifeLog";
			}
			if ($fullmsg["headers"]["__message_type"]=="multipart"){
				$ctype=$fullmsg["headers"]["content-type"]." (".$fullmsg["headers"]["__parts"]." parts)";
			} else {
				$ctype=$fullmsg["headers"]["content-type"];
			}
			
			
			$msgheaders=""; //Code for header display
			$msgheaders.="<tr><td><span style='color:green;'>Date:</span></td><td>".date("Y/m/d H:i:s",$fullmsg["headers"]["__timestamp"])." (".getHumanReadableLengthOfTime(time()-$fullmsg["headers"]["__timestamp"])." ago)</td></tr>";
			$msgheaders.="<tr><td><span style='color:green;'>Sender:</span></td><td style='font-weight:bold;'>".$from."</td></tr>";
			$msgheaders.="<tr><td><span style='color:green;'>Subject:</span></td><td style='color:#822;'>".$fullmsg["headers"]["subject"]."</td></tr>";
			$msgheaders.="<tr><td><span style='color:green;'>Sent from:</span></td><td style='color:gray;'>".$fullmsg["headers"]["__sender_address"]."</td></tr>";
			$msgheaders.="<tr><td><span style='color:green;'>Content-type:</span></td><td style='color:gray;'>".$ctype."</td></tr>";
			
			
			$msgheaders="<table>$msgheaders</table>";

			$display_body=nl2br($fullmsg["body"]);
			//Catch base64 encoding
			if (strtolower($fullmsg["headers"]["content-transfer-encoding"])=="base64"){
				$display_body=nl2br(base64_decode($fullmsg["body"]));
			}
			//Add headers
			$result.=$msgheaders;
			$result.="<table style='width:100%; border-top:1px solid gray;'><tr><td style='color:#822;'>".$display_body."</td></tr></table>";
		}
		return $result;
	}
	
	//Build an SQL filter from the array $q. Like "WHERE key1 like value1 AND key2 like value2
	//$and -> AND, !$and -> OR
	function buildfilter($q,$and=true) {
		$r="";
		$x=" OR";
		if ($and){ $x=" AND"; }
		foreach ($q as $key=>$value){
			if (($value!="") && ($key!="filterform")){ //filterform is the name of the submit button in the managepeople - filter form
				if ($r!="") { $r.=$x; } // Add AND or OR only if at least one condition is already in the result string
				$r.=" $key like '".$value."%'";
			}
		}
		if ($r!=""){
			$r="WHERE".$r;
		}
		return $r;
	}
	
	
	//

?>