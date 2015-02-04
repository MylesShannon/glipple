<?php
//search result page
	
	$query = trim($_GET["s"]);
	
	$querycaps = strtoupper($query); 
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";
	
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());
	
	echo "<h1> Artists Matching  $query </h1>";
	$result = mysql_query("SELECT * FROM id3 WHERE UPPER(artist) LIKE '%$querycaps%'") or die(mysql_error());  
	
	while ($row = mysql_fetch_array($result)) {
		echo "<table>";
		echo "<tr>";
		echo "<td>".$row['title']."</td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['album']."</td></tr></table>";
		echo "</a>";
	}
	
	
	echo "<h1> Songs matching $query </h1>";
		$result = mysql_query("SELECT * FROM id3 WHERE UPPER(title) LIKE '%$querycaps%'") or die(mysql_error());  
	
	while ($row = mysql_fetch_array($result)) {
		echo "<table>";
		echo "<tr>";
		echo "<td>".$row['title']."</td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['album']."</td></tr></table>";
		echo "</a>";
	}
			echo "<h1> Albums matching $query </h1>";
		$result = mysql_query("SELECT * FROM id3 WHERE UPPER(album) LIKE '%$querycaps%'") or die(mysql_error());  
	
	while ($row = mysql_fetch_array($result)) {
		echo "<table>";
		echo "<tr>";
		echo "<td>".$row['title']."</td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['album']."</td></tr></table>";
		echo "</a>";
	}


	
		echo "<h1> Genres matching $query </h1>";
		$result = mysql_query("SELECT * FROM id3 WHERE UPPER(genre) LIKE '%$querycaps%'") or die(mysql_error());  
	
	while ($row = mysql_fetch_array($result)) {
		echo "<table>";
		echo "<tr>";
		echo "<td>".$row['title']."</td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['album']."</td></tr></table>";
		echo "</a>";
	}


	

	
	mysql_close();
?>