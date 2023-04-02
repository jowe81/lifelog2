<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content'>");

	//Produce this page
	$step=10000;
	if (($_GET["navto"]=="") || ($_GET["navto"]<0) ){$_GET["navto"]='0';}
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
	
	//$s->p("<a href='".me()."&navto=".($_GET["navto"]-$step)."'>Back</a> | <a href='".me()."&navto=".($_GET["navto"]+$step)."'>Next</a>");
	$s->p(show_people($ll,$_GET["navto"],"managepeople",$filter,"lastname,firstname",$step,520));
	
	//Close content div
	$s->p("</div><!--content-->");
	
	//JS to set initial focus on lastname
	$s->set_initial_focus('lastname');
?>