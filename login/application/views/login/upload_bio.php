<?php
// band bio upload

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());

$userID = Session::get('user_id');

mysql_query("INSERT INTO profiles WHERE 'user_id' = $userID (id, user_id, band_image, band_bio, timestamp) VALUES(NULL, NULL, NULL, $_POST('bandBio'), NULL") or die(mysql_error());

mysql_close();
?>