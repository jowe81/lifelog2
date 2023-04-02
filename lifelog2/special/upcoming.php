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
	$s->h('<meta http-equiv="refresh" content="3600">');
	$s->h('<meta name="viewport" content="width=device-width, initial-scale=1">');
	$s->h('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">');
	$s->h('<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>');
	$s->h('<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>');
	$s->h('<link rel="stylesheet" href="upcomingStyles.css">');
	//Create the lifelog database interaction object $ll
	$ll=new jowe_lifelog();
	$calviews=new jowe_lifelog_calendar_views($ll);

	//Get timezone from settings/params table
	date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));	



	$allevents="<table style='border-collapse:collapse;font-size:60%;'>";
	$x="";
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE active=1 AND  
					(timestamp>".getBeginningOfDay(time()).")
					ORDER BY timestamp ASC
				;")){
	    $lastWeekTs=0;
	    $count=0;
		while ($r=mysqli_fetch_array($res)){
		  $rowclass="";		    
		  $currentWeekTs=getBeginningOfWeek($r["timestamp"]);
		  if ($currentWeekTs>$lastWeekTs){
		      $lastWeekTs=$currentWeekTs;
		      $x.="<div class='row spacerWeek'><div class='col-sm-12'>Week of ".date("F j",$currentWeekTs)."</div></div>";		      
		  }
		  //
		  $y1=date("D, F j",$r["timestamp"]);
		  $y2="";
		  $y3="";
		  $y4="";
		  if ($r["cat1"]=="work"){
		      $rowclass="markerWorkOther";
		      if ($r["cat2"]=="Tenth Church"){
		          $rowclass="markerWorkTenth";
		          if ($r["cat3"]=="time off"){
		              $y2="Day off/".$r["cat4"];
		              $rowclass="markerDayOff";
		          } else {
		              $y2="Tenth Church";
		              if ($r["cat3"]!=""){
		                  $y2.="/".$r["cat3"];
		              }
		              $y2.="<div class='time'>".$r["notes"]."</div>";
		          }
		      } else if ($r["cat4"]=="LEC") {
		          $y2="LEC/".$r["cat5"];
		          $y2.="<div class='time'>".$r["notes"]."</div>";
		      }
		  } else if (($r["cat1"]=="notes") && ($r["cat2"]=="post-it")){		      
		      //post it
		      $y2=$r["notes"];
		      $rowclass="markerPostIt";
		  } else if (($r["cat1"]=="medical") && ($r["cat2"]=="consultation")){
		      $y2=$r["cat3"];
		      if ($r["cat4"]!=""){
		          $y2.="/".$r['cat4'];
		      }
		      $y2.="<div class='time'>".$r["notes"]."</div>";		      
		  } else if ($r["cat1"=="cultural"]){
		      $y2=$r["cat2"];
		      if ($r["cat3"]!=""){
		          $y2.="/".$r['cat3'];
		      }
		      $y2.="<div class='time'>".$r["notes"]."</div>";		      
		  }
		  
		  //Other entry, not covered above
		  if ($y2==""){
		      $y2=$r["cat2"];
		      if ($r["cat3"]!=""){$y2.="/".$r["cat3"];}
		      if ($r["cat4"]!=""){$y2.="/".$r["cat4"];}
		      $y2.="<div class='time'>".$r["notes"]."</div>";		      
		  }
		    
        
		  //Add time frame if given
		  if ($r["duration"]>0 && $r["duration"]!=60*60*24){
		      $y1.="<div class='time'>".date("h:ia",$r["timestamp"])." - ".date("h:ia",$r["timestamp"]+$r["duration"])."</div>";
		  }
		  $x.="<div class='row $rowclass'><div class='col-sm-3 col1'>$y1</div><div class='col-sm-9 col2'>$y2</div></div>";
		  $count++;
		}	

	}
	$allevents.="</table>";
    $masthead="<div id='top-jumbotron' class='jumbotron sticky-top text-center'><h2>Johannes'&nbsp;upcoming calendar&nbsp;events</h2>$count entries, updated ".date("Y/m/d, H:i:s")."</div>";
    $s->p($masthead);
	$s->p("<div id='outerContainer'><div class='container'>$x</div></div>");
	$s->flush();

?>