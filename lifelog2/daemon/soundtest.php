<?php
	echo "Trying to play sound...";
	for ($i=1;$i<10;$i++){
		if ($r=passthru("ogg123 tada.ogg")){
			echo "Result: $r";
		} else {
			echo "FAIcLED";
		}
	}
?>