<?php // Rewritten in 0.4.3. 
function fancy_tr(&$tmpcount,$tdnorm,$tdalt,$tdhighlight) {
	if ($tmpcount/2==round($tmpcount/2)) { 
		print "\n";
		
		echo '<tr class="tr_norm">';
	} else {
	 	echo '<tr class="tr_alt">';
	}		 		
	$tmpcount++;
} 

function fancy_tr_buf(&$tmpcount,$tdnorm,$tdalt,$tdhighlight) {
	$ret='';
	if ($tmpcount/2==round($tmpcount/2)) { 
	 	$ret='<tr class="tr_norm">';
	} else {
		$ret='<tr class="tr_alt">';
	}	 	
	$tmpcount++;
	return $ret;
} 
	

$tmpcount=1;
require_once("db.php");
?>
