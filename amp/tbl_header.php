<?php

function tbl_header($what,$hl,$align,$s,$order_by,$dir,$newdir,$count,$special) {
	echo '<th align="'.$align.'">';
	echo '<a href="index.php?what='.$what.'&order_by=';
	echo $s.'&dir=';
	if ($order_by==$s) { echo $newdir; } else { echo $dir; }
	echo "&start=0&count=$count&$special";
	echo '">';
	if ($order_by==$s) { 
	 	$img=get_icon($_SESSION['icon_dir'],'arrow_down','');
	 	if ($img=="") {
			$img="./ampjukeicons/arrup.gif"; 
		}
		if ($dir=="ASC") {
		 	$img=get_icon($_SESSION['icon_dir'],'arrow_up','');
		 	if ($img=="") {
				$img="./ampjukeicons/arrdown.gif"; 
			}	
		}
		echo '<p>'.$img.($hl).'</a>';
	} else { echo '<p>'.($hl).'</a>'; }
	print "</th> \n";
} 
?>

