<?php
include "../header.php";

//query json through http and pass array to $obj
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/status-json.xsl"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$data = curl_exec($ch);
curl_close($ch);
$obj = json_decode($data, true);

// connect to msql
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());

// print anything in id3 where id = currently playing ($obj)
$row = mysql_fetch_array(mysql_query("SELECT * FROM id3 WHERE id = ".$obj["icestats"]["source"]["title"]))or die("missing song info");
echo "<a href='http://".URL."music/".$row['owner']."/".$row['id'].".mp3' download='".$row['title'].".mp3'>".$row['title']." - ".$row['artist']."</a>";

mysql_close(); 
?>