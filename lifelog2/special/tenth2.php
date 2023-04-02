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

	//Get timezone from settings/params table
	date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));	

	$allevents="";
	$ae="";
	$threshould_over1=35;
	$threshould_over2=39;
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
					    if (($week_total<29) && (!$is_current_week)){
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
			    if ($r["cat3"]=="STAT HOLIDAY"){
			        $ae.="<div class='event' style='background-color:#DCC'><span style='color:#755'>STATUTORY HOLIDAY (equiv. 6.4h)</span></div>";
			        $statlength=round(60*60*6.4);
			        $week+=$statlength;
			        $grandtotal+=$statlength;
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
		}	
	}
	$balance=floor(($grandtotal/HOUR)-(((time()-1520841600)/WEEK)*32));
	$balance=number_format($balance,1);
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
	
	$s->p("<div style='padding:3px;margin:3px;color:#55A;width:905px;background:#E8E8FF;margin-bottom:10px;text-align:center;border:1px dotted #55A;'>This live calculation is powered by LifeLog - A project of <a href='http://www.jowe.de'>http://www.jowe.de</a></div><div style='width:700px;font-size:120%;'><span style='font-size:120%;font-style:italic;'>Johannes Weber, Tenth Church time from March 12, 2018</span>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Current as of: ".date("D, M j, Y - H:i:s",time())." PST</p>
			<div style='margin:10px;margin-left:20px;border:1px solid green;padding:10px;text-align:center;background:yellow;'>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;font-weight:bold;'>Summary:</p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Grand total: <span style='color:blue;'>".number_format($grandtotal/HOUR,1)." hours</span></p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Average time per week (target: 32): <span style='color:red;'>".number_format(($grandtotal/((time()-1520800000)/WEEK))/HOUR,1)." hours</span></p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Time balance (unpaid/banked hours): <span style='color:red;'>$balance hours</span></p>
			</div>
			<p></p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Time this week so far: <span style='color:red;'>".number_format($thisweek/HOUR,1)."h</span></p>
			</div>");
	$s->p("<div>$ae</div>");
	//$s->p("<div style='left:20px;top:300px;position:absolute;margin-left:20px;width:400px;'><div style='color:navy;margin-left:-20px;font-size:20pt;'>Work Log ($e_nr entries)</div>$allevents</div>");
	//$s->p("<div style='left:480px;top:260px;position:absolute;width:10px;height:5000px;border-left:1px solid black;'></div>");
	//$s->p("<div style='left:520px;top:260px;position:absolute;width:400px;'><div style='color:navy;margin-left:-20px;margin-bottom:10px;font-size:20pt;'>Upcoming scheduled events ($ue_nr)</div>$all_upcoming_events</div>");	
	//$s->flush();

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
    		<div>Grand total (hrs): 
    			<div class='data'>
    				<?php 
    				    echo "<span class='green'>".number_format($grandtotal/HOUR,1)."</span>"; 
    				?>
    			</div>
    		</div>
    		<div>Average time per week (hrs):
    			<div class='data'>
    				<?php
    				    $class="green";
    				    $avg=number_format(($grandtotal/((time()-1520841600)/WEEK))/HOUR,1);
    				    if ($avg>34){
    				        $class="orange";
    				    }
    				    if ($avg>36){
    				        $class="red";
    				    }
    				    echo "<span class='$class'>$avg</span>";
    				?>
    			</div>
    		</div>
    		<div>Time balance (overtime hrs):
    			<div class='data'>
    				<?php
    				    $class="orange";
    				    $balance>=0 ? $vorzeichen="+" : $vorzeichen="-"; 
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