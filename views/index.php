<?php include "../header.php"; ?>

<table id="latest" class="display" cellspacing="0">

        <thead>

            <tr>
            	<th> </th>
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
		echo "<tr><td><div id='jquery_jplayer_".$row['id']."' class='cp-jplayer'></div>";
		echo "<div id='cp_container_".$row['id']."' class='cp-container'>";
		echo "<div class='cp-buffer-holder'> <!-- .cp-gt50 only needed when buffer is > than 50% -->";
		echo "<div class='p-buffer-1'></div>";
		echo "<div class='cp-buffer-2'></div>";
		echo "</div><div class='cp-progress-holder'> <!-- .cp-gt50 only needed when progress is > than 50% -->";
		echo "<div class='cp-progress-1'></div>";
		echo "<div class='cp-progress-2'></div>";
		echo "</div>";
		echo "<div class='cp-circle-control'></div>";
		echo "<ul class='cp-controls'>";
		echo "<li><a class='cp-play' tabindex='1'>play</a></li>";
		echo	"<li><a class='cp-pause' style='display:none;' tabindex='1'>pause</a></li>";
		echo 	"<!-- Needs the inline style here, or jQuery.show() uses display:inline instead of display:block -->";
		echo	"	</ul>";
		echo "	</div>";
		echo "<script type='text/javascript'> $(document).ready(function(){ var circleplayer".$row['id']." = new CirclePlayer(";
		echo "'#jquery_jplayer_".$row['id']."', { mp3: '".URL.$path[1]."' }, { cssSelectorAncestor: '#cp_container_".$row['id']."' });});</script>";
		echo "</td>";
		echo "<td>".$count++."</td>";
		echo "<td><a class='dl' href='".URL.$path[1]."' id='".$row['id']."' download='".$title.".".$type[1]."'>".$row['title']."</a></td>";
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
    $("#latest").dataTable( {
        "pageLength": 30,
        "info":     false,
		"bLengthChange": false	
    } );
} );
</script>

