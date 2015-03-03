<?php
error_reporting(E_ALL ^ E_DEPRECATED);

if(isset($_POST['del'])) {
	$id = $_POST['del'];
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
	$owner = Session::get('user_id');
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	// delete file
	$result = mysql_query("SELECT * FROM id3 WHERE owner LIKE '$owner'") or die(mysql_error());
	$row = mysql_fetch_array($result);
	unlink($row['path']);
	
	// take passed info and commit it to db
	$result = mysql_query("DELETE FROM id3 WHERE id = '$id' AND owner = '$owner'") or die(mysql_error());
	

	// echo "deleted song '$id'";
	
	// close sql connection
	mysql_close();
}
?>