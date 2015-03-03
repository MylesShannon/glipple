<?php include "../header.php"; ?>

<table id="latest" class="display" cellspacing="0">

        <thead>

            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Genre</th>
            </tr>
        </thead>
        <tbody>
<?php
	// header('Content-Disposition: attachment');
	
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";
	
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT * FROM id3 ORDER BY timestamp DESC") or die(mysql_error()); 
	$count = 1;
	
	//$row = mysql_fetch_array($result);
	preg_match('/\/media\/(.*)/', mysql_fetch_array($result)['path'], $path);

	while ($row = mysql_fetch_array($result)) {
		echo "<tr><td>".$count++."</td>";
		echo "<td><a href='".URL.$path[1]."' download='".preg_replace("/[^a-zA-Z0-9 ]+/", "", $row['title']).".mp3'>".$row['title']."</a></td>";
		echo "<td><a href='#profile' id='".$row['owner']."' class='profile'>".$row['artist']."</a></td>";
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
        "pageLength": 15,
        "info":     false,
		"bLengthChange": false
		
    } );
} );
</script>
<script type="text/javascript" src="<?php echo URL; ?>public/js/profile.js"></script>