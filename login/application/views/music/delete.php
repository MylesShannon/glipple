<?php
// delete music
$id = trim($_POST["del"]);

// connect to mysql
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


// take passed info and commit it to db
$result = mysql_query("DELETE FROM id3 WHERE id = $id") or die(mysql_error());
  
// delete file
unlink("/media/music/".Session::get('user_id')."/".$id.".mp3");

echo "deleted song '$id'";
// close sql connection
mysql_close();
?>