<?php 
//Default Timezone GMT minus:
DEFINE("TIMEZONE","-8");

//Where are the library scripts?
DEFINE("PATH_TO_LIBRARY","../lib/");
//Where are the page content scripts?
DEFINE("PATH_TO_PAGES","pages/");
//Generic title
DEFINE("PAGE_TITLE","jowe.de - LifeLog");
//Stylesheet
DEFINE("DEFAULT_CSS","styles.css");

//==================CLASSES======================
//The auth class
require_once PATH_TO_LIBRARY."class_jowe_auth.php";
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
//Functions for strings
//require_once PATH_TO_LIBRARY."string_tools.php";
//Basic processing for lifelog
require_once "ll2_basicfns.php"; //This is in the same dir as index.php

//Create the website object $s
$s=new jowe_site();
//Create the auth object $auth
$auth=new jowe_auth();
//Create the lifelog database interaction object $ll
$ll=new jowe_lifelog();


if (!$ll->get_status("sleep_wake")){
	//$ll->add_default_alert("doorbell.ogg");
	$ll->ring_doorbell();
}



?>