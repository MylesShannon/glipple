<?php
$fp = fsockopen("localhost", "1234", $errno, $errstr); 

if (!$fp) {
    echo "$errstr ($errno)</br>";
} else {
	fwrite($fp, "request.on_air\r\n");
    fwrite($fp, "request.metadata ".fgets($fp)."\r\n");
    fwrite($fp, "exit\r\n");
    while (!feof($fp)) {
		$output .=fgets($fp); 
    }
	
	preg_match('/title="(.*)"/', $output, $title);
	preg_match('/artist="(.*)"/', $output, $artist);
	echo $title[1]." - ".$artist[1];
}

fclose($fp);
?>