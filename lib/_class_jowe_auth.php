<?php
/*
	JW, July 13, 2010
*/
class jowe_auth {
	
	//Database info for auth tables
	private $mysql_host="mariadb";
	private $mysql_user="jowede";
	private $mysql_pass="33189";
	private $mysql_database="jowe_auth";
	
	private $connected=false; //Is instance connected to the database?
	private $link;		    //Resource link of connected object
	
	private $session_timeout_lan=60480000; //Session timeout for local machines is 100 weeks
	private $session_timeout_remote=43200; //Session timeout for internet is 12 hrs
	
	private $local_ip_range="192.168.";


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

	//Database query (internal)
	function db($q) {
		if ($this->connected) {
			return mysql_query($q,$this->link);
		} else {
			return false;
		}
	}

	//---------------------------- CONSTRUCTOR/DESTRUCTOR -------------------

	function __construct() {
		$this->connect();
		return $this->link;
	}
	
	function __destruct() {
		if ($this->connected) {
			mysql_close($this->link);
		}
	}

	//---------------------------- TABLE CREATION FUNCTIONS -------------------
	
	public function create_tables() {
		echo "<br>CREATING TABLES (class_jowe_auth)...";
		//Table users holds usernames and their login info
		if ($this->db("CREATE TABLE users (
				id INT AUTO_INCREMENT,
				timestamp INT,
				login VARCHAR(20),
				password VARCHAR(20),
				lastname VARCHAR(50),
				firstname VARCHAR(50),
				email VARCHAR(50),
				phone VARCHAR(30),
				cell VARCHAR(30),
				address VARCHAR(150),
				zip VARCHAR(15),
				country VARCHAR(50),
				notes TEXT,
				PRIMARY KEY (id, login)
				);")) {
			echo "<br>SUCCESS (users)";
		} else {
			echo "<br>ERROR (user)";
		}
		//Table services holds services hierarchically (modified preorder tree traversal)
		if ($this->db("CREATE TABLE services (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(50),
				parent INT,
				lft INT,
				rgt INT
				);")) {
			echo "<br>SUCCESS (services)";
		} else {
			echo "<br>ERROR (services)";
		}
		//Table permissions connects users with service nodes
		if ($this->db("CREATE TABLE permissions (
				id INT PRIMARY KEY AUTO_INCREMENT,
				user VARCHAR(20),
				service INT
				);")) {
			echo "<br>SUCCESS (permissions)";
		} else {
			echo "<br>ERROR (permissions)";
		}
		//Table session holds live session data
		if ($this->db("CREATE TABLE sessions (
			id INT PRIMARY KEY AUTO_INCREMENT,
			sid CHAR(10) ,
			user VARCHAR(20),
			hits INT,
			ip CHAR(15),
			init_time INT,
			update_time INT,
			active BOOL)"))
		{ 
			echo "<br>SUCCESS (sessions)";
		} else {
			echo "<br>ERROR (sessions)";
		}
		
	}

	public function drop_tables() {
		echo "<br>DROPPING TABLES (class_jowe_auth)...";
		if ($this->db("DROP table users;")) {
			echo "<br>SUCCESS (users)";
		} else {
			echo "<br>ERROR (users)";
		}
		if ($this->db("DROP table services;")) {
			echo "<br>SUCCESS (services)";
		} else {
			echo "<br>ERROR (services)";
		}
		if ($this->db("DROP table permissions;")) {
			echo "<br>SUCCESS (permissions)";
		} else {
			echo "<br>ERROR (permissions)";
		}
		if ($this->db("DROP table sessions;")) {
			echo "<br>SUCCESS (sessions)";
		} else {
			echo "<br>ERROR (sessions)";
		}
		
	}


	//========================================== PUBLIC FUNCTIONS =======================

	//---------------------------- USERS --------------------------------
	//Check if user exists
	public function user_exists($login) {
		if ($res=$this->db("SELECT * from users WHERE login='$login';")) {
			if (mysql_num_rows($res)>0) {
				return true;
			} else {
				return false;
			}
		}
	}

	//Check the user's password (will simply return false if user does not exist)
	public function check_password($login,$password) {
		if ($res=$this->db("SELECT password from users WHERE login='$login';")) {
			if (mysql_num_rows($res)>0) {
				$row=mysql_fetch_array($res);
				if ($row["password"]==$password) {
					return true;
				}
			}
		}
		return false;
	}

	//Create a new user
	public function create_user($login,$password,$lastname,$firstname,
				$email,$phone,$cell,$address,$zip,$country,$notes) {
		//Check whether loginname exists already		
		if ($res=$this->db("SELECT id from users where login='$login';"))
		{
			if (mysql_num_rows($res)>0) {
				//User exists
				return false;
			} else {
				//User does not yet exist, attempt to create.
				return ($this->db("INSERT into users values (
							'',
							'".time()."',
							'".mysql_real_escape_string($login)."',
							'".mysql_real_escape_string($password)."',
							'".mysql_real_escape_string($lastname)."',
							'".mysql_real_escape_string($firstname)."',
							'".mysql_real_escape_string($email)."',
							'".mysql_real_escape_string($phone)."',
							'".mysql_real_escape_string($cell)."',
							'".mysql_real_escape_string($address)."',
							'".mysql_real_escape_string($zip)."',
							'".mysql_real_escape_string($country)."',
							'".mysql_real_escape_string($notes)."'
							);"));				
			}
		}
	}
	
	//Delete an existing user
	public function delete_user($login) {
		//Does user even exist?
		if ($this->user_exists($login)) {
			//User exists, try to delete
			if ($this->db("DELETE from users where login='$login';")) {
				//Was deletion successful
				if (!$this->user_exists($login)) {
					//User now does not exist anymore, success!
					return true;
				} else {
					//User still exists, something went wrong
					return false;
				}	
			}
		} else {
			//User never existed, so we can't delete
			return false;
		}
	}

	//---------------------------- SERVICES --------------------------------
	
	//Build hierarchy with Modified preorder tree traversal
	private function build_tree($node=1,$left=1) {
		//Assume this is a leaf
		$right=$left+1;
		//Get children if there are any
		$res=$this->db("SELECT id FROM services WHERE parent=$node;");
		//Execute function for each child
		while ($row=mysql_fetch_array($res)) {
			//The rgt value of the last child plus 1 will be the rgt value for the calling node
			$right=$this->build_tree($row["id"],$right)+1; 
		}
		//We know $left, now since the children have been processed we also know $right and can store
		if ($this->db("UPDATE services SET lft=$left, rgt=$right WHERE id=$node;")) 
		//Return $right for the calling node
		return $right;
	}

	//Create a service-node ($parent is id of parent node; use get_service_id() if needed)
	public function create_service($name,$parent) {
		//Service names should be unique, check if $name exists already
		if ($this->get_service_id($name)==0) {
			//Service name is available, create it
			if ($this->db("INSERT into services VALUES('','$name',$parent,'','');")) {	
				//Just rebuild the whole tree
				$this->build_tree();
				return true;
			}
		}
		return false;
	}

	//Display tree (http://articles.sitepoint.com/print/hierarchical-data-database)
	public function display_tree($node=1) {
		//Retrieve this node
		$res=$this->db("SELECT * FROM services WHERE id=$node;");
		$row=mysql_fetch_array($res);
		//Create empty array of rgt values
		$right=array();
		//Retrieve the children of this node
		$res=$this->db("SELECT * FROM services WHERE lft BETWEEN ".$row["lft"]." AND ".$row["rgt"]." ORDER BY lft ASC;");
		//Go through each child
		while ($row=mysql_fetch_array($res)) {
			//Pop off values from the $right stack if necessary
			if (count($right)>0) {
				while($right[count($right)-1]<$row["rgt"]) {
					array_pop($right);
				}
			}
			//Display indented node name
			echo "<br>".str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",count($right))
					.$row["name"]." (".$row["id"].")";
			//Add this node to the stack
			$right[]=$row["rgt"];
		}
	}
	
	//Return the ID of the service with the name $service_name (0 if non-extant)
	public function get_service_id($service_name) {
		$res=$this->db("SELECT id FROM services WHERE name='$service_name'");
		if (mysql_num_rows($res)>0) {
			$row=mysql_fetch_array($res);
			return $row["id"];
		}
		return 0;
	}
	
	//---------------------------- PERMISSIONS --------------------------------
	
	//Add a permission record. Expects service id and user login name
	public function add_permission($service,$user) {
		//Verify existence of user $user and make sure this permission does not exist already
		if (($this->user_exists($user)) && (!$this->check_user_permission($service,$user))){
			$res=$this->db("SELECT * from services WHERE id=$service;");
			if (mysql_num_rows($res)>0) {
				//Both user and service exist, add record
				if ($this->db("INSERT into permissions VALUES('','$user',$service);")) {
					return true;
				}
			}
		}
		return false;
	}

	//Check a permission (in the tree) Expects service id and user login name
	public function check_user_permission($service,$user) {
		//Grab the requested service node's lft (could also be the rgt, doesnt matter)
		if ($res=$this->db("SELECT lft FROM services WHERE id=$service;")) {
			$row=mysql_fetch_array($res);
			$target_lft=$row["lft"];
		}
		//Grab all the user's permissions
		if ($res=$this->db("SELECT * from permissions WHERE user='$user';")) {
			//Look at each permission
			while($row=mysql_fetch_array($res)) {
				//Grab this permissions' service node's lft/rgt values
				if ($res2=$this->db("SELECT lft,rgt FROM services WHERE id=".$row["service"].";")) {
					$row2=mysql_fetch_array($res2);
				}
				if (($row2["lft"]<=$target_lft) && ($row2["rgt"]>$target_lft)) {
					//Permission granted
					return true;
				}
			}
			//None of the permissions applies to this service - not granted.
		}
		//The user does not have any permissions or does not event exist
		return false;
	}

	//Check user permission throug session-id, service-id  [to be called by validate_session() ]
	private function check_session_permission($service,$session) {
		//Grab the session's owner
		$res=$this->db("SELECT user FROM sessions WHERE sid='$session';");
		if ($row=mysql_fetch_array($res)) {
			//Now we know the username of the sessions' owner and can check the permission
			return $this->check_user_permission($service,$row["user"]);
		}
	}

	//---------------------------- SESSIONS --------------------------------
	private function create_sessionid($length)
	{
		$sid="";
		for ($i=0;$i<$length;$i++)
		{
			$r=mt_rand(48,122);  //45-57, 65-90, 97-122
			while ( (($r>57)&&($r<65)) or (($r>90)&&($r<97)) )
			{ //get one digit
				$r=mt_rand(48,122);				
			}
			$sid=$sid.chr($r);
		}
		return $sid;
	}
	
	//Generate a session for $user with $pass
	public function create_session($user,$pass) {
		//Check login data first
		if ($this->check_password($user,$pass)) {
			//Login data OK
			//Now get session ID and make sure that the unlikely case of preexisting sid gets caught
			$p=true;
			while ($p) {
				//Get session id
				$new_sid=$this->create_sessionid(10);
				//check if it exists already
				$res=$this->db("SELECT * FROM sessions WHERE sid='$new_sid';");
				if (mysql_num_rows($res)==0) {
					//sid does not exist in db, so all is good. break.
					$p=false;
				}
			}
			//Write session info
			$this->db("INSERT INTO sessions VALUES('','$new_sid','$user',1,'".$_SERVER['REMOTE_ADDR']."',".time().",".time().",true);");
			//Return the session id
			return $new_sid;
		}
		return false;
	}
	
	//Return the full details of the session owner
	public function get_session_owner($session) {
		//Find session record, which has loginname of owner
		$res=$this->db("SELECT user FROM sessions WHERE sid='$session';");
		if ($row=mysql_fetch_array($res,MYSQL_ASSOC)) {
			//Now grab full user record via user login name and return
			$res2=$this->db("SELECT * FROM users WHERE login='".$row["user"]."';");
			if ($row2=mysql_fetch_array($res2,MYSQL_ASSOC)) {
				return $row2;
			}
		}
		return false;
	}
	
	//Check whether or not this IP begins with "192.168."
	private function isLocalConnection($ip) {
		return (substr($ip,0,8)==$this->local_ip_range);
	}
	
	//Check session-id and permissions as well as IP and timeout, and update session
	public function validate_session($service,$session) {
		//Is the owner of the session allowed to use $service?
		if ($this->check_session_permission($service,$session)) {
			//Retrieve session info to check IP and session timeout
			$res=$this->db("SELECT * FROM sessions WHERE sid='$session' AND active=true;");
			if ($row=mysql_fetch_array($res)) {
				//Does the session belong to the inquiring host?
				if ($row["ip"]==$_SERVER["REMOTE_ADDR"]) {
					//Yes, now check timeout.
					//Local or remote connection?
					$validity=$this->session_timeout_remote;
					if ($this->isLocalConnection($_SERVER["REMOTE_ADDR"])) {
						$validity=$this->session_timeout_lan;
					}
					//Is the session still valid?
					if ($row["update_time"]>(time()-$validity)) {
						//Yes. Now update session
						$this->db("UPDATE sessions SET update_time=".time().",hits=hits+1 WHERE sid='$session';");
						return true;
					}
				}
			}
		}
		return false;
	}
	
	//Logout 
	public function destroy_session($session) {
		return $this->db("UPDATE sessions set active=false where sid='$session';");
		/*
		//Simply delete session record
		$res=$this->db("DELETE FROM sessions WHERE sid='$session';");
		*/
	}
	
	//Is user $user (Loginname) logged in (i.e. has at least one valid session that has been inactive for less than $inactive seconds)
	//If $external, check for remote IP to be non-intranet
	//If $include_inactive_sessions then this becomes more of a "was_logged_in within $inactive?" function
	public function is_logged_in($user,$external=true,$inactive=120,$include_inactive_sessions=false){
		if ($include_inactive_sessions){
			$cond="";
		} else {
			$cond="AND active=true";
		}
		if ($res=$this->db("SELECT * FROM sessions WHERE user='$user' AND update_time>".(time()-$inactive)." $cond;")){
			//User has one ore more active sessions
			while ($r=mysql_fetch_array($res)){
				if (!$external){
					//Break and return if internal/external does not matter
					return true;
				} else {
					//Break and return if external matters AND it is an external connection
					if (!($this->isLocalConnection($r["ip"]))){
						return true;
					}
				}
			}
		}
		return false;
	}
	
	//Returns the number of currently active sessions (active = valid sids that have been idle for less than $inactive)
	public function get_active_sessions($local_sessions_only=false,$inactive=70){
		$cond="AND hits>1"; //Hits>1 filters spambots
		if ($local_sessions_only){
			$cond.=" AND (ip like '".$this->local_ip_range."%' OR ip like '127.%') ";
		}
		if ($res=$this->db("SELECT * FROM sessions WHERE active=true AND update_time>".(time()-$inactive)." $cond;")){
			return mysql_num_rows($res);
		}
		return false;
	}
}


?>