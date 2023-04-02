<?php
/*
	JW, Aug 5 2010
	String tools
*/
	/*
		Definition: word
			A word is any continuous sequence of the following characters: A-Z,a-z,0-9,_,-
	*/


	//Return the first word (separated by whitespace or any characters other than A-Z,a-z,0-9,_,-) in $s
	function getFirstWord($s){
		$matches=array();
		preg_match("/[\w]+/",$s,$matches);
		return $matches[0];
	}

	//Return the last word (separated by whitespace
	function getLastWord($s){
		$matches=array();
		preg_match("/[\w]+[\W]*$/",$s,$matches);
		return getFirstWord($matches[0]);
	}

?>