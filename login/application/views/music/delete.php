<?php
// delete music
$query = trim($_GET["artist"]);
$artistcaps = strtoupper($query); 
echo $artistcaps;
$querytitle = trim($_GET["title"]);
$titlecaps = strtoupper($querytitle); 
echo $titlecaps;
// connect to mysql
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


$result = mysql_query("DELETE * FROM id3 WHERE UPPER(artist) LIKE '%$artistcaps%' AND UPPER(title) LIKE '%titlecaps%'") or die(mysql_error());  
// take passed info and commit it to db
echo "deleted";
// close sql connection
mysql_close();
?>