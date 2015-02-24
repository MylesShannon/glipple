<?php
error_reporting(E_ALL ^ E_DEPRECATED);

// delete music
$id = trim($_POST['del']);
echo $_POST["del"];
echo $id;

if(isset($_POST['del'])) {
	deleteSong();
} else {
	echo "No submission!";
}


function deleteSong() {
// connect to mysql
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


// take passed info and commit it to db
$result = mysql_query("DELETE FROM id3 WHERE id = '$id'") or die(mysql_error());
  
// delete file
unlink("/media/music/".Session::get('user_id')."/".$id.".mp3");

echo "deleted song '$id'";
// close sql connection
mysql_close();
}
?>