<?php
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());
	
	$result = mysql_query("SELECT * FROM id3") or die(mysql_error());  
	
	while ($row = mysql_fetch_array($result)) {
		echo $row['id'].", ";
		echo $row['owner'].", ";
		echo $row['title'].", ";
		echo $row['artist'].", ";
		echo $row['album'].", ";
		echo $row['year'].", ";
		echo $row['genre'].", ";
		echo $row['comment'].", ";
		echo $row['track'].", ";
		echo $row['timestamp']."<br>";
	}

	mysql_close();
?>