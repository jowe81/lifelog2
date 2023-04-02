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


        /*
          Start counter
          #1 Go through events of next-oldest day. Sum up hours.
          #2 If Sunday,
            add hours of the last 7 days and calculate extra hours
             Else go to #1 
          
          -- Or:
          
          #1 Go to next calendar day
          #2 Get all events of that day
          #3 Go through all events, add durations
          #4 print table line for this day
          #5 Add day's hours to week's hours
          #6 If Sunday, print line for this week and reset weekly hours
          #7 Go to #1
        
        */

  //Did we get a starting date?
  if (isset($_GET['beginning'])){
    $b=$_GET['beginning'];  
    $year=substr($b,0,4);
    $month=substr($b,5,2);
    $day=substr($b,8,2);
    if (($month==0)||($year==0)||($day==0)){ $month=9; $day=1; $year=2011;}
    $beginning=mktime(0,0,0,$month,$day,$year);
  }
  if ($beginning<1314860400){
    $beginning=1314860400; //(Sep 1 2011)
  }
  $calc_begin=$beginning;
  //Find first Monday if $beginning is not a Monday
  while (getDayOfWeek($calc_begin)!=0){
    $calc_begin+=DAY;
  }
  
  $curr_day=getBeginningOfDay($calc_begin);
  
  $curr_day_hrs=0;
  $curr_week_hrs=0;
  $bank=0;
  while (isBeforeToday($curr_day)){
   	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
  					(timestamp>=$curr_day AND timestamp<=".getEndOfDay($curr_day)." AND cat1='work' AND cat2='King Road MB' AND active=true)
  					ORDER BY timestamp ASC
  				;")){
      $curr_day_hours=0; //Reset counter for daily hours
      //Loop through events of current day
      while ($r=mysql_fetch_array($res)){
        $curr_day_hours+=$r["duration"];
      }    
      if ($curr_day_hours==0){
        $out.="<tr><td>".date("m/d - D",$curr_day)."</td><td class='value'>-&nbsp;</td><td class='off'>OFF</td><td></td><tr>";
      } else {
        $out.="<tr><td>".date("m/d - D",$curr_day)."</td><td class='value'>".number_format($curr_day_hours/HOUR,1)."</td><td></td><td></td><tr>";    
      }
      $curr_week_hours+=$curr_day_hours;
      //This a Sunday? Then get week summary.
      if (getDayOfWeek($curr_day)==6){
        //$total_extra_hrs is the deviance from the norm
        $total_extra_hrs=$curr_week_hours-(24*HOUR);
        $volunteered=0; //Reset vol. counter
        //If $total_extra_hrs>0, we volunteered time and possibly add to the bank
        if ($total_extra_hrs>0){
	  //Consider hours as volunteered only if bank is positive
	  if ($bank>0) {
	    $volunteered=min($total_extra_hrs,(24*HOUR)*0.25);
	  }
          $net_extra_hrs=$total_extra_hrs-$volunteered;
          $bank+=$net_extra_hrs;        
        } else {
          //No extra hours, but drawn on bank
          $bank+=$total_extra_hrs;        
        }
        
        //Determine CSS class for normal/low and high load weeks
        $wk_class='week_normal';
        if ($total_extra_hrs>$volunteered){
          $wk_class='week_high';
        }
        $wk_dates=date('D M d, Y',getBeginningOfWeek($curr_day))." - " .date('D M d, Y',($curr_day));
        //No extra hours, but drawn on account
        if ($total_extra_hrs<0){
          $out.="<tr class='$wk_class'><td>Week total:<br/>$wk_dates</td><td class='value'>".number_format($curr_week_hours/HOUR,1)."</td><td></td><td>Extra hours drawn down: ".number_format(abs($total_extra_hrs)/HOUR,1)."<br>Bank: ".number_format($bank/HOUR,1)."</td><tr>";                
        } else {
	  $vol_text="";
	  if ($volunteered>0) $vol_text="<br>Thereof considered volunteered: ".number_format($volunteered/HOUR,1);
          $out.="<tr class='$wk_class'><td>Week total:<br/>$wk_dates</td><td class='value'>"
	    .number_format($curr_week_hours/HOUR,1)."</td><td></td><td>Total extra hours worked: ".number_format($total_extra_hrs/HOUR,1)
	    .$vol_text
	    ."<br>Net extra hours: ".number_format($net_extra_hrs/HOUR,1)."<br>Bank: ".number_format($bank/HOUR,1)."</td><tr>";        
        }
        $curr_week_hours=0;          
      }        
    }
    //Go to next day
    $curr_day=getBeginningOfDay($curr_day+DAY+HOUR); //If we don't add the hour we get a Endlosschleife at end of DST
  }    


  $out="<table><tr class='headline'><td>Day</td><td>Hours worked</td><td>Day off?</td><td>Week summary</td></tr>".$out."</table>";
  
  $s->p("<div id='header'>Hours worked at King Road Church from ".date("M j, Y",$calc_begin)." (for Johannes Weber)</div>");
  $s->p("<div id='start'><form>Want this calculation to start from a different day? Enter YYYY-MM-DD: <input type='text' name='beginning'><input type='submit' value='Go!'><br/><span style='font-size:70%'>Note: Calculation will start from the first Monday after the date entered. Bank is considered 0 at starting date."
    ."<br>Remember that more detailed information about each day's work may be accessed <a href='http://jowe.dyndns.tv:499/~johannes/webservices/lifelog2/special/kr.php'>here</a>".
    "<br><span style='font-weight:bold'>This calcalation does NOT account for stat holidays, vacation, sick days etc. All of that needs to be credited to the bank.</span></span></span></form></div>");
  //$s->p("<div id='legend'>Legend:<br/><span style='font-size:70%'>Green shaded weeks either included drawing from the bank or a maximum of 6 extra hours worked (I consider up to 25% of extra time as volunteered)<br/>Red weeks include more than 6 extra hours (extra hours exceeding this amount go into the bank)<br/>Remember that more detailed information about each day's work may be accessed <a href='http://jowe.dyndns.tv:499/~johannes/webservices/lifelog2/special/kr.php'>here</a></span></div>");
  $s->p($out);


  $s->h('<style type="text/css">
          table {
            width:800px;
            color:#811;
            border-collapse:collapse;
          }
          td {
            border:1px solid sienna;
          }
          tr {
            background:#FFD;
          }
          .value {
            text-align:right;
          }
          .off {
            text-align:center;
            background:yellow;
            
          }
          .week_high {
            background:#FBA;
            border-bottom:5px solid black;        
          }
          .week_normal {
            background:#BBFFBB;  
            border-bottom:5px solid black;        
          }
          .headline {
            background:#BBFFBB;  
            border-bottom:5px solid black;                  
          }
          .headline td{
            text-align:center;
            font-weight:bold;
            background:#CCC;
            color:black;
          }
          #header {
            height:50px;
            width:800px;
            font-size:150%;
            text-align:center;
          }
          #start {
            width:794px;
            padding:10px 2px;
            margin-bottom:20px;
            text-align:center;
            background:#FFFFCC;
            border:1px dotted gray;
          }
          #legend {
            width:794px;
            padding:10px 2px;
            margin-bottom:20px;
            text-align:center;
            background:#EEEEEE;
            border:1px dotted gray;          
          }
          </style>
        ');


	$s->flush();

?>
