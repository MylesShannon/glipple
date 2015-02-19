<?php
// -----------------------------------
// ------- Band Bio Upload ---------
// -----------------------------------

if(isset($_POST['link1'])) {
	upload_links();
} else {
	echo "No submission!";
}

function upload_links(){
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	$table = "profiles";
	$userID = Session::get('user_id');
	$link1 = $_POST['link1'];

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))){
		mysql_query("UPDATE $table SET link1 = '$link1' WHERE user_id = '$userID' ") or die(mysql_error());
		echo "Existing row updated!";
	} else {
		mysql_query("INSERT INTO $table (id, user_id, band_image, band_bio, link1, timestamp) VALUES(NULL, '$userID', NULL, '$link1', NULL) ") or die(mysql_error());
		echo "Added row!";
	}

	mysql_close();
}

?>