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



	$allevents="<table style='border-collapse:collapse;font-size:60%;'>";
	//Cayla
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp<=".time()." AND timestamp>1325404800) AND ((cat1='medical') OR ((cat1='recreation') AND (cat2!='walk')))
					ORDER BY timestamp DESC
				;")){
		while ($r=mysqli_fetch_array($res)){
          if ($r["cat2"]=="blood pressure"){
            $r["notes"]=floor($r["value1"])."/".floor($r["value2"]).", pulse: ".floor($r["value3"])." ".$r["notes"];
          }
          if ($r["cat3"]=="Triptan"){
            if (($r["notes"]==50) || ($r["notes"]=="")){
              $r["notes"]="50mg";
            }
          }
          if ($r["cat2"]=="swimming"){
            if ($r["notes"]==""){
              $r["notes"]="500m";
            }
          }
          if ($r["cat2"]=="running"){
            if ($r["notes"]==""){
              $r["notes"]="10k";
            }
          }
          if ($r["cat1"]=="medical"){
            $style="background:#DDD";
          } else {
            $style="background:#FFF";          
          }
    
					$allevents.="<tr style='$style;border:1px solid black;vertical-align:top;'>
              <td style='width:60px'>".date("Y/m/d",$r["timestamp"])."</td>
              <td style='width:40px'>".date("H:i",$r["timestamp"])."</td>
              <td style='width:100px'>".$r["cat2"]."</td><td>".$r["cat3"]."</td>
							<td style='width:300px'>"
								.nl2br($r["notes"])."
							</td>
						</tr>";		
		}	
	}
	$allevents.="</table>";
	$s->p("<span style='font-size:120%;font-style:italic;'>Medical and exercise tracking / Johannes Weber</span>&nbsp;-&nbsp;<span style='font-size:80%;'>Powered by LifeLog - A project of <a href='http://www.jowe.de/'>http://www.jowe.de</a></span>
			");
	$s->p("<div style='margin-left:20px;'>$allevents</div>");
	$s->flush();

?>