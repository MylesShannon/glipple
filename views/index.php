<?php include '../header.php'; ?>

<table id="latest" class="display" cellspacing="0">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Title</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Genre</th>
            </tr>
        </thead>
        
        <tfoot>
            <tr>
                <th>Timestamp</th>
                <th>Title</th>
                <th>Album</th>
                <th>Artist</th>
                <th>Genre</th>
                </tr>
        </tfoot>
        <tbody>
<?php
	header('Content-Disposition: attachment');

	echo "<h1> Latest Tracks </h1>";
	
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";
	
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT * FROM id3 ORDER BY timestamp DESC") or die(mysql_error());  

	while ($row = mysql_fetch_array($result)) {
	//	echo "<a href='http://54.148.79.138/dl.php?file=".$row['id'].".mp3&?id=".$row['owner']."'>";
		echo "<tr><td>".$row['timestamp']."</td>";
		echo "<td><a href='http://54.148.79.138/music/".$row['owner']."/".$row['id'].".mp3' download='".$row['title'].".mp3'>".$row['title']."</a></td>";
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
        "paging":   false,
        "info":     false
    } );
} );

</script>

