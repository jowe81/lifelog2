<?php


	//Where are the library scripts?
	DEFINE("PATH_TO_LIBRARY","../../lib/");

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
	//Basic processing for lifelog
	require_once "../ll2_basicfns.php"; //This is in the same dir as index.php	
	
	//Create the website object $s
	$s=new jowe_site();
	
	//Create the lifelog database interaction object $ll
	$ll=new jowe_lifelog();
	$calviews=new jowe_lifelog_calendar_views($ll);

	//Used for avg and overtime calc
	function xtime(){
	    //return time(); //Use current timestamp for these calculations (real time)
	    return getBeginningOfDay(time()); //Calculate to begining of today (calculations update only once a day)
	    return getEndOfDay(time()); //Calculate to end of today (calculations update only once a day)
	}
	
	
	//Get timezone from settings/params table
	date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));	
	
	$ts20181101=1541055600; //Timestamp Nov 1, 2018 *40 hour pay until Dec 16*
	$ts20180312=1520841600; //Timestamp Mar 12, 2018
	$ts20181217=1545033600; //Timestamp Dec 17, 2018 *back to 32 hour pay*
	$totalBefore20181217=0;
	$totalBefore20181101=0;
	$balanceBefore20181101=0;
	$balanceBefore20181217=0;

	$allevents="";
	$ae="";	
	$threshould_under=0;
	$threshould_over1=0;
	$threshould_over2=0;
	$week=0;
	$grandtotal=0;
	$thisweek=-1;
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>1520800000 AND timestamp<=".time()." AND cat1='work' AND cat2='Tenth Church' AND active=true)
					ORDER BY timestamp DESC
				;")){
		$e_nr=mysqli_num_rows($res);
		$lastts=0;
		while ($r=mysqli_fetch_array($res)){
		    $is_last_entry=($r["id"]==51353);
		    
		    if (($r["timestamp"]>$ts20181101) && ($r["timestamp"]<$ts20181217)){
		        $threshould_under=37;
		        $threshould_over1=43;
		        $threshould_over2=47;		        
		    } else if ($r["timestamp"]<$ts20181101){
		        $threshould_under=29;
		        $threshould_over1=35;
		        $threshould_over2=39;		        
		    }
		    
			if (!isSameDay($lastts,$r["timestamp"])){
				if (!isSameWeek($lastts,$r["timestamp"])){
					if (isCurrentWeek($r["timestamp"])){
					    $ae.="<div class='week_seperator'>Current week</div>";
						$allevents.="<div style=margin-left:-20px;margin-top:20px;font-size:150%;'>Current week</div>";					
					} else {
					    $week_total=round(($week/HOUR),1);
					    $is_current_week=isSameWeek($lastts,time());
					    $c="bg_green";
					    $graphic="<img src='checkicon.png' alt='regular week'/>";
					    if (($week_total<$threshould_under) && (!$is_current_week)){
					        //$c="bg_under";
					        $graphic="<img src='undericon.png' alt='short week'/>";
					    }
					    if ($week_total>$threshould_over1){
					        $c="bg_orange";
					        $graphic="<img src='over1icon.png' alt='overtime week'/>";
					    }
					    if ($week_total>$threshould_over2){
					        $c="bg_red";
					        $graphic="<img src='over2icon.png' alt='serious overtime week'/>";					        
					    }
					    $is_current_week ? $label="Week to date:" : $label="Week total:";
					    $ae.="<div class='week_total'>$graphic<span class='$c fixed_width_block'>$label ".round(($week/HOUR),1)."h</span></div>";
					    $allevents.="<div style=margin-left:-10px;color:red;'>Week total: ".round(($week/HOUR),1)."h</div>";
						if ($thisweek==-1) { $thisweek=$week; }
						$week=0;
						if (!$is_last_entry){
						  $ae.="<div class='week_seperator'>Week of ".date("l, F j",getBeginningOfWeek($r["timestamp"]))." to ".date("l, F j Y",getEndOfWeek($r["timestamp"]))."</div>";
						}
						$allevents.="<div style=margin-left:-20px;margin-top:20px;font-size:150%;'>Week of ".date("D, M j",getBeginningOfWeek($r["timestamp"]))." - ".date("D, M j",getEndOfWeek($r["timestamp"]))."</div>";
					}
				}
				if (isToday($r["timestamp"])){
				    $ae.="<div class='day_seperator'>Today (".date("l, F j",$r["timestamp"]).")</div>";
					$allevents.="<div style=margin-left:-10px;'>Today (".date("D, M j",$r["timestamp"]).")</div>";
				} elseif (isYesterday($r["timestamp"])){
				    $ae.="<div class='day_seperator'>Yesterday (".date("l, F j",$r["timestamp"]).")</div>";
				    $allevents.="<div style=margin-left:-10px;'>Yesterday (".date("D, M j",$r["timestamp"]).")</div>";
				} else {
				    if (!$is_last_entry){
				        $ae.="<div class='day_seperator'>".date("l, F j",$r["timestamp"])."</div>";
				    }
					$allevents.="<div style=margin-left:-10px;'>".date("D, M j",$r["timestamp"])."</div>";
				}
			}
			if (!$is_last_entry){
			    $eventicon="<img class='event_info_icon' src='churchicon.png'/>";
			    
			    //echo 'bit='.(($word>>$bitNumber)&1);
			    
			    if (($r["value2"]>>0)&1){
			         //first bit (biked)    
			        $eventicon="<img class='event_info_icon' src='bikechurchicon.png'/>";
			    }
			    if (($r["value2"]>>1)&1){
			        //second bit set (off site)
			        $eventicon="<img class='event_info_icon' src='homeicon.png'/>";
			    }
			    if (($r["value2"]>>2)&1){
			        //third bit set (site support)
			        //$eventicon=$r["value2"]."<img class='event_info_icon' src='homeicon.png'/>";
			    }
			    if (($r["value2"]>>3)&1){
			        //fourth bit set (off-site other (pro-D etc))
			        $eventicon="off site";
			    }
			    
			    /*
			    if ($r["value2"]==1) {
			        $eventicon="<img class='event_info_icon' src='bikechurchicon.png'/>"; 
			    }
			    if (($r["value2"]==2) || ($r["cat3"]=="off-site")){
			        $eventicon="<img class='event_info_icon' src='homeicon.png'/>";			        
			    }
			    */
			    
			    if (($r["timestamp"]>$ts20181101) && ($r["timestamp"]<$ts20181217)){
			        //Nov 1 - Dec 16 2018: days off are worth 8 hours
			        $dayLengthHrs=8;
			    } else {
			        //Before Nov 1, 2018 and after Dec 16 days off are worth 6.4 hours
			        $dayLengthHrs=6.4;
			    }
			    
			    
			    if (($r["cat4"]=="STAT HOLIDAY") || ($r["cat4"]=="VACATION") || ($r["cat4"]=="RETREAT") || ($r["cat4"]=="SICK DAY")){
			        $statlength=round(60*60*$dayLengthHrs);
			        $week+=$statlength;
			        $grandtotal+=$statlength;
			        if (($r["timestamp"]>$ts20181101) && ($r["timestamp"]<$ts20181217)){
			             $totalBefore20181217+=$statlength;
			        } else if ($r["timestamp"]<$ts20181101){
			             $totalBefore20181101+=$statlength;
			        }
			            
			    }
			    
			    if ($r["cat4"]=="STAT HOLIDAY"){
			        $ae.="<div class='event' style='background-color:#FEE'><span style='color:#755'>STATUTORY HOLIDAY (equiv. avg. work day: ".$dayLengthHrs."h)</span></div>";
			    } else if ($r["cat4"]=="VACATION"){
			        $ae.="<div class='event' style='background-color:#FEE'><span style='color:#755'>VACATION DAY (equiv. avg. work day: ".$dayLengthHrs."h)</span></div>";
			    } else if ($r["cat4"]=="OVERTIME COMP"){
			        $ae.="<div class='event' style='background-color:#FEE'><span style='color:#755'>OVERTIME COMP</span></div>";
			    } else if ($r["cat4"]=="RETREAT"){
			        $ae.="<div class='event' style='background-color:#EEF'><span style='color:#755'>RETREAT (equiv. avg. work day: ".$dayLengthHrs."h)</span></div>";
			    } else if ($r["cat4"]=="SICK DAY"){
			        $ae.="<div class='event' style='background-color:#FFE'><span style='color:#755'>SICK DAY (equiv. avg. work day: ".$dayLengthHrs."h)</span></div>";
			    } else {			    
        			$ae.="<div class='event'>"
        			    ."   <div class='event_time'>".date("H:i",$r["timestamp"])." - ".date("H:i",$r["timestamp"]+$r["duration"])." | ".getHumanReadableLengthOfTime($r["duration"])."</div><div class='event_cat'>".$calviews->getDeepestCategories($r,3,2)."</div>"
        			    .""
        			    ."   <div class='event_notes'>".nl2br(htmlentities($r["notes"],ENT_QUOTES))."</div>"
        			    .$eventicon    
        			    ."</div>";
			    }
			}
			$lastts=$r["timestamp"];
			$week+=$r["duration"];
			$grandtotal+=$r["duration"];
			
			if (($r["timestamp"]>$ts20181101) && ($r["timestamp"]<$ts20181217)){
			    //Calculate total time from Nov 1 to Dec 16 2018
			    $totalBefore20181217+=$r["duration"];
			} else if ($r["timestamp"]<$ts20181101){
		        //Calculate total time before Nov 1 2018
		        $totalBefore20181101+=$r["duration"];
		    }
			
		}	
	}
	//Overtime calculation		
	$balanceBefore20181101=floor(($totalBefore20181101/HOUR)-((($ts20181101-$ts20180312)/WEEK)*32)); //Overtime before Nov 1 2018
	$balanceBefore20181217=floor(($totalBefore20181217/HOUR)-((($ts20181217-$ts20181101)/WEEK)*40)); //Overtime Nov 1-Dec 16 2018	
	//$balance=floor(($grandtotal/HOUR)-(((time()-$ts20180312)/WEEK)*32));
	$balance=floor($balanceBefore20181101+$balanceBefore20181217);
	
	
	/*
	if ($balance>=0) {
		$balance="<span style='color:red;'>+$balance</span>";
	} else {
		$balance="<span style='color:red;'>$balance</span>";	
	}
	*/

	//Upcoming events
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>".time()." AND cat1='work' AND cat2='Tenth Church' AND active=true)
					ORDER BY timestamp 
				;")){
		$ue_nr=mysqli_num_rows($res);
		while ($r=mysqli_fetch_array($res)){
			//$all_upcoming_events.=$ll->display_event($r,0,"general","regular",150);
		}	
	}
	

