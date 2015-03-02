<?php
error_reporting(E_ALL ^ E_DEPRECATED);
include "../header.php";

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());

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
	
	preg_match('/filename="\/media\/music\/\/(.*)"/', $output, $file);
	preg_match('/filename="\/media\/music\/\/(.*)\/(.*)"/', $output, $id);
	preg_match('/title="(.*)"/', $output, $title);
	preg_match('/artist="(.*)"/', $output, $artist);
	
	echo "<a href='".URL."music/".$file[1]."' download='".$title[1]."'>".$title[1]."</a> - <a href='#profile' id='works!' class='profile'>".$artist[1]."</a>";
}

fclose($fp);
?>