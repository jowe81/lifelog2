<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Get refresh interval in seconds
	//$refresh=$ll->param_retrieve_value("REFRESH_INTERVAL","BILLBOARD");
	$refresh=60;
	if (($_GET["processform"])!=""){
		$refresh=3;
	}

	//Default 
	if ($_GET["bb_width"]==0) { $_GET["bb_width"]=900; }
	if ($_GET["bb_height"]==0) { $_GET["bb_height"]=600; }
	$_GET["bb_width"]=900;
	$_GET["bb_height"]=580;
	//What screen?
	$view="mobile";

	//Produce
	$s->p("<p style='height:30px;'></p>"); //This line for the iPod app
	$s->p($ll->produce_billboard($_GET["bb_width"],$_GET["bb_height"],time(),$view));



	$s->set_refresh($refresh,me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=$view"); 
	

	//Just unicolor background for mobile view
	$s->h("
	
			<style type='text/css'>
			body {background-image:url('');background:black;}
			</style>
			
			<meta name='apple-mobile-web-app-capable' content='yes' />
			<meta name='apple-mobile-web-app-status-bar-style' content='black-translucent' />
		");
		
	
?>