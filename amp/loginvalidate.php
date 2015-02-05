<?php
// 0.7.2: Do we actually come from login.php ?
// 0.7.3: Added !isset below:
if ((!isset($_POST['uuid'])) || (!file_exists($_POST['uuid']))) {
	echo 'Sorry. Cannot validate username+password. Please <a href="login.php">login again</a>';
	die();
}	
@unlink($_POST['uuid']);

// 0.6.3: New, AmpJuke offers "Keep me logged in":
if (isset($_POST['saved_url_params'])) {
	$saved_url_params=$_POST['saved_url_params'];
} else {
	$saved_url_params="";
}
if (isset($_POST['remember_login'])) {
	$ok=setcookie('ampjuke_username', $_POST['login'], time()+1209600, '/', false);
	if (!isset($_COOKIE['ampjuke_password'])) { // We don't have a hashed cookie...
		$firstcookie=1; // ...so remember that.
	} else {
		$firstcookie=0; // We DO have a hashed cookie: use that (later).
	}		
	$ok=setcookie('ampjuke_remember_all','1',time()+1209600, '/', false); // 1209600=two weeks
	$saved=time()+1209600;
} else {
	$ok=setcookie('ampjuke_remember_all','',time()+1, '/', false);
	$ok=setcookie('ampjuke_login', '', time()+1, '/', false);
	$ok=setcookie('ampjuke_password', '', time()+1, '/', false);
	$saved=0;
}


// init.
session_start();
require("db.php");
include_once("disp.php");
$now=date('U');

$tcount=dskspace('./tmp/',24); // Remove anything in ./tmp/ that's older than 1 day. 0.8.2: Moved here from make_header.php
$tcount=dskspace('./toptags/',8760); // 0.8.2: Housekeeping in 'toptags'. 8760=1 year

$connection=mysql_connect($db_host,$db_user,$db_password) or die('Could not connect.');
mysql_select_db($db_name) or die('Could not select database !');

