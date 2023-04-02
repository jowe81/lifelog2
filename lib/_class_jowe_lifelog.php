<?php
/*
	JW, July 13, 2010
*/
class jowe_lifelog {

	//Database info for auth tables
	private $mysql_host="localhost";
	private $mysql_user="jowede";
	private $mysql_pass="33189";
	private $mysql_database="lifelog";
	
	private $params=array(); //At time of instantiation, load the entire param table into this array
	
	private $connected=false; //Is instance connected to the database?
	private $link;		    //Resource link of connected object
	//======================================= PRIVATE FUNCTIONS =======================

	//---------------------------- BASIC DATABASE FUNCTIONS -------------------

	//Connect object with database
	function connect() {
		if ($link=mysql_connect($this->mysql_host,$this->mysql_user,$this->mysql_pass,true)) {
			//Link established
			if ($selected=mysql_select_db($this->mysql_database,$link)) {
				//Everything's okay
				$this->link=$link;
				$this->connected=true;
			} else  {
				//Database could not be selected
				$this->link=false;
			}
		} else {
			//Could not connect
			$this->link=false;
		}
	}

	//Database query (should be internal!)
	public function db($q) {
		if ($this->connected) {
			return mysql_query($q,$this->link);
		} else {
			return false;
		}
	}

	//---------------------------- CONSTRUCTOR/DESTRUCTOR -------------------
	
	function __construct() {
		$this->connect();
		//If connecting with SQL was successful, load parameters into $params
		if ($this->connected){
			$this->load_params();
		}
		return $this->link;
	}
	
	function __destruct() {
		if ($this->connected) {
			mysql_close($this->link);
		}
	}

	//---------------------------- TABLE CREATION FUNCTIONS -------------------
	
