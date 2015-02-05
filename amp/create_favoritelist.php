<?php
session_start();
if (isset($_SESSION['login'])) {
	require("db.php");
	require("sql.php");
	require("disp.php"); 
	require('lastfm_lib.php'); // 0.8.1
	
	if (isset($_POST['favoritelistname'])) {
		$_SESSION['favoritelistname']=$_POST['favoritelistname'];
	}
	if (isset($_POST['copy'])) { 
		$qry="SELECT * FROM queue WHERE user_name='".$_SESSION['login']."'";
		$result=execute_sql($qry,0,100000000,$nr);
		while ($row=mysql_fetch_array($result)) {
			$r=get_track_extras($row['track_id']);
			$qry2="INSERT INTO fav (track_id, performer_id, album_id, name, duration, ";
			$qry2.="last_played, times_played, year, user_id, fav_name) VALUES ";
			$qry2.="('".$row['track_id']."', '".$r['performer_id']."', ";
			$qry2.="'".$r['album_id']."', '".$r['name']."', '".$r['duration']."', ";
			$qry2.="'".$r['last_played']."', '".$r['times_played']."', ";
			$qry2.="'".$r['year']."', '".get_user_id($_SESSION['login'])."', ";
			$qry2.="'".$_SESSION['favoritelistname']."')";
			$res2=execute_sql($qry2,0,-1,$nr);
		}
	}		
	if (isset($_POST['new_favlist'])) {
		$_POST['new_favlist']=my_filter_var($_POST['new_favlist']);
		$qry="INSERT INTO fav (user_id, fav_name) VALUES"; // 0.8.4
		$qry.="('".get_user_id($_SESSION['login'])."', '".$_POST['new_favlist']."')"; 
		$result=execute_sql($qry,0,-1,$nr);
	}
// 0.8.1: We want to create a new favorite list using the 'tags' mode:
	if ((isset($_POST['tags'])) && (strlen($_POST['tags'])>1)) { 	
		$_POST['tags']=my_filter_var($_POST['tags']); // 0.8.4
		$tag=explode(',',$_POST['tags']);
		$x=0;
		while ($x<count($tag)) {
			lastfm_add_artists_by_tag($_POST['new_favlist'],trim($tag[$x]));
			$x++;
		}
	}	
}
echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
echo '</script>';  
?>
</body>
</html>