// 0.8.4: Delete inactive users ?
if ((isset($delete_inactive_users)) && ($delete_inactive_users>0)) {
	$max_age=$now-($delete_inactive_users*86400); // Compute timestamp based on "now" - (max_days_inactive*86400)
	$qry="SELECT * FROM user WHERE last_login<'".$max_age."'";
	if (isset($ampjuke_tbl_prefix)) {
		$qry=str_replace("FROM ", "FROM $ampjuke_tbl_prefix", $qry);
	}	
	$result=mysql_query($qry) 
	or die('Could not select a username from the user-table ('.$qry.'). Have you installed AmpJuke correctly ?<br>
	Detailed installation instructions here: <a href="http://www.ampjuke.org/?id=installation">http://www.ampjuke.org/?id=installation</a>');

	$num_rows=mysql_num_rows($result);
	
	if ($num_rows>0) { // There is at least one victim:
		while ($row=mysql_fetch_array($result)) {
			$qry="SELECT id,name FROM user WHERE id='".$row['id']."'";
			$result2=mysql_query($qry);
			$row2=mysql_fetch_array($result2);
			$name=$row2['name'];

			// delete the user-account:
			$qry="DELETE FROM user WHERE id=".$row['id']." LIMIT 1";
			$result2=mysql_query($qry);

			// delete the FAVORITES as well:
			$qry="DELETE FROM fav WHERE user_id='".$row['id']."'";
			$result2=mysql_query($qry);

			// Delete any entries in fav_shares:
			$qry="DELETE FROM fav_shares WHERE share_id='".$row['id']."'";
			$result2=mysql_query($qry);

			// Delete queue-entries:
			$qry="DELETE FROM queue WHERE user_name='".$name."'";
			$result2=mysql_query($qry);			
		}
	}
}
			


// 0.3.7: Changed two statements from $HTTP_POST_VARS to $_POST.
// 0.7.2: Ensure we ONLY accept letters+numbers:
$user=ereg_replace('[^a-zA-Z0-9]', "", $_POST['login']);
$pw=ereg_replace('[^a-zA-Z0-9]', "", $_POST['password']);

// 0.7.4: Have we hashed the password ?
$qry="SELECT * FROM user WHERE name='$user'";
if (isset($ampjuke_tbl_prefix)) {
	$qry=str_replace("FROM ", "FROM $ampjuke_tbl_prefix", $qry);
}	
$result=mysql_query($qry) or die('Could not select a username from the user-table: '.$qry);
$nr=mysql_num_rows($result);
if ($nr==1) {
	$row=mysql_fetch_array($result);
	if ($row['password_salt']<>'0') { // Yes - password is hashed:
		$pw=md5($row['password_salt'].$pw);
		// 0.7.5: Get pw from cookie instead, IF it's there:
		if (isset($_POST['remember_login']) && ($firstcookie==0)) { 
			$pw=$_COOKIE['ampjuke_password'];
		}	
	} else { // No - password is NOT hashed: do it & store it:
		$salt=generate_password_salt();
		$pw=md5($salt.$pw);		
		$qry2="UPDATE user SET password='".$pw."', password_salt='".$salt."' WHERE id=".$row['id']." LIMIT 1";
		if (isset($ampjuke_tbl_prefix)) {
			$qry2=str_replace("UPDATE user ", "UPDATE ".$ampjuke_tbl_prefix."user ", $qry2);
		}	
		$r2=mysql_query($qry2);
	}	
}	

$qry="SELECT * FROM user WHERE name='$user' AND password='$pw'";
//die($qry);
// 0.6.7: Take table-prefixes into account:
if (isset($ampjuke_tbl_prefix)) {
	$qry=str_replace("FROM ", "FROM $ampjuke_tbl_prefix", $qry);
}	
$result=mysql_query($qry) 
	or die('Could not select a username from the user-table. Have you installed AmpJuke correctly ?<br>
	Detailed installation instructions here: <a href="http://www.ampjuke.org/?id=installation">http://www.ampjuke.org/?id=installation</a>');

$num_rows=mysql_num_rows($result);
$row=mysql_fetch_array($result);

// 0.3.7: Allow anonymous users (guests) access ? If yes: grant them a session...
if (($allow_anonymous==1) && ($user=="anonymous") && ($pw=="anonymous")) {
	$num_rows=1; // we need that in the next if-statement below...now setup some default values:
	$row['user']="anonymous";
	$row['password']="anonymous";
	$row['autoplay']="0";
	$row['lang']="EN";
	$row['count']=20;
	$row['enqueue']="0";
	$row['disp_last_played']="1";
	$row['disp_times_played']="1";
	$row['disp_jump_to']="1";
	$row['disp_id_numbers']="0";
	$row['show_letters']="1";
	$row['show_ids']="0";
	$row['disp_duration']="1";
	$row['disp_totals']="1";
	$row['disp_related_performers']="1"; 
	$row['confirm_delete']="0";
	$row['can_download']="0";
	$row['disp_download']="0";	
	$row['disp_lyrics']="1";
	$row['autoplay']="0";
	$row['last_login']="N/A";
	$row['admin']="0";
	$row['cssfile']="AmpJukeStandard.css"; // The CSS-file to use
	$row['icon_dir']=""; // Empty means: No icons
	$row['autoplay_last']="";
	$row['autoplay_last_list']="";
	$row['disp_fav_shares']="0"; 
	$row['disp_small_images']="1"; 
	$row['ask4favoritelist']="0"; 
	$row['disp_now_playing']="0"; // 0.8.3: Changed from "1" -> might introduce more problems than we want...
	$row['disp_help']="1"; 
	$row['avoid_duplicate_entries']="1"; 
	$row['can_upload']="0";	
	$row['disp_upload']="0"; 
	$row['welcome_num_items']=10; 
	$row['welcome_content_1']="Recently played tracks";
	$row['welcome_content_2']="Random albums";
	$row['welcome_content_3']="Recently added performers";
	$row['lastfm_active']="0";
	$row['lastfm_username']="";
	$row['lastfm_password']="";
	$row['browse_albums_by_covers']='0';
	$row['browse_performer_by_picture']='0';
	$row['xspf_active']='0'; // 0.8.0
	$row['disp_now_playing_add2favorite']='0'; // 0.8.2
	$row['auto_add2favorite']='0'; // 0.8.2;
	$row['auto_add2favorite_create_new']='0'; // 0.8.3
	$row['auto_add2favorite_prefix']=''; // 0.8.2
	$row['ask4favoritelist_disp_suggestion']='0'; // 0.8.2
	$row['hide_icon_text']='0'; // 0.8.4
}

if ($num_rows==1) { // then we know we have a winner, - setup some stuff for later use:
	// 0.7.5: Store the HASHED password in the cookie, if we have "remember me" turned on (on loginpage):
	if (isset($_POST['remember_login'])) {
		$ok=setcookie('ampjuke_password', $pw, time()+1209600, '/', false); 		
	}
	// session settings (from the db):
	$_SESSION['login']=$user; // who is it we're dealing with ? (username)
	$_SESSION['lang']=$row['lang']; // language
	$_SESSION['passwd']=$row['password']; // 
	$_SESSION['count']=$row['count']; // items/pages

	$_SESSION['enqueue']=$row['enqueue']; // enqueue/play tracks (0|1)
	$_SESSION['disp_last_played']=$row['disp_last_played']; // show when tracks  was last played (0|1)
	$_SESSION['disp_times_played']=$row['disp_times_played']; // show #times track was played (0|1)
	$_SESSION['show_letters']=$row['disp_jump_to']; // show the 'jump to letter' option (0|1)
	$_SESSION['show_ids']=$row['disp_id_numbers']; // show ID-numbers (0|1)
	$_SESSION['disp_duration']=$row['disp_duration']; // show durations on individual tracks ? (0|1)
	$_SESSION['disp_totals']=$row['disp_totals']; // show total durations on f.ex. albums ? (0|1)
	// 0.6.1:
	$_SESSION['disp_related_performers']=$row['disp_related_performers']; // show related performers ? (0|1)
	$_SESSION['confirm_delete']=$row['confirm_delete'];	// confirm deletion of something ?

	$_SESSION['can_download']=$row['can_download']; // can we download ? (0|1)
	$_SESSION['disp_download']="0"; // don't display download option...
	if ($_SESSION['can_download']=="1" && $row['disp_download']=="1") { //...unless we're 
	// allowed to download...and user actually want to display that option:
		$_SESSION['disp_download']="1";
	}		
	$_SESSION['can_upload']=$row['can_upload']; // 0.6.1: can we upload ?

	// 0.7.4: Upload functionality (disp/hide) same as for download:
	$_SESSION['disp_upload']="0"; 
	if ($_SESSION['can_upload']=="1" && $row['disp_upload']=="1") { //...unless we're 
	// allowed to download...
	// ...and user actually want to display that option:
		$_SESSION['disp_upload']="1";
	}		

	$_SESSION['disp_lyrics']=$row['disp_lyrics']; // 0.3.8: Show/hide "Lyrics" link	
	// 0.7.7: If lyrics isn't enabled, hide the "Lyrics" link no matter what:
	if ($lyrics_enabled<>1) {
		$_SESSION['disp_lyrics']="0";
	}

	// 0.8.4: Hide text after icons ?
	$_SESSION['hide_icon_text']=$row['hide_icon_text'];
	
	$_SESSION['disp_fav_shares']=$row['disp_fav_shares']; // 0.5.2: Show/hide shared favorites
	$_SESSION['disp_small_images']=$row['disp_small_images']; // 0.7.3: Small images ?
	$_SESSION['favoritelistname']=""; 
	if ($row['last_login']=="") {
		$row['last_login']=date('U');
	}		
    $_SESSION['msg']=$row['last_login'];
	$_SESSION['admin']=$row['admin']; // is this an admin ? (0|1)
    $_SESSION['filter_tracks']="0"; // 0.3.3: 0=All; 1=Tracks w. Albums; 2=Tracks wo. albums

    // 0.4.3: Add backward compatibility for users w/o this setting:
    if ($row['cssfile']=="") {
    	$row['cssfile']="AmpJukeStandard.css";
    }	
    $_SESSION['cssfile']=$row['cssfile']; // 0.4.3: what CSS should support us during this session ?
  
    $_SESSION['icon_dir']=$row['icon_dir']; // 0.7.0
    
	// 0.3.7: UPDATE last login for everybody, except anonymous/guests:
	if (($_SESSION['login']!="anonymous") && ($_SESSION['passwd']!="anonymous")) {
		$qry="UPDATE user SET last_ip='".$_SERVER['REMOTE_ADDR']."', last_login='".$now."'";
		$qry.=" WHERE name='".$_SESSION['login']."'";
		require("sql.php");
		$result=execute_sql($qry,0,-1,$nr);
	}		

	// 0.4.4: Setup the "last-search" setting:
	$_SESSION['last_search']="";

	// 0.5.0: Autoplay last...
	$_SESSION['autoplay_last']=$row['autoplay_last'];
	$_SESSION['autoplay_last_list']=$row['autoplay_last_list'];

	// 0.5.5: Ask for name of favoritelist:
	$_SESSION['ask4favoritelist']=$row['ask4favoritelist'];

	// 0.6.4: Display what's currently playing:
	$_SESSION['disp_now_playing']=$row['disp_now_playing'];	

	// 0.8.2: Show option to add to favorite during "Now playing":
	$_SESSION['disp_now_playing_add2favorite']=$row['disp_now_playing_add2favorite'];
	// Clear what's playing 'now' (may actually be from an old session):
	$user_id=$row['id'];
	$handle=fopen('./tmp/np'.$user_id.'.txt', 'w'); 
	fwrite($handle,'<b>AmpJuke</b><br>...and YOUR hits keep on coming !');
	fclose($handle);
	// 0.6.5: Display help to the AmpJuke FAQ:
	$_SESSION['disp_help']=$row['disp_help'];
	// 0.6.6: Avoid duplicate entries:
	$_SESSION['avoid_duplicate_entries']=$row['avoid_duplicate_entries'];
	// 0.6.3: Set 'welcome' page preferences:
	$_SESSION['welcome_num_items']=$row['welcome_num_items'];
	$_SESSION['welcome_content_1']=$row['welcome_content_1'];
	$_SESSION['welcome_content_2']=$row['welcome_content_2'];
	$_SESSION['welcome_content_3']=$row['welcome_content_3'];
	// 0.7.2: Set last.fm preferences:
	$_SESSION['lastfm_active']=$row['lastfm_active'];
	$_SESSION['lastfm_username']=$row['lastfm_username'];
	$_SESSION['lastfm_password']=$row['lastfm_password'];
	// 0.7.9: Set preferences for browsing albums/performers:
	$_SESSION['browse_albums_by_covers']=$row['browse_albums_by_covers'];
	$_SESSION['browse_performer_by_picture']=$row['browse_performer_by_picture'];
	// 0.8.0: Do we have the xspf activated ?
	$_SESSION['xspf_active']=$row['xspf_active'];
	if (($_SESSION['xspf_active']=='1') && ($xspf_enabled=='1')) {
		$_SESSION['disp_now_playing']='0'; // Because we need to
	}	
	// 0.8.0: Use flash player exclusively ?
	if ((isset($xspf_enabled)) && ($xspf_enabled=='1')
	&& (isset($xspf_only_player)) && ($xspf_only_player=='1')) {
		$_SESSION['xspf_active']='1';
		$_SESSION['disp_now_playing']='0';
	}
	// 0.8.0: Have flash player enabled in personal settings, but disabled system-wide ?
	if ($row['xspf_active']=='1') {
		if ((!isset($xspf_enabled)) || ($xspf_enabled=='0')) {
			$_SESSION['xspf_active']='0';
		}
	}
	// 0.8.2: Automatically add tracks to favorite list(s) ?
	$_SESSION['auto_add2favorite']=$row['auto_add2favorite'];
	$_SESSION['auto_add2favorite_prefix']=$row['auto_add2favorite_prefix'];
	// 0.8.3: Create new favorite lists automatically ?
	$_SESSION['auto_add2favorite_create_new']=$row['auto_add2favorite_create_new'];
	// 0.8.2: Display suggestions from last.fm when adding to a favorite list ?
	$_SESSION['ask4favoritelist_disp_suggestion']=$row['ask4favoritelist_disp_suggestion'];
	// 0.3.6: Determine what happens next (autoplay on|off):
	if ($row['autoplay']=="0") { 
		// 0.6.3: If we're recovering a session ("remember me"), redir somewhere else:
		if ($saved_url_params!="") {
			redir("index.php?saved_session=1&".$saved_url_params);
		} else {
			redir("index.php?what=welcome");
		}
	} else { // ...we want to start playing some tracks automatically:
		$_SESSION['autoplay']=1;
		redir("index.php?what=welcome&num_tracks=".$row['autoplay_num_tracks']."&list=".$row['autoplay_list']);	
	}
} else { // wrong user/passwd, just redisplay login...
// 0.6.3: ...but remember to clear the "remember_all" + "remember_password" cookies first !
	$ok=setcookie('ampjuke_remember_all','',time()+1, '/', false);
	$ok=setcookie('ampjuke_login', '', time()+1, '/', false);
	$ok=setcookie('ampjuke_password', '', time()+1, '/', false);
	// 0.7.5: Record the IP:
	$handle=fopen('./tmp/banned_ips.txt', 'a');
	fwrite($handle,$_SERVER["REMOTE_ADDR"] . chr(10).chr(13));
	fclose($handle);
	$x=rand(1,5);
	sleep($x);
	redir("login.php");
}

?>