	public function create_tables() {
		/*
			created_at is a timestamp
			created_by links to the table of users
			untimed indicates no particular starting time
			person links to id in the people table (for birthdays, invitations, etc)
			value1/2/3 could be used for: kilometers, liters, pages read, beers consumed etc
			is_filter indicates whether the record is a pseudo-record (filter criteria)
		*/
		$this->connect();
		if ($this->connected) {
			//The main events table structure: used for ll2_events, ll2_templates, ll2_filters
			$t="
				id INT PRIMARY KEY AUTO_INCREMENT,
				created_at INT,
				created_by INT,
				timestamp INT,
				duration INT,
				untimed BOOL,
				timezone INT,
				priority TINYINT DEFAULT 0,
				cat1 VARCHAR(50),
				cat2 VARCHAR(50),
				cat3 VARCHAR(50),
				cat4 VARCHAR(50),
				cat5 VARCHAR(50),
				person INT,
				attachment VARCHAR(100),
				notes TEXT,
				notes_to_billboard BOOL,
				billboard_text VARCHAR(200),
				expense FLOAT(10,2) DEFAULT 0,
				account VARCHAR(20),
				privacy ENUM('public','friends','personal','confidential','N/A') DEFAULT 'N/A',
				value1 FLOAT(10,3) DEFAULT 0,
				value2 FLOAT(10,3) DEFAULT 0,
				value3 FLOAT(10,3) DEFAULT 0,
				active BOOL DEFAULT true";
		
		
			echo "<br>CREATING TABLES (class_jowe_lifelog)...";
			echo "<br>CREATING TABLE ll2_events...";
			if ($this->db("CREATE TABLE ll2_events (".$t.");")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			echo "<br>CREATING TABLE ll2_templates...";
			if ($this->db("CREATE TABLE ll2_templates (".$t.");")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			echo "<br>CREATING TABLE ll2_filters";
			if ($this->db("CREATE TABLE ll2_filters (".$t.");")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			echo "<br>CREATING TABLE ll2_people";
			if ($this->db("CREATE TABLE ll2_people (
				id INT PRIMARY KEY AUTO_INCREMENT,
				created_at INT,
				created_by INT,
				timezone INT,
				attachment VARCHAR(100),
				notes TEXT,
				main_address_street VARCHAR(200),
				main_address_zip VARCHAR(20),
				main_address_city VARCHAR(50),
				main_address_province VARCHAR(10),
				main_address_country VARCHAR(50),
				main_address_homephone VARCHAR(30),
				second_address_street VARCHAR(200),
				second_address_zip VARCHAR(20),
				second_address_city VARCHAR(50),
				second_address_province VARCHAR(10),
				second_address_country VARCHAR(50),
				second_address_homephone VARCHAR(30),
				work_address_street VARCHAR(200),
				work_address_zip VARCHAR(20),
				work_address_city VARCHAR(50),
				work_address_province VARCHAR(10),
				work_address_country VARCHAR(50),
				work_address_homephone VARCHAR(30),
				cell_personal VARCHAR(30),
				cell_work VARCHAR(30),
				email_personal VARCHAR(50),
				email_work VARCHAR(50),
				email_extra VARCHAR(50),
				skype VARCHAR(50),
				icq VARCHAR(50),
				website VARCHAR(80),
				lastname VARCHAR(30),
				firstname VARCHAR(30),
				middlename VARCHAR(30),
				birthday INT,
				year_of_birth_unknown BOOL,
				show_birthday BOOL,
				relationship_type VARCHAR(30),
				active BOOL DEFAULT true
				birthday_day TINYINT,
				birthday_month TINYINT,
				birthday_year TINYINT,
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			//This table holds information for background colors of event items
			echo "<br>CREATING TABLE ll2_colors";
			if ($this->db("CREATE TABLE ll2_colors (
				id INT PRIMARY KEY AUTO_INCREMENT,
				catname VARCHAR(50),
				catid INT,
				bgcolor VARCHAR(15),
				rulepriority INT,
				active BOOL DEFAULT true
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			//This table holds all email
			echo "<br>CREATING TABLE ll2_emails";
			if ($this->db("CREATE TABLE ll2_emails (
				id INT PRIMARY KEY AUTO_INCREMENT,
				timestamp INT,
				email_timestamp INT,
				sender VARCHAR(255),
				sender_address VARCHAR(100),
				person INT,
				size INT,
				subject VARCHAR(255),
				message_type VARCHAR(100),
				parts INT,
				message TEXT,
				isread BOOL DEFAULT false,
				priority INT,
				active BOOL DEFAULT true
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			//This table holds  parameters
			echo "<br>CREATING TABLE ll2_params";
			if ($this->db("CREATE TABLE ll2_params (
				id INT PRIMARY KEY AUTO_INCREMENT,
				created_at INT,
				created_by INT,
				updated INT,
				pname VARCHAR(100),
				pvalue VARCHAR(100),
				pgroup VARCHAR(100),
				pdescription VARCHAR(200),
				active BOOL DEFAULT true
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			//This table holds  statusflags (in_out,available_busy,sleep_wake etc.)
			echo "<br>CREATING TABLE ll2_statusflags";
			if ($this->db("CREATE TABLE ll2_statusflags (
				id INT PRIMARY KEY AUTO_INCREMENT,
				timestamp INT,
				created_by INT,
				flagname VARCHAR(50),
				flagvalue BOOL,
				active BOOL DEFAULT true
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			//This table holds  acoustic alerts 
			echo "<br>CREATING TABLE ll2_alerts";
			if ($this->db("CREATE TABLE ll2_alerts (
				id INT PRIMARY KEY AUTO_INCREMENT,
				timestamp INT,
				duration INT,
				created_by INT,
				priority TINYINT,
				itvl INT,
				kind VARCHAR(20),
				filename VARCHAR(200),
				last_played INT,
				times_played INT,
				active BOOL DEFAULT true
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			
			//This table holds  weather data. location refers to the billboard clocks 1,2,3
			echo "<br>CREATING TABLE ll2_weather";
			if ($this->db("CREATE TABLE ll2_weather (
				id INT PRIMARY KEY AUTO_INCREMENT,
				timestamp INT,
				location TINYINT,
				full_description VARCHAR(255),
				sky_condition VARCHAR(100),
				temperature TINYINT,
				humidity TINYINT,
				wind_direction VARCHAR(2),
				wind_speed TINYINT,
				1daytitle VARCHAR(50),
				2daytitle VARCHAR(50),
				3daytitle VARCHAR(50),
				1day VARCHAR(255),
				2day VARCHAR(255),
				3day VARCHAR(255),
				active BOOL DEFAULT true
				);")) {
				//Success
				echo("...DONE.");
			} else {
				//Failure
				echo("...FAILED.");
			}
			
		} else {
			echo "No connection to database";
		}
		
	}

	public function drop_tables() {
		echo "<br>DROPPING TABLES (class_jowe_lifelog)...NOT ACTIVE"; /*
		if ($this->db("DROP table ll2_events;")) {
			echo "<br>SUCCESS (ll2_events)";
		} else {
			echo "<br>ERROR (ll2_events)";
		}		
		if ($this->db("DROP table ll2_templates;")) {
			echo "<br>SUCCESS (ll2_templates)";
		} else {
			echo "<br>ERROR (ll2_templates)";
		}		
		if ($this->db("DROP table ll2_filters;")) {
			echo "<br>SUCCESS (ll2_filters)";
		} else {
			echo "<br>ERROR (ll2_filters)";
		}		
		if ($this->db("DROP table ll2_people;")) {
			echo "<br>SUCCESS (ll2_people)";
		} else {
			echo "<br>ERROR (ll2_people)";
		}		
		if ($this->db("DROP table ll2_colors;")) {
			echo "<br>SUCCESS (ll2_colors)";
		} else {
			echo "<br>ERROR (ll2_colors)";
		}	*/	
	}


	//---------------------------- All queries / tables -------------------

	//Build the query to insert new record $e in table $table ($e["id"], if present, will be ignored)
	public function build_insert_query($e,$table) {
		//We expect $e to be immaculate - build query from all fields except id
		$keys="(";
		$values="(";
		foreach ($e as $key=>$value) {
			if ($key!="id") {
				$keys.="$key,";
				$values.="'".mysql_real_escape_string($value)."',";
			}
		}
		$keys=substr($keys,0,strlen($keys)-1).")"; //Cut the last comma
		$values=substr($values,0,strlen($values)-1).")"; //Cut the last comma
		$query="INSERT INTO $table $keys VALUES $values;"; 
		return $query;		
	}
	
	//Build the query to update existing record $e in table $table ($e["id"] identifies the record to be updated)
	public function build_update_query($e,$table) {
		//We expect $e to be immaculate - build query from all fields except id
		$q="";
		foreach ($e as $key=>$value) {
			if ($key!="id") {
				$q.=$key."='".mysql_real_escape_string($value)."',";
			}
		}
		$q=substr($q,0,strlen($q)-1); //Cut the last comma
		$query="UPDATE $table SET $q WHERE id=".$e["id"].";"; 
		return $query;		
	}


	//Insert a new record $e into table $table. Indices correspond to table columns (see create_tables()). $e["id"] will be ignored.
	public function add_record($e,$table) {
		if ($this->db($this->build_insert_query($e,$table))) {
			return true;
		}
		return false;		
	}

	//Update an existing record $e in table $table. $e["id"] points to the record to be updated
	public function update_record($e,$table='ll2_events') {
		if ($this->db($this->build_update_query($e,$table))) {
			return true;
		}
		return false;
	}

	//Delete record $id in table $table
	public function delete_record($id,$table='ll2_events') {
		if ($this->db("DELETE FROM $table WHERE id=$id;")) {
			return true;
		}
		return false;	
	}
	
	//Deactivate record $id table $table
	public function deactivate_record($id,$table) {
		if ($this->db("UPDATE $table SET active=false WHERE id=$id;")) {
			return true;
		}
		return false;
	}
	
	/*Retrieve single record with id $id from table $table*/
	public function retrieve($id,$table='ll2_events') {
		if ($res=$this->db("SELECT * from $table WHERE  id=$id;")) {
			if (mysql_num_rows($res)>0) {
				return mysql_fetch_array($res);
			}
		}
		return false;
	}
	
	//---------------------------- Events --------------------------------------------------

	//Create a new event. $e is the event array. Indices correspond to table columns (see create_tables()). 
	public function add_event($e) {
		return $this->add_record($e,"ll2_events");
	}
	
	//Update an existing event. $e["id"] points to the record to be updated
	public function update_event($e) {
		return $this->update_record($e,"ll2_events");
	}

	//Delete event $id
	public function delete_event($id) {
		return delete_record($id,"ll2_events");
	}
	
	//Deactivate event $id
	public function deactivate_event($id) {
		return deactivate_record($id,"ll2_events");
	}
	
	//Return the record of the current event (if any)
	public function get_current_event(){
		$query="SELECT * from ll2_events WHERE (timestamp+duration>".time().") AND (timestamp<=".time().") AND (cat1!='notes') AND (untimed=false) AND (active=true) ORDER BY timestamp ASC LIMIT 1;";
		if ($res=$this->db($query)){
			return mysql_fetch_array($res);
		}
		return false;
	}
	
	//Return next upcoming event (if any)
	public function get_next_upcoming_event($todayonly=false){
		$query="SELECT * from ll2_events WHERE
				(timestamp>".time().") AND (cat1!='notes') AND (untimed=false) AND (active=true) ORDER BY timestamp ASC LIMIT 1;
				;";
		if ($res=$this->db($query)){
			$r=mysql_fetch_array($res);
			//Return event if it is either today, or not today but it does not matter
			if ((($todayonly) && (isToday($r["timestamp"]))) || (!$todayonly)){
				return $r;
			}
		}
		return false;
	}
	
	//Return next event with "social"->"hosting visitors"; include event currently progress
	public function get_next_hosting_event($todayonly=false){
		$query="SELECT * from ll2_events WHERE
				(timestamp>".time()."-duration) AND (cat1!='notes') AND (untimed=false) AND (active=true) AND (cat1='social') AND (cat2='hosting visitors') ORDER BY timestamp ASC LIMIT 1;
				;";
		if ($res=$this->db($query)){
			$r=mysql_fetch_array($res);
			//Return event if it is either today, or not today but it does not matter
			if ((($todayonly) && (isToday($r["timestamp"]))) || (!$todayonly)){
				return $r;
			}
		}
		return false;
	}
	
	
	//-------------------------------- People ----------------------------------------------------
	
	//Create a new person. Indices correspond to table columns (see create_tables()). 
	public function add_person($e) {
		return $this->add_record($e,"ll2_people");
	}
	
	//Update an existing person. $e["id"] points to the record to be updated
	public function update_person($e) {
		return $this->update_record($e,"ll2_people");
	}

	//Delete person $id
	public function delete_person($id) {
		return delete_record($id,"ll2_people");
	}
	
	//Deactivate person $id
	public function deactivate_person($id) {
		return deactivate_record($id,"ll2_people");
	}
	
	//Retrieve the display name (Lastname, firstname M) of persion with id $id
	public function get_person_displayname($id) {
		if ($res=$this->db("SELECT lastname,firstname,middlename FROM ll2_people WHERE id=$id;")) {
			if ($r=mysql_fetch_array($res)) {
				return $r["lastname"].", ".$r["firstname"]." ".$r["middlename"];
			}
		}
		return false;
	}

	//Retrieve the first name of person $id
	public function get_person_firstname($id) {
		if ($res=$this->db("SELECT lastname,firstname,middlename FROM ll2_people WHERE id=$id;")) {
			if ($r=mysql_fetch_array($res)) {
				return $r["firstname"];
			}
		}
		return false;
	}
	
	//Retrieve  Lastname, F of persion with id $id
	public function get_person_abbreviated_displayname($id) {
		$n=$this->get_person_displayname($id);
		return substr($n,0,strpos($n,",")+3);
	}
	
	//Try all we can to use $s to unequivocally identify a person. Return id.
	public function get_personid_by_name($s){
		if ($s!=""){
			$firstname="";
			$lastname="";
			$middlename="";
			//Is this firstname, lastname?
			if ($d=strpos($s,',')) {
				//Extract lastname
				$lastname=substr($s,0,$d);
				//Chop off that part, including the comma
				$s=substr($s,$d+1);
				//If first character now is a space, discard it
				while (substr($s,0,1)==" ") {
					$s=substr($s,1);
				}
				//If there's still a space somewhere in the string we have first and middle name
				if ($d=strpos($s,' ')) {
					//Extract first name
					$firstname=substr($s,0,$d);
					//Chop off that part, including the space
					$s=substr($s,$d+1);
					//If first character now is a space, discard it
					while (substr($s,0,1)==" ") {
						$s=substr($s,1);
					}
					//Now the remaining part must be the middle name
					$middlename=$s;
				} else {
					//The rest is likely the first name
					$firstname=$s;
				}
			} else {
				//With no comma, this must be just a last name (perhaps not, but who cares)
				$lastname=$s;
			}
			//Build query
			$q="";
			if ($firstname!="") { $q.="AND firstname='$firstname' "; }
			if ($middlename!="") { $q.="AND middlename='$middlename' "; }
			$q="SELECT id FROM ll2_people WHERE lastname='$lastname' $q;";
			//Execute query
			if ($res=$this->db($q)){
				if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
					//Found the person
					return $r["id"];
				}
			}
		} 
		return false;
	}
	
	//Identify person by email address
	function get_personid_by_email($s){
		if ($s!=""){
			$q="SELECT id FROM ll2_people WHERE email_personal='$s' OR email_work='$s' OR email_extra='$s';";
			if ($res=$this->db($q)){
				if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
					//Found person
					return $r["id"];
				}
			}
		}
		return false;
	}
	
	//Get email from person id. $type='personal', 'work', or 'extra'
	function get_email_address_by_personid($id,$type){
		if ($id!=0){
			$q="SELECT email_$type FROM ll2_people WHERE id='$id';";
			if ($res=$this->db($q)){
				if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
					//Found record.
					if ($r["email_$type"]!=""){
						return $r["email_$type"];
					}
				}
			}
		}
		return false;		
	}
	
	//Retrieve the birthdays in the next $n days.
	//Return array with the three fields display_name,days_out,turning_age
	function get_next_birthdays($n=1,$time=0){
		/*
			This is harder than I thought. What are the conditions?
			bday=day or bday=day+DAY or bday=day+2*DAY....n*DAY
			bmonth=month or bmonth=month+month of the date in n*DAY
		*/
		if ($time==0) { $time=time(); }
		$condition="(birthday_day=".date("j",$time)." AND birthday_month=".date("n",$time).")";
		for ($i=1;$i<=$n;$i++){
			$condition.=" OR (birthday_day=".date("j",$time+$i*DAY)." AND birthday_month=".date("n",$time+$i*DAY).")";
		}
		$query="SELECT id,lastname,firstname,middlename,birthday_day,birthday_month,birthday_year,year_of_birth_unknown
				FROM ll2_people WHERE ($condition) AND show_birthday=true;";
		$r=array(); //This is the master array
		$p=array(); //This is a tmp subarray 
		$i=0;
		if ($res=$this->db($query)) {
			while ($q=mysql_fetch_array($res)){
				$p["displayname"]=$q["firstname"]." ".substr($q["lastname"],0,1).".";
				//To calculate how many days away the birthday is, we cannot just assume this year.
				//---If it happened this year already, we must calculate the distance for NEXT year's birthday
				if (($time-DAY)<mktime(0,0,0,$q["birthday_month"],$q["birthday_day"],date("Y",$time))) {
					//Birthday has not yet happened  (is not over yet) this year, so we are looking for this year's
					$year_for_daysout=date("Y",$time);
				} else {
					//Birthday this year is past, so look for next year's
					$year_for_daysout=date("Y",$time)+1;
				}
				$p["days_out"]=getDaysOut(mktime(0,0,0,$q["birthday_month"],$q["birthday_day"],$year_for_daysout),$time)+1;
				$p["turning_age"]=0;
				if (!$q["year_of_birth_unknown"]) {
					$p["turning_age"]=date("Y",$time+($p["days_out"]*DAY))-$q["birthday_year"];
				}
				$r[$p["days_out"].zerofill($i,4)]=$p;  //This is a trick to be able to sort by days out - key is daysout.index
				$i++;
			}
			//Okay, now a normal arsort should do the trick (sort by days_out)
			ksort($r);
			return $r;
		}		
	}
	
	//-------------------------------Emails----------------------------------------------------
	public function add_email($e){
		return $this->add_record($e,"ll2_emails");
	}
	
	public function delete_email($id){
		return delete_record($id,"ll2_emails");
	}
	
	//Mark email $id as read/unread.
	public function toggle_read($id){
		if ($this->is_marked_read($id)){
			if ($res=$this->db("UPDATE ll2_emails SET isread=false WHERE id=$id;")){
				return true;
			}		
		} else {
			if ($res=$this->db("UPDATE ll2_emails SET isread=true WHERE id=$id;")){
				return true;
			}
		}
		return false;
	}
	
	//Check if email has been marked as read alrady
	public function is_marked_read($id){
		if ($res=$this->db("SELECT isread FROM ll2_emails WHERE id=$id;")){
			if ($r=mysql_fetch_array($res)){
				return $r["isread"];
			}
		}
		return false;
	}
	
	//Return an array of todays emails with id,from,email_timestamp,timestamp,subject
	public function get_this_days_emails($time,$unread_only=false){
		$result=array();
		$read_condition="";
		if ($unread_only){
			$read_condition=" AND isread=false";
		}
		$query="SELECT id,sender,sender_address,email_timestamp,timestamp,subject,isread,person
				FROM ll2_emails
				WHERE email_timestamp>=".getBeginningOfDay($time)."
					AND email_timestamp<".getBeginningOfDay($time+DAY)." AND active=true $read_condition ORDER BY email_timestamp ASC;";
		if ($res=$this->db("$query")){
			while ($r=mysql_fetch_array($res)){
				$result[]=$r; //Add this record to the result
			}
		}
		return $result;
	}
	
	//Get unread messages, max: $limit
	public function get_unread_emails($limit=0){
		$result=array();
		$limit_condition="";
		if ($limit!=0){
			$limit_condition=" LIMIT $limit";
		}
		$query="SELECT id,sender,email_timestamp,timestamp,subject,isread,person
				FROM ll2_emails WHERE isread=false AND active=true ORDER BY email_timestamp DESC $limit_condition;";
		if ($res=$this->db("$query")){
			while ($r=mysql_fetch_array($res)){
				$result[]=$r; //Add this record to the result
			}
		}
		return $result;	
	}
	
	//Are unread emails present?
	public function haveUnreadEmails(){
		return (count($this->get_unread_emails(1))>0);
	}

	//Delete all unread flags
	public function unflag_unread_emails(){
		$query="UPDATE ll2_emails SET isread=true WHERE isread=false;";
		return $this->db($query);
	}

	//Does an email from this person require an alert now?
	public function alertPerson($id=0){
		if ($id!=26){ //#26 is Johannes Weber - don't alert own messages (which are usually spam)
			//Only alert if not away, and not sleeping.
			if (($this->get_status("out_in")==1) || ($this->get_status("sleep_wake")==1)){
				return false;
			}
			return true;
		}
		return false;
	}

	//-------------------------------Colors----------------------------------------------------
	
	//Add a rule for event background coloring. 
	//$catid=0 indicates that the $catname may occur in any cat-column
	//A higher $rulepriority will overwrite rules with lower priorities upon retrieval. Default to 5.
	public function add_color_rule($catname,$catid,$color,$rulepriority=5){
		//Check if this record exists already
		if ($res=$this->db("SELECT id FROM ll2_colors WHERE catname='$catname' AND catid='$catid';")){
			if (mysql_num_rows($res)==0){
				if ($res=$this->db("INSERT into ll2_colors VALUES(0,'$catname',$catid,'$color',$rulepriority,true);")){
					return true;
				}
			}
		}
		return false;
	}
	
	//Determine background color for event record $r
	public function get_bgcolor_for_event($r){
		//The long query is to make sure that priorities are honored in case of conflicting rules
		if ($res=$this->db("SELECT bgcolor FROM ll2_colors WHERE
						(catname='".$r["cat1"]."' AND (catid=1 OR catid=0))
						OR (catname='".$r["cat2"]."' AND (catid=2 OR catid=0))
						OR (catname='".$r["cat3"]."' AND (catid=3 OR catid=0))
						OR (catname='".$r["cat4"]."' AND (catid=4 OR catid=0))
						OR (catname='".$r["cat5"]."' AND (catid=5 OR catid=0))
						ORDER BY rulepriority DESC;")){
			if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				return $r["bgcolor"]; //Grab the first (top pri) rule and return color
			}
		}
		return false;
	}
	
	//--------------------------------CATEGORY SELECTION------------------------------

	//popular values add (used in the process of creating an array with the most popular values)
	//$a is the array, $v the new value (key)
	private function pv_add($a,$v){
		if (array_key_exists($v,$a)){
			$a[$v]++;
		} else {
			$a[$v]=1;
		}
		//Return the array
		return $a;
	}

	//Return the most popular values from field $field. Look in the last $search_range fields
	//$other_criteria are limiting conditions in SQL (e.g.: cat1='whatever' AND cat2='whatever')
	public function get_popular_values($field,$other_criteria='',$search_range=1000) {
		$pvs=array(); //This array will hold the popular values as follows: $a['popular value']=number of occurences
		//In the selection, sort by timestamp and ignore the empty option
		$res=$this->db("SELECT $field from ll2_events WHERE ($field<>'' AND active=true) $other_criteria ORDER BY timestamp DESC LIMIT $search_range;");
		while ($r=mysql_fetch_array($res,MYSQL_ASSOC)) {
			$pvs=$this->pv_add($pvs,$r[$field]);
		}
		//Sort the result
		arsort($pvs);
		return $pvs;
	}

	
	//-------------------------------------------Calendar--------------------------------------

	public function get_priority_label($i) {
		switch ($i) {
			case 5: return "critical";
			case 4: return "higher";
			case 3: return "normal";
			case 2: return "lower";
			case 1: return "lowest";
			default: return "not assigned";
		}
	}

	//Get sunset timestamp for day $time
	public function get_sunset($time,$use_shift=false){
		//Read latitude and longitude
		$latitude=$this->param_retrieve_value("LATITUDE","LOCATION"); //These are integers, need to divide by 100
		$longitude=$this->param_retrieve_value("LONGITUDE","LOCATION");	
		$shift=0; //If the function was called with $use_shift, then apply the night shift param 
		if ($use_shift) { $shift=$this->param_retrieve_value("NIGHTVIEW_SHIFT","BILLBOARD"); }
		return date_sunset($time, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100))+$shift;
	}
	
	//Get sunrise timestamp for day $time
	public function get_sunrise($time,$use_shift=false){
		//Read latitude and longitude
		$latitude=$this->param_retrieve_value("LATITUDE","LOCATION"); //These are integers, need to divide by 100
		$longitude=$this->param_retrieve_value("LONGITUDE","LOCATION");		
		$shift=0; //If the function was called with $use_shift, then apply the night shift param 
		if ($use_shift) { $shift=$this->param_retrieve_value("NIGHTVIEW_SHIFT","BILLBOARD"); }
		return date_sunrise($time, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100))-$shift;
	}
	
	//Do we have daylight or nighttime right now? (consider shift)
	public function is_nighttime($time=0) {
		if ($time==0) { $time=time(); }
		return (($this->get_sunset($time,true)<$time) || ($this->get_sunrise($time,true)>$time));
	}
	
	//The inverse of is _nighttime
	public function is_daytime($time=0){
		return !$this->is_nighttime($time);
	}
	
	//Are we using nightview right now? (I.e. is it night time, and the parameter USE_NIGHTVIEW is set?)
	public function is_nightview(){
		return (($this->is_nighttime()) && ($this->param_retrieve_value("USE_NIGHTVIEW","BILLBOARD")));
	}
	
	//This function returns a percentage between day (0.0) and night (1.0), depending on transition time set in BILLBOARD, NIGHTVIEW_TRANSITION_TIME (seconds)
	public function nightview_percent($time=0){
		if ($this->param_retrieve_value("USE_NIGHTVIEW","BILLBOARD")){
			//Nightview has been activated, so go ahead...
			if ($time==0) { $time=time(); }
			$ttime=floor($this->param_retrieve_value("NIGHTVIEW_TRANSITION_TIME","BILLBOARD")/2);
			//0% if we are in daytime, and NIGHTVIEW_TRANSITION_TIME away from nighttime
			//100% if we are in nighttime, and NIGHTVIEW_TRANSITION_TIME away from daytime
			//A percentage in between in all other cases
			if (($this->is_daytime($time)) && (($this->is_daytime($time+$ttime)) && ($this->is_daytime($time-$ttime)))){
				//Fully in daytime
				return 0;
			} elseif (($this->is_nighttime($time)) && (($this->is_nighttime($time+$ttime)) && ($this->is_nighttime($time-$ttime)))) {
				//Fully in nighttime
				return 1;
			} else {
				//Within transition
				//We need to find out which way the transition goes.
				//--if abs(sunset-time())<abs(sunrise-time()) then we are transitioning into the night, else into the day
				if (abs($this->get_sunset($time,true)-$time)<abs($this->get_sunrise($time,true)-$time)){
					//This is a transition into the night bc we are closer to the sunset than the sunrise
					//Determine beginning and end of transition and calculate current position percentage
					$tstart=$this->get_sunset($time,true)-$ttime;
					$tend=$tstart+(2*$ttime);
					$tpercent=(($time-$tstart)/($tend-$tstart)); //This should now be 0.xxxxx (between 0 and 1)
					return $tpercent;
				} else {
					//This is a transition into the day bc we are closer to the sunrise than the sunset
					//Determine beginning and end of transition and calculate current position percentage
					$tstart=$this->get_sunrise($time,true)-$ttime;
					$tend=$tstart+(2*$ttime);
					$tpercent=(($time-$tstart)/($tend-$tstart)); //This should now be 0.xxxxx (between 0 and 1)
					//Invert $tpercent
					return (1-$tpercent);
				}
			}
		} else {
			return 0; //Nightview is not active, so default to daytime.
		}
	}
	
	//------------------------PASSTHROUGHS to calview-----------------------------------
	public function produce_day_view($time){
		//Create calendar_views object and pass handle to this instance of ll so that the methods in calendar_views will have access to the ll-db
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->produce_day_view($time);
	}
	
	public function produce_billboard($width,$height,$time,$view="standard"){
		//Create calendar_views object and pass handle to this instance of ll so that the methods in calendar_views will have access to the ll-db
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->produce_billboard($width,$height,$time,$view);
	}
	
	public function produce_screen($top,$left,$width,$height,$content,$bgcolor='black'){
		//Create calendar_views object and pass handle to this instance of ll so that the methods in calendar_views will have access to the ll-db
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->produce_screen($top,$left,$width,$height,$content,$bgcolor);
	}
	
	function produce_billboard_content($mode,$time,$fs=100){
		//Create calendar_views object and pass handle to this instance of ll so that the methods in calendar_views will have access to the ll-db
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->produce_billboard_content($mode,$time,$fs);	
	}
	
	public function produce_navbar($time,$me){
		//Create calendar_views object and pass handle to this instance of ll so that the methods in calendar_views will have access to the ll-db
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->produce_navbar($time,$me);
	}
	
	public function display_event($r,$enum,$mode="general",$content="regular",$fs=100,$delete_link_id="",$edit_link_id="",$nolinks=true){
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->display_event($r,$enum,$mode,$content,$fs,$delete_link_id,$edit_link_id,$nolinks);
	}


	function get_transition_color($color1,$color2,$percent){
		$calviews=new jowe_lifelog_calendar_views($this);
		return $calviews->get_transition_color($color1,$color2,$percent);
	}


	//Checks the content of a message for spam
	public function spamcheck($message){
		//As we are using this for screenmessages, simply don't allow links or some other characters
		if ((strpos($message,"://")!==false) || (strpos($message,"[")!==false) || (strpos($message,"]")!==false)){
			return true;
		}
		return false;
	}
	
	//Increases a spam counter (in params)
	public function count_spam($type){
		$cur=$this->param_retrieve_value("SPAM_$type","SYSTEM_STATUS")+1;
		$this->param_store("SPAM_$type",$cur,"SYSTEM_STATUS");
		return $cur;
	}
	
	public function add_bb_message_through_webinterface($message,$sid,$sender=""){
		if ((!$this->spamcheck($message)) && (!$this->spamcheck($sender))){
			$e["cat1"]="notes";
			$e["cat2"]="billboard";
			$e["cat3"]="webinterface";
			$e["cat4"]=$sid;
			$e["cat5"]=htmlentities($sender);
			$e["timestamp"]=time();
			$e["duration"]=0;
			$e["billboard_text"]=htmlentities($message);
			//Check if this session has already created a message
			if ($res=$this->db("SELECT id FROM ll2_events WHERE cat1='notes' AND cat2='billboard' AND cat3='webinterface' AND cat4='$sid' AND active=true;")){
				/*if ($r=mysql_fetch_array($res)){
					//Yes, so update it, unless the message is empty (then delete)
					if ($message!=""){
						$e["id"]=$r["id"];
						$this->update_event($e);
					} else {
						$this->db("DELETE FROM ll2_events WHERE id=".$r["id"].";");				
					}
				} else*/ {
					//No, so create new (unless message is empty)
					if ($message!=""){
						$this->add_event($e);
						//Alert?
						if (($this->get_status("out_in")==0) && ($this->get_status("sleep_wake")==0)){
							$this->add_default_alert("tada.ogg");
						}					
					}
				}
			}
			return true;
		} else {
			//SPAM!
			$this->count_spam("screenmsg");
			return false;
		}
	}
	
	//Delete the latest billboard message (that came through the webinterface)
	public function delete_latest_screenmessage(){
		if ($res=$this->db("SELECT id FROM ll2_events WHERE cat1='notes' AND cat2='billboard' AND cat3='webinterface' AND active=true ORDER BY timestamp DESC LIMIT 1;")){
			if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				return $this->delete_record($r["id"]);
			}
		}
		return false;
	}
	
	//Return an array with current (active) screen messages
	public function get_current_screenmessages($limit=0){
		$result=array();
		$limit_condition="";
		if ($limit!=0){
			$limit_condition="LIMIT $limit";
		}
		if ($res=$this->db("SELECT id,timestamp,billboard_text,cat4,cat5 FROM ll2_events WHERE cat1='notes' AND cat2='billboard' AND cat3='webinterface' AND active=true ORDER BY timestamp DESC $limit_condition;")){
			while ($q=mysql_fetch_array($res,MYSQL_ASSOC)){
				$result[]=$q; //Add to resulting array
			}
		}		
		return $result;
	}
	
	public function haveUnreadScreenmessages(){
		return (count($this->get_current_screenmessages(1))>0);
	}
	
	//Return the record of the latest active screenmessage
	public function get_top_screenmessage(){
		$q=$this->get_current_screenmessages(1);
		if (count($q)>0){
			return $q[0];
		} else {
			return false;
		}
	}
	
	//------------------------------------------------- STATUS FLAGS ----------------------------------------------------
	
	//Set the Do not distrub LED
	public function setDNDLED($flagname,$newstatus){
		if ($newstatus){
			//No need to check for flagname: all flags mean LED goes on
			shell_exec("curl 192.168.1.220/switchled.php?s=1 > /dev/null 2>/dev/null &");
		} else {
			//Turn LED off only if no flag is set
			if (! (($this->get_status("SLEEP_WAKE")) || ($this->get_status("OUT_IN")) || ($this->get_status("BUSY_AVAILABLE")))  ){
				shell_exec("curl 192.168.1.220/switchled.php?s=0 > /dev/null 2>/dev/null &");
			}
		}
	}
	
	//Toggle status flag
	public function toggle_statusflag($flagname){
		//Read current and determine new status of this flag
		$newstatus=!$this->get_status($flagname);
		//Try to record
		if ($res=$this->record_statusflag($flagname,($newstatus))){
			
			$this->setDNDLED($flagname,$newstatus); //set the Do not disturb LED
			
			//On success, add alert if necessary
			if ($this->params["ALERTS"]["ALERT_ON_".strtoupper($flagname)]==1){
				if ($newstatus){
					$param="FILE_".strtoupper($flagname)."_1"; //E.g. FILE_SLEEP_WAKE_1
				} else {
					$param="FILE_".strtoupper($flagname)."_0"; //E.g. FILE_SLEEP_WAKE_0				
				}
				$this->add_alert(time(),0,5,0,$this->params["ALERTS"][$param]);
			}
			//In case that we went from in to out (away), we might have to schedule a delayed systen lock
			if (($flagname=="out_in") && ($newstatus) && ($this->params["SYSTEM"]["AUTO_LOCK_ON_AWAY"]==1)){
				$this->lock_controls($this->params["SYSTEM"]["AUTO_LOCK_GRACE"]);
			}
			//We also might have to turn off lights
			if (($flagname=="out_in") && ($newstatus)){
				$this->pct_recall_preset("_AWAY");
				if ($this->params["POWERCONTROL"]["DAYTIME_OVERRIDES_FLAGS"]==1){
					$this->pct_recall_timeofday_preset();
				}
			}
			//We might have to turn on lights if we came in
			if (($flagname=="out_in") && (!$newstatus)){
				//
				$presetnr=$this->params["POWERCONTROL"]["PRESET_NR_ON_RETURN"];
				if (strlen($presetnr)>0){
					$this->pct_recall_preset($presetnr);
				}
			}
			//In the case that we went from sleep to wake we can delete alarms  (if preselected)
			if (($flagname=="sleep_wake") && (!$newstatus)){
				$p=$this->params["ALERTS"]["DLT_ALARMS_ON_WAKE"];
				//1 simply stands for cancelling all scheduled alarms, 0 stands for no automatic cancellations.
				//A value >1 stands for canelling all alarms that are less than $p seconds in the future
				if ($p>0){
					if ($p==1){
						$this->cancel_scheduled_alarms(); //Cancel everything
					} else {
						$this->cancel_scheduled_alarms($p); //Cancel within given range
					}
				}
				//Turn heat blanked off
				$this->set_channel_status(9, false);
			}		
			//If we go to sleep we recall the PCT status for sleep
			if (($flagname=="sleep_wake") && ($newstatus)){
				$this->pct_recall_preset("_SLEEP");				
			}
		}
		return $res;
	}
	
	//Record a status flag to the database. Do plausibility test (minimum distance).
	public function record_statusflag($flagname,$flagvalue=true,$time=0){
		if ($time==0) { $time=time(); } //NOW, if no time provided
		$e=array();
		$e["timestamp"]=$time;
		$e["created_by"]=0; //System
		$e["flagname"]=$flagname;
		$e["flagvalue"]=$flagvalue;
		//Record this flag only if it is not redundant
		if ($this->get_status($flagname,$time)!==$flagvalue){
			//OK, flag is not redundant...but how long ago is the last status change?
			$lastchange=$this->get_latest_status_change($flagname);
			if ($lastchange+$this->params["STATUSFLAGS"]["MIN_LOGGING_DISTANCE"]>$time){
				//Minimum logging distance has not elapsed yet, so instead of recording a new change accomplish the new status by deleting the previous change
				return $this->db("DELETE FROM ll2_statusflags WHERE timestamp=$lastchange AND flagname='$flagname';");
			} else {
				//Time distance to previous change OK, record.
				return $this->add_record($e,"ll2_statusflags");
			}
		} else {
			//The status to be record is the current status - so ignore request.
			return false;
		}
	}
	
	//Retrieve the status for flag $flagname at time $time
	public function get_status($flagname,$time=0){
		if ($time==0) { $time=time(); } //NOW, if no time provided
		if ($res=$this->db("SELECT flagvalue FROM ll2_statusflags WHERE timestamp<=$time AND flagname='$flagname' ORDER BY timestamp DESC LIMIT 1;")){
			//This should return the latest record for the flag $flagname
			if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				//Got the latest flag & current status
				if ($r["flagvalue"]==1){
					return true;
				}
			} 
		}
		return false;
	}
	
	//Get an array with all status flags for the period marked by $begins and $ends
	//$flagnames contains an array of flagnames to look for. Need at least one element.
	//-- returns $a[timestamp][flagname]=flagvalue
	public function get_statusflags_for_period($flagnames,$begins,$ends){
		if ((count($flagnames)>0) && ($ends-$begins>0)){
			//There's at least one flagname to look for, and a valid time period - so do it.
			$a=array(); //The result array
			/*
			  First we check the status for each flagname at 00:00:00.
			  If it is flagged, we create an event at the beginning of the day (makes it easier for the display function in dayview)
			  We also revoke any flags at the end of the day even if the flag indeed spans across midnight (for the same purpose).
			*/
			//Create elements at beginning of period for those flags that are set at that time.
			foreach($flagnames as $value){
				if ($this->get_status($flagname,$begins)){
					$a[$begins][$value]=1;
				}			
			}
			//Build query conditions
			$q="";
			foreach ($flagnames as $value){
				$q.=" OR flagname='$value'";
			}
			//Delete the first " OR "
			$q=substr($q,4);
			//Select all flags from the day
			$query="SELECT timestamp,flagname,flagvalue FROM ll2_statusflags WHERE timestamp>=$begins AND timestamp<=$ends AND ($q) ORDER BY timestamp;";
			if ($res=$this->db($query)){
				while ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
					$a[$r["timestamp"]][$r["flagname"]]=$r["flagvalue"];
				}
			}
			//Create elements (unflag for display purposes) at end of the period for those flags that are set at that time.
			$ends=min($ends,time()); //We can't predict the future, so the latest we'll draw is now
			foreach($flagnames as $value){
				if ($this->get_status($value,$ends)){
					$a[$ends][$value]=0;
				}			
			}
			return $a;
		} else {
			//no flagnames to look for
			return false;
		}
	}
	
	//Special instance of get_statusflags_for_period (see above). Used by the day view for status indication.
	public function get_statusflags_for_day($flagnames,$time=0){
		if ($time==0) { $time=time(); } //NOW, if no time provided
		return $this->get_statusflags_for_period($flagnames,getBeginningOfDay($time),getEndOfDay($time));
	}
	
	//Returns for the flag $flagname the timestamp of the latest status change that lies in the past (seen from $time)
	public function get_latest_status_change($flagname,$time=0){
		if ($time==0) { $time=time(); } //NOW, if no time provided
		if ($res=$this->db("SELECT timestamp FROM ll2_statusflags WHERE timestamp<=$time AND flagname='$flagname' ORDER BY timestamp DESC LIMIT 1;")){
			if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				return $r["timestamp"];
			}
		}
		return false;
	}
	
	//Returns the duration (how long the flag was set) for the flag $flagname within the time period $begins and $ends
	public function get_flag_duration_for_period($flagname,$begins,$ends){
		//Get an array with indications as to when the flag was set.
		$r=$this->get_statusflags_for_period(array($flagname),$begins,$ends);
		//Init
		$started=0;
		$total=0;
		if (is_array($r)){
			foreach ($r as $key=>$value){
				if ($value[$flagname]==1){
					//If this is a "1", remember this value (Timestamp)
					$started=$key;
				} else {
					//It's a "0", so add the distance to the previous "1" to the total
					$total+=($key-$started);			
				}
			}
		}
		return $total;
	}
	
	//Returns an array that for the last $span days indicates the duration of the set flag $flagname
	//like this: keys are number of days back, values are the duration in seconds
	//$offset shifts beginning and enf of day (you'll want noon for sleep history)
	public function get_flag_history($flagname,$span,$offset=0){
		$r=array();
		for ($i=0;$i<$span;$i++){
			//Retrieve the flag duration for each of the days (starting with today, going into the past)
			$beg=getBeginningOfDay(time()-($i*DAY))+$offset;
			$end=$beg+DAY-1;
			$r[$i]=$this->get_flag_duration_for_period($flagname,$beg,$end);
		}
		return $r;
	}
	
	//$timestamp and $flagname identify a record in ll2_statusflags.
	//This function will look for the previous record and return the difference between the timestamps
	public function get_flag_distance_to_previous($timestamp,$flagname){
		if ($res=$this->db("SELECT timestamp FROM ll2_statusflags WHERE timestamp<$timestamp AND flagname='$flagname' ORDER BY timestamp DESC LIMIT 1;")){
			if ($r=mysql_fetch_array($res)){
				return abs($timestamp-$r["timestamp"]); //The abs should be obsolote...but whatever.
			}
		}
		return false;
	}
	
	//-------------------------------------------------STATS-----------------------------------------------------------------
	
	//Retrieve an array as follows
	//-- $a[subcategoryname]=total duration (within $begins, $ends)
	function get_category_durations_for_period($cats=array(),$begins,$ends){
		//If $cats is not empty, it represents a path to subcategories. E.g. if $cats contains $cats[1]='personal', $cats[2]='expense' then all the events under personal/expense would be selected
		//If $cats is empty, we get the durations for the top categories
		$rs=array();
		$i=0;
		foreach ($cats as $value)
		{
			$i++;
			$cond.="AND cat$i='$value'";
		}		
		//Get durations from events
		$i++; //important!
		$query="SELECT cat$i,duration FROM ll2_events WHERE active=true and cat1!='notes' and timestamp>=".$begins." and duration>0 and timestamp<=".$ends." $cond;"; 
		if ($res=$this->db($query)){
			while ($r=mysql_fetch_array($res)){
				//Fill the array such that the keys are the top categories and the values the total time for the selected time window
				if (!(array_key_exists($r["cat$i"],$rs))){
					$rs[$r["cat$i"]]=$r["duration"];
				} else {
					$rs[$r["cat$i"]]+=$r["duration"];
				}
			}
		}
		if (count($rs)==0){
			//Return false if $rs contains no elements
			return false;
		}
		return $rs;
	}
	
	//Retrieve an array as follows
	//-- $a[subcategoryname]=total expense (within $begins, $ends)
	//-----FOR NOW CREDIT IS HARDCODED
	function get_category_expense_for_period($cats=array(),$begins,$ends){
		//If $cats is not empty, it represents a path to subcategories. E.g. if $cats contains $cats[1]='personal', $cats[2]='expense' then all the events under personal/expense would be selected
		//If $cats is empty, we get the durations for the top categories
		$rs=array();
		$i=0;
		foreach ($cats as $value)
		{
			$i++;
			$cond.="AND cat$i='$value'";
		}		
		//Get durations from events
		$i++; //important!
		$query="SELECT cat$i,expense FROM ll2_events WHERE active=true and cat1!='notes' and timestamp>=".$begins." and expense>0 and account='credit' and timestamp<=".$ends." $cond;"; 
		if ($res=$this->db($query)){
			while ($r=mysql_fetch_array($res)){
				//Fill the array such that the keys are the top categories and the values the total time for the selected time window
				if (!(array_key_exists($r["cat$i"],$rs))){
					$rs[$r["cat$i"]]=$r["expense"];
				} else {
					$rs[$r["cat$i"]]+=$r["expense"];
				}
			}
		}
		if (count($rs)==0){
			//Return false if $rs contains no elements
			return false;
		}
		return $rs;
	}
	
	//------------------------------------------------- in/out -----------------------------------------------------------------
	//Determine whether the user came in or went out most recently
	public function get_next_inout(){
		//Select the latest in-out event and check which way it went
		if ($res=$this->db("SELECT cat3 FROM ll2_events WHERE timestamp<".time()." AND cat1='notes' AND cat2='in-out' ORDER BY timestamp DESC LIMIT 1;")) {
			if ($q=mysql_fetch_array($res)){
				if ($q["cat3"]=="in"){
					return "out";
				} else {
					return "in";
				}
			} else {
				//No inout event exists, so the next one must be out!
				return "out";
			}
		}
	}

	//------------------------------- Parameter handling -------------------------------------------------------------------
	
	//Alle parameter aus ll2_params in $params laden
	//So now a param can be accessed by $this->params[pgroup][pname]
	function load_params(){
		if ($res=$this->db("SELECT pgroup,pname,pvalue FROM ll2_params;")){
			while ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				$this->params[$r["pgroup"]][$r["pname"]]=$r["pvalue"];
			}
		}
	}

	//Save parameter to ll-registry (does not reload the $params array)
	public function param_store($pname,$pvalue,$pgroup="DEFAULT",$pdescription=""){
		if ($e=$this->param_retrieve($pname,$pgroup)){
			//Parameter exists. Overwrite. ($e["id"] now holds the correct id and $e["pname"] the right param name)
			$e["pvalue"]=$pvalue;
			$e["updated"]=time();
			if ($this->update_record($e,"ll2_params")){
				//Save the change not only to db but also to current object instance
				$this->params[$pgroup][$pname]=$pvalue;
				return true;
			} else {
				//Could not save
				return false;
			}	
		}
		//Parameter does not exist, create.
		$e=array();
		$e["updated"]=time();
		$e["created_at"]=time();
		$e["created_by"]=0; //System
		$e["pname"]=$pname;
		$e["pvalue"]=$pvalue;
		$e["pgroup"]=$pgroup;
		$e["pdescription"]=$pdescription;
		if ($this->add_record($e,"ll2_params")){
			//Save the new param not only to db but also to current object instance
			$this->params[$pgroup][$pname]=$pvalue;
			return true;
		} else {
			//Could not save
			return false;
		}
	}
	
	//Retrieve a parameter from the ll-registry (full Record)
	public function param_retrieve($pname,$pgroup="DEFAULT"){
		if ($res=$this->db("SELECT * FROM ll2_params WHERE pname='$pname' AND pgroup='$pgroup';")){
			if ($e=mysql_fetch_array($res,MYSQL_ASSOC)){
				//Parameter exists. Return.
				return $e;
			}
		}
		return false;
	}
	
	//Retrieve a parameter but return the value only (faster because this reads $params - no db query
	public function param_retrieve_value($pname,$pgroup="DEFAULT"){
		if (isset($this->params[$pgroup][$pname])){
			return $this->params[$pgroup][$pname];
		} else {
			return false;
		}
		/* //This is old source code - with db access.
		if ($tmp=$this->param_retrieve($pname,$pgroup)){
			return $tmp["pvalue"];
		} else {
			return false;
		}
		*/
	}
	
	//Discard all parameters and restore default
	public function params_to_default(){
		if ($res=$this->db("DELETE from ll2_params;")){
		//Statusflags
			$this->param_store("MIN_LOGGING_DISTANCE",5*MINUTE,"STATUSFLAGS","Min. delay b/w status-changes of a kind (logging)");
			$this->param_store("MIN_FLAG_DISTANCE","2","STATUSFLAGS","Min. delay b/w status-changes of a kind (flagging)");
			$this->param_store("SLEEP_TRANSITION_TIME",20*MINUTE,"STATUSFLAGS","Average time to fall asleep (for sleep calculations, seconds)");
		//Alerts
			$this->param_store("ALERT_ON_OUT_IN","1","ALERTS","Trigger acoustic alert on in/out events");
			$this->param_store("FILE_OUT_IN_0","alert_in.ogg","ALERTS",".ogg-file to play on coming in");
			$this->param_store("FILE_OUT_IN_1","alert_out.ogg","ALERTS",".ogg-file to play on going out");
			$this->param_store("ALERT_ON_SLEEP_WAKE","1","ALERTS","Trigger acoustic alert on sleep/wake events");
			$this->param_store("FILE_SLEEP_WAKE_1","alert_sleep.ogg","ALERTS",".ogg-file to play on going to bed");
			$this->param_store("FILE_SLEEP_WAKE_0","alert_wake.ogg","ALERTS",".ogg-file to play on getting up");
			$this->param_store("ALERT_ON_BUSY_AVAILABLE","1","ALERTS","Trigger acoustic alert on busy/available events");
			$this->param_store("FILE_BUSY_AVAILABLE_1","alert_busy.ogg","ALERTS",".ogg-file to play on 'busy'");
			$this->param_store("FILE_BUSY_AVAILABLE_0","alert_available.ogg","ALERTS",".ogg-file on 'available'");
			$this->param_store("ALERT_ON_LOCKED_UNLOCKED","1","ALERTS","Trigger acoustic alert on locked/unlocked events");
			$this->param_store("FILE_LOCKED_UNLOCKED_1","alert_locked.ogg","ALERTS",".ogg-file to play on 'locked'");
			$this->param_store("FILE_LOCKED_UNLOCKED_0","alert_unlocked.ogg","ALERTS",".ogg-file on 'unlocked'");
			$this->param_store("FILE_AUTO_LOCK","shortbeep.ogg","ALERTS",".ogg-file for beeps during autolock grace period");
		//Alert timer presets
			$this->param_store("TIMER_PRESET_1","180","ALERTS","Timer preset 1 (seconds)");
			$this->param_store("TIMER_PRESET_2","1800","ALERTS","Timer preset 2 (seconds)");
			$this->param_store("TIMER_PRESET_3","3600","ALERTS","Timer preset 3 (seconds)");
			$this->param_store("ALARM_PRESET_1","06:45","ALERTS","Alarm preset 1 (HH:MM)");
			$this->param_store("ALARM_PRESET_2","08:00","ALERTS","Alarm preset 2 (HH:MM)");
			$this->param_store("ALARM_PRESET_3","09:00","ALERTS","Alarm preset 3 (HH:MM)");
			$this->param_store("ALARM_INTERVAL","60","ALERTS","Interval at which to perform the alarm");
			$this->param_store("ALARM_TTL","900","ALERTS","Maximum duration for a live alarm");
			$this->param_store("DLT_ALARMS_ON_WAKE","1","ALERTS","Cancel alarms on wake (within given range if >1");


			$this->param_store("FILE_ALARM","alert_alarm.ogg","ALERTS","File for scheduled alarms (timer/alarm clock)");
			$this->param_store("TIMER_FILE_1","3-minutes.ogg","ALERTS","File for scheduled alarms (timer/alarm clock)");
			$this->param_store("TIMER_FILE_2","4-minutes.ogg","ALERTS","File for scheduled alarms (timer/alarm clock)");
			$this->param_store("TIMER_FILE_3","6-30minutes.ogg","ALERTS","File for scheduled alarms (timer/alarm clock)");
		//Livestats
			$this->param_store("MARKER_TIMESTATS","1275714000","LIVESTATS","Marker for livestats timestats (timestamp)");		
			$this->param_store("MARKER_FINANCESTATS","1275714000","LIVESTATS","Marker for livestats finance stats (timestamp)");		
			$this->param_store("FINANCESTATS_CUTOFF","10","LIVESTATS","Accounting/cutoff day of the month (1-28)");		
		//Billboard
			$this->param_store("USE_NIGHTVIEW","1","BILLBOARD","Switch to nightview after sunset");
			$this->param_store("NIGHTVIEW_TRANSITION_TIME",MINUTE*30,"BILLBOARD","Transition time in seconds for day/nightview");
			$this->param_store("NIGHTVIEW_SHIFT",MINUTE*15,"BILLBOARD","Consider it night if we are this far (s) into the night");
			$this->param_store("REFRESH_INTERVAL","6","BILLBOARD","Billboard refresh interval in seconds");
			$this->param_store("BIRTHDAYS_ANNOUNCE_DAYS","7","BILLBOARD","# of days to announce b-days in advance");
			$this->param_store("SHOW_LIVESTATS","0","BILLBOARD","Display live statistics");		
			$this->param_store("SHOW_WEATHER_FORECAST","0","BILLBOARD","Display weather forecast");		
			$this->param_store("SHOW_SYSTEM_STATUS","0","BILLBOARD","Permanently display system status");		
			$this->param_store("STILL_IMAGE_TO_BB","0","BILLBOARD","Show webcam still on interactive billboard");		
			$this->param_store("STILL_IMAGE_TO_NIBB","0","BILLBOARD","Show webcam still on non-interactive billboard");		
			$this->param_store("STILL_IMAGE_CAM_NR","0","BILLBOARD","Cam# for still. 0=all cams.");		
			$this->param_store("MOVIEMODE","0","BILLBOARD","Movie-Mode for all views");				
			$this->param_store("NI_PROGRESS_FOR_SOCIAL","0","BILLBOARD","Show NI progress bar on social events?");	
		
			$this->param_store("LATITUDE","4904","LOCATION","Calendar Latitude*100 (INT), for sunrise/sunset");
			$this->param_store("LONGITUDE","-12247","LOCATION","Calendar Longitude*100 (INT), for sunrise/sunset");
			$this->param_store("TIMEZONE","America/Vancouver","LOCATION","Calendar Timezone in PHP format");
			
			$this->param_store("SHOW_PROGRESS_INFO","0","BILLBOARD","Show the progress-info window?");				
			$this->param_store("PROGRESS_INFO1","1294643498,1394643498,Demo","BILLBOARD","timestamp1,timestamp2,caption");				

		//Clocks
			$this->param_store("C1_NAME","Vancouver","BILLBOARD","Clock 1 Name");
			$this->param_store("C1_LATITUDE","4904","BILLBOARD","Clock 1 Latitude*100 (INT), for sunrise/sunset");
			$this->param_store("C1_LONGITUDE","-12247","BILLBOARD","Clock 1 Longitude*100 (INT), for sunrise/sunset");
			$this->param_store("C1_TIMEZONE","America/Vancouver","BILLBOARD","Clock 1 Timezone in PHP format");
			$this->param_store("C1_DIM","0","BILLBOARD","Clock 1 Dimming");
			$this->param_store("C1_WEATHER_URL","http://rss.theweathernetwork.com/weather/cabc0010","BILLBOARD","Clock 1 RSS feed url (weathernetwork)");
			$this->param_store("C2_NAME","Chicago","BILLBOARD","Clock 2 Name");
			$this->param_store("C2_LATITUDE","4161","BILLBOARD","Clock 2 Latitude*100 (INT), for sunrise/sunset");
			$this->param_store("C2_LONGITUDE","-8820","BILLBOARD","Clock 2 Longitude*100 (INT), for sunrise/sunset");
			$this->param_store("C2_TIMEZONE","America/Chicago","BILLBOARD","Clock 2 Timezone in PHP format");
			$this->param_store("C2_DIM","1","BILLBOARD","Clock 2 Dimming");
			$this->param_store("C2_WEATHER_URL","http://rss.theweathernetwork.com/weather/usil0225","BILLBOARD","Clock 2 RSS feed url (weathernetwork)");
			$this->param_store("C3_NAME","Gelsenkirchen","BILLBOARD","Clock 3 Name");
			$this->param_store("C3_LATITUDE","5152","BILLBOARD","Clock 3 Latitude*100 (INT), for sunrise/sunset");
			$this->param_store("C3_LONGITUDE","712","BILLBOARD","Clock 3 Longitude*100 (INT), for sunrise/sunset");
			$this->param_store("C3_TIMEZONE","Europe/Berlin","BILLBOARD","Clock 3 Timezone in PHP format");
			$this->param_store("C3_DIM","1","BILLBOARD","Clock 3 Dimming");
			$this->param_store("C3_WEATHER_URL","http://rss.theweathernetwork.com/weather/denw0022","BILLBOARD","Clock 3 RSS feed url (weathernetwork)");

		//DayView
			$this->param_store("CONTRACT_EXTEND_LENGTH",MINUTE*15,"DAYVIEW","Seconds to add/detract with +-links");

		//System
			$this->param_store("PIN_INPUT","","SYSTEM","(For pin-entry to unlock)"); //When the system is locked, when a digit is being put in it is temporarily stored here.
			$this->param_store("PIN","8797","SYSTEM","The PIN that will unlock the billboard/ctrl system and disarm");
			$this->param_store("AUTO_LOCK_ON_AWAY","1","SYSTEM","Lock controls when away (out) flag is set");
			$this->param_store("AUTO_LOCK_GRACE","30","SYSTEM","Grace period for auto locking");
			$this->param_store("DELETE_AWAY_ON_UNLOCK","1","SYSTEM","Remove away flag on unlock");
		//System_status
			$this->param_store("LATEST_EMAIL_POLL","","SYSTEM_STATUS","Timestamp of latest successful email poll");
			$this->param_store("LATEST_WEATHER_POLL","","SYSTEM_STATUS","Timestamp of latest successful weather poll");
			$this->param_store("LATEST_DNS_CHECK","","SYSTEM_STATUS","Latest successful resolution of EXTERNAL_HOSTNAME");
			$this->param_store("CURRENT_IP","","SYSTEM_STATUS","Current outside IP");
			$this->param_store("PANIC","1","SYSTEM_STATUS","Current panic level");
			$this->param_store("EXT_IP_OK",0,"SYSTEM_STATUS","External IP actually points to this system");
		//PowerControl
			$this->param_store("PCT_PORT1",0,"POWERCONTROL","PowerControl Port 1 Status");
			$this->param_store("PCT_PORT2",0,"POWERCONTROL","PowerControl Port 2 Status");
			for ($i=1;$i<17;$i++){
				$this->param_store("PCT_CH".$i."_NAME","","POWERCONTROL","PowerControl Channel Name");
			}
			for ($i=1;$i<9;$i++){
				$this->param_store("PCT_PRESET".$i."_NAME","","POWERCONTROL","PowerControl Preset Name");
				$this->param_store("PCT_PRESET".$i,"","POWERCONTROL","Channel Preset: 1 on, 0 off, * leave untouched");
			}
			$this->param_store("PCT_PRESET_NIGHT","****11**********","POWERCONTROL","PowerControl nighttime general preset");
			$this->param_store("PCT_PRESET_DAY","****00**********","POWERCONTROL","PowerControl daytime general preset");
			$this->param_store("PCT_PRESET_SLEEP","0000*00000000000","POWERCONTROL","PowerControl sleep-flag general preset");
			$this->param_store("PCT_PRESET_AWAY","0000**0000000000","POWERCONTROL","PowerControl away-flag general preset");
			$this->param_store("DAYTIME_OVERRIDES_FLAGS","1","POWERCONTROL","The day/night presets override away/sleep etc");
			$this->param_store("PRESET_NR_ON_RETURN","","POWERCONTROL","Preset #/name to recall on return");
			$this->param_store("BLOCKED_CHANNELS","0000000000000000","POWERCONTROL","1=blocked, 0=unblocked");
			
			
		//System_config
			$this->param_store("EXTERNAL_HOSTNAME","wnet2305.accessmyhome.net","SYSTEM_CONFIG","External hostname");		
			$this->param_store("TESTHOSTS","www.heise.de,www.abc.ca,www.ubc.ca","SYSTEM_CONFIG","Test hosts for WAN connection diagnosis (,-seperate)");		
			$this->param_store("TEST_PINGS_NUM","2","SYSTEM_CONFIG","Number of test pings to send to each host");		
			$this->param_store("FILESYSTEMS","/dev/sda2,/dev/sdb1,/dev/sdb2","SYSTEM_CONFIG","Filesystems to monitor (,-separate)");		
		}
	}
	
	//---------------------------------------------- ALERTS ----------------------------------------------------------
	
	//Creates an alert
	function add_alert($timestamp,$duration=1,$priority=0,$interval=0,$filename='alert_default.ogg',$kind="SYSTEM"){
		$e=array();
		$e["timestamp"]=$timestamp;
		$e["duration"]=$duration;
		$e["priority"]=$priority;
		$e["itvl"]=$interval; //in seconds
		$e["last_played"]=0; //A timestamp, too
		$e["times_played"]=0;
		$e["filename"]=$filename;
		$e["kind"]=$kind;
		return ($this->add_record($e,"ll2_alerts"));
	}
	
	//Creates a default alert (will play default alert immediately and once and at highest priority, also allows for specification of a filename)
	//This function is used by statusflag changes (and by the email deamon for new mail)
	function add_default_alert($filename=""){
		if ($filename!=""){
			$this->add_alert(time(),0,5,0,$filename);		
		} else {
			$this->add_alert(time(),0,5);
		}
	}
		
	//Returns an array of record ids of alerts to be played at this time. DOES NOT PERFORM PRIORITY CHECK!
	function get_current_alerts(){
		$time=time(); //NOW
		$a=array();
		//Select those that...
		//either are valid still, or that were valid in the past but have not been played yet - also check interval (w/ last_played)
		$query="SELECT id FROM ll2_alerts WHERE (timestamp<=$time) AND (times_played<1 OR (timestamp+duration>=$time)) AND (last_played+itvl<=$time) AND (active=true) ORDER BY timestamp;";
		if  ($res=$this->db($query)){
			while ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				//Add each alert to the result array
				$a[]=$r["id"];
			}
		}		
		return $a;
	}
	
	//Mark the alert $id as played at this time (will increase times_played and update last_played only - no check for whether or not the alert is due, just logging!)
	function flag_alert_execution($id){
		if ($r=$this->retrieve($id,"ll2_alerts")){
			return ($this->db("UPDATE ll2_alerts SET times_played=".($r["times_played"]+1).",last_played=".time()." WHERE id=".$id.";"));
		} else {
			return false;
		}
	}
	
	//Determines whether or not the priority of alert $id is sufficient for it to be executed (no time-check)
	function alert_priority_sufficient($id){
		if ($this->get_status("out_in") || $this->get_status("busy_available") || $this->get_status("sleep_wake")){
			//We need high priority
			$query="SELECT priority FROM ll2_alerts WHERE id=$id";
			if ($res=$this->db($query)){
				if ($r=mysql_fetch_array($res)){
					if ($r["priority"]>=5){
						return true;
					}
				}
			}
			return false;
		}	
		//No flag is set, so normal priority (for now: any priority) will do
		return true;
	}
	
	//Deletes alert records from the past (just to keep the table reasonably small)
	function delete_expired_alerts(){
		//We are keeping old alerts for a week though, for debugging.
		$this->db("DELETE FROM ll2_alerts WHERE (timestamp+duration<".(time()-WEEK)." AND (times_played>0)) || active=0;");
	}
	
	//Gets scheduled and current alerts of the kind ALARM 
	function get_scheduled_alarms(){
		$a=array();
		if ($res=$this->db("SELECT * FROM ll2_alerts WHERE timestamp+duration>=".time()." AND kind='ALARM' AND active=true ORDER BY timestamp;")){
			//Selecting all current and future alerts of the kind ALARM (scheduled alert, either timer or alarm clock)
			while ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				$a[]=$r; //Add this alert to the result array
			}
		}
		//Return $a if alarms were found
		if (count($a)>0){
			return $a;
		} else {
			return false;
		}
	}
	
	//Cancels all scheduled and current alerts of the kind ALARM.
	//If $range is given only those alarms will be canceled which are scheduled less than $range seconds in the future
	function cancel_scheduled_alarms($range=0){
		if ($a=$this->get_scheduled_alarms()){
			foreach ($a as $v){
				if (($range==0) || (($v["timestamp"])<(time()+$range))){
					$this->delete_record($v["id"],"ll2_alerts");
				}
			}
		}
	}
	
	//-----------------------------------------------------AVAILABILITY------------------------------------------------------
	//Checks whether events (with a duration) a scheduled within the indicated period. Buffer is added before and after the period in question.
	function checkAvailability($time=0,$duration=0,$buffer=0){
		if ($time==0) { $time=time(); }
		$query="SELECT id FROM ll2_events WHERE (timestamp+duration+$buffer>$time) AND (timestamp<".($time+$duration+$buffer).") AND (active=true) AND (duration>0);";
		if ($res=$this->db($query)){
			if ($r=mysql_fetch_array($res)){
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
	
	
	//----------------------------------------------------WEATHER DATA-----------------------------------------------------

	function get_temp_from_local_sensors(){
		return file_get_contents("http://192.168.1.220:45890/gettemp.php");
	}
	
	
	//Locate the temperature within the full description and return
	function extract_temp_from_fulldescr($t){
		$end=strpos($t,"&deg;C");
		$start=strrpos(substr($t,0,$end),",")+1;
		$tmp=trim(substr($t,$start,$end-$start)); //This is the temperature with possibly an $nbsp; - like '16&nbsp;'
		$end=strpos($tmp,"&");
		return substr($tmp,0,$end);
	}
	
	//Locate the sky conditions within the full descriptoin and return
	function extract_sky_condition_from_fulldescr($t){
		return substr($t,0,strpos($t,","));
	}

	//Traverses the RSS feed of weathernetwork to extract current weather, for locations 1,2,3 (according to billboard clocks)
	function fetch_weather_from_rss($location=1){
		$url=$this->param_retrieve_value("C".$location."_WEATHER_URL","BILLBOARD");
		if ($url!=""){
			if ($src=file_get_contents($url)){
				$xml = new SimpleXMLElement($src);
				$fulldescr=$xml->channel->item[0]->description;
				//Create result array according to ll2_weather which can be added right into the db
				$x = array();
				$x["location"]=$location;
				$x["full_description"]=$fulldescr;
				$x["temperature"]=$this->extract_temp_from_fulldescr($fulldescr);
				$x["sky_condition"]=$this->extract_sky_condition_from_fulldescr($fulldescr);
				$x["timestamp"]=time();
				$x["1day"]=$xml->channel->item[1]->description; //Tomorrows forecast
				$x["1daytitle"]=$xml->channel->item[1]->title;
				$x["2day"]=$xml->channel->item[2]->description; //2days out
				$x["2daytitle"]=$xml->channel->item[2]->title;
				$x["3day"]=$xml->channel->item[3]->description; //3days out
				$x["3daytitle"]=$xml->channel->item[3]->title;
				//Check success by checking temperature
				if (strlen($x["temperature"])>0){
					return $x;
				}
			}
			return false;
		} else {
			//If no feed url is provided, just return true (so no error)
			return true;
		}
	}

	//Checks the weather for all three locations and creates records in ll2_weather
	function fetch_weather_to_db(){
		$result=true;
		for ($i=1;$i<4;$i++){
			if ($x=$this->fetch_weather_from_rss($i)){
				//The rss fetch either worked or there was no url (in that case boolean true). Add record to db if there is one.
				if (is_Array($x)){
					$this->add_record($x,"ll2_weather");
				}
			} else {
				//Rss fetch failed.
				$result=false;
			}
		}
		//If we are returning "false" that means that at least with one location there occurred a problem
		return $result;
	}
	
	
	//Returns the stored weather record closest to $time, but it must originate from within the $grace period around $time.
	//If none is found boolean false is returned
	function get_weather_info($time,$grace=10800,$location=1){
		$query="SELECT * from ll2_weather WHERE location=$location AND timestamp>".($time-$grace)." AND timestamp<".($time+$grace)."  ORDER BY timestamp DESC LIMIT 1";
		if ($res=$this->db($query)){
			if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				return $r;
			}
		}
		return false;
	}
	
	//Get temperature from local sensor - sluggish!
	function get_weather_info_with_local_temp(){
		$d=$this->get_weather_info(time());
		//var_dump($d);
		$sdata=json_decode($this->get_temp_from_local_sensors(),true);
		$d["temperature"]=number_format($sdata["Sensoren"][0]["temp"],1);
		$d["temperature_bedroom"]=number_format($sdata["Sensoren"][1]["temp"],1);
		return $d;
	}
	
	//Returns latest wheather record for location 1,2 or 3
	public function get_current_weather_from_db($location=1){
		//Select the latest record for this location if it is less than an hour old
		$query="SELECT * from ll2_weather WHERE location=$location AND timestamp>".(time()-3600)." ORDER BY timestamp DESC LIMIT 1;";
		if ($res=$this->db($query)){
			if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
				return $r;
			} else {
				//There exists no current weather record. Daemon not running, or internet not working, or some other kind of error.
				return false;
			}
		} else {
			//Database error
			return false;
		}
	}
	
	function get_next_sunevent($clocknr=1){
		$time=time();
		$latitude=$this->param_retrieve_value("C".$clocknr."_LATITUDE","BILLBOARD");
		$longitude=$this->param_retrieve_value("C".$clocknr."_LONGITUDE","BILLBOARD");
		$timezone=$this->param_retrieve_value("C".$clocknr."_TIMEZONE","BILLBOARD");
		//Temporarily switch to the timezone of the clock
		date_default_timezone_set($timezone);
		//Get Sunrise/Sunset for this location
		$sr=date_sunrise($time, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));
		if (!isFuture($sr)){
			//Sunrise for this day is in the past already. Get tomorrow's.
			$sr=date_sunrise($time+DAY, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));
		}
		$ss=date_sunset($time, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));
		if (!isFuture($ss)){
			//Sunset for this day is in the past already. Get tomorrow's.
			$ss=date_sunset($time+DAY, SUNFUNCS_RET_TIMESTAMP, ($latitude/100), ($longitude/100));		
		}
		//Only announce the next sun event
		if ($sr<$ss){
			$sunevent="Sunrise in ".getHumanReadableLengthOfTime(abs($sr-$time));
		} else {
			$sunevent="Sunset in ".getHumanReadableLengthOfTime(abs($ss-$time));		
		}	
		return $sunevent;
	}

	//------------------------------------------------- CONTROLS LOCK --------------------------------------------
	
	//Sets the lock flag for the billboard. Locking can be delayed (for autolock) by $delay seconds
	function lock_controls($delay=0){
		if ($delay==0){
			if (!$this->isLocked()){
				if ($this->toggle_statusflag("locked_unlocked")){
					return true;
				}
			}
		} else {
			//Delay is not 0 = autolock later.
			if ($this->record_statusflag("locked_unlocked",1,time()+$delay)){
				$this->add_alert(time(),$delay,5,3,$this->param_retrieve_value("FILE_AUTO_LOCK","ALERTS")); //Should be replaced by autolock sound
				return true;
			}		
		}
		return false;
	}
	
	//Removes the lock flag if the pin is correct.
	function unlock_controls(){
		if ($this->isLocked()){
			if ($this->param_retrieve_value("PIN_INPUT","SYSTEM")==$this->param_retrieve_value("PIN","SYSTEM")){
				//Pin correct. Unlock.
				$this->param_store("PIN_INPUT","","SYSTEM"); //Delete the pin_input field
				if ($this->toggle_statusflag("locked_unlocked")){
					//Remove away flag on unlocking if configured
					if (($this->get_status("out_in")==1) && ($this->param_retrieve_value("DELETE_AWAY_ON_UNLOCK","SYSTEM"))){
						$this->toggle_statusflag("out_in");
					}
					return true;
				}
			}
		}
		//Wrong pin (or other issue)
		return false;
	}
	
	//----------------------------------------System status----------------------------------------------------
	//Are the controls locked?
	function isLocked(){
		return ($this->get_status("locked_unlocked")==1);
	}
	
	//Return status indication html table (or just an array in case of !$table)
	function getSystemStatus($table=true){
		$ok="<span style='color:green;'>+OK</span>";
		$warn="<span style='color:orange;text-decoration:blink;font-size:85%;'>WARNING</span>";
		$alarm="<span style='color:red;text-decoration:blink;'>ALARM</span>";
		//Assoc array with live status parameters
		$r=array();
		//HTML table
		$t="";
		//SUMMARY STATUS
		$panic=$this->systemPanic(); //Retrieve overall panic level
		if ($panic==0){
			$r["Summary status"]=$ok;
		} elseif ($panic<.7) {
			$r["Summary status"]=$warn;
		} else {
			$r["Summary status"]="<span style='color:red;text-decoration:blink;'>PANIC</span>";
		}
		if ($panic==0) { $w="HEALTHY"; } else { $w=min(100,round($panic*100))."%"; }
		$t.="<tr><td style='text-align:left;width:60%'>Summary status / panic level</td><td style='text-align:right;width:22%'>$w</td><td style='text-align:right;'>".$r["Summary status"]."</td></tr>";
		//WAN
		$r["Connectivity"]=$this->param_retrieve_value("CONNECTIVITY_INDEX","SYSTEM_STATUS");
		if ($r["Connectivity"]>0.75){
			$c=$ok;
		} elseif ($r["Connectivity"]>0){
			$c=$warn;
		} else {
			$c=$alarm;
		}
		$t.="<tr><td style='text-align:left;'>WAN connectivity</td><td style='text-align:right;'>".round($r["Connectivity"]*100)."%</td><td style='text-align:right;'>$c</td></tr>";
		//EXTERNAL IP
		$host=$this->param_retrieve_value("EXTERNAL_HOSTNAME","SYSTEM_CONFIG");
		$ip=$this->param_retrieve_value("CURRENT_IP","SYSTEM_STATUS");
		if ($this->param_retrieve_value("EXT_IP_OK","SYSTEM_STATUS")==1){
			//External IP points to us
			$c=$ok;
		} else {
			//External IP does not seem to point to us
			$c=$alarm;
		}
		$t.="<tr><td style='text-align:left;'>Ext-IP: <span style='font-size:80%'>$host</span></td><td style='text-align:right;'>".$ip."</td><td style='text-align:right;'>$c</td></tr>";					
		//EMAIL
		$latest_email_poll=$this->param_retrieve_value("LATEST_EMAIL_POLL","SYSTEM_STATUS");
		//Concern if email could not be checked for 15 Minutes. Alarm after 1 hour.
		$c=$ok;
		$distance=time()-$latest_email_poll;
		if ($distance>15*MINUTE){
			$c=$warn;
		}
		if ($distance>HOUR){
			$c=$alarm;
		}
		$t.="<tr><td style='text-align:left;'>Latest email poll</td><td style='text-align:right;'>".getHumanReadableLengthOfTime($distance)." ago</td><td style='text-align:right;'>$c</td></tr>";			
		//WEATHER
		$latest_weather_poll=$this->param_retrieve_value("LATEST_WEATHER_POLL","SYSTEM_STATUS");
		//Concern if weather could not be checked for 60 Minutes. Alarm after 2 hours.
		$c=$ok;
		$distance=time()-$latest_weather_poll;
		if ($distance>60*MINUTE){
			$c=$warn;
		}
		if ($distance>2*HOUR){
			$c=$alarm;
		}
		$t.="<tr><td style='text-align:left;'>Latest weather poll</td><td style='text-align:right;'>".getHumanReadableLengthOfTime($distance)." ago</td><td style='text-align:right;'>$c</td></tr>";			
		//Websessions
		//--LL
		$c=$ok;
		$auth = new jowe_auth();		
		$ll_sessions_total=$auth->get_active_sessions();
		$ll_sessions_local=$auth->get_active_sessions(true);
		$ll_sessions_remote=$ll_sessions_total-$ll_sessions_local;
		//Remote connections exist and might need flagging
		if ($ll_sessions_remote>0){
			$c="<span style='color:orange;text-decoration:blink;'>R</span>";		
			//More than one remote connection is a reason for a warning at any rate, so is a remote connection with no OUT flag
			if (($ll_sessions_remote>1) || (!$this->get_status("out_in"))){
				$c=$warn;
			}
		}
		$t.="<tr><td style='text-align:left;'>WSs LifeLog (rm/lc)</td><td style='text-align:right;'>$ll_sessions_remote/$ll_sessions_local</td><td style='text-align:right;'>$c</td></tr>";			
		//--jowe.de - this is stupid and makeshift, manually go into jowe_sessions/////////////////////////////////////////////////////
		//
		//
		//
		//
		//
		//
		//
		$c=$ok;
		if ($link=mysql_connect($this->mysql_host,$this->mysql_user,$this->mysql_pass,true)) {
			//Connected to mysql
			if (mysql_select_db("jowe",$link)){
				//Connected to jowe.de database
				//Total conns (hits>1 is to filter spambots)
				if ($res=mysql_query("SELECT * FROM jowe_sessions WHERE update_time>".(time()-130)." AND hits>1;",$link)){
					$jowe_sessions_total=mysql_num_rows($res);
				} else {
					$jowe_sessions_total="DB error";
				}
				//Remote conns
				if ($res=mysql_query("SELECT * FROM jowe_sessions WHERE update_time>".(time()-130)." AND NOT ((ip like '192.168.%') OR (ip like '127.%') OR (ip like '".$this->param_retrieve_value("CURRENT_IP","SYSTEM_STATUS")."%'))AND hits>1 ORDER BY update_time DESC;",$link)){
					$jowe_sessions_remote=mysql_num_rows($res);
					if ($jowe_sessions_remote>0){
						if ($r=mysql_fetch_array($res,MYSQL_ASSOC)){
							//
							$color=$this->get_transition_color("#FF8833","#990088",$this->nightview_percent(time()));
							$jowe_sessions_remote_details="<span style='color:$color;'>".(time()-$r[update_time])."s/".gethumanreadablelengthoftime(time()-$r[init_time])."/".$r["hits"]."</span>";
							//If at least one remote connection is active, indicate in status column
							$c="<span style='color:orange;text-decoration:blink;'>R</span>";
						}
					}
				}
				//Local
				$jowe_sessions_local=$jowe_sessions_total-$jowe_sessions_remote;
			}
		}
	
		$t.="<tr><td style='text-align:left;'>WSs jowe.de (rm/lc) $jowe_sessions_remote_details</td><td style='text-align:right;'>$jowe_sessions_remote/$jowe_sessions_local</td><td style='text-align:right;'>$c</td></tr>";					
		//
		//
		//
		//
		////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		//FILESYSTEMS
		$fs=explode(",",$this->param_retrieve_value("FILESYSTEMS","SYSTEM_CONFIG"));
		foreach ($fs as $value){
			//For each filesystem
			$status=explode(",",$this->param_retrieve_value("FS_$value","SYSTEM_STATUS"));
			$c=$ok;
			if ($status[0]>92){
				$c=$warn;
			} elseif ($status[0]>97){
				$c=$alarm;
			}
			$t.="<tr><td style='text-align:left;'>FS load $value (".$status[1].")</td><td style='text-align:right;'>".$status[0]."% of ".$status[2]."</td><td style='text-align:right;'>$c</td></tr>";			
			$r["FS load $value"]=$status[0]."%";
		}
		
		//What to return?
		if ($table){
			//The table
			return "<table style='width:100%;'>$t</table>";
		} else {
			//The array
			return $r;
		}
	}
	
	//Retrieve the system panic level
	function systemPanic(){
		return ($this->param_retrieve_value("PANIC","SYSTEM_STATUS"));
	}

	//Check if we can reach $testhost (i.e. if ping reponse comes back)
	//Returns assoc array
	function CheckConnectivity($testhost,$pings=2){
		if ($pings<1) { $pings=1; } //At least 1 ping!
		$r=array();
		exec("ping -c$pings $testhost", $ping_result);
		//We are interested in element 3+$pings of the resulting array, which should look sth like: "2 packets transmitted, 2 received, 0% packet loss, time 1000ms"
		$a=explode(", ",$ping_result[3+$pings]);
		//Now it is a[0] amd a[1] who have sent and received packets
		$r["packets out"]=substr($a[0],0,strpos($a[0]," "));
		$r["packets in"]=substr($a[1],0,strpos($a[1]," "));
		if ($r["packets out"]<1){
			$r["succes rate"]=0;
		} else {
			//Apparently packets went out, so we can check some more 
			$r["success rate"]=$r["packets in"]/$r["packets out"];
			//We'll also get the average time from $a[4+$pings] which should look like: "rtt min/avg/max/mdev = 77.675/78.960/80.246/1.315 ms"
			$a=explode(" ",$ping_result[4+$pings]);
			$b=explode("/",$a[3]); //eg"77.675/78.960/80.246/1.315"
			$r["avg response time"]=$b[1];
		}
		$r["timestamp"]=time(); //Save the timestamp of the test
		return $r;
	}

	//Diagnoses the filesystem $t and saves to ll2_params /SYSTEM_STATUS
	function diagnoseFileSystem($t){
		$p=exec("df -h $t");
		//Find percentage
		$end=strpos($p,"%");
		$start=strrpos(substr($p,0,$end)," ")+1;
		$full=substr($p,$start,$end-$start);
		//Find mount point
		$mpoint=substr($p,strrpos($p," ")+1);
		//Find capacity
		$start=strlen(substr($p,0,strpos($p," ")));
		$p=substr($p,$start);
		$p=trim($p);
		$capacity=substr($p,0,strpos($p," "));
		$this->param_store("FS_$t",$full.",".$mpoint.",".$capacity."","SYSTEM_STATUS");
		//Return panic level for this fs
		$panic=0;
		if ($full>92){$panic=.2;} elseif ($full>97) {$panic=.7;}
		return $panic;
	}

	//Diagnoses the system and saves the data to ll2_params /SYSTEM_STATUS. This is typically called by a daemon only
	function diagnoseSystem(){
		$panic=0; //The panic index
		//----------------------------------------------------WAN CONNECTION----------------------------------------------------------------
		//WAN connection test. Try to connect to test hosts
		$hosts=explode(",",$this->param_retrieve_value("TESTHOSTS","SYSTEM_CONFIG"));
		if (count($hosts)>0){
			//At least 1 testhost
			$pings=$this->param_retrieve_value("TEST_PINGS_NUM","SYSTEM_CONFIG");
			$connectivity=0;
			foreach ($hosts as $value){
				$a=$this->CheckConnectivity($value,$pings);
				$connectivity+=$a["success rate"]; //Increase connectivity index according to results for this host
				$this->param_store("LATEST_RESULTS_$value",implode(",",$a),"SYSTEM_STATUS");
			}
			$connectivity_index=$connectivity/count($hosts); //What percentage of hosts was (how well) reachable?
		} else {
			$connectivity_index=-1; //No test hosts given
		}
		//Connection test done. We have stored the results for each host, and also created a connectivity index.
		//Save connectivity index
		$this->param_store("CONNECTIVITY_INDEX",$connectivity_index,"SYSTEM_STATUS");
		//Ensure status
		if ($connectivity_index>0){
			//At least some connectivity
			$this->record_statusflag("connected_disconnected",true);
		} else {
			//No connection!
			$this->record_statusflag("connected_disconnected",false);		
		}
		//Add reverse connectivity_index to panic_index
		$panic+=(1-$connectivity_index);
		//------------------------------------------------GET OUR HOSTNAME---------------------------------------------------------------
		$ip=getHostByName($this->param_retrieve_value("EXTERNAL_HOSTNAME","SYSTEM_CONFIG"));
		if ($ip!=$this->param_retrieve_value("EXTERNAL_HOSTNAME","SYSTEM_CONFIG")){
			//Resolution was successful (i.e., getHostByName did not return the unchanged hostname)
			//Now store the ip in system status
			$this->param_store("CURRENT_IP",$ip,"SYSTEM_STATUS");		
			$this->param_store("LATEST_DNS_CHECK",time(),"SYSTEM_STATUS");
		} else {
			//Could not resolve. But that need not be of concern (sometimes fails)
		}		
		//-----------------------------------------------SEE IF OUR IP POINTS TO US---------------------------------------------------
		$r=$this->checkConnectivity($this->param_retrieve_value("EXTERNAL_HOSTNAME","SYSTEM_CONFIG"),1);
		if (isset($r["avg response time"]) && ($r["avg response time"]<5)){
			//If the server response in less than 5ms all is well. 
			$this->param_store("EXT_IP_OK",1,"SYSTEM_STATUS");
		} else {
			//Response time to long. External hostname does not seem to point to us.
			$this->param_store("EXT_IP_OK",0,"SYSTEM_STATUS");
		}
		//-------------------------------------------------FILESYSTEMS------------------------------------------------------------------
		$fs=explode(",",$this->param_retrieve_value("FILESYSTEMS","SYSTEM_CONFIG"));
		foreach  ($fs as $value){
			$panic+=$this->diagnoseFileSystem($value);
		}
		//------------------------------------------------EMAIL------------------------------------------------------------------------------
		//This is in the system anyways (done by email deamon), but check panic indication
		$latest_email_poll=$this->param_retrieve_value("LATEST_EMAIL_POLL","SYSTEM_STATUS");
		$distance=time()-$latest_email_poll;
		if ($distance>15*MINUTE){
			$panic+=.2;
		}
		if ($distance>1*HOUR){
			$panic+=.6;
		} 
		
		//------------------------------------------------WEATHER--------------------------------------------------------------------------
		//This is in the system anyways (done by weather deamon), but check panic indication
		$latest_weather_poll=$this->param_retrieve_value("LATEST_WEATHER_POLL","SYSTEM_STATUS");
		$distance=time()-$latest_weather_poll;
		if ($distance>45*MINUTE){
			$panic+=.2;
		}
		if ($distance>2*HOUR){
			$panic+=.5;
		} 
		//-------------------------- STORE PANIC INDEX ------------------
		$this->param_store("PANIC",$panic,"SYSTEM_STATUS");
	}
	
	//////////////////////////POWERCONTROL
	
	//Returns an array with 8 elements each of which will be either 1 or 0 depending on whether or not the bit is set
	//http://neverblog.net/php-function-to-parse-out-an-integer-into-its-component-bits/
	function parse_my_bits( $int = null )  {
	     $bits = array();
	     $i=1;
	     for($j=0;$j<8;$j++) {
		 if( ($i & $int) > 0) {
			$bits[$j]=1;
		} else {
			$bits[$j]=0; 
		}
		 $i=$i*2;
	     }
	     return $bits;
	}	
	
	//Take bit array and calculate int value
	function bits_to_int($bits){
		$r=0;
		$j=1;
		for ($i=1;$i<=sizeof($bits);$i++){
			if ($bits[$i-1]==1){
				$r=$r+($j);
			}
			$j=$j*2;
		}
		return $r;
	}

	
	//Set the Bathroom LED
	public function setBathroomLED($newstatus){
		$s=0;
		if ($newstatus){
			$s=1;
		}
		shell_exec("curl \"192.168.1.221/switchled.php?s=$s\" 2>/dev/null &");
	}
	
	//Determine the current status of channel $n from the status table
	function get_channel_status($n,$physical=false){
		if (($n>0) && ($n<9)){
			//Port 1
			if ($physical){
				$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT1_PHYSICAL","POWERCONTROL"));			
			} else {
				$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT1","POWERCONTROL"));
			}
			return $bits[$n-1];
		} elseif (($n>8) && ($n<17)){
			//Port 2
			$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT2","SYSTEM_STATUS"));
			if ($physical){
				$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT2_PHYSICAL","POWERCONTROL"));			
			} else {
				$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT2","POWERCONTROL"));
			}
			return $bits[$n-9];
		} elseif (($n>16) & ($n<=20)){
			return $this->param_retrieve_value("PCT_CH$n","POWERCONTROL");
		}
	}
	
	//Write a value to channel $n (1 or 0) without affecting other channels 
	function set_channel_status($n,$x){
		if (($n>0) && ($n<9)){
			//Port 1
			$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT1","POWERCONTROL")); //Read the whole port
			$bits[$n-1]=$x; //Set new value for channel $n
			$z=$this->bits_to_int($bits);
			$this->param_store("PCT_PORT1",$z,"POWERCONTROL");
		} elseif (($n>8) && ($n<17)){
			//Port 2
			$bits=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT2","POWERCONTROL")); //Read the whole port
			$bits[$n-9]=$x; //Set new value for channel $n
			$z=$this->bits_to_int($bits);
			$this->param_store("PCT_PORT2",$z,"POWERCONTROL");
		}	
		$this->param_store("PCT_CH$n",$x,"POWERCONTROL");
		//Channels 17 qnd 18: bedroom RPI
		if (($n==17) || ($n==18)):
			//Determine gpio pin number.
			//Ch 17 -> GPIO 18, physical 12
			//Ch 18 -> GPIO 27, physical 13
			$gpio=27; 
			if ($n==17) { $gpio=18; }
			shell_exec("curl \"http://192.168.1.220/switchrelais.php?gpio=$gpio&value=$x\" > /dev/null 2>/dev/null &");		
		endif;
		//Channels 19 and 20: pct RPI
		if (($n==19) || ($n==20)):
		//Determine gpio pin number.
		//Ch 19 -> GPIO 14
		//Ch 20 -> GPIO 4
		$gpio=4;
		if ($n==19) { $gpio=14; }
		shell_exec("curl \"http://192.168.1.221/switchrelais.php?gpio=$gpio&value=$x\" > /dev/null 2>/dev/null &");
		endif;
		
		//Bathroom LED indicates heater status
		if ($n==8){
			$this->setBathroomLED($x);		
		}
		//Couple DND LED with bathroom light
		if ($n==19){
			$this->setDNDLED('', $x);
		}
	}
	
	function pct_toggle_channel($n){
		$x=$this->get_channel_status($n);
		if ($x==1) {$x=0;} else {$x=1;}
		$this->set_channel_status($n,$x);
	}
	
	function pct_recall_preset($n){
		//Read requested pattern
		$ps=$this->param_retrieve_value("PCT_PRESET$n","POWERCONTROL");
		for ($i=1;$i<(min(19,strlen($ps)+1));$i++){
			//Preset value for this channel
			$p=substr($ps,$i-1,1); 
			//Is this channel determined in the preset?
			if (($p=="0") || ($p=="1")){
				//Preset determines this channel (0 or 1, not *), so set it
				$this->set_channel_status($i,$p);
			}
		}
	}
	
	function pct_recall_timeofday_preset(){
		if ($this->is_nightview()){
			$this->pct_recall_preset("_NIGHT");
		} else {
			$this->pct_recall_preset("_DAY");		
		}
	}
	
	//Read the PCT ports and convert to a series of 101010001
	function pct_ports_to_bitstring(){
		$r="";
		$bits_p1=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT1","POWERCONTROL")); //Read the whole port	
		$bits_p2=$this->parse_my_bits($this->param_retrieve_value("PCT_PORT2","POWERCONTROL")); //Read the whole port
		foreach ($bits_p1 as $value){
			$r.=$value;
		}
		foreach ($bits_p2 as $value){
			$r.=$value;
		}
		return $r;
	}
	
	//Check whether the patterns match
	function pct_match_channel_patterns($p1,$p2){
		$r=true;
		$l=min(strlen($p1),strlen($p2)); //Pick the shorter pattern
		if ($l>0){
			for ($i=1;$i<=$l;$i++){
				//Get character
				$a=substr($p1,$i-1,1);
				$b=substr($p2,$i-1,1);
				//Determine match
				if ((($a!="1") && ($a!="0")) || (($b!="1") && ($b!="0"))) {
					//At least one of them has *
				} else {
					//Both of them are either 1 or 0 - are they the same?
					if ($a!=$b){
						$r=false;
					}
				}
			}
		} else {
			//Return false if one of the patterns is empty
			$r=false;
		}
		return $r;
	}
	
	//See whether preset $n is currently active (i.e. fits the active pattern)
	function pct_check_preset_activity($n){
		//Read requested pattern
		$ps=$this->param_retrieve_value("PCT_PRESET$n","POWERCONTROL");
		//Read current port patterns
		$current=$this->pct_ports_to_bitstring();
		return ($this->pct_match_channel_patterns($ps,$current));
	}

	//Return formatted html for billboard
	function getPCTstatus(){
		//Channels
		for ($i=1;$i<25;$i++){
			//Position
			$pos="position:absolute;left:".((($i-1)%4)*25)."%;top:".(floor((($i-1)/4))*14)."%;";
			if ($i<17){
				//Channels
				//See if channel has name
				$name=$this->param_retrieve_value("PCT_CH".$i."_NAME","POWERCONTROL");
				//If not, just display its number
				if ($name==""){
					$name=$i;
				} else {
					//$name="$i-$name";
				}
				//Get status
				$s=$this->get_channel_status($i);
				$t=$this->get_channel_status($i,true);
				if ($s!=$t){
						$bg="#666600";			
				} else {
					if ($s==1){
						//Channel active
						$bg="#006600";
					} else {
						//Channel off
						$bg="#660000";			
					}
				}
				//Channel #/name and link
				$content="
					
					<form id='f$i' action='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=standard&processform=pct_toggle_channel&channel=$i"."' method='post'>
					<a style='color:#AAA;' href='javascript:{}' onclick=\"document.getElementById('f$i').submit(); return false;\">$name</a>
					</form>
					";
					//<a href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=standard&processform=pct_toggle_channel&channel=$i"."' style='color:#AAA;' >$name</a>
					
				
			} else {
				//Presets
				//See if preset has name
				$name=$this->param_retrieve_value("PCT_PRESET".($i-16)."_NAME","POWERCONTROL");
				//If not, just display its number
				if ($name==""){
					$name="preset ".($i-16);
				}
				//Get status
				if ($this->pct_check_preset_activity($i-16)){			
					//Preset active
					$bg="#222266";
				} else {
					$bg="#444444";			
				}
				//Preset #/name and link			
				$content="
				
					<form id='f$i' action='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=standard&processform=pct_recall_preset&preset=".($i-16)."' method='post'>
					<a style='color:#AAA;' href='javascript:{}' onclick=\"document.getElementById('f$i').submit(); return false;\">$name</a>
					</form>
					";
				
					//<a href='".me()."&bb_width=".$_GET["bb_width"]."&bb_height=".$_GET["bb_height"]."&view=standard&processform=pct_recall_preset&preset=".($i-16)."' style='color:#AAA;'>$name</a>
					
					
			}	
			//Content #/name and link
			$r.=" <div style='$pos width:25%;height:12%;'>
					<div style='position:relative;width:98%;height:100%;margin-left:auto;margin-right:auto;background:$bg;'>
						$content
					</div>
				</div>";
		}
		return $r;
	}

}
?>