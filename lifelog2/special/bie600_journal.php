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



	//Find out the students grade for last week's practice (link to illustration)
	function extract_grade($r) {
		switch ($r["value1"]):
			case 0: $text='Practice last week was not evaluated'; $r="src='noeval.gif' alt='$text' title='$text'"; break;
			case 1: $text='Practice last week was very poor'; $r="src='verypoor.gif' alt='$text' title='$text'"; break;
			case 2: $text='Practice last week was poor'; $r="src='poor.gif' alt='$text' title='$text'"; break;
			case 3: $text='Practice last week was normal'; $r="src='average.gif' alt='$text' title='$text'"; break;
			case 4: $text='Practice last week was good'; $r="src='good.gif' alt='$text' title='$text'"; break;
			case 5: $text='Practice last week was outstanding'; $r="src='great.gif' alt='$text' title='$text'"; break;
		endswitch;
		return "<img $r><br><span style='color:gray;'>".$text."</span>";
	}



	$allevents="<table>";
	//
	$order="DESC";
	if ((isset ($_GET["order"])) && ($_GET["order"] == "ascending")){
		$order ="";
	}
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>=1295337600 AND timestamp<1304146800 AND cat1='notes' AND cat2='journal' AND cat3='devotional' AND active=true)
					ORDER BY timestamp $order
				;")){
		while ($r=mysql_fetch_array($res)){
					$allevents.="<tr>
							<td style='width:150px; padding-top:3px; border-top:1px dotted gray; font-size:13pt; vertical-align:top;'><span style='color:brown;'>"
								.date("D, M d, Y",($r["timestamp"]))."</span><br/>".date("h:i a",($r["timestamp"]))."<br/><span style='color:gray;'>(".gethumanreadablelengthoftime(time()-$r["timestamp"])." ago)</span>
							</td>
							<td style='color:navy;width:800px; padding-top:3px; padding-bottom:15px; padding-left:5px; padding-right:5px; border-top:1px dotted gray;vertical-align:top; font-size:11pt; background:#FFFFCC;'>"
								.nl2br($r["notes"])."
							</td>
						</tr>";		
		}	
	}
	$allevents.="</table>";
	$entries=mysql_num_rows($res);
	$days=floor((time()-1295337600)/DAY);
	$avg=number_format($entries/((time()-1295337600)/WEEK),1);
	$s->p("<span style='font-size:120%;font-style:italic;'>Johannes Weber: BIE600 - Journal as of ".date("Y/m/d, h:i a",time())."</span><br/><span style='font-size:80%;'>Powered by LifeLog - A project of <a href='http://www.jowe.de/'>http://www.jowe.de</a></span>
			<p style='color:red;'>($entries entries over $days days - $avg entries per week)
			</p>
			<p style='font-weight:bold;'>
				You can download a professor-friendly Word-document for: <a href='bie600_create_doc.php?month=1'>January</a> | <a href='bie600_create_doc.php?month=2'>February</a> | <a href='bie600_create_doc.php?month=3'>March</a> | <a href='bie600_create_doc.php'>the entire semester</a>
			</p>
			");
	$s->p("<div style='margin-left:20px;'>$allevents</div>");
	$s->flush();

?>