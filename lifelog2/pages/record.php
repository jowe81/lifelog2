<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/

	

	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content'>");

	//Produce this page
	
	//See if we have a value for navto. If not, set to now - because we never want to create an event on Dec31,1969
	if ($_GET["navto"]==0) {
		$_GET["navto"]=time();
	}

	//Where to go after the form processing?
	$goto=$_GET["goto"]."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"];

	if ($_GET["form"]=="edit_event"){
		$s->p("<div id='content_header'>Edit an event (ID: ".$_GET["id"].")</div>");
		//Bring up edit-event form
		//In the call of display_event_form, the third parameter (with "edit_event" used) determines the event id to be edited
		$s->p(display_event_form($s,$ll,$_GET["id"],"edit_event",$goto,$_GET["navto"]));	
	} elseif ($_GET["form"]=="add_postit") {
		//The postit form works in essence the same as the event form
		$s->p("<div id='content_header'>Add post-it</div>");
		$s->p(display_postit_form($s,$ll,0,"add_postit",$goto,$_GET["navto"]));
		//JS to set initial focus on lastname
		$s->set_initial_focus('textarea_notes_postit');
	} elseif ($_GET["form"]=="edit_postit") {
		$s->p("<div id='content_header'>Edit post-it (ID: ".$_GET["id"].")</div>");
		$s->p(display_postit_form($s,$ll,$_GET["id"],"edit_postit",$goto,$_GET["navto"]));
		//JS to set initial focus on lastname
		$s->set_initial_focus('textarea_notes_postit');
	} else {
		//Default is add_event
		$s->p("<div id='content_header'>Record an event (default template)</div>");
		//Bring up empty new-event form
		//In the call of display_event_form, the third parameter (with "add_event" used) determines the template to use
		//(id from ll2_templates). 0 is the default template.
		$s->p(display_event_form($s,$ll,0,"add_event",$goto,$_GET["navto"]));
	}
	//Close content div
	$s->p("</div><!--content-->");
?>