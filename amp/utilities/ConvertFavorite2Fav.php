<?php
// 0.5.0: Convert favorites from (old) table 'favorites' to (new) table 'fav':
// quick & dirty...no fancy output....
die('Sorry...');

require("../sql.php");
require("../disp.php");

$qry="SELECT * FROM favorites";
$result=execute_sql($qry,0,10000000,$n);
$count=0;

while ($row=mysql_fetch_array($result)) {
	$user_id=get_user_id($row['fuser']);
	$fav_name=$row['fname'];
	$id=$row['track_id'];
	
		$pid=get_performer_id($id);
		$aid=get_album_id($id);
		$r=get_track_extras($id);
		$uid=$user_id;
		$q="INSERT INTO fav (track_id, performer_id, album_id, name, duration,";
		$q.=" last_played, times_played, year, user_id, fav_name) VALUES";
		$q.=" ('".$id."', '".$pid."', '".$aid."', ";
		$q.='"'.$r['name'].'"';
		$q.=", '".$r['duration']."', ";
		$q.="'".$r['last_played']."', '".$r['times_played']."', ";
		$q.="'".$r['year']."', '".$uid."', '".$row['fname']."')";
		$r=execute_sql($q,0,-1,$nr);	

	$count++;	// Love this '++' stuff... you will NOT find that in all languages...
}

echo $count.' favorite entries converted from "favorites" -> "fav"';
?>
		
