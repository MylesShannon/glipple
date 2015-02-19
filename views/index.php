<?php include '../header.php'; ?>

<!--
// <div class="popular_downloads">
// <table>
    // <tr>
	// <td>
	// <a href="https://wildchildsounds.bandcamp.com/album/christmas-mixtape-volume-1?from=discover-top" class="item_link playable-hover-target" id=
// "a4255030022top">
    // <span class="item_art  playable">
    
        // <img class="art" src="https://f1.bcbits.com/img/a1608914087_7.jpg">
        // <span class="plb-btn">
            // <span class="plb-bg"></span>
            // <span class="plb-ic"></span>
        // </span>
        
    
    // </span>
    // <div class="itemtext">
        
        // Christmas Mixtape, Volume 1
        
    // </div>

    // <div class="itemsubtext">
        
        // Wild Child
        
    // </div>





    // <div class="itemsubsubtext">
        // pop
    // </div>

// </a></td>

// <td>
	// <a href="https://wildchildsounds.bandcamp.com/album/christmas-mixtape-volume-1?from=discover-top" class="item_link playable-hover-target" id=
// "a4255030022top">
    // <span class="item_art  playable">
    
        // <img class="art" src="https://f1.bcbits.com/img/a1608914087_7.jpg">
        // <span class="plb-btn">
            // <span class="plb-bg"></span>
            // <span class="plb-ic"></span>
        // </span>
        
    
    // </span>
    // <div class="itemtext">
        
        // Christmas Mixtape, Volume 1
        
    // </div>

    // <div class="itemsubtext">
        
        // Wild Child
        
    // </div>





    // <div class="itemsubsubtext">
        // pop
    // </div>

// </a></td>

// <td>
	// <a href="https://wildchildsounds.bandcamp.com/album/christmas-mixtape-volume-1?from=discover-top" class="item_link playable-hover-target" id=
// "a4255030022top">
    // <span class="item_art  playable">
    
        // <img class="art" src="https://f1.bcbits.com/img/a1608914087_7.jpg">
        // <span class="plb-btn">
            // <span class="plb-bg"></span>
            // <span class="plb-ic"></span>
        // </span>
        
    
    // </span>
    // <div class="itemtext">
        
        // Christmas Mixtape, Volume 1
        
    // </div>

    // <div class="itemsubtext">
        
        // Wild Child
        
    // </div>





    // <div class="itemsubsubtext">
        // pop
    // </div>

// </a></td>
	// </tr>
	
// </table>
// </div>
// -->      <!-- datatables -->

<table id="music" class="display" cellspacing="0">
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
		echo "<a href='http://54.148.79.138/music/".$row['owner']."/".$row['id'].".mp3' download='".$row['title'].".mp3'>";
		echo "<tr><td>".$row['timestamp']."</td>";
		echo "<td>".$row['title']."</td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['album']."</td>";
        echo "<td>".$row['genre']."</td></tr>";
		echo "</a>";
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

