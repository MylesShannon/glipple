<?php
die('Sorry...');
// 0.5.0: Convert favorites from (old) table 'favorites' to (new) table 'fav':
// quick & dirty...no fancy output....

require("../sql.php");
require("../disp.php");

$qry="SELECT * FROM album";
$result=execute_sql($qry,0,10000000,$n);
$count=0;

function save_cover($cover,$amazon_string) {

    $handle=fopen($cover,"r");
	$out_handle=fopen('../covers/'.$amazon_string.'.jpg', "w");
	while (!feof($handle)) {
		$data=fread($handle,4096);
		fwrite($out_handle,$data);
	}
	fclose($handle);
	fclose($out_handle);

	if (is_writable($cover.'.jpg')) { // get rid of 'old' cover ("name.jpg"):
		unlink($cover);
	}

	echo "COVER=".$cover.' CONV='.$amazon_string.'<br>';
}


while ($row=mysql_fetch_array($result)) {
	//get the performer name:
	$target=get_performer_name_album($row['aid']);
	$target.=' - '.$row['aname'].'.jpg';
	if (file_exists('../covers/'.$target)) {
		save_cover('../covers/'.$target,$row['aid']);
	}
	$count++;	// Love this '++' stuff... you will NOT find that in all languages...
}

//echo $count.' favorite entries converted from "favorites" -> "fav"';
?>
		
