<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Where to go after the login process?
	if ($_GET["goto"]!="") {
		//User has requested a particular page
		$refertoservice=$_GET["goto"];
	} else {
		//Nothing specific requested - go to main view (today)
		$refertoservice="today";
	}
	
	$loginform="
		\n<div id='login_form' style='height:80px; padding:15px; margin:auto;'>
		\n<form action='?processform=login&page=$refertoservice' method='POST'>
		\n<table>
		\n	<tr><td></td><td><span style='font-weight:bold; font-size:13pt;'>Welcome to LifeLog!</span><br>Please log in.<br>&nbsp;</td></tr>
		\n	<tr><td style='text-align:right;'>User:</td><td><input id='login_user' style='width:200px;' type='text' name='flogin'></td></tr>
		\n	<tr><td style='text-align:right;'>Password:</td><td><input id='login_password' style='width:200px;' type='password' name='fpassword'></td></tr>
		\n	<tr><td style='text-align:right;'>After login:</td><td>
					<select id='login_selectaction' name='action_after_login'>
						".create_option($_GET["goto"],'(select action)')."
						<option value='today'>View today</option>
						<option value='record'>Record new event</option>
						<option value='opencurrentevent'>Open current event</option>
						<option value='billboard'>Open billboard</option>
					</select>
					<div style='float:right;'>
						<input id='login_submit' type='submit' name='fsubmit' value='Login'>
					</div>
		\n	<tr><td></td><td></td></tr>
		\n</table>
		\n</form>
		\n</div>
		";
	
	$s->p("<div id='logincontainer'>");
	$s->p("<div id='loginform'>$loginform</div>");
	$s->p("</div>");
	
	$s->set_initial_focus('login_user');

?>