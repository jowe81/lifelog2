<?php
    ini_set('display_errors', 'Off');
    
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


	//Get timezone from settings/params table
	date_default_timezone_set($ll->param_retrieve_value("TIMEZONE","LOCATION"));

  //skip all overhead for tablet control in intranet
  if (($_GET["page"]=="internal_tablet_control") || ($_GET["page"]=="itc")){
  
    $pct_btns="";
    
    $actions=array("pct_recall_preset","toggle_status","pct_toggle_channel");
    
    if (!in_array($_GET["processform"],$actions)){
      for($i=1;$i<9;$i++){
        $pct_btns.="<div class=\"button normal_button noselect\" onclick=\"pct_preset($i)\">".$ll->param_retrieve_value("PCT_PRESET".$i."_NAME","POWERCONTROL")."</div>";
      }
      
      echo "
      <DOCTYPE! html>
        <head>
          <title>Lifelog Intranet Tablet Control</title>
          <script src=\"../lib/jquery/jquery-1.7.1.min.js\"></script>
          <style>
            body{
              background:black;
            }
            
            .button_container{
              overflow:auto;
            }
            
            .button{
              background:gray;
              float:left;
              border:1px white;
              border-radius:10px;
              margin:10px;
              padding:5px;
              color:white;
              font-size:40px;
              font-family:arial;
              text-align:center;
              cursor:pointer;
            }

			.noselect {
			  -webkit-touch-callout: none; /* iOS Safari */
			  -webkit-user-select: none;   /* Chrome/Safari/Opera */
			  -khtml-user-select: none;    /* Konqueror */
			  -moz-user-select: none;      /* Firefox */
			  -ms-user-select: none;       /* Internet Explorer/Edge */
			  user-select: none;           /* Non-prefixed version, currently
			                                  not supported by any browser */
			}
            
  
            .normal_button{
              background:#A22;
              width:200px;
              height:100px;          
            }
            
            .big_button{
              background:navy;
              width:430px;
              height:150px;          
            }
                      
            .button:active{
              color:red;
              background:#900;
            }
          </style>
        </head>
        
        <body>
          <div class=\"button_container\">
            <div class=\"button big_button\" id=\"toggle_away\"onclick=\"toggle_status('out_in')\">Toggle away</div>
            <div class=\"button big_button\" id=\"toggle_sleeping\"onclick=\"toggle_status('sleep_wake')\">Toggle sleeping</div>
            <!-- <div class=\"button big_button\" id=\"toggle_busy\"onclick=\"toggle_status('busy_available')\">Toggle busy</div> -->
          </div>
          <div class=\"button_container\">
            $pct_btns
          </div>
          
          <script>
            
            function toggle_status(flagname){
              $.get(\"$me\"+\"?page=itc&processform=toggle_status&flagname=\"+flagname,function(result){
                //alert (result);            
              });
            } 
  
            function pct_preset(preset_id){
              $.get(\"$me\"+\"?page=itc&processform=pct_recall_preset&preset=\"+preset_id,function(result){
                //alert (result);            
              });
            } 
          
          </script>
          
        </body>
      </html>
      ";                  
      die;    
    } else {
      echo"processed this";    
    }      
  }

	//Evaluate $_GET parameters
	$_GET=evaluate_GET($_GET);
	
	/*
		For any required page in $_GET["page"] the following must exist for it to be displayed:
		- a php-file with the page content
		- a service node in auth
		- a session id in $_GET["sid"] which is valid for the node, i.e. $auth->validate_session() returns true 
		(exception: login page)
	*/

	//Is this a login attempt? (The login form is the only form to be processed apart from validation)
	if ($_GET["processform"]=="login") {
		processlogin($auth,$s);
		//Reset this parameter after processing the login
		$_GET["processform"]=""; 
	}

	//Add the user record to $_GET as $_GET["owner"]
	$_GET["owner"]=$auth->get_session_owner($_GET["sid"]);

	//Open main container (do this here so that auth-error message appear inside, too), unless this is the billboard module
	if (($_GET["page"]!="billboard") && ($_GET["page"]!="mobile")){
		$s->p("<div id='container'>");
	}

	//Validate session (unless login page is being requested)
	if (($auth->validate_session($auth->get_service_id($_GET["page"]),$_GET["sid"])) || ($_GET["page"]=="login") || ($_GET["page"]=="itc")) {
		//Okay, request is authorized
		//Process form if one was submitted
		if ($_GET["processform"]!="") {
			if (processform($_GET["processform"],$ll,$s)) {
				//Form processing successful
			} else {
				//Issue with form processing
				$s->error("Form processing failed");
			}
		}
		//Perform dbaction (eg. deletion of a record) if one was requested
		if ($_GET["dbaction"]=="delete"){
			//$ll->db("DELETE from ".$_GET["table"]." WHERE id=".$_GET["id"].";");
			//Don't really delete, just deactivate.
			$ll->deactivate_record($_GET["id"],$_GET["table"]);
		} elseif ($_GET["dbaction"]=="toggleread"){
			$ll->toggle_read($_GET["id"]);
		} elseif ($_GET["dbaction"]=="updateparam"){
			process_param_update($ll);
		}
    if (($_GET["page"]=="itc") || ($_GET["page"]=="internal_tablet_control")){
      //processed
      die;    
    } else {
  		//Load page
  		require_once PATH_TO_PAGES.$_GET["page"].".php";    
    }
	} else {
		//Request is not authorized - defer to login, but push requested page to 'goto'
		$s->set_refresh(2,"?page=login&goto=".$_GET["page"]);
		$s->error("Authorization failed, transferring you to login.");
	}
	
	//Close main container (unless this is the billboard module)
	if (($_GET["page"]!="billboard") && ($_GET["page"]!="mobile")) {
		$s->p("</div><!--container-->");
		$s->p("<div id='belowcontainer'>");
		$s->p("LifeLog is being developed by Johannes Weber. (C) 2010-".date("Y")." by <a href='http://www.jowe.de' class='belowcontainer'>http://www.jowe.de</a>. All rights reserved.");
		$s->p("</div><!--belowcontainer-->");
	}

	//Output to browser
	$s->set_title(PAGE_TITLE);
	$s->set_stylesheet(DEFAULT_CSS);
	$s->flush();
	

?>