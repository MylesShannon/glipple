<?php
// delete music
$artist = trim($_GET["artist"]);
$artist = strtoupper($artist); 
$artist = urlencode($artist);
echo $artistc;
$title = trim($_GET["title"]);
$title = strtoupper($title); 
$title = urlencode($title);
echo $title;
// connect to mysql
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());



$result = mysql_query('DELETE * FROM id3 WHERE UPPER(artist) LIKE "'.$artist.'" AND UPPER(title) LIKE "'.$title.'"') or die(mysql_error());  
// take passed info and commit it to db
echo "deleted";
// close sql connection
mysql_close();
?>