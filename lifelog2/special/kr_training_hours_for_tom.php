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


	//


	$s->p("<h2>King Road Tech Training Project 2015</h2><h4>Dear Tom, please choose a billing period.</h3>");
	for ($i=date("n")+1;$i>=4;$i--){
		$start_ts=mktime(0,0,0,$i-1,20,2015);
		if ($i==4){
			$start_ts=mktime(0,0,0,3,16,2015);
		}
		$end_ts=mktime(0,0,0,$i,20,2015)-1;
		$unfinished="<span style=\"color:green;\">(completed)</span>";
		if ($end_ts>time()){
			$unfinished="<span style=\"color:red;\">(currently open)</span>";
		}
		$s->p("<a href=\"?start_ts=$start_ts&end_ts=$end_ts\">Period ending ".date("F j",mktime(0,0,0,$i,20,2015))."</a> $unfinished<br>");
	}

	$last_month=date("n")-1;
	$year_of_last_month=date("Y");
	if ($last_month==0){
		$last_month=12;
		$year_of_last_month--;
	}
	
	$start_ts=$_GET["start_ts"];
	$end_ts=$_GET["end_ts"];
        
	$allevents="";
	$week=0;
	$grandtotal=0;
	$thisweek=-1;
	
	

	$out="";
	$db_end_ts=$end_ts;
	if ($end_ts>time()){
		//Do not display future events if in current period
		$db_end_ts=time();
	}
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>$start_ts AND timestamp<=$db_end_ts AND cat1='work' AND cat2='misc' AND cat3='KR volunteer training' AND active=true AND duration>0)
					ORDER BY timestamp ASC
				;")){
		$q=0;
		$total=0;
		$s->p("<h4>Just for you, Tom: training hours from ".date("F j",$start_ts)." to ".date("F j",$end_ts)."</h4>");
		while($r=mysql_Fetch_array($res)){
			$q++;
			$total+=$r["duration"];
			if ($q%2){
				$style="style=\"background:#DDD;\"";
			} else {
				$style="style=\"\"";
			}
			$out.="<tr $style><td>".date("F j, Y",$r["timestamp"])."</td><td>".date("H:i",$r["timestamp"])." - ".date("H:i",$r["timestamp"]+$r["duration"])."</td><td>".($r["duration"]/3600)."h</td><td style=\"width:50%\">".$r[notes]."</td></tr>";
		}
		if ($out==""){
			$s->p("<span style=\"background:yellow\">No hours have been registered yet in this billing period</span>");
		} else {}
		$s->p("<table style=\"border-collapse:collapse\">$out</table>");
		if ($db_end_ts==$end_ts){
			$s->p("<h4>And the total, Tom, in a carefree one-liner:</h4>");
			$s->p("<span style=\"background:yellow\">Total ".date("F j, H:i",$start_ts)." to ".date("F j, H:i",$end_ts).": ".($total/3600)." hours</span>");
		} else {
			$s->p("<h4>To avoid confusion, Tom, we will only show the total once the period is complete.</h4>".(($end_ts-$db_end_ts)/3600/24)." day(s) left until end of billing period!");
		}
		
		
	}




	$s->flush();

?>
