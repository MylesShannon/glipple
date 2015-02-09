<div class="content">
    <h1>Music</h1>
<?php $this->renderFeedbackMessages(); ?>
<?php


/*
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "music";
	
	$userID = Session::get('user_id');

	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());
	
	$result = mysql_query("SELECT * FROM id3 WHERE owner LIKE '$userID'") or die(mysql_error());  
	
	while ($row = mysql_fetch_array($result)) {
		echo $row['id'].", ";
		echo $row['owner'].", ";
		echo $row['title'].", ";
		echo $row['artist'].", ";
		echo $row['album'].", ";
		echo $row['year'].", ";
		echo $row['genre'].", ";
		echo $row['comment'].", ";
		echo $row['track'].", ";
		echo $row['timestamp']."<br>";
	}

	mysql_close();
*/
?>
<!--<div id="drozone">
<p style="width:50%;">
<form action="<?php echo URL ?>upload/upld" class="dropzone"></form>
</p>
</div> -->

<table id="music" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Title</th>
                <th>Album</th>
                <th>Artist</th>
            </tr>
        </thead>
		
		<tfoot>
            <tr>
                <th>Title</th>
                <th>Album</th>
                <th>Artist</th>
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
		echo "<tr>";
		echo "<td><input id='row-".$count."-title' name='row-".$count."-title' value='".$row['title']."' type='text'></td>";
		echo "<td><input id='row-".$count."-artist' name='row-".$count."-artist' value='".$row['artist']."' type='text'></td>";
		echo "<td><input id='row-".$count."-album' name='row-".$count."-album' value='".$row['album']."' type='text'></td>";
		echo "</tr>";
		$count++;
	}

mysql_close();
?>
		</tbody>
</table>

<script>
/*
$(document).ready(function() {
    $('#music').dataTable( {
        "paging":   false,
        "info":     false
    } );
} );
*/

$(document).ready(function() {
    var table = $('#music').DataTable();
 
    $('button').click( function() {
        var data = table.$('input, select').serialize();
        alert(
            "The following data would have been submitted to the server: \n\n"+
            data.substr( 0, 120 )+'...'
        );
        return false;
    } );
} );
</script>