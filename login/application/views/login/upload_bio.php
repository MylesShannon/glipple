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

mysql_query("UPDATE profiles SET band_bio = '$bio' WHERE user_id = $userID") or die(mysql_error());
//mysql_query("INSERT INTO profiles (a,b,c) VALUES (1,2,3) ON DUPLICATE KEY UPDATE c=c+1;") or die(mysql_error());

mysql_close();
?>