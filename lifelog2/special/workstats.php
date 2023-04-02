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


  $totaltime=0;
  $totalcwtime=0;
  $ref=1314860400; //reference timestamp - beginning of work contract
	$allevents="<table style='border-collapse:collapse;font-size:60%;'>";


	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					timestamp<=".time()." AND timestamp>$ref AND cat1='personal' AND cat2='creative project' AND cat3='ChurchWeb'
					ORDER BY timestamp DESC
				;")){
		while ($r=mysql_fetch_array($res)){    
         $totalcwtime+=$r["duration"];   	
		}	
	}


	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					timestamp<=".time()." AND timestamp>$ref AND cat1='work' AND cat2='King Road MB'
					ORDER BY timestamp DESC
				;")){
		while ($r=mysql_fetch_array($res)){    
					$allevents.="<tr style='$style;border:1px solid black;vertical-align:top;'>
              <td style='width:60px'>".date("Y/m/d",$r["timestamp"])."</td>
              <td style='width:60px'>".date("H:i",$r["timestamp"])."-".date("H:i",$r["timestamp"]+$r["duration"])."</td>
              <td style='width:100px'>".$r["cat2"]."</td><td>".$r["cat3"]."</td>
							<td style='width:400px'>"
								.nl2br($r["notes"])."
							</td>
						</tr>";	
         $totaltime+=$r["duration"];   	
		}	
	}
	$allevents.="</table>";

  $elapsed=time()-$ref;
  $elapsed_years=($elapsed/(365.25*DAY));
  $total_average=($totaltime/HOUR)/($elapsed/WEEK);
  $time_off_per_year=(11*4.8*HOUR)+(3*24*HOUR);
  $total_average_incl_time_off=(($totaltime+$time_off_per_year)/HOUR)/($elapsed/WEEK);
  $grand_total_weekly_average_hours=(($totaltime+$time_off_per_year+$totalcwtime)/HOUR)/($elapsed/WEEK);
  
	$s->p("<span style='font-size:120%;font-style:italic;'>Work time statistics / Johannes Weber current as of ".date("Y/m/d H:i",time())."</span>
        </br><span style='font-size:80%;'>Powered by LifeLog - A project of <a href='http://www.jowe.de/'>http://www.jowe.de</a></span>
			");
  $s->p("<div style='margin:5px;padding:5px;width:700px;border:1px dotted black;background:#EEE;'>
          <p>Total work time: ".(number_format($totaltime/HOUR,1))." hours since Sep 1, 2011</p>
          <p>Total days elapsed: ".(number_format($elapsed/DAY,1))." days (=".number_format($elapsed_years,2)." years)</p>
          <p>Total average work hours per week (w/o stat holidays and vacation): ".number_format($total_average,1)."</p>
          <p style='font-weight:bold;'>Total average work hours per week (with 11 stat holidays and 3 weeks vacation): ".number_format($total_average_incl_time_off,1)."</p>
          <p style='font-weight:bold;'>ChurchWeb total development time to date: ".number_format($totalcwtime/HOUR,1)." hours (= ".number_format($totalcwtime/HOUR/40,1)." weeks of full time work)</p>          
          <p style='font-weight:bold;'>Grand total weekly average time spent for the church: ".number_format($grand_total_weekly_average_hours,1)." hours</p>
         </div>");
	$s->p("<div style='margin-left:20px;'><p>Details (core tasks, excluding ChurchWeb):</p>$allevents</div>");
	$s->flush();

?>