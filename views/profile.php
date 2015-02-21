
	<?php include "../header.php"; ?>

<?php 

	$userid = trim($_GET["id"]);
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT * FROM profiles WHERE user_id LIKE ".$userid) or die(mysql_error());  

	$row=mysql_fetch_array($result);
		
		$bio = $row['band_bio'];
		$imagepath = URL.$row['band_image'];


	$result = mysql_query("SELECT * FROM users WHERE user_id LIKE ".$userid) or die(mysql_error());  
$usernamequery = mysql_fetch_array($result);

	$username = $usernamequery['user_name'];


mysql_close(); 

echo "<table><th>".$username."</th>";
echo "<tr><td><img src='".$imagepath."'></td><td>".$bio."</td></tr></table>";

?>
