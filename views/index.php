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
	
	while ($row = mysql_fetch_array($result)) {
		preg_match('/\/media\/(.*)/', $row['path'], $path);
		preg_match('/\/media\/music\/.*\/.*\.(.*)/', $row['path'], $type);
		$title = preg_replace("/[^a-zA-Z0-9 ]+/", "", $row['title']);
		echo "<tr><td>".$count++."</td>";
		echo "<td><a class='dl' href='".URL.$path[1]."' onclick='updatedl(".$row['artist'].", ".$row['title'].")'download='".$title.".".$type[1]."'>".$row['title']."</a></td>";
		echo "<td><a href='#profile' id='".$row['owner']."' class='profile'>".$row['artist']."</a></td>";
		echo "<td>".$row['album']."</td>";
        echo "<td>".$row['genre']."</td></tr>";
	}

mysql_close();
?>
</tbody>
</table>

<script>

function updatedl(artist, title){
	$.ajax(
		   type: "POST",
           url: 'update.php',
           data: {artist: artist, title: title},
           success:function(html) {
             alert(html);
           }
		)
}

$(document).ready(function() {
    $('#latest').dataTable( {
        "pageLength": 15,
        "info":     false,
		"bLengthChange": false
		
    } );
} );
</script>
<script type="text/javascript" src="<?php echo URL; ?>public/js/profile.js"></script>