?>
<!doctype html>
<html>
	<head>
		<title>Johannes Weber: Work log for Tenth Church</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="worklog.css">
	</head>
	<body>
		<div class="title">
			Johannes' live work log at Tenth
			<div style='float:right;'>Updated <?php echo date("d/m/Y H:i");?></div>
		</div>
		<div class="summary">
			<div class='summary_header'>Summary</div>
    		<div>Mar 12-Oct 31, 2018 (hrs): 
    			<div class='data'>
    				<?php 
    				    echo "<span class='green'>".number_format($totalBefore20181101/HOUR,1)."</span>"; 
    				?>
    			</div>
    		</div>
    		<div>Weekly avg. for period (hrs):
    			<div class='data'>
    				<?php
    				    $class="green";
    				    $avg=number_format(($totalBefore20181101/(($ts20181101-$ts20180312)/WEEK))/HOUR,1);
    				    if ($avg>33){
    				        $class="orange";
    				    }
    				    if ($avg>36){
    				        $class="red";
    				    }
    				    echo "<span class='$class'>$avg</span>";
    				?>
    			</div>
    		</div>
    		<div>Overtime for period (hrs):
    			<div class='data'>
    				<?php
    				    $class="orange";
    				    $balanceBefore20181101>=0 ? $vorzeichen="" : $vorzeichen="-"; 
    				    echo "<span class='$class'>$vorzeichen$balanceBefore20181101</span>";
    				?>
    			</div>
    		</div>
    		<div style='border-top:1px solid #444'>Nov 1-Dec 16, 2018 (hrs): 
    			<div class='data'>
    				<?php 
    				echo "<span class='green'>".number_format($totalBefore20181217/HOUR,1)."</span>"; 
    				?>
    			</div>
    		</div>
    		<div>Weekly avg. for period (hrs):
    			<div class='data'>
    				<?php
    				    $class="green";
    				    $avg=number_format(($totalBefore20181217/(($ts20181217-$ts20181101)/WEEK))/HOUR,1);
    				    if ($avg>42){
    				        $class="orange";
    				    }
    				    if ($avg>44){
    				        $class="red";
    				    }
    				    echo "<span class='$class'>$avg</span>";
    				?>
    			</div>
    		</div>
    		<div>Overtime for period (hrs):
    			<div class='data'>
    				<?php
    				    $class="orange";
    				    $balanceBefore20181217>=0 ? $vorzeichen="" : $vorzeichen="-"; 
    				    echo "<span class='$class'>$vorzeichen$balanceBefore20181217</span>";
    				?>
    			</div>
    		</div>
    		<div style='border-top:1px solid #444'>Grand total (hrs): 
    			<div class='data'>
    				<?php 
    				    echo "<span class='green'>".number_format($grandtotal/HOUR,1)."</span>"; 
    				?>
    			</div>
    		</div>
    		<!-- 
    		<div>Average time per week (hrs):
    			<div class='data'>
    				<?php
    				    /*
    				    $class="green";
    				    $avg=number_format(($grandtotal/((time()-$ts20180312)/WEEK))/HOUR,1);
    				    if ($avg>34){
    				        $class="orange";
    				    }
    				    if ($avg>36){
    				        $class="red";
    				    }
    				    echo "<span class='$class'>$avg</span>";
    				    */
    				?>
    			</div>
    		</div>
    		 -->
    		<div>Overtime total (hrs):
    			<div class='data'>
    				<?php
    				    $class="orange";
    				    $balance>=0 ? $vorzeichen="" : $vorzeichen="-"; 
    				    echo "<span class='$class'>$vorzeichen$balance</span>";
    				?>
    			</div>
    		</div>
    	</div>
    	<div class="data">
			<?php echo $ae;?>
		</div>
	</body>
</html>