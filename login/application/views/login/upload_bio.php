<?php
// -----------------------------------
// ------- Band Bio Upload ---------
// -----------------------------------

if(isset($_POST['bandBio'])) {
	upload_bio();
} else {
	echo "No submission!";
}

function upload_bio(){
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	$table = "profiles";
	$userID = Session::get('user_id');
	$bio = $_POST['bandBio'];

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))){
		mysql_query("UPDATE $table SET band_bio = '$bio' WHERE user_id = '$userID' ") or die(mysql_error());
		echo "Existing row updated!";
	} else {
		mysql_query("INSERT INTO $table (id, user_id, band_image, band_bio, timestamp) VALUES(NULL, '$userID', NULL, '$bio', NULL) ") or die(mysql_error());
		echo "Added row!";
	}

	mysql_close();
}

?>