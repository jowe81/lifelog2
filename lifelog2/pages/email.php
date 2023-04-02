<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content'>");

	//Produce this page
	$filter="";
	//Only build filter if user has submitted that form and has not hit 'clear filter'
	if ((isset($_POST["filterform"])) && ($_POST["filterform"]!="clear filter")) {
		$filter=buildfilter($_POST);
	} else {
		//Since we don't use a filter, don't show anything in the filter fields
		unset ($_POST["lastname"]);
		unset ($_POST["firstname"]);
	}
	//Touch form params
	if (!isset($_POST["filterform"])) { $_POST["filterform"]=""; }
	if ((!isset($_POST["lastname"])) || ($_POST["filterform"]=="clear filter")) { $_POST["lastname"]=""; }
	if ((!isset($_POST["firstname"])) || ($_POST["filterform"]=="clear filter")) { $_POST["firstname"]=""; }
	//Produce searchform
	$searchform="<div style='background:#FFE;'>		
				<form action='".me()."' id='form' method='POST'>
				<table><tr>
					<td>Filter by last name: </td>
					<td><input id='lastname' onFocus=\"javascript:this.select();\" type='text' name='lastname' value='".$_POST["lastname"]."' /></td>
					<td>First name: </td>
					<td><input id='firstname' onFocus=\"javascript:this.select();\" type='text' name='firstname' value='".$_POST["firstname"]."' /></td>
					<td><input type='submit' name='filterform' value='set filter'></td>
					<td><input type='submit' name='filterform' value='clear filter'></td>
					<td style='color:green;'>$filter</td>
				</table>
				</form>
			</div>";
	
	$s->p($searchform);


	if ($_GET["action"]=="showmail"){
		$messagediv=show_email($ll,$_GET["id"]);
	} else {
		$messagediv="Select a message to display";
	}


	//Only show list if this is not a call from a different page to show a particular email
	if ($_GET["goto"]==""){
		$s->p("<div style='position:absolute;
						top:30px;
						left:0px;
						width:535px;
						height:567px;
						overflow:auto;
						background:white;'>"
				.show_emails($ll,$filter)."</div>");
				
		$s->p("<div style='position:absolute;
						top:30px;
						left:540px;
						width:550px;
						height:567px;
						overflow:auto;
						background:white;
						padding-left:5px;
						border-left:1px dotted gray;'>"
				.$messagediv."</div>");
	} else {
		//We are just asked to show the email $_GET["id"] - which can be done full size, we don't need the list
		$s->p("<div style='position:absolute;
						top:30px;
						left:0px;
						width:1090px;
						height:567px;
						overflow:auto;
						background:white;
						padding-left:5px;
						;'>"
				.$messagediv."</div>");	
	}
	
	//Close content div
	$s->p("</div><!--content-->");
	
	//JS to set initial focus on lastname
	$s->set_initial_focus('lastname');
?>