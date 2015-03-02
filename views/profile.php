<?php include "../header.php";

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

echo "<div style='float:left'><table><th>".$username."</th>";
echo "<tr><td><img src='http://www.glipple.com/public/img/bands/".$userid."/profile.jpg'></td></tr></table></div>";

echo "<div style='float:right'>".$bio."</div";

echo "<div style='float:clear'></div>";
?>

<table id="latest" class="display" cellspacing="0">

        <thead>

            <tr>
                <th>Title</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Genre</th>
            </tr>
        </thead>
        <tbody>
<?php
mysql_select_db('music');
$result = mysql_query("SELECT * FROM id3 WHERE owner LIKE ".$userid) or die(mysql_error()); 

	while ($row = mysql_fetch_array($result)) {
		echo "<tr>";
		echo "<td><a href='".URL."music/".$row['owner']."/".$row['id'].".mp3' download='".preg_replace("/[^a-zA-Z0-9 ]+/", "", $row['title'])."'>".$row['title']."</a></td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['album']."</td>";
        echo "<td>".$row['genre']."</td></tr>";
	}	

mysql_close(); 
?>
</tbody>
</table>

<script>
$(document).ready(function() {
    $('#latest').dataTable( {
        "info":     false,
		"paging": false
    } );
} );
</script>