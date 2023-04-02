<?php
/*
	JW, July 13, 2010
*/
class jowe_site {

	private $head=array();
	private $body=array();
	private $header=array();
	private $title="";
	private $css_script="";
	private $initialfocus_js="";
	private $timestamp=0;

	function __construct() {
		$this->timestamp=microtime();
	}
	
	//How much time has elapsed since creation of the object?
	public function elapsed_time(){
		return abs(microtime()-$this->timestamp); //For some odd reason this might be negative on occasion (that's why abs)
	}

	//----------------------------ADDING TITLE, REFRESH, CSS SCRIPT-------------------------

	//Set a title
	public function set_title($q) {
		$this->title="\n<title>$q</title>";
	}
	
	//Create a refresh header
	public function set_refresh($seconds,$url) {
		$this->header("Refresh: $seconds; url=$url");
	}

	//Specify global CSS script
	public function set_stylesheet($url) {
		$this->css_script=$url;
	}
	//----------------------------ADDING GENERIC DATA-----------------------------------------

	//Add a header
	public function header($q) {
		$this->header[]=$q;
	}

	//Add a line to the <head> section
	public function h($q) {
		$this->head[]=$q;
	}

	//Add a line to the <body> section
	public function p($q) {
		$this->body[]=$q;
	}
	
	//----------------------------ADDING MESSAGES--------------------------------------------
	
	//Add an error message
	public function error($q) {
		$this->p("<div id='error'>ERROR: ".$q."</div><!--error-->");
	}

	//Add a genereic message
	public function message($q) {
		$this->p("<div id='message'>MESSAGE: ".$q."</div>");
	}

	//----------------------------SET INITIAL FOCUS-------------------------------------------
	public function set_initial_focus($element){
		$this->initialfocus_js="<script type='text/javascript'>document.getElementById('$element').focus();</script>";
	}


	//----------------------------FLUSH PAGE -----------------------------------------------------	
	
	//Flush page to browser
	public function flush() {
		//Headers first
		foreach ($this->header as $r){
			header ($r);
		}
		//DOCTYPE next
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
		//Open <html> next
		echo "\n<html xmlns=\"http://www.w3.org/1999/xhtml\">";	
		//<head> next
		echo "\n\n<head>";
		//insert title if present
		if ($this->title!="") {
			echo $this->title;
		}
		//insert link to css script if present
		if ($this->css_script!="") {
			echo "\n<link rel='stylesheet' type='text/css' href='".$this->css_script."'>";
		}
		foreach ($this->head as $r){
			echo "\n".$r;
		}
		echo "\n</head>";

		//<body> next
		echo "\n\n<body>";
		foreach ($this->body as $r){
			echo "\n".$r;
		}
		
		//JS for initial focus at end of script
		echo "\n".$this->initialfocus_js;
		
		echo "\n</body>";
		
		//Close <html>
		echo "\n\n</html>";
	}


}


?>