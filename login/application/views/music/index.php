<div class="content">
<?php 
$this->renderFeedbackMessages();
error_reporting(E_ALL ^ E_DEPRECATED);
?>

<table id="music" class="display" cellspacing="0">
        <thead>
            <tr>
                <th>Title</th>
                <th>Album</th>
                <th>Artist</th>
                <th>Genre</th>
				<th></th>
            </tr>
        </thead>
		
		<tfoot>
            <tr>
                <th>Title</th>
                <th>Album</th>
                <th>Artist</th>
                 <th>Genre</th>
				<th></th>
            </tr>
        </tfoot>
 
        <tbody>
<?php
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";
	$count = 1;
	

	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT * FROM id3 WHERE owner LIKE ".Session::get('user_id')) or die(mysql_error());
	
	while ($row = mysql_fetch_array($result)) {
		echo "<tr class='musicRows'>";
		/*
		echo "<td><input id='row-".$count."-title' name='row-".$count."-title' value='".$row['title']."' type='text'></td>";
		echo "<td><input id='row-".$count."-artist' name='row-".$count."-artist' value='".$row['artist']."' type='text'></td>";
		echo "<td><input id='row-".$count."-album' name='row-".$count."-album' value='".$row['album']."' type='text'></td>";
		
		echo "<td><div id='".$row['id']."-title'>".$row['title'];
		echo "</div><script type='text/javascript'>";
		echo "new Ajax.InPlaceEditor('".$row['id']."-title', '/demoajaxreturn.html')";
		echo "</script></td>";
		*/
		echo "<td>".$row['title']."</td>";
		echo "<td>".$row['album']."</td>";
		echo "<td>".$row['artist']."</td>";
		echo "<td>".$row['genre']."</td>";
		echo "<td><button id='delete' value='".$row['id']."' type='submit'>delete</button></td>";
		echo "</tr>";
		$count++;
	}

mysql_close();
?>
		</tbody>
</table>
</div>

<script>

$(document).ready(function() {
    $('#music').dataTable( {
        "paging":   false,
        "info":     false
    } );
	$('#delete').click(function() {
		var d = $("#delete").val();
		$.post("delete",
        { 
			del : d
		});
		return false;
	} );
} );
</script>