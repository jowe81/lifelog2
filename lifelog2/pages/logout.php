<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Send goodbye message
	$s->message("Goodbye, ".$_GET["owner"]["login"].".");
	//Defer to login page
	$s->set_refresh(1,"?page=login");
	//Destroy session record (log out of system)
	$auth->destroy_session($_GET["sid"]);
?>