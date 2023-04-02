<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content'>");

	//Produce this page
	if ($_GET["form"]=="") { $_GET["form"]="add_person"; } //Default
	if ($_GET["form"]=="edit_person") {
		$headline="View/edit person";
	} else {
		$headline="Add a person";	
		//JS to set initial focus on lastname
		$s->set_initial_focus('lastname');
	}
	//Bring up empty new-event form
	$s->p("<div id='content_header'>$headline</div>");
	
	//Produce the form. The third parameter will indicate the id in case of edit_person. 0 for add_person
	$s->p(display_person_form($s,$ll,$_GET["id"],$_GET["form"],$_GET["goto"]));

	//Close content div
	$s->p("</div><!--content-->");
	

?>