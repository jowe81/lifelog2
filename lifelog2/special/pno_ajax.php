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
	//$s=new jowe_site();
	
	//Create the lifelog database interaction object $ll
	$ll=new jowe_lifelog();
	//$calviews=new jowe_lifelog_calendar_views($ll);

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
		return "<img $r><span style=''> ".$text."</span>";
	}


	if ($res=$ll->db("SELECT * FROM ll2_events WHERE id=".$_GET["lesson_id"])){
		while ($r=mysqli_fetch_array($res)){
					$header="Lesson on ".date("l, F j, Y",$r["timestamp"]);
          $notes=nl2br($r["notes"]);
          $footer=extract_grade($r);
		}	
	}
  
  echo "
                <img src='notepaper_".$_GET["person"].".jpg' class='stretch' style='position:absolute;z-index:-1;'/>
                <div id='header'>$header</div>
                <div id='actual_notes'>$notes</div>
                <div id='footer'>$footer</div>
        ";

?>