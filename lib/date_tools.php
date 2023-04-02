<?php
	//Date-time related constants
	DEFINE ("WEEK",604800);
	DEFINE ("DAY",86400);
	DEFINE ("HOUR",3600);
	DEFINE ("MINUTE",60);
	
	//Number of full days in $secs seconds
	function days($secs) {
		return floor($secs/DAY);
	}

	//Number of full hours in $secs seconds
	function hours($secs) {
		return floor($secs/HOUR);
	}
	
	//Number of full minutes in $secs seconds
	function minutes($secs) {
		return floor($secs/MINUTE);
	}

	//Determine whether the two timestamps belong to the same day
	function isSameDay($time1,$time2) {
		return (date("Ymd",$time1)==date("Ymd",$time2));
	}

	//Determine whether the timestamp belongs to today
	function isToday($time) {
		return (isSameDay($time,time()));
	}
	
	function isYesterday($time){
		return (isToday($time+DAY));
	}
	
	//Determine whether the timestamp belongs to today
	function isTomorrow($time) {
		return (isSameDay($time,time()+DAY));
	}

	//Timestamp in the future?
	function isFuture($time) {
		return ($time>time());
	}
	
	//Timestamp in the past?
	function isPast($time) {
		return ($time<time());
	}

	//Timestamp before 00:00:00 today?
	function isBeforeToday($time) {
		return (getBeginningOfDay(time())>$time);
	}
	
	//Determine whether the timestamp is less than 1 week old
	function isThisWeek($time) {
		return ($time>(time()-(86400*7)));
	}
	
	//Two timestamps within the same week? ($shift is shift from Sunday 00:00:00)
	function isSameWeek($time1,$time2,$shift=0){
	    return getBeginningOfWeek($time1)==getBeginningOfWeek($time2);
	}
	
	function isSameMonth($time1,$time2){
	    return getBeginningOfMonth($time1)==getBeginningOfMonth($time2);
	}
	
	//Return timestamp of the beginning of the week that $time is in. $shift defaults such that the week begins MON not SUN
	function getBeginningOfWeek($time,$shift=DAY){
		//What day of week (w) is $time? Then: getBeginningOfDay($time)-w*DAY
		$w=date("w",$time)-round($shift/DAY);
		if ($w<0) {$w+=7;}
		return (getBeginningOfDay($time-($w*DAY)));
	}
	
	function getEndOfWeek($time){
		return (getBeginningOfWeek($time)+(WEEK-1));
	}
	
	function getBeginningOfNextWeek($time){
	    return getBeginningOfWeek(getBeginningOfWeek($time)+WEEK+HOUR); //add the hour offset in case of a leap second/minute/hour
	}
	
	function getBeginningOfLastWeek($time){
	    return getBeginningOfWeek(getBeginningOfWeek($time)-WEEK-HOUR); //add the hour offset in case of a leap second/minute/hour
	}
	
	

	//Is this timestamp within the current week?
	function isCurrentWeek($time){
		return (($time>getBeginningOfWeek(time())) && ($time-getBeginningOfWeek(time())<WEEK));
	}
	
	//Return Day of week (monday=0, sunday=6)
	function getDayOfWeek($time,$shift=DAY){
		$w=date("w",$time)-round($shift/DAY);
		if ($w<0) {$w+=7;}
		return $w;
	}
	
	//Get the first timestamp on the day
	function getBeginningOfDay($timestamp) {
		if ($timestamp=="") { $timestamp=0; }
		return mktime(0,0,0,date("n",$timestamp),date("j",$timestamp),date("Y",$timestamp));
	}
	
	//Get the timestamp of noon on this day
	function getNoonOfDay($timestamp) {
		return getBeginningOfDay($timestamp)+(HOUR*12);
	}

	//Get the last timestamp on the day
	function getEndOfDay($timestamp) {
		return getBeginningOfDay($timestamp)+(DAY-1);
	}
	
	//Get the first timestamp on the month
	function getBeginningOfMonth($timestamp) {
		return mktime(0,0,0,date("n",$timestamp),1,date("Y",$timestamp));
	}

	function getBeginningOfNextMonth($timestamp){
		return mktime(0,0,0,date("n",$timestamp)+1,1,date("Y",$timestamp));
	}

	function getBeginningOfNextYear($timestamp){
		$year=date("Y",$timestamp);
		$year++;
		return mktime(0,0,0,1,1,$year);
	}

	//Get first timestamp of year
	function getBeginningOfYear($timestamp){
		return mktime(0,0,0,1,1,date("Y",$timestamp));
	}

	//Turn $seconds into something lik 10m, 2h, 5d, 3wks
	//$smallestunit can by  "m" (minute) "d" (day), "w" week etc.
	function getHumanReadableLengthOfTime($seconds,$smallestunit="m") {
		$w=floor($seconds/WEEK);
		$d=floor($seconds/DAY);
		$h=floor($seconds/HOUR);
		$m=floor($seconds/MINUTE);
		//For more than 9 weeks all we want is the number of weeks
		if ($w>=9){
			$result=$w."wks";
		//For between 2 and 9 weeks we want sth like 4w 3d
		} elseif ($w>=2) {
			$result=$w."w"; //Full weeks
			if ($d>$w*7) { //How many days on top? $d-($w*7)
				$result.=" ".($d-$w*7)."d";
			}
			if ($smallestunit=="d"){ return $result; }
		//For anything between 2d and 2wks we want something like 12d 4h
		} elseif ($d>=2) {
			$result=$d."d"; //Full days
			if ($smallestunit=="d"){ return $result; }
			if ($h>$d*24) { //How many ours on top?
				$result.=" ".($h-$d*24)."h";
			}
		//For anything between 10hrs and 2days we just want hours: 49hrs
		} elseif ($h>=10) {
			if ($smallestunit=="d"){ return "1d"; }
			$result=$h."h";
		//For anything between 1h and 1d we want something like 12h 4m
		} elseif ($h>=1) {
			$result=$h."h"; //Full hours
			if ($m>$h*60) { //How many minutes on top?
				$result.=" ".($m-$h*60)."m";
			}
		//For anything between 1m and 1h we want something like 53m
		} elseif ($m>=1) {
			$result=$m."m";
		} elseif ($seconds>0){
			if ($smallestunit=="s"){
				$result=$seconds."s";
			} else {
				$result="<1m";
			}
		} else {
			$result="";
		}
		return $result;	
	}
	
	//How many full days is the timestamp in the future (seen from now, or $reference)? -1 simply means it is past.
	function getDaysOut($timestamp,$reference=0){
		if ($reference==0) { $reference=time(); }
		if ($timestamp-time()>=0){
			return floor(abs($timestamp-$reference)/DAY);
		} else {
			return -1;
		}
	}
	
	//Convert month abbreviation to month number
	function month_abbr_to_nr($s){
		switch (strtolower($s)){
			case "jan": return 1; break;
			case "feb": return 2; break;
			case "mar": return 3; break;
			case "apr": return 4; break;
			case "may": return 5; break;
			case "jun": return 6; break;
			case "jul": return 7; break;
			case "aug": return 8; break;
			case "sep": return 9; break;
			case "oct": return 10; break;
			case "nov": return 11; break;
			case "dec": return 12; break;
		}
	}
	
	function month_nr_to_name($n,$abbr=false){
	    $abbr ? $x="M" : $x="F";
	    return date($x, mktime(0, 0, 0, $n, 10)); 
	}
	
	function day_nr_to_name($n,$abbr){
	    $fullNames=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday","Sunday");
	    $abbrNames=array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
	    if ($abbr){
	        return $abbrNames[$n];
	    } else {
	        return $fullNames[$n];
	    }
	}
	
	function getDayOfWeekByName($time,$abbr){
	    return day_nr_to_name(getDayOfWeek($time),$abbr);
	}
	
	//Convert a date string from an email to a timestamp, i.e. sth like  T"ue, 03 Aug 2010 22:19:21 -0700"
	function email_date_to_timestamp($s){
		return strtotime($s);
		/*
		//MANUAL: (not proper!)
		//It's worth finding out whether or not the day has a leading zero, because then the rest is easy (by position)
		if (strpos(" ",substr($s,5))==1){
			//no leading zero
			$offset=0;
		} else {
			//leading zero present
			$offset=1;
		}
		//Now life should be easy
		$day=substr($s,5,1+$offset);
		$month=substr($s,7+$offset,3);
		$year=substr($s,11+$offset,4);
		$hour=substr($s,16+$offset,2);
		$minute=substr($s,19+$offset,2);
		$second=substr($s,22+$offset,2);
		$result=mktime($hour,$minute,$second,month_abbr_to_nr($month),$day,$year);
		return $result;
		*/
	}

?>