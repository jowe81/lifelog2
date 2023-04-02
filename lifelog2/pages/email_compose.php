<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content'>");

	//Produce this page
	$s->p("<div id='content_header'>Send an email</div>");
	
	if (isset($_POST["sendmail"])){	
	
		//Produce footer for mail with some live info
		//Weather
		$weather=$ll->get_current_weather_from_db();
		if (is_Array($weather)){
			$weathertext=$weather["temperature"]." C - ".$weather["sky_condition"]." - ";
		} else {
			$weathertext="(weather data not available) - ";
		}
		$weathertext.=$ll->get_next_sunevent();	
		//Home?
		$since=time()-$ll->get_latest_status_change("out_in");
		if ($ll->get_status("out_in")==1){
			$homeawaytext="Johannes is not home (away since ".getHumanReadableLengthOfTime($since).") ";
		}else{
			$homeawaytext="Johannes is home (since ".getHumanReadableLengthOfTime($since).") ";
		}
		
		//Take out carriage returns
		$message=str_replace(chr(13),"",$_POST["data"]);

		/*$k=strlen($message);
		for ($i=0;$i<$k;$i++){
			$disp.=ord(substr($message,$i,1))."-";
		}*/
		
		
		
		//Send mail 
		if (mail($_POST["recipient"],$_POST["subject"],$message."\n--\nSent from my LifeLog system\rCurrent weather in Abbotsford: $weathertext \n$homeawaytext","From: Johannes Weber <johannes@drweber.de>\r\n"."Bcc: johannes@drweber.de")){
			$disp="Message sent successfully";
		} else {
			$disp="<span style='color:red;'>Message could not be sent</span>";
		}
		
	} else {
		//Show composition interface
		
		//Recepient address specified?
		$rcpt_addr="";
		if (isset($_GET["rcpt_addr"])){
			$rcpt_addr=$_GET["rcpt_addr"];
		}
		
		//Recepient preselected by id?
		if (isset($_GET["rcptid"])){
			$rcpt_addr=$ll->get_email_address_by_personid($_GET["rcptid"],'personal');
		}

		$disp="
			<form method='POST' action='?sid=".$_GET["sid"]."&page=email_compose'>
			<div width='100px'>To:</div>
			<div><input type='text' id='recipient' name='recipient' value='$rcpt_addr' style='width:300px;'/></div>
			<div width='100px'>Subject:</div>
			<div><input type='text' id='subject' name='subject' style='width:300px;'/></div>
			<div><textarea name='data' style='width:800px; height:440px;'></textarea>
			<div><input type='submit' name='sendmail' value='Send message'></div>
			</form>
		
		
		";
	
	}

	$s->p($disp);
	//Close content div
	$s->p("</div><!--content-->");
	
	if ($rcpt_addr!=""){
		$s->set_initial_focus('subject');
	} else {
		$s->set_initial_focus('recipient');
	}
?>