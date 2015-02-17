<?php
// band bio upload

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());

$userID = Session::get('user_id');
$bio = $_POST['bandBio'];

// mysql_query("UPDATE profiles SET band_bio = '$bio' WHERE user_id = $userID") or die(mysql_error());
mysql_query("INSERT INTO profiles (id, user_id, band_image, band_bio, timestamp) VALUES (NULL, '$userID', NULL, '$bio', NULL) ON DUPLICATE KEY UPDATE user_id = '$userID', band_bio = '$bio'") or die(mysql_error());

mysql_close();
?>