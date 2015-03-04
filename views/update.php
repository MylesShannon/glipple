<?php 

if(isset($_POST['artist']) && isset($_POST['title']))
{
    $artist = $_POST['artist'];
    $title = $_POST['title'];

    $server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	mysql_query("UPDATE id3 SET downloads = downloads+1 WHERE artist LIKE '$artist' AND title LIKE '$title'") or die(mysql_error());
		mysql_close();  
}
?>