<!doctype HTML>
<html>
<head>
</head>
<body>
	<table>
		<tr><td>Step</td><td>brightness</td></tr>
        <?php
        
        function calc($x){
            return ceil(pow(($x+60)/100,1.5));
        }
        
        $a=1023;
        $b=8192;
        $n=$a;
        $i=0;
        if ($b>=$n){
            while ($n<$b){
                $i++;
                $n+=calc($n);
                echo "<tr><td>$i</td><td>$n</td></tr>";
            }            
        } else {
            while ($n>$b){
                $i++;
                $n-=calc($n);
                echo "<tr><td>$i</td><td>$n</td></tr>";
            }            
        }
        
        
        ?>
	</table>
	<table>
		<?php 
		for ($i=0;$i<8192;$i+=10){
		    $f=ceil(pow(($i+60)/100,1.5));
		    echo "<tr><td>$i</td><td>$f</td></tr>";
		}		
		?>
	</table>
</body>
</html>
    