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
		return "<img $r><span style='color:#c3def2;'> ".$text."</span>";
	}

  function get_option($text,$value){
    return "<option value='$value'>$text</option>";
  }

  /*
    Pseudo code:
      get all lesson records
      put date/time in select - value id
      on select item click, ajax the notes into the actual_notes div  
  
  */

	//haley
  /*
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp<=".time()." AND cat1='friend support' AND cat2='piano lessons' AND person=5 AND active=true)
					ORDER BY timestamp DESC LIMIT 1
				;")){
		while ($r=mysql_fetch_array($res)){
					$header="Lesson on ".date("l, F j, Y",$r["timestamp"]);
          $notes=nl2br($r["notes"]);
          $footer=extract_grade($r);
		}	
	}
  */
  $js="";
  $select_options="";  
	if ($res=$ll->db("SELECT * FROM ll2_events WHERE 
					(timestamp<=".time()." AND cat2='piano lessons' AND person=71 AND active=true)
					ORDER BY timestamp DESC
				;")){
    
    $lessons=array();
		while ($r=mysql_fetch_array($res)){
      $lessons[]=$r;
		}
    $cnt=sizeof($lessons);
    foreach ($lessons as $r){
			$text="Lesson #$cnt on ".date("F j, Y ",$r["timestamp"]);
      $value=$r["id"];
      $select_options.=get_option($text,$value);                
      $cnt--;  
    }
    //**************************Average calculation
    //Total
    $total=0;	
    $na=0;
    foreach($lessons as $r){
      if ($r["value1"]==0){
        $na++;
      } else {
        $total+=$r["value1"];      
      }    
    }
    (sizeof($lessons)-$na)>0 ? $total_avg=number_format(($total/(sizeof($lessons)-$na))-1,2) : $total_avg=0;
    //Recent    
    $weeks=6; 
    $total=0;
    $na=0;
    for($i=0;$i<$weeks;$i++){
      if ($lessons[$i]["value1"]==0){
        $na++;
      } else {
        $total+=$lessons[$i]["value1"];
      }    
    }
    ($weeks-$na)>0 ? $recent_avg=number_format(($total/($weeks-$na))-1,1) : $recent_avg=0;
    	
	}
  
  echo "
  <!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\"
              \"http://www.w3.org/TR/html4/strict.dtd\">

  <html>
          <head>
            <script type='text/javascript' src='jquery/jquery-1.7.1.min.js'></script>
            <script type='text/javascript'>
              $(document).ready(function(){
                  $('#sel').change(function(){
                    $('#notes').load('pno_ajax.php?person=178&lesson_id=' + $('#sel option:selected').val());
                  });
                  $('#sel option:eq(0)').attr('selected','selected');
                  $('#notes').load('pno_ajax.php?person=178&lesson_id=' + $('#sel option:selected').val());
              }); //end ready
            </script>
            <STYLE type='text/css'>
                body {background:#eee0d4;}
                #mainhead{
                  width:955px;
                  height:70px;
                  margin:0px auto;
                  font-size:50px;
                  color:#c7966d;
                  font-style:italic;
                  text-align:center;   
                  padding-top:20px;
                }
                #container{
                  width:955px;
                  height:450px;
                  margin:0px auto;
                }
                #footerdiv_cont{
                  width:955px;
                  height:50px;
                  margin:0px auto;
                  padding-top:1px;
                  padding-right:5px;
                  color:white;
                  font-size:11px;
                }
                #footerdiv{
                  width:400px;
                  height:50px;
                  margin:0px;
                  padding-top:1px;
                  padding-right:5px;
                  text-align:right;
                  color:black;
                  font-size:12px;
                  float:right;
                }
                #eval{
                  width:400px;
                  height:50px;
                  margin:0px 0px;
                  margin-left:5px;
                  padding-top:1px;
                  padding-right:5px;
                  color:black;
                  font-size:12px;
                  float:left; 
                  font-weight:bold;
                }
                #menu{
                  width:250px;
                  height:450px;
                  float:left;                
                }
                
                #menu select {
                  height:100%;
                  width:100%;
                  background:#eee0d4;
                  color:#c7966d;
                  border:1px solid #c7966d;
                }
                
                #notes {
                    background-repeat:no-repeat;
                    height:450px;
                    width:700px;
                    position:relative;
                    z-index:0;
                    overflow:hidden;
                }
                .stretch {
                  width:100%;
                  height:100%
                }
                #header {
                  padding-top:50px;
                  padding-left:60px;
                  font-size:120%;
                  color:#c7966d	;
                  font-style:italic;
                  font-weight:bold;
                }
                #actual_notes {
                  padding-top:20px;
                  padding-left:60px;
                  padding-right:60px;
                  color:#c7966d	; 
                  height:280px;
                  width:550px;
                  overflow:auto;
                }
                #footer {
                  position:absolute;
                  left:60px;
                  top:380px;
                  color:#c7966d;
                }
            </STYLE>

          </head>
          <body>
            <div id='mainhead'>
              Haley's piano lesson notebook 
            </div>
            <div id='container'>
              <div id='menu'>
                <select id='sel' size=50>
                  $select_options
                </select>
              </div>
              <div id='notes' style='margin-left:20px;'>
                <img src='notepaper_5.jpg' class='stretch' style='position:absolute;z-index:-1;'/>
                <div id='header'>$header</div>
                <div id='actual_notes'>$notes</div>
                <div id='footer'>$footer</div>
              </div>
            </div>
            <div id='footerdiv_cont'>
              <div id='footerdiv'>
                Powered by LifeLog - A project of <a href='http://www.jowe.de'>http://www.jowe.de</a>
              </div>
              <div id='eval'>
                Total average practice evaluation: $total_avg stars | Last six lessons: $recent_avg stars 
              </div>
            </div>
          </body>
         </html> 
        ";

?>