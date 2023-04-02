<?php
	require "lib/class_jowe_lifelog.php";
	
	$ll = new jowe_lifelog();
	
	//$ll->drop_tables();
	$ll->create_tables();
	/*
	echo "<br>INSERTING DEFAULT TEMPLATE...";
	if ($ll->db("insert into ll2_templates
		VALUES (0,0,0,0,0,0,-8,3,'','','','','',0,'','',0,'',0,'','personal',0,0,0,0);")) {
		echo "...OK";
	} else 
	{
		echo "...FAILED";
	}
	*/
	/*$ll->add_color_rule("deadline",0,"#FFD700",10);
	$ll->add_color_rule("journal",0,"#DDDDFF");
	$ll->add_color_rule("education",1,"#FAE8FF");
	$ll->add_color_rule("friend support",1,"#EFFFEA");
	$ll->add_color_rule("recreation",1,"#E0f6ff");
	$ll->add_color_rule("volunteering",1,"#FFFFEA");
	$ll->add_color_rule("medical",1,"#FFF3EA");
	$ll->add_color_rule("work",1,"#EFFFEA");
	
	echo "<br>COLOR for deadline in cat2: ".$ll->get_color("deadline",2);*/
?>