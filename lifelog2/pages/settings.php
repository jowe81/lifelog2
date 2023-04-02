<?php
/*
	THIS FILE IS PART OF LIFELOG AND TO BE CALLED ONLY FROM THE LIFELOG index.php
*/
	//Restore defaults?
	if ((isset($_GET["action"])) && ($_GET["action"]=='restoredefaults')){
		$ll->params_to_default();
	}
	
	//Produce context sensitive menu
	ll_menu($s,$auth);
	//Open content div
	$s->p("<div id='content' style='overflow:auto;'>");
	$s->p("<div id='content_header' style='float:left;'>LifeLog System Settings</div><div id='content_header' style='float:right;margin-right:3px;'><a href='".me()."&action=restoredefaults'>Restore defaults</a></div>");
	$x="";
	if ($res=$ll->db("SELECT * FROM ll2_params ORDER BY pgroup,pname;")){
		$x="<table style='width:99%;'>";
		$x.="\n<tr>
			<td style='font-style:italic;'>Param-name</td>
			<td style='font-style:italic;'>Param-value</td>
			<td style='font-style:italic;'></td>
			<td style='font-style:italic;'>Param description</td>
			<td style='font-style:italic;'>Last updated</td>
			</tr>";
		$i=0; //Odd/even counter
		$lastgroup=""; //Remember group of last param, to recognize new section
		while ($r=mysqli_fetch_array($res)){
			if ($lastgroup!=$r["pgroup"]){
				//For a new group, create a new section
				$x.="\n<tr><td style='background:black;color:white;font-weight:bold;' colspan='6'>Parameter group ".$r["pgroup"]."</td></tr>";
				$lastgroup=$r["pgroup"];
				$i=0;
			}
			$i++;
			if ($i%2==0){
				$background="#EEE";
			} else {
				$background="#FFF";
			}
			$x.="\n<tr>
					<td style='background:$background;'><form action='".me()."&dbaction=updateparam&id=".$r["id"]."' method='POST'>".$r["pname"]."</td>
					<td style='background:$background;'><input name='pvalue' value='".$r["pvalue"]."'></td>
					<td style='background:$background;'><input type='submit' value='save param'></form></td>
					<td style='background:$background;'>".$r["pdescription"]."</td>
					<td style='background:$background;'>".date("Y/m/d H:i:s",$r["updated"])."</td>
				</tr>";
		}
		$x.="\n</table></form>";
		$s->p($x);
	} else {
		$s->error("Could not access database table ll2_params.");
	}
	
	
	$s->p("</div><!--content-->");
	
?>