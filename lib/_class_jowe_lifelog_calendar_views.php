<?php
/*
	JW, July 18, 2010
	
	Requires date_tools.php
*/
class jowe_lifelog_calendar_views {
	
	//General parameters
	private $default_notes_length=500; //How many characters of notes if not otherwise specified?
	
	//Day view parameters
	private $dv_grid_vertical_offset=50;
	private $horizontal_offset=8;
	private $dv_grid_column_width=290;
	private $dv_grid_hour_height=43;
	private $dv_grid_hour_borderheight=1;
	private $dv_grid_horizontal_column_spacing=10;
	private $dv_timedentry_minimumheight=27; //Minimum height of a day_view timed event entry (on the 24h grid)
	private $dv_gridentry_notes_length=200; //How many characters of notes to display in day view?
	private $dv_col_height; //Calculated in the constructor
	//
	private $ll; //Lifelog object for database communication

	//Auxilliary
	
	//Returns the position of the first space in $s after $n characters
	function first_space_after_length($s,$n){
		$tmp=substr($s,$n); 		//This is the substring in which the first space is to be found
		$tmp_pos=strpos($tmp," ");	//Position of the desired space within $tmp
		return ($n+$tmp_pos);
	}
	
	//Creates a vertical line in a defined color - for event display
	function getVline(){
		$color=$this->get_transition_color("#AAAAAA","#333390",$this->ll->nightview_percent());
		return "<span style='color:$color;'> | </span>";
	}

	//Creates a link for the navigation bar (me is the page-identity, from me())
	function getNavLink($label,$me,$navto,$usebgcolorforweekday=false){
		$r="<a class='nav' href='$me&navto=$navto'>$label</a>";
		if ($usebgcolorforweekday){
			//Set background according to weekday if requested
			$r="<span style='background:".$this->getBGColorForWeekday(date("w",$navto)).";'>$r</span>";
		}
		return $r;
	
	}
	
	//bgcolors for weekdays. $n=0 is sunday, 6 is saturday
	function getBGColorForWeekday($n){
		switch($n) {
			case 6: return "#EEFFEE";
			case 0: return "#DDFFDD";
			default: return "white";
		}
	}
	
	//Return class id depending on whether $timestamp is past or future
	function getLinkClass($timestamp){
		if (isFuture($timestamp)) {
			return "link_future";
		}
		return "link_past";
	}
	
	//This is a header that occurs e.g. in the third column in dayview
	function getDisplayHeader($s){
			return "<span style='color:gray; font-size:10px;'>".$s."</span>";	
	}
	
	//As dechex, but adds a 0 in front if only one hexdigit
	function dechex2($v){
		$r=dechex($v);
		if (strlen($r)<2){
			$r="0".$r;
		}
		return $r;
	}
	
	//Adjust brightness by $factor. $color = #xxxxxx
	function brightness($color,$factor=0.7){
		$red=hexdec(substr($color,1,2))*$factor;
		$green=hexdec(substr($color,3,2))*$factor;
		$blue=hexdec(substr($color,5,2))*$factor;
		return "#".$this->dechex2($red).$this->dechex2($green).$this->dechex2($blue);		
	}
	
	//Calculates a color that is $percent percent between $color1 and $color2
	function get_transition_color($color1,$color2,$percent){
		$red1=hexdec(substr($color1,1,2));
		$red2=hexdec(substr($color2,1,2));
		//The final red is $red1+ (the difference bw the two, times $percent)
		$red=$red1+(($red2-$red1)*$percent);
	
		$green1=hexdec(substr($color1,3,2));
		$green2=hexdec(substr($color2,3,2));
		$green=$green1+(($green2-$green1)*$percent);

		$blue1=hexdec(substr($color1,5,2));		
		$blue2=hexdec(substr($color2,5,2));		
		$blue=$blue1+(($blue2-$blue1)*$percent);
		
		return "#".$this->dechex2($red).$this->dechex2($green).$this->dechex2($blue);
	}
	
	//**************************************************

	public function __construct($ll){
		//Give the object handle to this instance
		$this->ll=$ll;
		//Total height of dayview grid columns?
		$this->dv_col_height=(($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight)*12);
	}

