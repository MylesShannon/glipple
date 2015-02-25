<?php
error_reporting(E_ALL ^ E_DEPRECATED);

// delete music
$id = $_POST['del'];

if(isset($_POST['del'])) {
	deleteSong($id);
} else {
	echo "No submission!";
}


function deleteSong($id) {
	// connect to mysql
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";
	$user = Session::get('user_id')
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());


	// take passed info and commit it to db
	$result = mysql_query("DELETE FROM id3 WHERE id = '$id' AND owner = '$user'") or die(mysql_error());
	  
	// delete file
	unlink("/media/music/".$user."/".$id.".mp3");

	// echo "deleted song '$id'";
	
	// close sql connection
	mysql_close();
}
?>