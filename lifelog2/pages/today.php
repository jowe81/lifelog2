<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/


	//Calculations for navigation
	//---Where are we supposed to go to?
	if ($_GET["navto"]!=0){
		//Go to whichever day we are supposed to move to
		$time=$_GET["navto"];
	} else {
		//Nowhere...then today
		$time=getBeginningOfDay(time());
	}
	
	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content'>");

	//Obtain navigation bar from $ll->calview
	$s->p($ll->produce_navbar($time,me()));


	//Produce the actual content
	$s->p($ll->produce_day_view($time));
	//Close content div
	$s->p("</div><!--content-->");
	
	//Auto refresh only if we are looking at today
	if (isToday($time) || isSameDay($time,(time()-100))) { //The second condition is to jump across midnight
		$s->set_refresh(60,me()); 
	}
?>