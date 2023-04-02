<?php 
	include '../../lib/class_jowe_lifelog.php';
	include '../../lib/date_tools.php';
	
	$ll=new jowe_lifelog();
		
	$a=$_GET["a"]; //action
	$p=$_GET["p"]; //param

	//Guaranteed zero (make sure there's a 0 returned if no value)
	function gz($p){
		if ($p==0){
			$p=0;
		}		
		return $p;
	}
	
	if ($a=="get_status"){
		$s=gz($ll->get_status($p));
		$t=gz($ll->get_latest_status_change($p));
		$res='{ "flag":"'.$p.'", "status":'.$s.', "changed":"'.$t.'", "elapsed":"'.(time()-$t).'", "elapsed_h":"'.getHumanReadableLengthOfTime(time()-$t).'" }';
		echo $res;
	}
	
?>
