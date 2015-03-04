<?php 

if(isset($_POST['id']))
{
    $id = $_POST['id'];
    echo $id;

    $server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	mysql_query("UPDATE id3 SET downloads = downloads + 1 WHERE id LIKE '$id'") or die(mysql_error());
		mysql_close();  
}
?>