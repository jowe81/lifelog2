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
	//Cayla
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp<=".time()." AND cat1='friend support' AND cat2='piano lessons' AND person=35 AND active=true)
					ORDER BY timestamp DESC LIMIT 1
				;")){
		while ($r=mysql_fetch_array($res)){
					$allevents.="<tr>
							<td style='width:80px; padding-top:3px; border-top:1px dotted gray; font-size:13pt; vertical-align:top;'>"
								.$ll->get_person_displayname($r["person"])."
							</td>
							<td style='width:80px; padding-top:3px; border-top:1px dotted gray;vertical-align:top; font-size:8pt;'>
							".extract_grade($r)."
							</td>
							<td style='padding-top:3px; padding-bottom:15px; padding-left:5px; padding-right:5px; border-top:1px dotted gray;vertical-align:top; font-size:11pt; background:yellow;'>"
								.nl2br($r["notes"])."
							</td>
						</tr>";		
		}	
	}
	//Lahela
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp<=".time()." AND cat1='friend support' AND cat2='piano lessons' AND person=5 AND active=true)
					ORDER BY timestamp DESC LIMIT 1
				;")){
		while ($r=mysql_fetch_array($res)){
					$allevents.="<tr>
							<td style='width:80px; padding-top:3px; border-top:1px dotted gray; font-size:13pt; vertical-align:top;'>"
								.$ll->get_person_displayname($r["person"])."
							</td>
							<td style='width:80px; padding-top:3px; border-top:1px dotted gray;vertical-align:top; font-size:8pt;'>
							".extract_grade($r)."
							</td>
							<td style='padding-top:3px; padding-bottom:15px; padding-left:5px; padding-right:5px; border-top:1px dotted gray;vertical-align:top; font-size:11pt; background:pink;'>"
								.nl2br($r["notes"])."
							</td>
						</tr>";		
		}	
	}
	//Salome
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp<=".time()." AND cat1='friend support' AND cat2='piano lessons' AND person=34 AND active=true)
					ORDER BY timestamp DESC LIMIT 1
				;")){
		while ($r=mysql_fetch_array($res)){
					$allevents.="<tr>
							<td style='width:80px; padding-top:3px; border-top:1px dotted gray; font-size:13pt; vertical-align:top;'>"
								.$ll->get_person_displayname($r["person"])."
							</td>
							<td style='width:80px; padding-top:3px; border-top:1px dotted gray;vertical-align:top; font-size:8pt;'>
							".extract_grade($r)."
							</td>
							<td style='padding-top:3px; padding-bottom:15px; padding-left:5px; padding-right:5px; border-top:1px dotted gray;vertical-align:top; font-size:11pt; background:#AAF;'>"
								.nl2br($r["notes"])."
							</td>
						</tr>";		
		}	
	}
	$allevents.="</table>";
	$s->p("<span style='font-size:120%;font-style:italic;'>Current piano lesson notes</span>&nbsp;-&nbsp;<span style='font-size:80%;'>Powered by LifeLog - A project of <a href='http://www.jowe.de/'>http://www.jowe.de</a></span><br/><a href='pno_notebooks.php'>Go to individual lesson notebooks</a>
			");
	$s->p("<div style='margin-left:20px;'>$allevents</div>");
	$s->flush();

?>