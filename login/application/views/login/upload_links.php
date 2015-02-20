<?php
// -----------------------------------
// ------- Band Bio Upload ---------
// -----------------------------------

if(isset($_POST['link1']) || isset($_POST['link2']) || isset($_POST['link3']) || isset($_POST['link4']) || isset($_POST['link5']) || isset($_POST['link1p']) || isset($_POST['link2p']) || isset($_POST['link3p']) || isset($_POST['link4p']) || isset($_POST['link5p'])) {
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
	$link2 = $_POST['link2'];
	$link3 = $_POST['link3'];
	$link4 = $_POST['link4'];
	$link5 = $_POST['link5'];
	
	$link1p = $_POST['link1p'];
	$link2p = $_POST['link2p'];
	$link3p = $_POST['link3p'];
	$link4p = $_POST['link4p'];
	$link5p = $_POST['link5p'];

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))){
		mysql_query("UPDATE $table SET link1 = '$link1' WHERE user_id = '$userID' ") or die(mysql_error());
		echo "Existing row updated!";
	} else {
		mysql_query("INSERT INTO $table (user_id, link1p, link1, link2p, link2, link3p, link3, link4p, link4, link5p, link5) VALUES('$userID', '$link1p', '$link1', '$link2p', '$link2', '$link3p', '$link3', '$link4p', '$link4', '$link5p', '$link5') ") or die(mysql_error());
		echo "Added row!";
	}

	mysql_close();
}

?>