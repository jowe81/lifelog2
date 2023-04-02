<?php
/*
	JW, Aug 04 2010
	
	Message parsing etc

*/

	//Take the array of headerlines (id,line) and create an array of extracted_headerlines, with the keys being from, to, date etc.
	function extract_headers($headers,$keys=array('date','from','to','subject','reply-to','content-type','boundary','content-transfer-encoding','filename','name')){
		$extracted_headers=array();
		//Init this array in case not all keys will be filled
		foreach ($keys as $value){
			$extracted_headers[$value]="";
		}
		//Now extract
		foreach ($headers as $key=>$value){
			//Extract key of the current headerline (anything left from the colon)
			if ($colon=strpos($value,": ")) {
				//Colon is present
				$ckey=trim(strtolower(substr($value,0,$colon)));
			} else {
				//No colon, so look for = (for boundary)
				$ckey=trim(strtolower(substr($value,0,strpos($value,"="))));				
			}
			//See if the key is wanted
			if (in_array(strtolower($ckey),$keys)){
				if ($colon){
					//Save this key with its value (anything to the right of the colon) in the $extracted_headers array
					$cvalue=trim(substr($value,strpos($value,": ")+2));
				} else {
					//No colon, but = (probably boundary)
					$cvalue=trim(substr($value,strpos($value,"=")+1),"\""); //trim quotes
				}
				$extracted_headers[$ckey]=$cvalue;
				//See if in the value are actually further variables, anything like 'variable=value' (e.g.boundary)
				if ($equal=strpos($cvalue,"=")){
					//SUBKEY
						$str_before_equal=substr($cvalue,0,$equal); //The part of the string before the equal sign - this is where we need to find the key
						$last_space_before_equal=strrpos($str_before_equal," ");
						$subkey=trim(substr($str_before_equal,$last_space_before_equal)); //This should be the key before the =
					//SUBVALUE
						$str_after_equal=trim(substr($cvalue,$equal+1)); //Trim whitespace directly after the =. Now the value begins at pos 0
						if ($space=strpos($str_after_equal," ")){
							//There is another space, so only copy until then
							$subvalue=trim(substr($str_after_equal,0,$space));
						} else {
							//No other space, so whole rest of line=value
							$subvalue=$str_after_equal;
						}
					$extracted_headers[$subkey]=$subvalue;
				}
			}
		}
		//In case we got a date, get timestamp
		if ($extracted_headers["date"]!=""){
			$extracted_headers["__timestamp"]=strtotime($extracted_headers["date"]);
		}
		//Try to extract the address in 'from' and save to '__sender_address'
		if ($extracted_headers["from"]!=""){
			$f=$extracted_headers["from"]; //just for legibility
			$startpos=strpos($f,"<")+1;
			$endpos=strrpos($f,">");
			$length=$endpos-$startpos;
			$sender_address=substr($f,$startpos,$length);
			$extracted_headers["__sender_address"]=$sender_address;
		}
		//Determine message type (plaintext,html,mime?), save to '__message_type'
		$extracted_headers["content-type"]=strtolower($extracted_headers["content-type"]);
		$extracted_headers["__message_type"]="unknown: (".$extracted_headers["content-type"].")"; //Default to this
		if (strpos($extracted_headers["content-type"],"text")!==false){
			//A text message: plain or html?
			if (strpos($extracted_headers["content-type"],"plain")!==false){
				$extracted_headers["__message_type"]="plain";
			} elseif (strpos($extracted_headers["content-type"],"html")!==false){
				$extracted_headers["__message_type"]="html";
			} else {
				$extracted_headers["__message_type"]="unknown text format";
			}
		} elseif (strpos($extracted_headers["content-type"],"multipart")!==false){
			//A multipart message
			$extracted_headers["__message_type"]="multipart";
		}
		
		
		return $extracted_headers;
	}

	//Parse message ($raw is an array of lines). Returns array.
	function parse_message($raw){
		$msg=array(); //The result array
		
		//Headers
		$headers=array();
		$rs=trim(next($raw));
		while ($rs!=""){ //empty line marks end of headers
			//Add line to headers-array
			$headers[]=$rs;
			//Read next line
			$rs=trim(next($raw));
		}
		//Parse headers
		$msg["headers"]=extract_headers($headers);

		//Body
		if ($msg["headers"]["__message_type"]!="multipart"){
			//--non-multipart message (Text or html), don't separate body into lines
			$t="";
			//Read until single point on empty line
			while (ereg_replace("[\t\n\r]","",$rs)!="."){
				if (!$rs=next($raw)) { break; }
				$t.=$rs;
			}
			$msg["body"]=$t;
		} else {
			//If it's a multipart message we now need to seperate the message body into parts
			$b="--".$msg["headers"]["boundary"]; //MIME boundary string
			$part=0;
			$msg["parts"]=array();
			//Read until single point on empy line
			$rs=next($raw); //This is the first line of the body.
			while (trim($rs)!="."){
				//Is current line a boundary?
				if (trim($rs)==$b){
					//New Part found
					$part++;
					$msg["parts"][$part]=array();
					$msg["parts"][$part]["headers"]=array();
					$msg["parts"][$part]["body"]="";
					$section="headers"; //What section of the part are we working on?
					$headers=array(); //Temp storage of part headers
				} elseif (trim($rs)==$b."--") {
					//End of last part found
				} else {
					//Current line belongs to part $part
					//So add it to it
					if ($part>0){
						if ($section=="headers"){
							//Working on the headers
							if (trim($rs)!=""){
								//Non-empty line: still a header
								$headers[]=$rs;
							} else {
								//Empty line -> end of headers
								//Now extract headers
								$msg["parts"][$part]["headers"]=extract_headers($headers);
								//Next section to work on is the body
								$section="body";
							}
						} else {
							//Working on the body: again, no line seperation
							$msg["parts"][$part]["body"].=$rs;
						}
					}
				}
				//Read next line
				if (!$rs=next($raw)){
					break;
				}
			}
			//Now we should have each part in $msg["parts"][$part]["headers"] and $msg["parts"][$part]["body"]

			//The number of parts is cound(in $part)
			$msg["headers"]["__parts"]=$part;
		}
		return $msg;	
	}



?>