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
	$week=0;
	$grandtotal=0;
	$thisweek=-1;
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>1280646000 AND timestamp<=".time()." AND cat1='work' AND cat2='King Road MB' AND active=true)
				OR
					(timestamp>1280646000 AND timestamp<=1283324400 AND cat1='volunteering' AND cat2='King Road MB' AND active=true)
					ORDER BY timestamp DESC
				;")){
		$e_nr=mysql_num_rows($res);
		$lastts=0;
		while ($r=mysql_fetch_array($res)){
			if (!isSameDay($lastts,$r["timestamp"])){
				if (!isSameWeek($lastts,$r["timestamp"])){
					if (isCurrentWeek($r["timestamp"])){
						$allevents.="<div style=margin-left:-20px;margin-top:20px;font-size:150%;'>Current week</div>";					
					} else {
						$allevents.="<div style=margin-left:-10px;color:red;'>Week total: ".getHumanReadableLengthOfTime($week)."</div>";
						if ($thisweek==-1) { $thisweek=$week; }
						$week=0;
						$allevents.="<div style=margin-left:-20px;margin-top:20px;font-size:150%;'>Week of ".date("D, M j",getBeginningOfWeek($r["timestamp"]))." - ".date("D, M j",getEndOfWeek($r["timestamp"]))."</div>";
					}
				}
				if (isToday($r["timestamp"])){
					$allevents.="<div style=margin-left:-10px;'>Today (".date("D, M j",$r["timestamp"]).")</div>";
				} elseif (isYesterday($r["timestamp"])){
					$allevents.="<div style=margin-left:-10px;'>Yesterday (".date("D, M j",$r["timestamp"]).")</div>";
				} else {
					$allevents.="<div style=margin-left:-10px;'>".date("D, M j",$r["timestamp"])."</div>";
				}
			}
			$allevents.=$ll->display_event($r,0,"general","regular",150);
			$lastts=$r["timestamp"];
			$week+=$r["duration"];
			$grandtotal+=$r["duration"];
		}	
	}
	$balance=floor(($grandtotal/HOUR)-(((time()-1280646000)/WEEK)*15));
	$balance=number_format($balance,1);
	if ($balance>=0) {
		$balance="<span style='color:green;'>+$balance</span>";
	} else {
		$balance="<span style='color:red;'>$balance</span>";	
	}

	//Upcoming events
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>".time()." AND cat1='work' AND cat2='King Road MB' AND active=true)
					ORDER BY timestamp 
				;")){
		$ue_nr=mysql_num_rows($res);
		while ($r=mysql_fetch_array($res)){
			$all_upcoming_events.=$ll->display_event($r,0,"general","regular",150);
		}	
	}
	/*  
	$s->p("<div style='padding:3px;margin:3px;color:#55A;width:905px;background:#E8E8FF;margin-bottom:10px;text-align:center;border:1px dotted #55A;'>This live calculation is powered by LifeLog - A project of <a href='http://www.jowe.de'>http://www.jowe.de</a></div><div style='width:700px;font-size:120%;'><span style='font-size:120%;font-style:italic;'>Johannes Weber, King Road time from August 1, 2010</span>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Current as of: ".date("D, M j, Y - H:i:s",time())." PST</p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Grand total: ".number_format($grandtotal/HOUR,1)."h</p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Weeks so far: ".getHumanReadableLengthOfTime(time()-1280646000)."</p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Average time per week: <span style='color:red;'>".number_format(($grandtotal/((time()-1280646000)/WEEK))/HOUR,1)."h</span></p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Current balance (hours): <span style='color:red;'>$balance</span></p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Time this week so far: <span style='color:red;'>".number_format($thisweek/HOUR,1)."h</span></p>
			</div>");
	*/
	$s->p("<div style='padding:3px;margin:3px;color:#55A;width:905px;background:#E8E8FF;margin-bottom:10px;text-align:center;border:1px dotted #55A;'>This live calculation is powered by LifeLog - A project of <a href='http://www.jowe.de'>http://www.jowe.de</a></div><div style='width:700px;font-size:120%;'><span style='font-size:120%;font-style:italic;'>Johannes Weber, King Road time from August 1, 2010</span>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Current as of: ".date("D, M j, Y - H:i:s",time())." PST</p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Grand total: ".number_format($grandtotal/HOUR,1)."h</p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Current balance (hours): <span style='color:red;'>$balance</span></p>
			<p style='margin-left:20px;margin-top:0px;margin-bottom:0px;'>Time this week so far: <span style='color:red;'>".number_format($thisweek/HOUR,1)."h</span></p>
			</div>");
	$s->p("<div style='left:20px;top:230px;position:absolute;margin-left:20px;width:400px;'><div style='color:navy;margin-left:-20px;font-size:20pt;'>Log of past events ($e_nr)</div>$allevents</div>");
	$s->p("<div style='left:480px;top:230px;position:absolute;width:10px;height:5000px;border-left:1px solid black;'></div>");
	$s->p("<div style='left:520px;top:230px;position:absolute;width:400px;'><div style='color:navy;margin-left:-20px;margin-bottom:10px;font-size:20pt;'>Upcoming scheduled events ($ue_nr)</div>$all_upcoming_events</div>");	
	$s->flush();

?>
