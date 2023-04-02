<?php


	if (isset($_GET["month"])){
	
		//Jan 2011
		if ($_GET["month"]==1){
			$start=1295337600;
			$end=1296547199;
			$month="January 2011";
			$filename="january";
		}
		//Feb 2011
		if ($_GET["month"]==2){
			$start=1296547200;
			$end=1298966399;
			$month="February 2011";
			$filename="february";
		}
		//Mar 2011 - until Apr 6
		if ($_GET["month"]==3){
			$start=1298966400;
			$end=1302158700;
			$month="March 2011";
			$filename="march";
		}
	
	
	} else {
		//The whole semester if no month specified
		$start=1295337600;
		$end=1302158700;
		$month="Entire semester";
		$filename="allentries";
	}
	
	if (time()<$end) {$month.=" - <span style='color:red;'>not final!</span>";}
	

	//Where are the library scripts?
	DEFINE("PATH_TO_LIBRARY","../../lib/");

	//The website class
	require_once PATH_TO_LIBRARY."class_jowe_site.php";
	//The lifelog class
	require_once PATH_TO_LIBRARY."class_jowe_lifelog.php";
	//=================FUNCTIONS===================
	//Functions around calculating dates and times
	require_once PATH_TO_LIBRARY."date_tools.php"; 
	//Basic processing for lifelog
	require_once "../ll2_basicfns.php"; //This is in the same dir as index.php
		
	//Create the lifelog database interaction object $ll
	$ll=new jowe_lifelog();	

	//Get timezone from settings/params table
	date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));	

	$allevents="";

	//
	$order="ASC";
	if ((isset ($_GET["order"])) && ($_GET["order"] == "descending")){
		$order ="DESC";
	}
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp>=$start AND timestamp<=$end AND cat1='notes' AND cat2='journal' AND cat3='devotional' AND active=true)
					ORDER BY timestamp $order
				;")){
		while ($r=mysql_fetch_array($res)){
					$allevents.="<p style='font-weight:bold;'>".date("D, M d, Y",($r["timestamp"]))."</p>
							<p>".nl2br($r["notes"])."</p><hr/>";		
		}	
	}


	$allevents="<h2>Johannes Weber: BIE600 - Journal</h2><h3>$month (".mysql_num_rows($res)." entries)</h3><hr>".$allevents;


header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=BIE600_Journal_JWeber_$filename.doc");

echo "<html>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo "<body style='font-family:arial;'>";
echo $allevents;
echo "</body>";
echo "</html>";
?>