	//Return the empty day grid with just time indications
	public function dv_produce_empty_day_grid($time){
		//
		$night_bg="#E5FAFF";   //Background for night hours
		$night_ends=date("H",$this->ll->get_sunrise($time));	//For night bg
		$night_starts=date("H",$this->ll->get_sunset($time));	//For night bg
		//
		$r="";
		$bordercolor="#FBB";
		//First column
		for ($i=0;$i<12;$i++) {
			if ($i<10) {$n='0';} else {$n='';} //For hours 0-9, precede with 0
			if ($i==11) { $border="border:none;"; } else { $border="border-bottom:1px dotted $bordercolor;"; }
			//
			$background="";
			if ($i<$night_ends){ //Night background col 1
				$background=$night_bg;
			}
			//
			$navto00=$time+($i*HOUR);
			$navto15=$navto00+(15*MINUTE);
			$navto30=$navto15+(15*MINUTE);
			$navto45=$navto30+(15*MINUTE);
			$r.="	<div id='dayview_hour' style='background:$background; $border width:".$this->dv_grid_column_width."px; height:".$this->dv_grid_hour_height."px; top:".($this->dv_grid_vertical_offset+($i*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight)))."px; left:".$this->horizontal_offset."px;'>
					<span style='font-size:11px'>
						<a class='".$this->getLinkClass($navto00)."' href='".me()."&page=record&form=add_event&navto=$navto00'>$n$i</a>
					</span>
					<span style='font-size:8px; color:maroon;'>
						<br /><a class='".$this->getLinkClass($navto15)."' href='".me()."&page=record&form=add_event&navto=$navto15'>:15</a>
						<br /><a class='".$this->getLinkClass($navto30)."' href='".me()."&page=record&form=add_event&navto=$navto30'>:30</a>
						<br /><a class='".$this->getLinkClass($navto45)."' href='".me()."&page=record&form=add_event&navto=$navto45'>:45</a>
					</span>
				</div>";
		}
		//Second column
		for ($i=0;$i<12;$i++) {
			if ($i==11) { $border="border:none;"; } else { $border="border-bottom:1px dotted $bordercolor;"; }
			//
			$background="";
			if ($i>($night_starts-13)){ //Night background col 2
				$background=$night_bg;
			}
			//
			$navto00=$time+(($i+12)*HOUR);
			$navto15=$navto00+(15*MINUTE);
			$navto30=$navto15+(15*MINUTE);
			$navto45=$navto30+(15*MINUTE);
			$r.="	<div id='dayview_hour' style='background:$background; $border width:".$this->dv_grid_column_width."px; height:".$this->dv_grid_hour_height."px; top:".($this->dv_grid_vertical_offset+($i*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight)))."px; left:".($this->horizontal_offset+$this->dv_grid_column_width+$this->dv_grid_horizontal_column_spacing)."px;'>
					<span style='font-size:11px'>
						<a class='".$this->getLinkClass($navto00)."' href='".me()."&page=record&form=add_event&navto=$navto00'>".($i+12)."</a>
					</span>
					<span style='font-size:8px; color:maroon;'>
						<br /><a class='".$this->getLinkClass($navto15)."' href='".me()."&page=record&form=add_event&navto=$navto15'>:15</a>
						<br /><a class='".$this->getLinkClass($navto30)."' href='".me()."&page=record&form=add_event&navto=$navto30'>:30</a>
						<br /><a class='".$this->getLinkClass($navto45)."' href='".me()."&page=record&form=add_event&navto=$navto45'>:45</a>
					</span>
				</div>";
		}
		//If this is today, set the NOW arrow 
		if (isToday($time)){
			$r.="<div id='nowarrow' style='left:".($this->get_grid_pos_left(date("H",time()))-25)."px; top:".$this->get_grid_pos_top(date("H",time()),date("i",time()))."px;'></div>";
		}
		return $r;
	}


	//Returns the $depth deepest non-empty categories of event record $r.
	//Return no higher cat then $highest (will usually be 2 because cat1 will be represented by background color)
	function getDeepestCategories($r,$depth=1,$highest=2){
		//Which is the deepest?
		$deepest=5;
		if ($r["cat5"]=="") { $deepest=4; }
		if ($r["cat4"]=="") { $deepest=3; }
		if ($r["cat3"]=="") { $deepest=2; }
		if ($r["cat2"]=="") { $deepest=1; }
		if ($r["cat1"]=="") { $deepest=0; }
		//Now build result
		$q="";		
		for ($i=max($deepest-$depth+1,$highest);$i<($deepest+1);$i++){
			$q.=" - ".$r["cat$i"];		
		}
		$q=substr($q,3); //chop off the " - " at the beginning
		return $q;
	}

	//Produce the actual code for a spaceholder. $col= 1 or 2. $rest_duration = anything between 1 and HOUR*12
	function dv_draw_spaceholder($col,$rest_duration){
		if ($col==1){
			//Left position for first column
			$sh_left=$this->horizontal_offset+18;
		} else {
			//Left for second column
			$sh_left=$this->horizontal_offset+18+$this->dv_grid_column_width+$this->dv_grid_horizontal_column_spacing;		
		}
		return "<div id='spaceholder'
				style='top:".$this->dv_grid_vertical_offset."px; 
					left:".$sh_left."px;
					width:".($this->dv_grid_column_width-21)."px;
					height:".floor(($rest_duration/3600)*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight))."px; 
					background:#EEE;
					position:absolute;'>
			</div>";	
	}

	//Create one or two spaceholders as needed
	function dv_create_spaceholder($r,$time){
		$q="";
		//Get end of event
		$end_of_event=$r["timestamp"]+$r["duration"];
		//A spaceholder in col1 is needed if the event started before this day AND ends before midnight this day
		if (($r["timestamp"]<getBeginningOfDay($time)) && ($end_of_event>getBeginningOfDay($time)) && ($end_of_event<=getEndOfDay($time))){
			//How much of the duration goes to this spaceholder? 
			//	The timestamp of the end of the event minus 00:00:00 this day.
			//	That, however, could be too much (if the event extends beyond noon), therfore:
			$sh_dur=min($end_of_event-getBeginningOfDay($time),(HOUR*12)); 
			//Create spaceholder for col 1
			$q.=$this->dv_draw_spaceholder(1,$sh_dur);
		}
		//A spaceholder in col2 is needed if the event started before noon this day AND ends before midnight this day
		if (($r["timestamp"]<getNoonOfDay($time)) && ($end_of_event>getNoonOfDay($time)) && ($end_of_event<=getEndOfDay($time))){
			//How much of the duration goes to this spaceholder? 
			//	The timestamp of the end of the event minus 12:00:00 this day.
			//	That should never be too much since we know that the event ends on this day.
			$sh_dur=$end_of_event-getNoonOfDay($time); 
			//Create spaceholder for col 2
			$q.=$this->dv_draw_spaceholder(2,$sh_dur);
		}
		return $q;
	}	
	
	//Calculate grid position offset
	function get_grid_pos_top($hour,$minute){
		//Calculate position
		if ($hour<12) {
			$pos_top=floor(($this->dv_grid_vertical_offset+$hour*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight))+(($minute/60)*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight)));
		} else {
			$pos_top=floor(($this->dv_grid_vertical_offset+($hour-12)*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight))+(($minute/60)*($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight)));
		}
		return $pos_top;
	}
	
	//Calculate grid position offset
	function get_grid_pos_left($hour,$minute=0){
		//Calculate position
		if ($hour<12) {
			$pos_left=$this->horizontal_offset+18;
		} else {
			$pos_left=$this->horizontal_offset+$this->dv_grid_column_width+$this->dv_grid_horizontal_column_spacing+18;		
		}
		return $pos_left;
	}
	//--------------------------------------------Master function for all event displays------------------------------------------------------
	
	// $r is 	the full event record
	// $enum is 	to indicate that this is the $enum-th event displayed on the present page (used by js to bring items to the front on mouseover)
	// $mode can be: 
	//		dv_grid, general
	//		It will affect formatting (dv_grid will place the divs in the grid)
	// $content can be:
	//		regular, untimed, punctual, journal, full_raw, bb_next_upcoming_event
	//		It will affect what information from the event record is displayed.
	//		Note, however, that the event record $r has already been retrieved - this function does not select events.
	// $fs is	font size in %. Used to scale the billboard
	// $delete_link_id and $edit_link_id will simply be included as attributs in the link (for billboard keyboard shortcuts)
	// $nolinks 	deactivates the display of [x] and [e]
	
	function display_event ($r,$enum,$mode="general",$content="regular",$fs=100,$delete_link_id="",$edit_link_id="",$nolinks=false){
		//Actually, if $content is "regular", we can find out ourselves whether it's untimed, punctual, or journal.
		if ($content=="regular"){
			if ($r["duration"]==0) {
				$content="punctual";
			}
			if ($r["untimed"]){
				$content="untimed";
			}
			if ($r["cat1"]=="journal") {
				$content="journal";
			}
		}
		
		//------------------------DEFAULT PARAMETERS  (may be overwritten by a given mode)-------------------------------------
		//Style info
		// See if the background for this event is governed by a color rule
		if (!$background=$this->ll->get_bgcolor_for_event($r)){	
			$background="#EEEEEE"; //If not, default to this;
		}
		$hovercolor="white";
		$padding_left="1px";	
		$border="1px solid #DDD";
		$position="relative";
		$top="auto";
		$left="auto";
		$width="100%";
		$font_size=(10*($fs/100))."px"; //MASTER FONT-SIZE - everything else is relative to this
		$z_Index=$enum;
		$overflow='auto';
		$height='auto';	
		$notes_size="80%";
		$notes_color="black";
		$categorycolor="#006000";
		$toplinecolor="#444444";

		//Content defaults
		$person="";
		//---if person is present, get abbreviated name
		if ($person=$this->ll->get_person_abbreviated_displayname($r["person"])) {
			$person=" (".$person.")";
		}		
		$expense="";
		//---if expense or deposit is present, display green or red
		if ($r["expense"]!=0){
			if ($r["expense"]>0){
				//expense
				$expense=$this->getVline()."<span style='color:red;'>";
			} else {
				//deposit
				$expense=$this->getVline()."<span style='color:green;'>";
			}
			$expense.="$".number_format(abs($r["expense"]),2)."</span>";
		}
		$priority="";
		//---if priority is higher than normal, indicate that
		if ($r["priority"]>3){
			$pstl="color:red;"; //Red for higher than normal
			if ($r["priority"]>4) {//For critical priority, use blink
				$pstl.="text-decoration:blink";
			}
			$priority=$this->getVline()."Pr.: <span style='$pstl'>".$this->ll->get_priority_label($r["priority"])."</span>";
		}	
		//How far away is the event? (only do that if no priority indication is shown, and no expense, or else too much info)
		$distance="";
		if (($priority=="") && ($expense=="")) {
			if ((isPast($r["timestamp"])) && (isFuture($r["timestamp"]+$r["duration"]))){
				//It's the current event. Give percentage.
				$percent=100-((($r["timestamp"]+$r["duration"]-time())/$r["duration"])*100);
				if ($mode=="dv_grid"){
					$distance=$this->getVline()."".round($percent)."% <span style='text-decoration:blink;color:#00CC00;'>*</span>";
				} else {
					$distance=$this->getVline()."".round($percent)."%";				
				}
			} else {
				//Not current event
				if (isPast($r["timestamp"])) {
					$acc="-";
					$xx=$r["duration"]; //This will be subtracted from distance (to the past) so that the distance is to the end of the event and not to its beginning.
				} else {
					$acc="+";
					$xx=0;
				}
				$distance=$this->getVline().$acc.getHumanReadableLengthOfTime(abs($r["timestamp"]-time())-$xx);			
			}
		}
		$notes=htmlentities($r["notes"],ENT_QUOTES);
		//---if notes are longer than default length, shorten respectively
		if (strlen($notes)>min($this->default_notes_length,$this->dv_gridentry_notes_length)) {
			//dv_grid mode
			if ($mode=="dv_grid") {			
				$notes=substr($r["notes"],0,$this->first_space_after_length($r["notes"],$this->dv_gridentry_notes_length))."...";
			} else {
				$notes=substr($r["notes"],0,$this->first_space_after_length($r["notes"],$this->default_notes_length))."...";
			}
		}
		
		//Links for edit and delete
		//---If this is a post-it, the edit link must go to the post-it-form, not the eventform
		if (($r["cat1"]=="notes") && ($r["cat2"]=="post-it")){
			$editform="edit_postit";
		} else {
			$editform="edit_event";
		}
		//Nightview?
		$linkcolor=$this->get_transition_color("#AA0000","#6666CC",$this->ll->nightview_percent());
		
		$links="";
		if (!$nolinks){
			$links="<a id='$edit_link_id' style='color:$linkcolor;' href='".sid()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&page=record&form=$editform&id=".$r["id"]."&goto=".$_GET["page"]."&navto=".$_GET["navto"]."'>[e]</a>
			<a id='$delete_link_id' style='color:$linkcolor;' href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&dbaction=delete&table=ll2_events&id=".$r["id"]."&navto=".$_GET["navto"]."'>[x]</a> ";
		} else {
			//Instead of links we'll display the actual date
			$links=date("d/m/Y",$r["timestamp"]);
		}
			

		//--------------FORMATTING ACCORDING TO $mode------------------------------------------------
		if ($mode=="dv_grid"){
			//------------------------Determine position, height and width in grid------------------------
			//Get hour and minute for positioning
			$hour=date("H",$r["timestamp"]);
			$minute=date("i",$r["timestamp"]);
			$pos_top=$this->get_grid_pos_top($hour,$minute);
			$pos_left=$this->get_grid_pos_left($hour,$minute)-1; //-1 to make more room for emails 
			//Calculate height depending on duration
			$height=floor(($r["duration"]/3600)*$this->dv_grid_hour_height);
			//Set minimum height
			if ($height<$this->dv_timedentry_minimumheight) { $height=$this->dv_timedentry_minimumheight; }
			//Is $pos_top + divheight higher than the column?
			if (($pos_top+$height)>($this->dv_col_height+$this->dv_grid_vertical_offset)){
				$height=(($this->dv_grid_hour_height+$this->dv_grid_hour_borderheight)*12)+$this->dv_grid_vertical_offset-$pos_top;
			}
			//However, if $height is now flatter than minimum height make it higher again but begin to display higher up, respectively
			if ($height<$this->dv_timedentry_minimumheight){
				//Display higher up
				$pos_top=$pos_top-($this->dv_timedentry_minimumheight-$height);
				$height=$this->dv_timedentry_minimumheight;
			}
			//Write remaining formatting data ($height has just been set)
			$top=$pos_top."px";
			$left=$pos_left."px";
			$width=($this->dv_grid_column_width-30)."px"; //Really only -24, but create some extra overlap space for emails
			$position="absolute";
			$height=$height."px";
			//If the event is close AND THERE IS NO CURRENT EVENT add "start now" link
			if ((($r["timestamp"]-time()<3*HOUR) && (isFuture(($r["timestamp"])))) && ( !is_Array($this->ll->get_current_event()) )){
				$links.="<a style='color:$linkcolor;background:#D0FFD0;' href='".me()."&processform=startnow&table=ll2_events&id=".$r["id"]."&navto=".$_GET["navto"]."'>[start]</a>";
			}
			//If its the current event, add "finish now" link. Also frame red.
			if ((isPast($r["timestamp"])) && (isFuture(($r["timestamp"]+$r["duration"])))){
				$links.=" <a style='color:white;background:#FF0000;' href='".me()."&processform=finishnow&table=ll2_events&id=".$r["id"]."&navto=".$_GET["navto"]."'>[finish]</a>";
				$border="1px solid red";
			}
			//Add extend/contract links in any case in dv_grid mode
			$links.=" <a style='color:$linkcolor;' href='".me()."&processform=contract&id=".$r["id"]."&navto=".$_GET["navto"]."'>[-]</a>
				    <a style='color:$linkcolor;' href='".me()."&processform=extend&id=".$r["id"]."&navto=".$_GET["navto"]."'>[+]</a>";
		} elseif ($mode=="billboard"){
			//This is used for the next-upcoming-events screen
			 //During daytime, category and notes use the $Background color (from the colors table, indicating category) on black bg
			$categorycolor=$this->get_transition_color($background,"#2828EE",$this->ll->nightview_percent());
			$notes_color=$categorycolor;
			$hovercolor="#333";
			$background="black";
			$toplinecolor=$this->get_transition_color("#CCCCCC","#2266CC",$this->ll->nightview_percent());
			if (isSameDay($r["timestamp"],time())){ 
				$border="1px dotted red"; //Put red border around those events that are still today	
			} else {
				$border="1px solid black";
			}
		}
		
		
		//--------------CONTENT ACCORDING TO $content-----------------------------------------------
		$contentcode="DEFAULT";
		if ($content=="regular") {
			$contentcode="
				<span style='color:$toplinecolor; font-size:100%;'>"
					.$links.$this->getVline().date("H:i",$r["timestamp"])."-".date("H:i",$r["timestamp"]+$r["duration"]).$distance.
					$this->getVline().getHumanReadableLengthOfTime($r["duration"]).$expense.$priority."
				</span>
				<br />
				<span style='color:$categorycolor;'>
					".$this->getDeepestCategories($r,2).$person."
				</span>
				<br />
				<span style='font-size:$notes_size; color:$notes_color;'>".nl2br($notes)."</span>
			";
		} elseif ($content=="punctual") {
			//This is the same as regular, just without duration
			$contentcode="
				<span style='color:$toplinecolor; font-size:100%;'>"
					.$links.$this->getVline().date("H:i",$r["timestamp"]).$distance.
					$expense.$priority."
				</span>
				<br />
				<span style='color:$categorycolor;'>
					".$this->getDeepestCategories($r,2).$person."
				</span>
				<br />
				<span style='font-size:$notes_size; color:$notes_color;'>".nl2br($notes)."</span>
			";		
		} elseif ($content=="untimed") {
			//This is again the same as regular, just not start/end time
			//If no duration is given, then also no duration
			$durationinfo=$this->getVline().getHumanReadableLengthOfTime($r["duration"]);
			if ($r["duration"]==0){
				$durationinfo="";
			}			
			$contentcode="
				<span style='color:$toplinecolor; font-size:100%;'>"
					.$links.$durationinfo.
					$expense.$priority."
				</span>
				<br />
				<span style='color:$categorycolor;'>
					".$this->getDeepestCategories($r,2).$person."
				</span>
				<br />
				<span style='font-size:$notes_size; color:$notes_color;'>".nl2br($notes)."</span>
			";
		} elseif ($content=="journal") {
			$notes_size="100%"; //Custom note size for journal		
			$contentcode="
				<span style='color:$toplinecolor; font-size:100%;'>".$links.$this->getVline().date("H:i",$r["timestamp"])."</span>"
				.$this->getVline()."
				<span style=''>".$this->getDeepestCategories($r,2).$person."</span>
				<br />
				<span style='font-size:$notes_size;color:#000080;'>".nl2br($notes)."</span>
			";
		} elseif ($content=="post-it") {
			//Postit has of course the yellow background (during the day)
			$background=$this->get_transition_color("#FFFF80","#303090",$this->ll->nightview_percent()); 
			$headlinecolor="orange"; //And an orange headline "P O S T - I T"
			$headlinetext="POST-IT-NOTE";
			if ($r["priority"]==4) {
				$background="#FFCC80";
				$headlinecolor="yellow";
			} elseif ($r["priority"]>4) {
				$background="#FFBB80";	
				$headlinecolor="red";
				$headlinetext="<span style='text-decoration:blink'>IMPORTANT</span>";
			}
			$notes_size="100%";	   //Custom notes size for postit
			$contentcode="
				<span style='color:$toplinecolor; font-size:100%;'>".$links."</span><span style='color:$headlinecolor; letter-spacing:1px;'>$headlinetext</span>
				<br />
				<span style='font-size:$notes_size; color:#DC4c4c;'>".nl2br($notes)."</span>
			";
		
		} elseif ($content=="bb_next_upcoming_event") {
			$background=$this->get_transition_color($background,"#2828EE",$this->ll->nightview_percent());
			$notes_color=$background; //Show notes and categories with the db-color for the type of event since the bg is always black
			$category_color=$background;
			$datetimecolor=$this->get_transition_color("#FFAAAA","#5533CC",$this->ll->nightview_percent());
			$dicolor=$this->get_transition_color("#CCCCCC","#2828EE",$this->ll->nightview_percent());
			$background="";
			$notes_size="100%";
			$border="none";
			//Does event have a duration?
			$durtext="";
			if ($r["duration"]>0){
				$durtext="-".date("H:i",$r["timestamp"]+$r["duration"]).$this->getVline().getHumanReadableLengthOfTime($r["duration"]);
			}
			$n=strpos($notes,chr(13)); //Is there at least 1 return in the notes? Then show only that line, and add ...
			if ($n>0){
				$n=min(200,$n); //Allow no more than 150 characters if the return is further out than that
				$notes=substr($notes,0,$n)."...";
			} else {
				if (strlen($notes)>200){
					$notes=substr($notes,0,200)."..."; //Shorten to 150 chars if there's no return
				}
			}
			$contentcode="
				$topline
				<span style='color:$datetimecolor; font-size:150%;'>"
					.date("l, F j, Y",$r["timestamp"]).", "
					.date("H:i",$r["timestamp"]).
					$durtext.$expense.$priority."
				</span>
				<br />
				<span style='color:$category_color; font-weight:bold; font-size:150%'>
					".$this->getDeepestCategories($r,2).$person."
				</span>
				<br />
				<span style='font-size:$notes_size; color:$notes_color;'>".$notes."</span>
			";
		}
		
		
		//-------------PUTTING IT ALL TOGETHER---------------------------------------------------------
		//JavaScript for mouse events
		//	Generally: when the div scrolls enlarge to fullsize when moused over and go back to defined size on mouse out.
		//	Also bring to top on mouseover and set zindex back to $enum on mouseout
		// However, no js in billboard contents.
		if (substr($content,0,2)!="bb"){
			$js_mouseover="javascript:	{
									this.style.zIndex='200';
									this.style.background='$hovercolor';
									if (this.clientHeight<this.scrollHeight) {
										this.style.height=this.scrollHeight+'px';
									}
								}";
			$js_mouseout="javascript:	{
										this.style.height='$height';
										this.style.zIndex='$enum';
										this.style.background='$background';
								}";
		} else {
			$js_mouseover="";
			$js_mouseout="";
		}
		$eventcode= "<div
				onMouseOver=\"".$js_mouseover."\"
				onMouseOut=\"".$js_mouseout."\"			
				style='
					padding-left:$padding_left; 
					border:$border;
					overflow:$overflow;
					position:$position;
					top:$top;
					left:$left;
					height:$height;
					width:$width;
					font-size:$font_size;
					background:$background;
					z-Index:$enum;
					text-align:left;'
					>$contentcode
				</div>";
		return $eventcode;
	}


	//Create code for the red out-mark and the sleep mark. Assumes that all time variables reference the same day
	function draw_out_mark($hr_out,$min_out,$hr_in,$min_in,$color="#E00",$caption=""){
		$result="";
		//If we need to go across noon, execute recursive call to draw left half first
		if (($hr_out<12) && ($hr_in>=12)){
			$result.=$this->draw_out_mark($hr_out,$min_out,11,59,$color);
			//After the recursive call we only need to draw the 2nd col, so adjust hr/min_out to noon
			$hr_out=12;
			$min_out=0;
		} 
		//Not going across noon (anymore), so simply draw in one column
		$divtop=$this->get_grid_pos_top($hr_out,$min_out);
		$divleft=$this->get_grid_pos_left($hr_out)-5;
		$divheight=max($this->get_grid_pos_top($hr_in,$min_in)-$divtop,2);
		$result.="<div style='position:absolute; top:".$divtop."px; left:".$divleft."px; width:2px; height:".$divheight."px; background:$color;'></div>";
		$result.="<div style='position:absolute; top:".$divtop."px; left:".($divleft+4)."px; width:100px; height:".$divheight."px;'>$caption</div>";
		return $result;
	}

	//Write the weather info $w (array) into the dayview. Timestamp in $w determines position
	function draw_weather_info($w){
		$r="";
		$hour=date("H",$w["timestamp"]);
		$minute=date("m",$w["timestamp"]);
		$top=$this->get_grid_pos_top($hour,$minute);
		$left=$this->get_grid_pos_left($hour);
		$r.="<div style='z-index:180;padding-left:2px;padding-right:2px;font-size:9px;color:#999999;background:white;border:1px solid #CCCCCC;position:absolute;top:".$top."px;left:".$left."px;'>".$w["temperature"]."&deg;C, ".$w["sky_condition"]."</div>";		
		return $r;
	}

	//Produce the event divs that are layered onto the 24h grid
	function dv_produce_daygrid_events($time){
		$r="";
		//Select this day's events (with a minimum duration, excluding journals, untimed events, posits)
		//Include possible past event that stretches into this day
		$query="SELECT * from ll2_events WHERE
				timestamp+duration>".getBeginningOfDay($time)."
				and timestamp<".getEndOfDay($time)."
				and duration>0
				and cat1!='notes'
				and untimed=false
				and active=true 
				ORDER BY timestamp
				;";
		if ($res=$this->ll->db($query)){
			//Place each event
			$enum=100; //This counter is for zIndex in the Grid. Offset at 100 to make sure it won't interefere with events in cols 3 or 4 
			while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
				$enum++;
				//If event begins today, return event code
				if (isSameDay($q["timestamp"],$time)){
					$r.=$this->display_event($q,$enum,"dv_grid","regular",108);
				}		
				//In any case, see if the event needs spaceholder(s) [will be the case if it started before (noon) this day and ends this day]
				$r.=$this->dv_create_spaceholder($q,$time);
			}
		}
		//Check email table and place this day's emails as symbols with links
		if ($emails=$this->ll->get_this_days_emails($time)){
			$height=24; //Height of the email info div
			$background_read="#6A6"; //Read msg bgcolor
			$background_unread="#F44"; //Unread msg bgcolor
			$hovercolor="blue";//Hover-bg
			$enum=150; //For zIndex
			$last_left=0;
			$last_top=0;
			foreach ($emails as $key=>$value){
				$enum++;
				if ($value["isread"]){
					$background=$background_read; //The actual bgcolor
				} else {
					$background=$background_unread; //The actual bgcolor				
				}
				
				$this_left=($this->get_grid_pos_left(date("H",$value["email_timestamp"]),date("i",$value["email_timestamp"]))+($this->dv_grid_column_width-190));
				$this_top=$this->get_grid_pos_top(date("H",$value["email_timestamp"]),date("i",$value["email_timestamp"]));
				if ((($this_top-$last_top)<$height+3) && ($last_left==$this_left)){
					//Ensure that this one won't cover the previous one
					$this_top=$last_top+$height+3;
				}
				
				//Create links
				$links="<a style='color:white;' href='".sid()."&page=email&navto=".$_GET["navto"]."&goto=today&action=showmail&id=".$value["id"]."'>[v]</a> 
					    <a style='color:white;' href='".me()."&navto=".$_GET["navto"]."&dbaction=toggleread&id=".$value["id"]."'>[r]</a> "
				          ."<a style='color:white;' href='".me()."&navto=".$_GET["navto"]."&dbaction=delete&table=ll2_emails&id=".$value["id"]."'>[x]</a>";
				//JS:
				$js_mouseover="javascript:	{
										this.style.zIndex='200';
										this.style.background='$hovercolor';
										if (this.clientHeight<this.scrollHeight) {
											this.style.height=this.scrollHeight+'px';
										}
									}";
				$js_mouseout="javascript:	{
											this.style.height='".$height."px';
											this.style.zIndex='$enum';
											this.style.background='$background';
									}";
				
				
				
				$r.="<div style='
						position:absolute;
						left:".$this_left."px;
						top:".$this_top."px;
						width:170px;
						height:".$height."px;
						background:$background;
						z-index:$enum;
						color:white;
						overflow:auto;
						border:1px solid black;
						font-size:7pt;'
						onMouseOver=\"".$js_mouseover."\"
						onMouseOut=\"".$js_mouseout."\"			
						>
						<span style='color:lime;'>$links</span>
						<span style='color:yellow;'>".$this->ll->get_person_abbreviated_displayname($value["person"])."</span>:
						".$value["subject"]."
					</div>";		
				$last_top=$this_top; //To make sure that the next one won't sit on top of this one
				$last_left=$this_left;
			}
		}
		if ((isToday($time)) || (isPast($time))){
			//Mark flags (sleeping and out) (of course not in the future)
			if ($a=$this->ll->get_statusflags_for_day(array("out_in","sleep_wake","connected_disconnected"),$time)){
				//$a has bool (tinyint) and looks like this: $a[timestamp][flagname]
				$last_out=getBeginningOfDay($time); //This should be obsolete because $a should contain an out mark at 00:00:00 if the day begins with "out"
				$last_sleep=getBeginningOfDay($time);
				foreach ($a as $key=>$value){
						if (isset($value["out_in"])){
							//In-Out Mark
							//We know that at $key (which is the timestamp) the flag $value["out_in"] was either set or revoked (true/false)
							if ($value["out_in"]==1){
								//Out mark at $key
								$last_out=$key; //Save the beginning of this period
							} else {
								//In mark at $key
								$r.=$this->draw_out_mark(date("H",$last_out),date("i",$last_out),date("H",$key),date("i",$key));
							}
						}
				}
				foreach ($a as $key=>$value){
						//The same thing for sleep/wake - except draw in green
						if (isset($value["sleep_wake"])){
							if ($value["sleep_wake"]==1){
								$last_sleep=$key;
							} else {
								//Retrieve the length of the sleep period to indicate in dayview, $Key contains the timestamp.
								$length_of_period=$this->ll->get_flag_distance_to_previous($key,"sleep_wake");
								if ($length_of_period>HOUR*2){
									//If this was a long sleep period, we'll subtract the time it takes to fall asleep
									$caption="<span style='margin-left:3px;color:#88A; font-size:8pt;'>Sleep ca. ".getHumanReadableLengthOfTime($length_of_period-$this->ll->param_retrieve_value("SLEEP_TRANSITION_TIME","STATUSFLAGS")).
										"<br><span style='margin-left:3px;font-size:7pt;'>(".date("H:i",$key-$length_of_period)."-".date("H:i",$key).")</span></span>";
								} else {
									$caption="<span style='margin-left:3px;color:#88A; font-size:8pt;'>Nap: ".getHumanReadableLengthOfTime($length_of_period).
										"<br><span style='margin-left:3px;font-size:7pt;'>(".date("H:i",$key-$length_of_period)."-".date("H:i",$key).")</span></span>";							
								}
								$r.=$this->draw_out_mark(date("H",$last_sleep),date("i",$last_sleep),date("H",$key),date("i",$key),"#AAF",$caption);
							}
						}
				}
				foreach ($a as $key=>$value){
						//The same thing for disconnected/connected - orange
						if (isset($value["connected_disconnected"])){
							if ($value["connected_disconnected"]==0){
								$last_sleep=$key;
							} else {
								//Retrieve the length of the sleep period to indicate in dayview, $Key contains the timestamp.
								$length_of_period=$this->ll->get_flag_distance_to_previous($key,"connected_disconnected");
								$caption="<span style='margin-left:3px;color:#88A; font-size:8pt;'>WAN Downtime ".getHumanReadableLengthOfTime($length_of_period).
									"<br><span style='margin-left:3px;font-size:7pt;'>(".date("H:i",$key-$length_of_period)."-".date("H:i",$key).")</span></span>";
								$r.=$this->draw_out_mark(date("H",$last_sleep),date("i",$last_sleep),date("H",$key),date("i",$key),"orange",$caption);
							}
						}
				}
			}
			//Weather info in the grid
			//Get info for 3h,9h,15h,21h
			for ($i=3;$i<25;$i=$i+6){
				//Do we have weather info here?
				if ($w=$this->ll->get_weather_info(getBeginningOfDay($time)+($i*HOUR))){
					//Yes, display it.
					$r.=$this->draw_weather_info($w);
				}				
			}
		}
		
		
		/*
		//Find in/out and mark times accordingly		
		$query="SELECT timestamp,cat3 FROM ll2_events WHERE cat1='notes' AND cat2='in-out' AND timestamp>=".getBeginningOfDay($time)." AND timestamp<=".getEndOfDay($time).";";
		if ($res=$this->ll->db($query)){
			if ($e=mysql_fetch_array($res)){ //At least one record found.
				$last_ts=getBeginningOfDay($time); //Init last timestamp
				do {
					if ($e["cat3"]=="in"){
						//now last_ts carries the out-stamp, and $e["timestamp"] is in.
						//No issue even if the first record is an out mark (bc $last_ts then is 00:00)
						$r.=$this->draw_out_mark(date("H",$last_ts),date("i",$last_ts),date("H",$e["timestamp"]),date("i",$e["timestamp"]));
					} else {
						$last_ts=$e["timestamp"]; //Save the timestamp of the out event for reference next time
					}
					$last_record=$e;
				} while ($e=mysql_fetch_array($res));
				//We need to look at the last record: if it's an "out" note we need to draw from wherever we are to the end of the day (or NOW if we are on today)
				if ($last_record["cat3"]=="out"){
					if (!isToday($time)){
						//We are dealing with a previous day, so the out mark goes to the end of the day
						$r.=$this->draw_out_mark(date("H",$last_record["timestamp"]),date("i",$last_record["timestamp"]),23,59);		
					} else {
						//We are on today, and since we don't have prophetic gifts, we only draw the out mark until time().
						$r.=$this->draw_out_mark(date("H",$last_record["timestamp"]),date("i",$last_record["timestamp"]),date("H",time()),date("i",time()));							
					}
				}
			}
		}
		*/
		return $r;
	}

	//Produce the third column which has birthdays (except todays' if $time is today) other untimed events, and timed punctual events
	function dv_produce_third_column($time){	
		$r="";
		$r.="<div id='dv_third_col' 
			style='left:".($this->horizontal_offset+($this->dv_grid_column_width*2)+($this->dv_grid_horizontal_column_spacing*2))."px;
				top:".$this->dv_grid_vertical_offset."px;
				height:".$this->dv_col_height."px;
				width:".($this->dv_grid_column_width-1)."px;
				background:#FFFFFF;
				border-right:1px dotted gray;'
			>";
		//This day's birthdays
		$birthdays="";
		$birthdays_array=$this->ll->get_next_birthdays(0,$time);
		if (count($birthdays_array)>0){
			foreach($birthdays_array as $value){
				$birthdays.="<br/>".$value["displayname"];
				if ($value["turning_age"]>0) {
					$birthdays.=" (".$value["turning_age"].")";
				}
			}
			//Delete first br tag
			$birthdays=substr($birthdays,5);
			$r.=$this->getDisplayHeader("Birthdays")."<div style='margin-left:5px;'>$birthdays</div>";
		}
		//This day's post-its. Do NOT show those with indefinite validity b/c they occur in the 4th col anyways
		$postits="";
		//Show nothing if this day is today, because then all post-its are in the 4th col anyways
		if (!isToday($time)){
			$query="SELECT * from ll2_events WHERE
					timestamp>=".getBeginningOfDay($time)."
					and timestamp<=".getEndOfDay($time)."
					and cat1='notes'
					and cat2='post-it'
					and duration>0
					and active=true 
					ORDER BY created_at
					;";
			if ($res=$this->ll->db($query)){
				//Place each event
				while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
					//Couch each event in another div because width=100% breaks out of the parent!
					$postits.="<div style='padding-left:5px; width:96%'>
							".$this->display_event($q,0,"general","post-it")."</div>";
				}
				if ($postits!="") {
					$postits=$this->getDisplayHeader("Post-it notes").$postits;
					$r.=$postits;
				}
			}
		}
		//This day's untimed events 
		$untimedevents="";
		$query="SELECT * from ll2_events WHERE
				timestamp>=".getBeginningOfDay($time)."
				and timestamp<=".getEndOfDay($time)."
				and untimed=true
				and active=true 
				ORDER BY created_at
				;";
		if ($res=$this->ll->db($query)){
			//Place each event
			while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
				//Couch each event in another div because width=100% breaks out of the parent!
				$untimedevents.="<div style='padding-left:5px; width:96%'>
						".$this->display_event($q,0,"general","untimed",108)."</div>";
			}
			if ($untimedevents!="") {
				$untimedevents=$this->getDisplayHeader("Untimed events").$untimedevents;
				$r.=$untimedevents;
			}
		}
		//This day's timed but punctual events 
		$punctuals="";
		$query="SELECT * from ll2_events WHERE
				timestamp>=".getBeginningOfDay($time)."
				and timestamp<=".getEndOfDay($time)."
				and duration=0 and untimed=false
				and cat1!='notes'
				and active=true 
				ORDER BY timestamp
				;";
		if ($res=$this->ll->db($query)){
			//Place each event
			while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
				//Couch each event in another div because width=100% breaks out of the parent!
				$punctuals.="<div style='padding-left:5px; width:96%'>
						".$this->display_event($q,0,"general","punctual",108)."</div>";
			}
			if ($punctuals!="") {
				$punctuals=$this->getDisplayHeader("Punctual events").$punctuals;
				$r.=$punctuals;
			}
		}
		//This day's journals
		$journals="";
		$query="SELECT * from ll2_events WHERE
				timestamp>=".getBeginningOfDay($time)."
				and timestamp<=".getEndOfDay($time)."
				and cat1='notes'
				and cat2='journal'
				and active=true 
				;";
		if ($res=$this->ll->db($query)){
			//Place each event
			while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
				//Couch each event in another div because width=100% breaks out of the parent!
				$journals.="<div style='padding-left:5px; width:96%'>
						".$this->display_event($q,0,"general","journal")."</div>";
			}
			if ($journals!="") {
				$journals=$this->getDisplayHeader("Journal entries").$journals;
				$r.=$journals;
			}
		}
		
		$r.="</div>";
		return $r;
	}

	//Produce the fourth column which has current to do notes
	function dv_produce_fourth_column($time){
		$r="";
		$r.="<div id='dayview_current_todo' 
			style='position:absolute;
				left:904px;
				top:24px;
				height:553px;
				width:187px;
				background:black;'
			>
			";	
		if (isToday($time)){
			//Fourth col for today
			$r.=$this->produce_screen(0,0,187,259,$this->produce_billboard_content("current_notes",$time));		
			//BB msgs higher priority
			$t=$this->produce_billboard_content("current_bb_messages",$time,60);
			if ($t==""){
				$t=$this->produce_billboard_content("alarms",$time,150);			
			}
			if ($t==""){
				$t=$this->produce_billboard_content("flagstatus",$time,150);						
			}
			if ($t==""){
				$t=$this->produce_billboard_content("pct_status",$time,120);						
			}
			$r.=$this->produce_screen(261,0,187,134,$t,'black','img/screen_bg_empty.jpg','none');		
			$r.=$this->produce_screen(397,0,187,134,$this->produce_billboard_content("current_still_image",$time));		
			$r.=$this->produce_screen(533,0,187,20,$this->produce_billboard_content("simple_message_indicator",$time));		
		
		} else {
			//Fourth col for days other than today
		}
		$r.="</div>";
		return $r;
	}

	//Produce the headline for the day view
	function dv_produce_headline($time){
		//The container for the headline
		//Frame container depending on where the day is located
		if (isToday($time)){
			//Today
			$color=$this->getBGColorForWeekday(date("w",$time));
			$textbg="#77A;";
			$clock=" - ".date("H:i",time());
		} elseif (isBeforeToday($time)){
			//Yesterday or before
			$color="#777";		
			$textbg="none;";
			$clock=" (".getHumanReadableLengthOfTime(time()-$time,"d")." ago)"; //How far in the past?
		} else {
			//Tomorrow or later
			$color="black";				
			$textbg="none;";
			$clock=" (+".getHumanReadableLengthOfTime(abs(time()-$time-DAY),"d").")"; //How far in the future?
		}
		
		$r="<div id='' style='width:888px;
							height:21px;
							position:absolute;
							top:24px;
							left:".$this->horizontal_offset."px;
							border:1px solid gray;
							background:".$this->getBGColorForWeekday(date("w",$time)).";'>";
							
		$r.="<div style='width:auto; height:21px; font-weight:bold; text-align:center; font-size:16px; position:relative; margin-left:auto; margin-right:auto; color:$color; background:$textbg;'>
			".date("l, F j, Y",$time).$clock."</div>";
		//Close container
		$r.="</div>";
		return $r;
	}

	//Produce the full day view for day $timestamp
	public function produce_day_view($time){
		//Produce headline
		$headline=$this->dv_produce_headline($time);
		//Produce the empty day-grid (hours and quarter-hours, first two columns)
		$daygrid=$this->dv_produce_empty_day_grid($time);
		//Produce events in day-grid
		$daygrid_events=$this->dv_produce_daygrid_events($time);
		//Produce third column
		$col3=$this->dv_produce_third_column($time);
		//Produce fourth column
		$col4=$this->dv_produce_fourth_column($time);
		//Build it together
		$r="<div id='dayview_container'>".$headline.$daygrid.$daygrid_events.$col3.$col4."</div>";
		return $r;
	}
	
	
	//Produce navigation bar for any calendar view. Me is the page identity me()
	public function produce_navbar($time,$me){
		//The container for the nav
		$r="<div id='navbar' style='width:1082px;
							height:15px;
							position:absolute;
							z-index:1;
							top:5px;
							left:".$this->horizontal_offset."px;
							border:1px dotted #CCC;
							letter-spacing:1px;'>";
							//date("l, F d, Y",$time)
		
		//YWMD-navigation
		$r.="<div style='width:245px; font-size:12px; position:absolute; left:0px; color:#BBB;'>";
		$r.="	Navigate: ".$this->getNavLink("y",$me,getBeginningOfYear($time-1))." "
					.$this->getNavLink("m",$me,getBeginningOfMonth($time-1))." "
					.$this->getNavLink("w",$me,(getBeginningOfDay($time)-7*DAY),true)." "
					.$this->getNavLink("d &lt;",$me,getBeginningOfDay($time)-DAY,true)." "
					.$this->getNavLink("&nbsp;today&nbsp;",$me,getBeginningOfDay(time()),true)." "
					.$this->getNavLink("&gt; d",$me,getBeginningOfDay($time)+DAY,true)." "
					.$this->getNavLink("w",$me,(getBeginningOfDay($time)+7*DAY),true)." "
					.$this->getNavLink("m",$me,getBeginningOfNextMonth($time))." "
					.$this->getNavLink("y",$me,getBeginningOfNextYear($time));
		$r.="</div>";
		//Next specific days navigation
		$r.="<div style='width:650px; letter-spacing:3px; text-align:center; font-size:12px; position:absolute; left:250px; color:#BBB;  '>";
		for ($i=1;$i<31;$i++){
			$navto=getBeginningOfDay($time+$i*DAY);
			$label=date("d",$navto);
			$r.=" <span style='letter-spacing:0px;'>".$this->getNavLink($label,$me,$navto,true)."</span>";
		}
		$r.="</div>";
		//Next specific months navigation
		$r.="<div style='width:170px; font-size:12px; position:relative; float:right; color:#BBB; text-align:right;'>";
		for ($i=1;$i<7;$i++){
			$navto=getBeginningOfMonth(mktime(0,0,0,date("m",$time)+$i,date("d",$time),date("Y",$time)));
			$label=date("M",$navto);
			$r.=" ".$this->getNavLink($label,$me,$navto);
		}
		$r.="</div>";
		//Close container
		$r.="</div>";
		return $r;
	}

	//******************************** BILLBOARD CONTENT -******************************************************
	
	//Produce a (billboard) screen
	public function produce_screen($top,$left,$width,$height,$content,$bgcolor='black',$emptybg='img/screen_bg_empty.jpg',$overflow="auto"){
		$bordercolor=$this->get_transition_color("#808080","#222299",$this->ll->nightview_percent()); //Nightview...
		if ($content=="") { $bgcolor="black"; } //If there is not actually content, default to black and the splash picutre
		$r="<div style='
				position:absolute;
				top:".$top."px;
				left:".$left."px;
				width:".($width-4)."px;
				height:".($height-4)."px;
				background:$bgcolor;
				padding:1px;
				border:1px solid $bordercolor;
				color:white;
				text-align:center;
				overflow:$overflow;
			'>";
		if ($content!=""){
			$r.=$content;
		} else {
			//Content is empty, just show logos then
			$imageinfo=getimagesize($emptybg);
			$scaleby=min(($width/$imageinfo[0]),($height/$imageinfo[1]));
			$image_actualwidth=floor($imageinfo[0]*$scaleby*0.95);
			$image_actualheight=floor($imageinfo[1]*$scaleby*0.95);
			
			$image_top=floor(($height/2)-($image_actualheight/2));
			$image_left=floor(($width/2)-($image_actualwidth/2));
			$r.="<img src='$emptybg'
				style='position:absolute;
					width:".$image_actualwidth."px;
					height:".$image_actualheight."px; 
					top:".$image_top."px; 
					left:".$image_left."px; 
					' />";
		}
		$r.="</div>";
		return $r;
	}

	//Produce the source code for the billboard clock with index clocknr. The parameters are stored in $params.
	function produce_clock($clocknr,$time,$fs,$title=true){
		//Retrieve the parameters for this clock
		$clockname=$this->ll->param_retrieve_value("C".$clocknr."_NAME","BILLBOARD");
		$latitude=$this->ll->param_retrieve_value("C".$clocknr."_LATITUDE","BILLBOARD");
		$longitude=$this->ll->param_retrieve_value("C".$clocknr."_LONGITUDE","BILLBOARD");
		$timezone=$this->ll->param_retrieve_value("C".$clocknr."_TIMEZONE","BILLBOARD");
		
		//Night view? -it is important that this line comes before the timezone switch below!
		$color=$this->get_transition_color("#FFFFFF","#3333FF",$this->ll->nightview_percent());
		$background=$this->get_transition_color("#104040","#000000",$this->ll->nightview_percent());
		
		//Temporarily switch to the timezone of the clock
		date_default_timezone_set($timezone);

		//Get Sunrise/Sunset for this location
		$sr=date_sunrise($time, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));
		if (!isFuture($sr)){
			//Sunrise for this day is in the past already. Get tomorrow's.
			$sr=date_sunrise($time+DAY, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));
		}
		$ss=date_sunset($time, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));
		if (!isFuture($ss)){
			//Sunset for this day is in the past already. Get tomorrow's.
			$ss=date_sunset($time+DAY, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));		
		}
		//Only announce the next sun event
		if ($sr<$ss){
			$sunevent="Sunrise in ".getHumanReadableLengthOfTime(abs($sr-$time));
		} else {
			$sunevent="Sunset in ".getHumanReadableLengthOfTime(abs($ss-$time));		
		}
		
		$srout=getHumanReadableLengthOfTime(abs($sr-$time));
		$ssout=getHumanReadableLengthOfTime(abs($ss-$time));
		
		//Get current weather
		$weather=$this->ll->get_current_weather_from_db($clocknr);
		
		//This clock dimmed or not? 
		if ($this->ll->param_retrieve_value("C".$clocknr."_DIM","BILLBOARD")==1){
			$color=$this->brightness($color,0.4);
			$background=$this->brightness($background,0.4);
		} 
		//Depending on whether or not title is required design the output a little different
		if ($title){
			$line1="<span style='font-weight:bold; color:$color; font-size:".($fs*.80)."%'>$clockname</span>";
			$line2="<span style='color:$color; font-size:".($fs*0.65)."%'>".date("l, F j, Y",$time)."</span>";
			if (is_array($weather)){
				$sunevent=$weather["temperature"]."&deg;C - ".$weather["sky_condition"]." - ".$sunevent;
			}
			$line3="<span style='color:$color; font-size:".($fs*0.45)."%'>$sunevent</span>";
			$line4="<span style='color:$color; font-size:".($fs*3.8)."%'>".date("H",$time)."<span style='text-decoration:none;'>:</span>".date("i",$time)."</span>";
		} else {
			$line1="";
			$line2="<span style='color:$color; font-size:".($fs*0.65)."%'>".date("l, F j, Y",$time)."</span>";
			if (is_array($weather)){
				$sunevent=$weather["temperature"]."&deg;C - ".$weather["sky_condition"]." - ".$sunevent;
			}
			$line3="<span style='color:$color; font-size:".($fs*0.45)."%'>$sunevent</span>";
			$line4="<span style='color:$color; font-size:".($fs*3.8)."%'>".date("H",$time)."<span style='text-decoration:none;'>:</span>".date("i",$time)."</span>";
		}
		$r="<div style='width:100%;height:100%;background:$background;overflow:none;'>
			$line1<br/>$line2<br/>$line3<br/>$line4
			</div>";
		
		//Restore calendar timezone
		date_default_timezone_set($this->ll->param_retrieve_value("TIMEZONE","LOCATION"));
		//
		return $r;
	}
	
	function produce_forecast($clocknr,$fs){
		$clockname=$this->ll->param_retrieve_value("C".$clocknr."_NAME","BILLBOARD");

		//Night view? -it is important that this line comes before the timezone switch below!
		$color=$this->get_transition_color("#FFFFFF","#3333FF",$this->ll->nightview_percent());
		$background=$this->get_transition_color("#104040","#000000",$this->ll->nightview_percent());

		//Temporarily switch to the timezone of the selected clock
		$timezone=$this->ll->param_retrieve_value("C".$clocknr."_TIMEZONE","BILLBOARD");
		date_default_timezone_set($timezone);

		//Get weather (forecasts are included here)
		$weather=$this->ll->get_current_weather_from_db($clocknr);
		if (is_array($weather)){
			$forecast="<div style='text-align:left'>"
						."<span style='font-weight:bold;'>".$weather["1daytitle"].":</span><br/>".$weather["1day"]
						."<br/><span style='font-weight:bold;'>".$weather["2daytitle"].":</span><br/>".$weather["2day"]
						."<br/<span style='font-weight:bold;'>".$weather["3daytitle"].":</span><br/>".$weather["3day"]
					."</div>";
		} else {
			$forecast="Forecast not available.";
		}

		$line1="<span style='font-weight:bold; color:$color; font-size:".($fs*.80)."%;'>$clockname</span>";
		$r="<div style='width:100%;height:100%;background:$background;overflow:none;color:$color;'>
			$line1<br/>$forecast
			</div>";
		//Restore calendar timezone
		date_default_timezone_set($this->ll->param_retrieve_value("TIMEZONE","LOCATION"));
		//
		return $r;
	}

	//Return billboard content. $mode can be: "current_notes",...
	//$fs is scale in percent
	function produce_billboard_content($mode,$time,$fs=100){
		$r="";
		switch($mode){
			case "current_notes":
			//Current notes include a number of things: currently valid postits, birthdays, progress displays (if selected) and perhaps the next deadlines
				//Post-its
				$current_notes="";
				$query="SELECT * from ll2_events WHERE active=true AND 
						((cat1='notes' AND cat2='post-it' AND timestamp<".time().")
						AND 
						((timestamp+duration>".time().")
						OR
						(duration=0)))
						ORDER BY priority DESC,created_at
						;";
				if ($res=$this->ll->db($query)){
					//Place each event
					$i=0;
					while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
						$i++; //Counter is for link ids
						//Couch each event in another div because width=100% breaks out of the parent!
						$current_notes.="<div style='width:99%'>
								".$this->display_event($q,0,"general","post-it",$fs,"postit_".$i."_delete","postit_".$i."_edit")."</div>";
					}
					if ($current_notes!="") {
						//$current_notes=$this->getDisplayHeader("Current notes").$current_notes;
						$r.=$current_notes;
					}
				}

				//Birthdays
				$bcd=""; //"Birthdaycode"
				$bdays=$this->ll->get_next_birthdays($this->ll->param_retrieve_value("BIRTHDAYS_ANNOUNCE_DAYS","BILLBOARD"));
				foreach ($bdays as $key=>$value){
					if ($bcd!="") { $bcd.="<br />"; } //Insert new line after each entry
					$bcd.="<span style='font-size:".($fs*.8)."%;'>".$value["displayname"];
					//Turning to age (might not be known -> 0)
					if ($value["turning_age"]>0){
						$bcd.=" (".$value["turning_age"].")";
					}
					$bcd.="</span>";
					//Days out
					if ($value["days_out"]==0) { $bcd.="<span style='color:red; font-size:".($fs*.7)."%;'> today</span>"; }
					elseif ($value["days_out"]==1) { $bcd.="<span style='color:#966; font-size:".($fs*.6)."%;'> tomorrow</span>"; }
					elseif ($value["days_out"]>1) { $bcd.="<span style='color:#AA7; font-size:".($fs*.6)."%;'> in ".$value["days_out"]." days</span>"; }
				}
				$bcd="<div style='width:99%;'>
					<div style='padding-left:1px; 
							border:1px solid #DDD;
							overflow:auto;
							position:relative;
							top:auto;
							left:auto;
							height:auto;
							width:100%;
							background:#004;
							margin-top:2px;
							color:gray;
							text-align:left;'>
							<div style='background:#77A; text-align:center; color:black; font-size:".($fs*.6)."%;'>Upcoming Birthdays</div>
							".$bcd."
					</div></div>";
				if (count($bdays)>0) { $r.=$bcd; } //Only show the bdays div if there actually are any
				//Only do progress info if selected
				if ($this->ll->param_retrieve_value("SHOW_PROGRESS_INFO","BILLBOARD")==1){
					//Progress Display
					$pdc="";
					//Go through all PROGRESS_INFOx variables that are not empty
					$n=1;
					while (($x=$this->ll->param_retrieve_value("PROGRESS_INFO$n","BILLBOARD"))!=""){
						$x=explode(',',$this->ll->param_retrieve_value("PROGRESS_INFO$n","BILLBOARD")); //Get the Data [timestamp1,timestamp2,caption]
						if ($x[0]<$x[1]){
							//The timestamps are in right order
							if ($x[0]<time()){
								//The timespan has begun
								if ($x[1]>time()){
									//The timespan is properly current
									$p_full=$x[1]-$x[0];
									$p_current=time()-$x[0];							
									$percent=number_format(round(($p_current/$p_full)*100,2),2);
									$pdc.="<div style='width:100%;border-top:1px dotted #006633;'><div style='padding-left:1%;width:".($percent-1)."%;background:#330088;white-space:nowrap;font-size:".($fs*.5)."%;'>".$x[2]." $percent% over, ".gethumanreadablelengthoftime($x[1]-time())." left</div></div>";
								} else {
									//The timespan is in the past
									$pdc.="<div style='width:100%;border-top:1px dotted #006633;'><div style='padding-left:1%;background:#330088;white-space:nowrap;font-size:".($fs*.5)."%;'><span style='color:red;text-decoration:blink;'>".$x[2]." COMPLETED</span></div></div>";
								}
							} else {
								//The timespan lies in the future
								$pdc.="<div style='width:100%;border-top:1px dotted #006633;'><div style='padding-left:1%;background:#330088;white-space:nowrap;font-size:".($fs*.5)."%;'><span style='color:magenta;'>".$x[2]." NOT YET BEGUN</span></div></div>";
							}
						}
						$n++;
					}
					
					$pdc="<div style='width:99%;'>
						<div style='padding-left:1px; 
								border:1px solid #DDD;
								overflow:auto;
								position:relative;
								top:auto;
								left:auto;
								height:auto;
								width:100%;
								background:#000;
								margin-top:2px;
								margin-bottom:2px;
								color:#564;
								text-align:left;'>
								<div style='background:#85F; text-align:center; color:black; font-size:".($fs*.6)."%;'>Progress Info</div>
								".$pdc."
						</div></div>";
					$r.=$pdc;
				}
			break;
			case "clock_ni":
				//Produce clock for Noninteractive view (this would be clock1)
				$r.=$this->produce_clock(1,$time,$fs,false);
			break;
			case "clock1":
				//Produce clock according to params in BILLBOARD C1_xx
				$r.=$this->produce_clock(1,$time,$fs);
			break;
			case "clock2":
				//Produce clock according to params in BILLBOARD C2_xx
				$r.=$this->produce_clock(2,$time,$fs);
			break;
			case "clock3":
				//Produce clock according to params in BILLBOARD C3_xx
				$r.=$this->produce_clock(3,$time,$fs);
			break;
			case "forecast1":
				//Produce weather forecast for clock1
				$r.=$this->produce_forecast(1,$fs);				
			break;
			case "forecast2":
				//Produce weather forecast for clock2
				$r.=$this->produce_forecast(2,$fs);				
			break;
			case "forecast3":
				//Produce weather forecast for clock3
				$r.=$this->produce_forecast(3,$fs);				
			break;
			case "simpleclock":
				//The clock for moviemode
				$color=$this->get_transition_color("#FFFFFF","#3333FF",$this->ll->nightview_percent());
				$r="<div style='width:100%;height:100%;background:black;overflow:hidden;'>
					<span style='color:$color; font-size:".($fs*3.8)."%'>".date("H",$time)."<span style='text-decoration:none;'>:</span>".date("i",$time)."</span>	
					</div>";
			break;
			case "simple_message_indicator":
				//For moviemode
				$dotcols=array();
				//Default to green (no alert)
				$dotcols[0]["color"]="#00DF00";
				$dotcols[0]["caption"]="system";
				$dotcols[0]["decoration"]="none";
				$dotcols[1]["color"]="#00DF00";
				$dotcols[1]["caption"]="billboard";
				$dotcols[1]["decoration"]="none";
				$dotcols[2]["color"]="#00DF00";
				$dotcols[2]["caption"]="email";
				$dotcols[2]["decoration"]="none";
				if ($this->ll->systemPanic()>0){
					$dotcols[0]["color"]="#FF8C00"; //Orange
					$dotcols[0]["decoration"]="blink";
				}
				if ($this->ll->systemPanic()>.6){
					$dotcols[0]["color"]="#FF0000";				
					$dotcols[0]["decoration"]="blink";
				}
				if ($this->ll->haveUnreadScreenmessages()){
					$dotcols[1]["color"]="#FF0000";
					$dotcols[1]["decoration"]="blink";
				}
				if ($this->ll->haveUnreadEmails()){
					$dotcols[2]["color"]="#FF0000";
					$dotcols[2]["decoration"]="blink";
				}
				$dmi="";
				$i=0;
				foreach($dotcols as $value){
					$i++;
					$dmi.="<div style='width:20%;height:85%;font-size:".($fs*.35)."%;background:".$value["color"].";text-decoration:".$value["decoration"]."; color:".$this->brightness($value["color"],0.7).";float:left;margin-top:1%;margin-left:10%;'>".strtoupper($value["caption"])."</div>";
				}
				
				$r="<div style='width:100%;height:100%;background:black;overflow:hidden;'>
							$dmi
					</div>";
			break;			
			case "flagstatus":
				$sa=$this->ll->get_status("out_in");
				$sb=$this->ll->get_status("busy_available");
				$sc=$this->ll->get_status("sleep_wake");
				if ($sa || $sb || $sc){
					//At least one of the three flags has been set
					$t=""; //text of notice
					$bgcolor="#040";
					if ($sa) { 
						$since=time()-$this->ll->get_latest_status_change("out_in"); //seconds (int)
						$autolock="";
						if ($this->ll->param_retrieve_value("AUTO_LOCK_ON_AWAY","SYSTEM")==1){
							$lock_in=($this->ll->param_retrieve_value("AUTO_LOCK_GRACE","SYSTEM")-$since);
							if ($lock_in>0){
								$autolock="<br/>Controls will lock in ".$lock_in."s";
							}
						}
						$since="(since ".getHumanReadableLengthOfTime($since).")";
						$t.="<div style='position:relative;width:80%;background:yellow;color:red;margin-left:auto;margin-right:auto;margin-top:2px;border:2px solid red;'>
								<span style='text-decoration:blink;color:red;font-weight:bold;font-size:".($fs*1.6)."%;'>AWAY</span>
								<br/><span style='color:red;font-size:".($fs*.7)."%;'>".$since."$autolock</span>
							</div>";
					}
					if ($sb) {
						$since="(since ".getHumanReadableLengthOfTime(time()-$this->ll->get_latest_status_change("busy_available")).")";
						$t.="<div style='position:relative;width:80%;background:#A33;color:red;margin-left:auto;margin-right:auto;margin-top:2px;border:2px solid red;'>
								<span style='color:#FDD;font-size:".($fs*1)."%;'>BUSY</span>
								<br/><span style='color:#FDD;font-size:".($fs*.5)."%;'>".$since."</span>
							</div>";
					}
					if ($sc) {
						$since="(since ".getHumanReadableLengthOfTime(time()-$this->ll->get_latest_status_change("sleep_wake")).")";
						$t.="<div style='position:relative;width:80%;background:#33A;color:red;margin-left:auto;margin-right:auto;margin-top:2px;border:2px solid red;'>
								<span style='color:#DDF;font-size:".($fs*1)."%;'>SLEEPING</span>
								<br/><span style='color:#DDF;font-size:".($fs*.5)."%;'>".$since."</span>
							</div>";
					}
					$r.="<div style='position:relatve;top:0px;left:0px;width:100%;height:100%;background:$bgcolor;'>
						<div style='background:#070; text-align:center; color:black; font-size:".($fs*.6)."%;'>Active Flags</div>
						$t</div>";
				}
			break;
			case "alarms":
				if ($alarms=$this->ll->get_scheduled_alarms()){
					//If alarms are schedule in the future or currently active, show those
					$t="";
					$bgcolor="#004";
					foreach ($alarms as $v){
						$deletelink="<a style='color:#AAF;' href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&dbaction=delete&table=ll2_alerts&id=".$v["id"]."'>[x]</a>";					
						$d="Scheduled alarm for ".date("D, M d @ H:i:s",$v["timestamp"])." ";
						if ($v["timestamp"]<(time()+5)){
							//Alarm is currently going off. (Announce a few secs early bc of bb refresh latency)
							$d.="<span style='white-space:nowrap;'><span style='text-decoration:blink; color:red;'>ALARM</span> (TTL: ".getHumanReadableLengthOfTime($v["timestamp"]+$v["duration"]-time()).")</span>";
						} else {
							$d.="<span style='white-space:nowrap;'>Due in ".getHumanReadableLengthOfTime($v["timestamp"]-time());
							if ($v["timestamp"]-time()<MINUTE){
								$d.=" [<span style='color:orange;'>".($v["timestamp"]-time())."s</span>]";
							}
							$d.="</span>";
						}
						$t.="<div style='text-align:left; border-bottom:1px dotted gray; color:#AAF; margin:0px; width:100%; font-size:".($fs*.45)."%;'>$deletelink $d</div>";
					}
					$r.="<div style='position:relatve;top:0px;left:0px;width:100%;height:100%;background:$bgcolor;'>
						<div style='background:#06F; text-align:center; color:black; font-size:".($fs*.6)."%;'>Alarms</div>
						$t</div>";					
				}
			break;
			case "next_upcoming_event":
				$query="SELECT * from ll2_events WHERE
						(timestamp+duration>".time().") AND (cat1!='notes') AND (untimed=false) AND (active=true) ORDER BY timestamp ASC LIMIT 1;
						;";
				if ($res=$this->ll->db($query)){
					if ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
						if (((isToday($q["timestamp"])) || ($q["timestamp"]<time()))){
							//The next upcoming event is either still coming up today or currently in progress
							if ($this->ll->nightview_percent()>0.5){
								//If at least 50% nightview, the background for a highlighted (very close or current) upcoming event:
								$bb_next_upcoming_event_highlight="#000038";
								$toplinecolor=$this->get_transition_color("#CCCCCC","#AAAAFF",$this->ll->nightview_percent());
								$toplinebg=$this->get_transition_color("#888888","#5533CC",$this->ll->nightview_percent());
							} else {
								//If no more than 50% (or none at all) nightview, the background for a highlighted (very close or current) upcoming event:
								$bb_next_upcoming_event_highlight="#600000";							
								$toplinecolor=$this->get_transition_color("#FFFFFF","#AAAAFF",$this->ll->nightview_percent());
								$toplinebg=$this->get_transition_color("#555555","#AAAAFF",$this->ll->nightview_percent());
							}
							//HIGHLIGHTED BG?
							$ttime=2*HOUR; //Transition time before event timestamp:
							$tout=$q["timestamp"]-time(); //That's how far the event is still out
							if ($tout<=$ttime){
								$tfactor=1-($tout/$ttime); //Will be a factor bw 0 and 1 now, for transition color determination
							}
							$bgcolor=$this->get_transition_color("#000000","$bb_next_upcoming_event_highlight",$tfactor);
							//Event in the future or in progress?
							if (($q["timestamp"]+$q["duration"])>time() && ($q["timestamp"]<time())){
								//Event in progress
								$time_left=$q["timestamp"]+$q["duration"]-time();
								if ($time_left<(MINUTE*10)){
									$time_left_col="red";
								} elseif ($time_left<(MINUTE*20)) {
									$time_left_col="yellow";					
								}
								
								$percent=100-((($q["timestamp"]+$q["duration"]-time())/$q["duration"])*100);
								$pbarcol=$this->get_transition_color("#00C000","#008000",$this->ll->nightview_percent());
								$topline="<div style='background:$toplinebg;
												border-bottom:1px solid black;
												text-align:left;
												margin-bottom:4px;
												color:$toplinecolor;
												font-size:".($fs*.6)."%;'>
												
												<div style='white-space:nowrap;padding-left:0px;width:".$percent."%;background:$pbarcol;'>
												<span style='padding-left:3px;'>Event in progress (".round($percent)."% done, over in ".getHumanReadableLengthOfTime($q["timestamp"]+$q["duration"]-time()).")</span>
												</div>
												
										</div>";
							} else {
								//Event in the future
								$topline="<div style='background:$toplinebg; border-bottom:1px solid black;text-align:left; margin-bottom:4px; padding-left:3px; color:$toplinecolor; font-size:".($fs*.6)."%;'>Next event coming up in ".getHumanReadableLengthOfTime($q["timestamp"]-time())."</div>";
							}
							$r.="<div style='padding:0px;margin:0px;border:0px;width:100%;height:100%;background:$bgcolor;'>
								$topline
								".$this->display_event($q,0,"","bb_next_upcoming_event",$fs)."</div>";
						} else {
							//No more events scheduled today
							$bgcolor=$this->get_transition_color("#000000","#000000",$this->ll->nightview_percent());
							$color=$this->get_transition_color("#BBBBBB","#404090",$this->ll->nightview_percent());							
							$r.="<div style='
									text-align:left;
									padding:0px;
									margin:0px;
									border:0px;
									width:100%;
									height:100%;
									background:$bgcolor;
									'>
									<span style='color:$color;font-size:".$fs."%'>There are no more events scheduled for today.</span>
								</div>";
						}
					}
				}			
			break;
			case "next_upcoming_events":
			//The next couple of upcoming events (not THE next one)
				$upcoming_events_to_display=12;
				$query="SELECT * from ll2_events WHERE
						(timestamp+duration>".time().") AND (cat1!='notes') AND (active=true) ORDER BY timestamp ASC LIMIT $upcoming_events_to_display
						;";
				$nue=""; //Temp for this text
				if ($res=$this->ll->db($query)){
					//Place each event
					$last_ts=0;
					while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
						if (!isSameDay($q["timestamp"],$last_ts)){
							$color=$this->get_transition_color("#808080","#5533CC",$this->ll->nightview_percent());
							$nue.="<div style='border-bottom:1px solid ".$this->brightness($color,0.6)."; margin-bottom:4px; margin-left:1%;margin-right:1%;margin-top:1px; font-style:italic;font-size:".($fs*0.7)."%; text-align:right; color:$color;'>";
							if (isToday($q["timestamp"])){
								$nue.="Today";
							} elseif (isTomorrow($q["timestamp"])){
								$nue.="Tomorrow (".date("D",$q["timestamp"]).")";
							} else {
								$nue.=date("l, F j",$q["timestamp"]);
							}
							$nue.="</div>";
						}
						//Couch each event in another div because width=100% breaks out of the parent!
						//The border-bottom is to space events apart a tiny bit
						$nue.="<div style='width:99%; border-bottom:2px solid black;'>
								".$this->display_event($q,0,"billboard","regular",$fs)."</div>";
						$last_ts=$q["timestamp"]; //Save this timestamp so that we can detect if the next event is on a different day
					}
					//How many more events are coming up after these?
					$query="SELECT id from ll2_events WHERE
							(timestamp+duration>".time().") AND (cat1!='post-it') AND (cat1!='journal') AND (active=true);
							;";
					$totalupcoming="";
					if ($res=$this->ll->db($query)){
						$totalupcoming=mysql_num_rows($res);
					}
					$nuebg=$this->get_transition_color("#708080","#5533CC",$this->ll->nightview_percent());
					$r.="<div style='overflow:hidden;position:relatve;top:0px;left:0px;width:100%;height:100%;background:$background;'>
					<div style='background:$nuebg; text-align:center; color:black; font-size:".($fs*.8)."%;'>$totalupcoming Upcoming Events</div>
					$nue</div>";								
				}
			break;
			case "current_bb_messages":
			//Messages to the center area of the billboard
				$msg_event=""; //Save event msg in here
				//If the current event has a billboard message, bring it up
				$query="SELECT notes,billboard_text,notes_to_billboard FROM ll2_events
					WHERE (timestamp<=".time()." AND timestamp+duration>".time()." AND cat1!='notes' AND active=true);";
				if ($res=$this->ll->db($query)){
					if ($q=mysql_fetch_array($res)){
						if ($q["notes_to_billboard"]){
							$msg_event=html_entity_decode($q["notes"]);
						} else {
							$msg_event=html_entity_decode($q["billboard_text"]);
						}
						$from="<span style='color:white;'>Message from current event</span>";
					}
				}	

				//But if actual current billboard_msg exists, it takes precedence
				$msg_web=""; //Save webmsg in here
				$deletelink="";
				$q=$this->ll->get_top_screenmessage(); //Get the record of the latest active screen message
				if (is_array($q)){
						$color=$this->get_transition_color("#FFFFFF","#FFFFFF",$this->ll->nightview_percent());
						$background=$this->get_transition_color("#008000","#0000FF",$this->ll->nightview_percent());
						$sender="";
						if ($q["cat5"]!=""){ $sender="(".$q["cat5"].") "; }
						//PINK FOR LAHELA :)
						if ((strpos(strtolower($sender),"lali")) || (strpos(strtolower($sender),"lahela"))){
							$background="pink";
						}
						//MAGENTA FOR CANA:)
						if ((strpos(strtolower($sender),"cana")) || (strpos(strtolower($sender),"cana"))){
							$background="magenta";
						}
						$from="<span style='color:$color; text-decoration:blink;'>Message from web-session: ".$q["cat4"]." ".$sender."at ".date("l, F j, H:i",$q["timestamp"])."</span>";
						$msg_web="<span style='color:$color;'>".html_entity_decode($q["billboard_text"])."</span>";
						$deletelink="<a href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&dbaction=delete&table=ll2_events&id=".$q["id"]."'>[x]</a>";
				}

				//Webmessage takes precendence over current event bb text. Assign accordingly.
				if ($msg_web!=""){ 
					$msg=$msg_web;
				} else {
					$msg=$msg_event;
				}
				
				//Try to scale properly
				$n=strlen($msg);
				$s=$fs*3.1;
				if ($n>75){ $s=$fs*2.1; }
				if ($n>120){ $s=$fs*1.8; }
				if ($n>175){ $s=$fs*1.5; }
				if ($n>235){ $s=$fs*1.2; }
				if (strlen($msg)>0){
					$r.="<div style='background:$background;width:100%;height:100%;'>
							<div style='color:#555; background:brown; text-align:left; font-size:".($fs*0.5)."%;'>
								$deletelink
								$from
							</div>
							<span style='font-size:".$s."%;'>
								$msg
							</span>
						</div>";
				}
			break;
			case "unread_emails":
				$lem="";
				$maxshow=4; //Max # of emails to show
				$blinkstars=""; //Will hold a number of blinking stars for display, depending on unread message count 
				//Get meta-data for unread emails
				if ($emails=$this->ll->get_unread_emails()){
					//Display each
					foreach ($emails as $key=>$e){
					
							$links="<a style='color:white;' href='".sid()."&goto=billboard&page=email&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&action=showmail&id=".$e["id"]."'>[v]</a> 
								   <a style='color:white;' href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&dbaction=toggleread&id=".$e["id"]."'>[r]</a> "
								  ."<a style='color:white;' href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&dbaction=delete&table=ll2_emails&id=".$e["id"]."'>[x]</a> ";
					
					
							$lem.="<div style='font-size:".($fs*.45)."%; border-bottom:1px dotted gray;'>$links<span style='color:white;'>"
								.date("H:i",$e["timestamp"])." - "
								.$this->ll->get_person_abbreviated_displayname($e["person"])
								." - ".getHumanReadableLengthOfTime(time()-$e["email_timestamp"])." ago</span><br/>".$e["subject"]."</div>";
					}
					$blinkstars=strmult("*",min(count($emails),10)); //Create a maximum of 10 blinking stars
				}
				//Display something only if there are in fact recent mails
				if ($lem!=""){
					$lem="<div style='width:100%; height:100%; overflow:none;'>
						<div style='padding-left:0px; 
								border:0px;
								overflow:auto;
								position:relative;
								top:auto;
								left:auto;
								height:100%;
								width:100%;
								background:#200;
								margin-top:0px;
								color:gray;
								text-align:left;'>
								<div style='background:#700; text-align:center; color:black; font-size:".($fs*.6)."%;'><span style='text-decoration:blink; color:red;'>$blinkstars</span>&nbsp;Unread Emails&nbsp;<span style='color:#700;'>$blinkstars</span></div>
								".$lem."
						</div></div>";
					$r.=$lem;
				}
				
			break;
			case "system_status":
				$t=$this->ll->getSystemStatus(true);
			
				$t="<div style='width:100%;font-size:".($fs*.4)."%;color:".$this->get_transition_color("#AAAA77","#3333FF",$this->ll->nightview_percent($time)).";'>$t</div>";
		
				$r.="<div style='position:relative;top:0px;left:0px;width:100%;height:100%;background:$bgcolor;'>
					<div style='background:#06F; text-align:center; color:black; font-size:".($fs*.6)."%;'>System status</div>
					$t</div>";								
			break;
			case "pct_status":
				$t=$this->ll->getPCTstatus();
				
				//$t="<div style='width:100%;font-size:".($fs*.4)."%;color:".$this->get_transition_color("#AAAA77","#3333FF",$this->ll->nightview_percent($time)).";'>$t</div>";
		
				$r.="<div style='position:relative;top:0px;left:0px;width:100%;height:100%;background:$bgcolor;'>
					<div style='background:".$this->get_transition_color("#AAAA77","#551122",$this->ll->nightview_percent($time))."; text-align:center; color:black; font-size:".($fs*.6)."%;'>PowerControl Status</div>
					<div style='position:relative;width:100%;height:98%;top:2%;font-size:".($fs*.35)."%;'>$t</div>
					</div>";								
			break;
			case "live_stats":
				//Livestats active?
				if ($this->ll->param_retrieve_value("SHOW_LIVESTATS","BILLBOARD")){
					//Get category path
					$catpath=$this->ll->param_retrieve_value("CURRENT_CATPATH","LIVESTATS");
					if ($catpath!=""){
						$catpatharray=explode(",",$catpath);
						$uplink="<a href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&processform=cat_ascend'>[move up]</a> ($catpath)";
					} else {
						//Top level
						$catpatharray=array();
						$uplink="select catogory to descend";
					}
					
					//Prepare timespans for columns
					//Today
					$cols[1]["start"]=getBeginningOfDay($time);
					$cols[1]["end"]=$time;
					//This week
					$cols[2]["start"]=getBeginningOfWeek($time);
					$cols[2]["end"]=$time;
					//Last week
					$cols[3]["start"]=getBeginningOfWeek($time-WEEK);
					$cols[3]["end"]=getEndOfWeek($time-WEEK);
					//Last full four weeks (not including this week)
					$cols[4]["start"]=getBeginningOfWeek($time-4*WEEK);
					$cols[4]["end"]=getEndOfWeek($time-WEEK);
					//Time span since set marker
					$cols[5]["start"]=$this->ll->param_retrieve_value("MARKER_TIMESTATS","LIVESTATS");
					if ($cols[5]["start"]==0) { $cols[5]["start"]=1275714000; } //Default to June 4, 2010 (lifelog birthday) if 0
					$cols[5]["end"]=time();
					//--------------Get all the data
					$cols_data=array();
					foreach ($cols as $key=>$value){
						//Perform db requests and store everything in $cols_data[]
						$cols_data[$key]=$this->ll->get_category_durations_for_period($catpatharray,$value["start"],$value["end"]);
					}
					//The sixth column - finances this month 					
					$day_of_month=$this->ll->param_retrieve_value("FINANCESTATS_CUTOFF","LIVESTATS");
					if ($day_of_month==0) { $day_of_month=1; } //In case no cutoff day provided, assume the first day of the month
					//--Are we on/past that day already this month?
					if ((date("j",$time))>=$day_of_month){
						//Yes. So: this same month, earlier day.
						$cols[6]["start"]=mktime(0,0,0,date("n",$time),$day_of_month);
					} else {
						//No. Previous month
						$cols[6]["start"]=mktime(0,0,0,date("n",$time)-1,$day_of_month);
					}
					$cols[6]["end"]=$time;
					$cols_data[6]=$this->ll->get_category_expense_for_period($catpatharray,$cols[6]["start"],$cols[6]["end"]);
					//The seventh column - finances monthly avg since marker 
					$cols[7]["start"]=$this->ll->param_retrieve_value("MARKER_FINANCESTATS","LIVESTATS");
					if ($cols[7]["start"]==0) { $cols[7]["start"]=1275714000; } //Default to June 4, 2010 (lifelog birthday) if 0					
					$cols[7]["end"]=$time;
					$cols_data[7]=$this->ll->get_category_expense_for_period($catpatharray,$cols[7]["start"],$cols[7]["end"]);
	
					//Now aggregate all the categories (it is possible that some are in some elements of $cols_data but not in others
					$categories=array();
					foreach ($cols_data as $value){
						if (is_array($value)){ //This condition is important because if the timespan was empty, get_category_durations returns false
							foreach ($value as $category=>$q){
								if (!in_array($category,$categories)){
									$categories[]=$category;
								}
							}
						}
					}
					sort($categories);
					//All needed categories should be in $categories now, in alphabetical order
					//-------------Format
					//So now we can create an array of tablecells
					$cells=array(); //$cells[col][row]
					for ($i=0;$i<count($categories);$i++){
						//The first col is the category name, linked to descend into subcat
						$cells[0][$i]="<a href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&processform=cat_descend&cat=".urlencode($categories[$i])."'>".$categories[$i]."</a>"; 
						//Go through the first two data columns, where total time is needed
						for($j=1;$j<4;$j++){
							if (isset($cols_data[$j][$categories[$i]])){
								$cells[$j][$i]=number_format($cols_data[$j][$categories[$i]]/HOUR,1)."h"; //All other cols contain data
							} else {
								$cells[$j][$i]="-"; //There was no data for that category in this particular column
							}
						}
						//Go through the third and fourth data columns, where avg weekly time is needed
						for($j=4;$j<6;$j++){
							if (isset($cols_data[$j][$categories[$i]])){
								$weeks=(($cols[$j]["end"]-$cols[$j]["start"])/WEEK); //Calculate the actual number of weeks for this col
								$cells[$j][$i]=number_format($cols_data[$j][$categories[$i]]/HOUR/$weeks,1)."h";
							} else {
								$cells[$j][$i]="-"; //There was no data for that category in this particular column
							}
						}
						//The sixth is finances (since cutoff)
						if (isset($cols_data[6][$categories[$i]])){
							$cells[6][$i]="<span style='color:red;'>$".number_format($cols_data[6][$categories[$i]],2)."</span>";
						} else {
							$cells[6][$i]="-"; //There was no data for that category in this particular column
						}
						//The seventh is finances (monthly avg since marker)
						if (isset($cols_data[7][$categories[$i]])){
							$months=(($cols[7]["end"]-$cols[7]["start"])/(365.25*DAY/12)); //Calculate the actual number of month for this col
							$cells[7][$i]="<span style='color:red;'>$".number_format($cols_data[7][$categories[$i]]/$months,2)."</span>";
						} else {
							$cells[7][$i]="-"; //There was no data for that category in this particular column
						}
						
					}
					//We should have our table ready to display now. We'll do so by going through line by line again.
					$tbl="";
					$dcw=floor(85/count($cells)); //Data-col width percetage
					//First lines
					$tbl.="<tr style='font-style:italic;'>
							<td><table style='width:100%;'><tr>
							<td style='text-align:left;width:15%;'>Category</td>
							<td style='text-align:right;width:$dcw%;'>Today</td>
							<td style='text-align:right;width:$dcw%;'>This Wk</td>
							<td style='text-align:right;width:$dcw%;'>Last Wk</td>
							<td style='text-align:right;width:$dcw%;'>4Wks</td>
							<td style='text-align:right;width:$dcw%;'>".date("M j",$cols[5]["start"])."</td>
							<td style='text-align:right;width:$dcw%;'>Exp. ".date("M",getBeginningOfMonth($time))."</td>
							<td style='text-align:right;width:$dcw%;'>Exp. Mthly</td>
							</tr></table></td>
						</tr>";
					$tbl.="<tr style='font-size:80%;'>
							<td><table style='width:100%;'><tr>
							<td style='text-align:left;width:".(15+2*$dcw)."%;colspan=3;'>$uplink</td>
							<td style='text-align:right;width:$dcw%;'></td>
							<td style='text-align:right;width:$dcw%;'>weekly</td>
							<td style='text-align:right;width:$dcw%;'>weekly</td>
							<td style='text-align:right;width:$dcw%;'>(from ".date("M d",$cols[6]["start"]).")</td>
							<td style='text-align:right;width:$dcw%;'>(".date("Y/m/d",$cols[7]["start"]).")</td>
							</tr></table></td>
						</tr>";
					for($i=0;$i<count($categories);$i++){
						//First col with category name
						$line="<td style='text-align:left;width:15%;'>".$cells[0][$i]."</td>";
						for($j=1;$j<count($cells);$j++){
							$line.="<td style='text-align:right;width:$dcw%;'>".$cells[$j][$i]."</td>";
						}
						if ($i%2==0){ $bg=$this->get_transition_color("#333333","#000055",$this->ll->nightview_percent($time)); } else { $bg=""; }
						$tbl.="<tr><td><table style='width:100%;background:$bg;'><tr>$line</tr></table></td></tr>";
					}
					if ($catpath==""){
						//Add Flaginfo on top level only
						//Get duration from status-flags (sleep)
						$grace=$this->ll->param_retrieve_value("SLEEP_TRANSITION_TIME","STATUSFLAGS");
						$lastnight=($this->ll->get_flag_duration_for_period("sleep_wake",getBeginningOfDay($time),$time));
						if ($lastnight>$grace) { $lastnight=$lastnight-$grace; }
						$lastnight=getHumanReadableLengthOfTime($lastnight);
						//This week
						$thisweek=$this->ll->get_flag_duration_for_period("sleep_wake",getBeginningOfWeek($time),$time);
						$numdays=($time-getBeginningOfWeek($time))/DAY;
						$thisweek=($thisweek/$numdays);
						if ($thisweek>$grace) { $thisweek=$thisweek-($numdays*$grace); }
						$thisweek=getHumanReadableLengthOfTime($thisweek);
						//Last week
						$lastweek=getHumanReadableLengthOfTime(($this->ll->get_flag_duration_for_period("sleep_wake",getBeginningOfWeek($time-WEEK),getEndOfWeek($time-WEEK))/7)-$grace);
						//Last week
						$lastweek=getHumanReadableLengthOfTime(($this->ll->get_flag_duration_for_period("sleep_wake",getBeginningOfWeek($time-WEEK),getEndOfWeek($time-WEEK))/7)-$grace);
						//Four weeks
						$fourweeks=getHumanReadableLengthOfTime(($this->ll->get_flag_duration_for_period("sleep_wake",getBeginningOfWeek($time-WEEK*4),getEndOfWeek($time-WEEK))/28)-$grace);
						$tbl.="<tr style='font-style:italic;'>
								<td><table style='width:100%;'><tr>
								<td style='text-align:left;width:15%;'>Flags</td>
								<td style='text-align:right;width:$dcw%;'>Today</td>
								<td style='text-align:right;width:$dcw%;'>This Wk</td>
								<td style='text-align:right;width:$dcw%;'>Last Wk</td>
								<td style='text-align:right;width:$dcw%;'>4Wks</td>
								<td style='text-align:right;width:$dcw%;'></td>
								<td style='text-align:right;width:$dcw%;'></td>
								<td style='text-align:right;width:$dcw%;'></td>
								</tr></table></td>
							</tr>";
						$tbl.="<tr style=''>
								<td><table style='width:100%;background:#112222;'><tr>
								<td style='text-align:left;width:15%;'>Sleep</td>
								<td style='text-align:right;width:$dcw%;'>$lastnight</td>
								<td style='text-align:right;width:$dcw%;'>$thisweek</td>
								<td style='text-align:right;width:$dcw%;'>$lastweek</td>
								<td style='text-align:right;width:$dcw%;'>$fourweeks</td>
								<td style='text-align:right;width:$dcw%;'></td>
								<td style='text-align:right;width:$dcw%;'></td>
								<td style='text-align:right;width:$dcw%;'></td>
								</tr></table></td>
							</tr>";
					}
					$ls="<table style='color:".$this->get_transition_color("#FFFFFF","#9999FF",$this->ll->nightview_percent($time)).";width:100%;text-align:center;font-size:".($fs*.25)."%;'>$tbl</table>";
					/**/
					if ($ls!=""){
						$r.="<div style='position:relatve;top:0px;left:0px;width:100%;height:100%;background:".$this->get_transition_color("#022111","#000022",$this->ll->nightview_percent($time)).";'>
							<div style='background:".$this->get_transition_color("#888855","#00493A",$this->ll->nightview_percent($time))."; text-align:center; color:black; font-size:".($fs*.6)."%;'>Live Statistics</div>
							$ls</div>";										
					}
				}
			break;
			case "current_event_progress":
				//Progressbar if there is a current event
				if ($q=$this->ll->get_current_event()){
					//Event in progress
					$percent=100-((($q["timestamp"]+$q["duration"]-time())/$q["duration"])*100);
					$pbarcol=$this->get_transition_color("#008000","#006000",$this->ll->nightview_percent());
					$person="";
					//---if person is present, get abbreviated name
					if ($person=$this->ll->get_person_abbreviated_displayname($q["person"])) {
						$person=" (".$person.")";
					}		
					//Colors and nightview
					if ($this->ll->nightview_percent()>0.5){
						//If at least 50% nightview, the background for a highlighted (very close or current) upcoming event:
						$bb_next_upcoming_event_highlight="#000038";
						$toplinecolor=$this->get_transition_color("#CCCCCC","#AAAAFF",$this->ll->nightview_percent());
						$toplinebg=$this->get_transition_color("#888888","#3311AA",$this->ll->nightview_percent());
					} else {
						//If no more than 50% (or none at all) nightview, the background for a highlighted (very close or current) upcoming event:
						$bb_next_upcoming_event_highlight="#600000";							
						$toplinecolor=$this->get_transition_color("#FFFFFF","#AAAAFF",$this->ll->nightview_percent());
						$toplinebg=$this->get_transition_color("#555555","#AAAAFF",$this->ll->nightview_percent());
					}
					$time_left_col=$toplinecolor;
					$time_left=$q["timestamp"]+$q["duration"]-time();
					if ($time_left<(MINUTE*10)){
						$time_left_col="#FF0055";
					} elseif ($time_left<(MINUTE*20)) {
						$time_left_col="yellow";					
					}
					$topline="<div style='background:$toplinebg;
									border-bottom:1px solid black;
									text-align:left;
									margin-bottom:4px;
									color:$toplinecolor;
									height:100%;
									font-size:".($fs*1.9)."%;'>
									
									<div style='position:absolute;top:0px;left:0px;white-space:nowrap;padding-left:0px;width:".$percent."%;height:100%;background:$pbarcol;float:none;'></div>
									<div style='position:absolute;top:1%;left:0px;padding-left:5px;'>".$this->getDeepestCategories($q,2).$person." </div>
									<div style='position:relative;top:1%;padding-right:5px;color:$time_left_col;text-align:right;float:right;'>".getHumanReadableLengthOfTime($time_left)." left</div>
									</div>
									
							</div>";	
					$r.=$topline;
				}
			break;
			case "controls_lock":
				$r="
						<div style='width:100%;height:100%;background:red;'>
							<span style='font-size:".($fs*2.8)."%;'>CONTROLS LOCKED</span>
						</div>
					";
			
			break;
			case "pin_input":
				$pin=strmult("*",strlen($this->ll->param_retrieve_value("PIN_INPUT","SYSTEM")));
				if ($pin!=""){
					$r="
							<div style='width:100%;height:100%;background:#500;color:#C00;'>
								<span style='font-size:".($fs*2.8)."%;'>$pin</span>
							</div>
						";
				}
			break;	
			case "current_still_image": //Display webcam live image
				$liveimage='../webservices/live/cam1.jpg';
				if (!file_exists($liveimage)){
					$liveimage='../live/cam1.jpg';				
				}
				if (file_exists($liveimage)){
					$imagedate=filemtime($liveimage); //When created?
					if ((time()-$imagedate)<60){
						//Don't show old pictures
						$r="<div style='position:absolute;left:1%;height:99%;width:99%;overflow:hidden;text-align:center;'><img style='width:100%;' src='$liveimage'/></div>";
						$r.="<div style='float:right;background:#333333;position:relative;overflow:hidden;font-size:".($fs*.7)."%;'>LiveView as of ".date("M d, Y H:i:s", $imagedate)."</div>";
					}
				}
			break;
			case "cam0": //Display live image from all cams
				$r=$this->generate_cam_code(0,$fs);
			break;
			case "cam1": //Display webcam live image
				$r=$this->generate_cam_code(1,$fs);
			break;
			case "cam2": //Display webcam live image
				$r=$this->generate_cam_code(2,$fs);
			break;
			case "cam3": //Display webcam live image
				$r=$this->generate_cam_code(3,$fs);
			break;
			case "cam4": //Display webcam live image
				$r=$this->generate_cam_code(4,$fs);
			break;
			default:
			break;
		}
		return $r;
	}


	//Generates the code for webcam display in BB. Cam nr 0 = overview of cams 1-4
	function generate_cam_code($camnr,$fs=100){
		$r="";
		if ($camnr>0){
			$toplinecolor=$this->get_transition_color("#FFFFFF","#AAAAFF",$this->ll->nightview_percent());
			//Cam number specified (1-4)
			$liveimage='../webservices/live/cam'.$camnr.'.jpg';
			if (!file_exists($liveimage)){
				$liveimage='../live/cam'.$camnr.'.jpg';				
			}
			if (file_exists($liveimage)){
				$imagedate=filemtime($liveimage); //When created?
				if ((time()-$imagedate)<60){
					//Don't show old pictures
					$r="<div style='position:absolute;left:1%;height:99%;width:99%;overflow:hidden;text-align:center;'><img style='width:100%;' src='$liveimage'/></div>";
					$r.="<div style='float:left;background:#333333;position:relative;overflow:hidden;font-size:".($fs*.7)."%;color:$toplinecolor;'>Cam $camnr - ".date("M d, Y H:i:s", $imagedate)."</div>";
				} else {
					$r="<div style='position:absolute;left:1%;height:99%;width:99%;overflow:hidden;text-align:center;'><img style='width:100%;' src='$liveimage'/></div>";
					$r.="<div style='float:left;background:#333333;position:relative;overflow:hidden;font-size:".($fs*.7)."%;color:$toplinecolor;'>Cam $camnr <span style='color:red;text-decoration:blink;'>OLD PICTURE</span></div>";
				}
			} else {
					$r="<div style='position:absolute;left:1%;height:99%;width:99%;overflow:hidden;text-align:center;'></div>";
					$r.="<div style='float:left;background:#333333;position:relative;overflow:hidden;font-size:".($fs*.7)."%;color:$toplinecolor;'>Cam $camnr no data</div>";
			}
		} else {
			//Cam 0 = compound image
			$r="<div style='position:relative;left:0%;width:50%;height:50%;background:black;'>".$this->generate_cam_code(1,$fs)."</div>";
			$r.="<div style='position:absolute;left:50%;top:0%;width:50%;height:50%;background:black;'>".$this->generate_cam_code(2,$fs)."</div>";
			$r.="<div style='position:absolute;left:0%;width:50%;top:50%;height:50%;background:black;'>".$this->generate_cam_code(3,$fs)."</div>";
			$r.="<div style='position:absolute;left:50%;top:50%;width:50%;height:50%;background:black;'>".$this->generate_cam_code(4,$fs)."</div>";
		}
		return $r;
	}

	//Put the values in an array
	function get_screen_array($top,$left,$width,$height,$content,$bgcolor="#000000",$overflow="auto"){
		$x=array();
		$x["top"]=$top;
		$x["left"]=$left;
		$x["width"]=$width;
		$x["height"]=$height;
		$x["content"]=$content;
		$x["bgcolor"]=$bgcolor;
		$x["overflow"]=$overflow;
		return $x;
	}

	//Produce the billboard view for $timestamp, scaled to $width and $height
	//$view = standard means the usual interactive billboard. "noninteractive" is another view.
	public function produce_billboard($width,$height,$time,$view="standard"){
		//Calculate item scale
		//Define standard measurments:
		$bb_std_width=1250;
		$bb_std_height=780;
		//fs ratio (for item scaling) we would use on a standard size screen
		$standard_fs=200; 
		//For font/item-scaling, use width and disregard height
		$fs_scaleby=$width/$bb_std_width; //Ratio of actual width to standard width
		//The actual scaling factor is determind by the product of the standard scale and the ratio of current width to standard width;
		$fs=$standard_fs*$fs_scaleby;
		//Contents
		$screens=array();
		//
		$r="";
		
		if($this->ll->param_retrieve_value("MOVIEMODE","BILLBOARD")==0){
			//NO MOVIE MODE (normal)
			if ($view=="standard"){		
				//STANDARD VIEW
				//**********************************TOP************LEFT************WIDTH**********HEIGHT
				//Top left
				$screens[1]=$this->get_screen_array(($height*0.005),($width*0.0025),($width*0.745),($height*0.24),$this->produce_billboard_content("next_upcoming_event",$time,$fs),"black","none");
				//Top right
				$screens[2]=$this->get_screen_array(($height*0.005),($width*0.7525),($width*0.245),($height*0.59),$this->produce_billboard_content("next_upcoming_events",$time,$fs*0.75),"black","none");
				//right middle
				$screens[9]=$this->get_screen_array(($height*0.60),($width*0.7525),($width*0.245),($height*0.145),$this->produce_billboard_content("pct_status",$time,$fs),"black","none");
				//Left middle
				$screens[3]=$this->get_screen_array(($height*0.25),($width*0.0025),($width*0.245),($height*0.495),$this->produce_billboard_content("current_notes",$time,$fs));
				//Center
				$screens[4]=$this->get_screen_array(($height*0.25),($width*0.2525),($width*0.495),($height*0.495),$this->produce_billboard_content("live_stats",$time,$fs*1.7));
				if ($this->ll->param_retrieve_value("STILL_IMAGE_TO_BB","BILLBOARD")){
					$camnr=$this->ll->param_retrieve_value("STILL_IMAGE_CAM_NR","BILLBOARD");
					$screens[4]=$this->get_screen_array(($height*0.25),($width*0.2525),($width*0.495),($height*0.495),$this->produce_billboard_content("cam$camnr",$time,$fs*0.5),"black","none");				
				}
				//Bottom left. centerleft, centerright, right
				$screens[5]=$this->get_screen_array(($height*0.75),($width*0.0025),($width*0.245),($height*0.245),"","black","none");
				if ($this->ll->param_retrieve_value("SHOW_SYSTEM_STATUS","BILLBOARD")){
					$screens[5]=$this->get_screen_array(($height*0.75),($width*0.0025),($width*0.245),($height*0.245),$this->produce_billboard_content("system_status",$time,$fs));
				}
				//Clocks or weather forecast, if activated
				if ($this->ll->param_retrieve_value("SHOW_WEATHER_FORECAST","BILLBOARD")){
					$screens[6]=$this->get_screen_array(($height*0.75),($width*0.2525),($width*0.245),($height*0.245),$this->produce_billboard_content("forecast3",$time,$fs*1.1),"black","none");
					$screens[7]=$this->get_screen_array(($height*0.75),($width*0.5025),($width*0.245),($height*0.245),$this->produce_billboard_content("forecast2",$time,$fs*1.1),"black","none");
					$screens[8]=$this->get_screen_array(($height*0.75),($width*0.7525),($width*0.245),($height*0.245),$this->produce_billboard_content("forecast1",$time,$fs*1.1),"black","none");
				} else {
					$screens[6]=$this->get_screen_array(($height*0.75),($width*0.2525),($width*0.245),($height*0.245),$this->produce_billboard_content("clock3",$time,$fs*1.1),"black","none");
					$screens[7]=$this->get_screen_array(($height*0.75),($width*0.5025),($width*0.245),($height*0.245),$this->produce_billboard_content("clock2",$time,$fs*1.1),"black","none");
					$screens[8]=$this->get_screen_array(($height*0.75),($width*0.7525),($width*0.245),($height*0.245),$this->produce_billboard_content("clock1",$time,$fs*1.1),"black","none");
				}
				
				//HIGHER PRIORITY CONTENTS
				//Screen 5
				$t=$this->produce_billboard_content("unread_emails",$time,$fs);
				if ($t!=""){
					//There are unread emails. Override screen 5
					$screens[5]=$this->get_screen_array(($height*0.75),($width*0.0025),($width*0.245),($height*0.245),$t);			
				}
				//Screen 4
				$t=$this->produce_billboard_content("current_bb_messages",$time,$fs);
				if ($t!=""){
					//There are current messages. Override screen 4
					$screens[4]=$this->get_screen_array(($height*0.25),($width*0.2525),($width*0.495),($height*0.495),$t);		
				}			
				//Screen 6
				$t=$this->produce_billboard_content("alarms",$time,$fs);
				if ($t!=""){
					//The flagstatus screen is not empty, so override screen 6
					$screens[6]=$this->get_screen_array(($height*0.75),($width*0.2525),($width*0.245),($height*0.245),$t);
				}
				//Screen 7
				$t=$this->produce_billboard_content("flagstatus",$time,$fs); //Any scheduled/current alarms?
				if ($t!=""){
					//The alarm screen is not empty, so override screen 7
					$screens[7]=$this->get_screen_array(($height*0.75),($width*0.5025),($width*0.245),($height*0.245),$t);
				}
				
				//The PANIC status is top priority
				if ($this->ll->systemPanic()>.6){
					$screens[4]=$this->get_screen_array(($height*0.25),($width*0.2525),($width*0.495),($height*0.495),$this->produce_billboard_content("system_status",$time,$fs*2),"black","none");	
				}
				
			} elseif ($view=="noninteractive") {
				//NON-INTERACTIVE VIEW
				//First see if there are flags set
				$t=$this->produce_billboard_content("flagstatus",$time,$fs*2); 
				//Alarms override flags
				$s=$this->produce_billboard_content("alarms",$time,$fs*2.2);
				if ($s!="") {$t=$s;}
				
				if ($t==""){
					//No flags or alarms
					//Order of priority here: (Highest) Screenmessage, Email(s), Clock
					$t=$this->produce_billboard_content("current_bb_messages",$time,$fs*1.7);
					if ($t==""){
						$t=$this->produce_billboard_content("unread_emails",$time,$fs*3.6);
					}
					if ($t!=""){
						$screens[1]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.995),($height*0.995),$t);
					} else {
						//If the clock is due to be shown, we'll also add a progress for the current event, if applicable
						if ($w=$this->ll->get_current_event()){
							//We have to here find out at some point whether or not this is a social event and whether or not the pbar should be displayed
							$screens[1]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.995),($height*0.895),$this->produce_billboard_content("clock_ni",$time,$fs*4),"black","none");									
							$screens[2]=$this->get_screen_array(($height*0.9025),($width*0.0025),($width*0.995),($height*0.095),$this->produce_billboard_content("current_event_progress",$time,$fs),"black","none");									
						} else {
							$screens[1]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.995),($height*0.995),$this->produce_billboard_content("clock_ni",$time,$fs*4),"black","none");			
						}
						//$screens[4]=$this->get_screen_array(($height*0.7525),($width*0.0025),($width*0.995),($height*0.245),$this->produce_billboard_content("",$time,$fs));
					}
				} else {
					//Flags or alarms present. In this case create two screens side by side
					$screens[2]=$this->get_screen_array(($height*0.2525),($width*0.0025),($width*0.495),($height*0.495),$t);
					//Order of priority here: (Highest) Screenmessage, Email(s), Clock
					$t=$this->produce_billboard_content("current_bb_messages",$time,$fs*0.85);
					if ($t==""){
						$t=$this->produce_billboard_content("unread_emails",$time,$fs*1.8);
					}
					if ($t!=""){
						$screens[1]=$this->get_screen_array(($height*0.2525),($width*0.5025),($width*0.495),($height*0.495),$t);
					} else {
						$screens[1]=$this->get_screen_array(($height*0.2525),($width*0.5025),($width*0.495),($height*0.495),$this->produce_billboard_content("clock_ni",$time,$fs*2.0),"black","none");			
					}
					//Also create empty screens top/botom
					$screens[3]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.995),($height*0.245),$this->produce_billboard_content("",$time,$fs));
					$screens[4]=$this->get_screen_array(($height*0.7525),($width*0.0025),($width*0.995),($height*0.245),$this->produce_billboard_content("",$time,$fs));
					
				
				}
				//If webcam still is active for NI, this takes priority over (almost) all the other stuff
				if ($this->ll->param_retrieve_value("STILL_IMAGE_TO_NIBB","BILLBOARD")){
					$screens=array(); //Delete and start over
					$camnr=$this->ll->param_retrieve_value("STILL_IMAGE_CAM_NR","BILLBOARD");					
					$screens[99]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.995),($height*0.995),$this->produce_billboard_content("cam$camnr",$time,$fs),"black","none");			
				}
				//The PANIC status is top priority
				if ($this->ll->systemPanic()>.6){
					$screens=array();
					$screens[100]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.995),($height*0.995),$this->produce_billboard_content("system_status",$time,$fs*4),"black","none");	
				}
				
			} elseif ($view=="mobile"){
				$screens[1]=$this->get_screen_array(($height*0.00),($width*0.00),($width*1),($height*1),$this->produce_billboard_content("pct_status",$time,$fs*4),"black","none");
			
			}
		
		} else {
			//MOVIE MODE (all views)
			
			$screens[0]=$this->get_screen_array(($height*0.0025),($width*0.0025),($width*0.7475),($height*0.995),$this->produce_billboard_content("",$time,$fs*1.1),"black","none");
			$screens[1]=$this->get_screen_array(($height*0.0025),($width*0.7525),($width*0.245),($height*0.075),$this->produce_billboard_content("simple_message_indicator",$time,$fs*1.1),"black","none");
			$screens[2]=$this->get_screen_array(($height*0.0825),($width*0.7525),($width*0.245),($height*0.17),$this->produce_billboard_content("simpleclock",$time,$fs*1.1),"black","none");
			
			$screens[3]=$this->get_screen_array(($height*0.2575),($width*0.7525),($width*0.245),($height*0.74),$this->produce_billboard_content("",$time,$fs*1.1),"black","none");

		}
		//Independent of view, display "locked" message if system is locked
		if ($this->ll->isLocked()){
			$screens[99]=$this->get_screen_array(($height*0.15),($width*0.15),($width*0.70),($height*0.12),$this->produce_billboard_content("controls_lock",$time,$fs),"white","none");
			$t=$this->produce_billboard_content("pin_input",$time,$fs,"white","none");
			if ($t!=""){
				//User has begun to enter pin
				$screens[98]=$this->get_screen_array(($height*0.30),($width*0.30),($width*0.40),($height*0.08),$t,"","none");	
			}
		}
		
		//Now screen data is in $screens[1-8]
		//Background for empty screen depends on nightview applicability
		if ($this->ll->is_nightview()){
			$emptybg='img/screen_bg_empty_nightview.jpg';
		} else {
			$emptybg='img/screen_bg_empty.jpg';		
		}
		foreach ($screens as $v){
				$r.=$this->produce_screen($v["top"],$v["left"],$v["width"],$v["height"],$v["content"],$v["bgcolor"],$emptybg,$v["overflow"]);
		}
		
		return "<div style='background:black; position:relative; margin-left:auto; margin-right:auto; width:".$width."px; height:".$height."px;'>$r</div>";
	}


}
?>