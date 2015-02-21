<table id="profile">
<tr>


<td >
<?php 

	$userid = trim($_GET["id"]);
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT band_bio FROM profiles WHERE user_id LIKE ".$userid) or die(mysql_error());  

	while ($row = mysql_fetch_array($result)) {
		echo $row['band_bio'];

}

mysql_close(); 

?>
</td>


<td>

</td>


</tr>
</table>