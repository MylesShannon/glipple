<?php
// 0.7.0: REWRITTEN completely.

session_start();
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	redir("login.php");
    die('<a href="./login.php">Timeout. Login again.</a>');
}

require("db.php");
require("sql.php");
require("disp.php");
//error_reporting(0);

/*

		STEP 1: VALIDATE INPUT:

*/
// Array of stuff we under no circumstance want POST'ed:
$forbidden = array('*', '/', '=', "<", ">", "+", "'"); 

// Things that might not have been POST'ed due to the fact 
// that it's an anonymous user we're dealing with:
if ($_SESSION['login']=="anonymous") {
	$_POST['playmethod']="0"; // defaults to "play immediately". Set to 1 to allow queueing.
	$_POST['autoplay_num_tracks']=0; // irrelevant, but still needed...
	$_POST['autoplay_list']=""; // irrelevant, but still needed...
	$_POST['autoplay_last']=0; // 0.5.0
	$_POST['autoplay_last_list']=""; // 0.5.0
	$_POST['disp_now_playing_add2favorite']='0'; // 0.8.2
	$_POST['auto_add2favorite']='0'; // 0.8.2
}	


// Playmethod. 0=play now 1=queue:
$q=$_POST['playmethod'];
if (strlen($q)<>1) {
	$q="0";
}	
$_POST['playmethod']=$q;

// 0.8.0: Stream using flash-player ?
if (!isset($_POST['xspf_active'])) { $_POST['xspf_active']='0'; }
else { $_POST['xspf_active']='1'; }

// Items per page:
if (!is_numeric($_POST['count']) || ($_POST['count']<1)) {
	$_POST['count']=10;
}

// Display last played:
if (!isset($_POST['disp_last_played'])) { $_POST['disp_last_played']="0"; }
else { $_POST['disp_last_played']='1'; }

// Display times played:
if (!isset($_POST['disp_times_played'])) { $_POST['disp_times_played']="0"; }
else { $_POST['disp_times_played']='1'; }

// Show ID's:
if (!isset($_POST['show_ids'])) { $_POST['show_ids']="0"; }
else { $_POST['show_ids']='1'; }

// Show "jump to":
if (!isset($_POST['show_letters'])) { $_POST['show_letters']="0"; }
else { $_POST['show_letters']='1'; }

// Display duration:
if (!isset($_POST['disp_duration'])) { $_POST['disp_duration']="0"; }
else { $_POST['disp_duration']='1'; }

// Display totals:
if (!isset($_POST['disp_totals'])) { $_POST['disp_totals']="0"; }
else { $_POST['disp_totals']='1'; }

// Display related performers:
if (!isset($_POST['disp_related_performers'])) { $_POST['disp_related_performers']="0"; }
else { $_POST['disp_related_performers']='1'; }

// Confirm deletion:
if (!isset($_POST['confirm_delete'])) { $_POST['confirm_delete']="0"; }
else { $_POST['confirm_delete']='1'; }

// Display download:
if (!isset($_POST['disp_download'])) { $_POST['disp_download']="0"; }
else { $_POST['disp_download']='1'; }

// 0.7.4: Display upload:
if (!isset($_POST['disp_upload'])) { $_POST['disp_upload']="0"; }
else { $_POST['disp_upload']='1'; }

// Display lyrics:
if (!isset($_POST['disp_lyrics'])) { $_POST['disp_lyrics']="0"; }
else { $_POST['disp_lyrics']='1'; }

// 0.8.4: Hide icons ?
if (!isset($_POST['hide_icon_text'])) { $_POST['hide_icon_text']='0'; }
else { $_POST['hide_icon_text']='1'; }

// Display shared favorite lists:
if (!isset($_POST['disp_fav_shares'])) { $_POST['disp_fav_shares']="0"; }
else { $_POST['disp_fav_shares']='1'; }

// 0.7.3: Display small images associated w. albums/performers:
if (!isset($_POST['disp_small_images'])) { $_POST['disp_small_images']="0"; }
else { $_POST['disp_small_images']='1'; }

// 0.7.9: Browse albums by covers:
if (!isset($_POST['browse_albums_by_covers'])) { $_POST['browse_albums_by_covers']="0"; }
else { $_POST['browse_albums_by_covers']='1'; }

// 0.7.9: Browse performers by pictures:
if (!isset($_POST['browse_performer_by_picture'])) { $_POST['browse_performer_by_picture']="0"; }
else { $_POST['browse_performer_by_picture']='1'; }

// Ask for name of favorite list everytime:
if (!isset($_POST['ask4favoritelist'])) { $_POST['ask4favoritelist']="0"; }
else { $_POST['ask4favoritelist']='1'; }

// 0.8.2: Suggest favorite lists:
if (!isset($_POST['ask4favoritelist_disp_suggestion'])) { $_POST['ask4favoritelist_disp_suggestion']="0"; }
else { $_POST['ask4favoritelist_disp_suggestion']='1'; }

// Display "Now playing":
if (!isset($_POST['disp_now_playing'])) { $_POST['disp_now_playing']="0"; }
else { $_POST['disp_now_playing']='1'; }

// 0.8.2: Show option to add something to favoritelist in "Now playing":
if (!isset($_POST['disp_now_playing_add2favorite'])) { $_POST['disp_now_playing_add2favorite']='0'; }
else { $_POST['disp_now_playing_add2favorite']='1'; }

// 0.7.2: Display whats up next:
if (!isset($_POST['disp_now_playing_next'])) { $_POST['disp_now_playing_next']="0"; }
else { $_POST['disp_now_playing_next']='1'; }

// Avoid duplicate entries when adding to favorite lists:
if (!isset($_POST['avoid_duplicate_entries'])) { $_POST['avoid_duplicate_entries']="0"; }
else { $_POST['avoid_duplicate_entries']='1'; }

// Automatic play:
if (!isset($_POST['autoplay'])) { $_POST['autoplay']="0"; }
else { $_POST['autoplay']='1'; }

// Number of tracks to play autmatically:
if (!is_numeric($_POST['autoplay_num_tracks']) || ($_POST['autoplay_num_tracks']<1)) {
	$_POST['autoplay_num_tracks']=0; // 0.7.6: Whoops - typo here...
}

// Automatic play - after last track:
if (!isset($_POST['autoplay_last'])) { $_POST['autoplay_last']="0"; }
else { $_POST['autoplay_last']='1'; }

// 0.8.2: Automatically add to favorite list(s) ?
if (!isset($_POST['auto_add2favorite'])) { $_POST['auto_add2favorite']='0'; }
else { $_POST['auto_add2favorite']='1'; }

// 0.8.3: Automatically create new favorite lists ?
if (!isset($_POST['auto_add2favorite_create_new'])) { $_POST['auto_add2favorite_create_new']='0'; }
else { $_POST['auto_add2favorite_create_new']='1'; }

// Automatic play - after last track - what should be played ?
$_POST['autoplay_last_list'] = str_replace($forbidden, "", $_POST['autoplay_last_list']);

// Theme:
//$_POST['cssfile'] = str_replace($forbidden, "", $_POST['cssfile']);

// Icons:
$_POST['icon_dir'] = str_replace($forbidden, "", $_POST['icon_dir']);

// Language:
if (strlen($_POST['lang']>2)) {
 	$_POST['lang']="EN";
} 	


// Change password ?
$pw_alert="0"; // No change wanted/needed
if ($_POST['change_password_1']!="") {	
	if ($_POST['change_password_1']==$_POST['change_password_2']) {
		$pw_alert="1"; // Password could/should be changed - filter ths stuff:
		$_POST['change_password_1'] = str_replace($forbidden, "", $_POST['change_password_1']);		
		$_POST['change_password_2'] = str_replace($forbidden, "", $_POST['change_password_2']);	
	} else { 
		$pw_alert="2"; // passwd 1 & 2 does not match -> generate error later
	}	
}		

// Display links to the AmpJuke FAQ:
if (!isset($_POST['disp_help'])) { $_POST['disp_help']="0"; }
else { $_POST['disp_help']='1'; }

// Number of items on the 'welcome' page:
if (!is_numeric($_POST['welcome_num_items']) || ($_POST['welcome_num_items']<1)) {
	$_POST['welcome_num_items']=5;
}

// Welcome box 1-3:
$_POST['box1'] = str_replace($forbidden, "", $_POST['box1']);
$_POST['box2'] = str_replace($forbidden, "", $_POST['box2']);
$_POST['box3'] = str_replace($forbidden, "", $_POST['box3']);
if (($_POST['box1']=="") && ($_POST['box2']=="") && ($_POST['box3']=="")) {
	$_POST['box1']='Recently played tracks';
}	

// 0.7.1: Autoplay after last track (why haven't I done this before ??)
if (!isset($_POST['autoplay_last'])) {
	$_POST['autoplay_last']="0";
}

// 0.7.2: Submit tracks to last.fm:
if (!isset($_POST['lastfm_active'])) {
	$_POST['lastfm_active']="0";
} else {
 	$_POST['lastfm_active']="1";
} 	
if ((isset($_POST['lastfm_username'])) && (isset($_POST['lastfm_password']))) {
	$_POST['lastfm_username'] = strip_tags($_POST['lastfm_username']);
//	$_POST['lastfm_username']=ereg_replace('[^a-zA-Z0-9]', "", $_POST['lastfm_username']); // 0.7.4:
	$_POST['lastfm_username']=preg_replace ("/[^0-9^a-z^A-Z^_^.^ ^#]/", "", $_POST['lastfm_username']);
	$_POST['lastfm_password'] = strip_tags($_POST['lastfm_password']);
//	$_POST['lastfm_password']=ereg_replace('[^a-zA-Z0-9]', "", $_POST['lastfm_password']); // 0.7.4:
	$_POST['lastfm_password']=preg_replace ("/[^0-9^a-z^A-Z^_^.^ ^#]/", "", $_POST['lastfm_password']);
} else {
 	$_POST['lastfm_username']='';
 	$_POST['lastfm_password']='';
}	

/*

		STEP 2: ALL VALIDATED (hopefully) - UPDATE THE SESSION:

*/
$_SESSION['enqueue']=$_POST['playmethod']; // 0: Play now, 1: Put in queue
$_SESSION['xspf_active']=$_POST['xspf_active']; // 0.8.0: Use flash-player ?
$_SESSION['count']=$_POST['count']; // Items/page
$_SESSION['disp_last_played']=$_POST['disp_last_played']; // Show 'last played' for tracks
$_SESSION['disp_times_played']=$_POST['disp_times_played']; // Show # of times a track has been played
$_SESSION['show_ids']=$_POST['show_ids']; // Display ID-numbers
$_SESSION['show_letters']=$_POST['show_letters']; // Display 'jump to' option
$_SESSION['disp_duration']=$_POST['disp_duration']; // Show duration for tracks
$_SESSION['disp_totals']=$_POST['disp_totals']; // Show totals for albums, favorites etc.
$_SESSION['disp_related_performers']=$_POST['disp_related_performers']; // last.fm related performers
$_SESSION['confirm_delete']=$_POST['confirm_delete']; // Confirm deletions
$_SESSION['disp_download']=$_POST['disp_download']; // Display download option
$_SESSION['disp_upload']=$_POST['disp_upload']; // 0.7.4: Display upload option
$_SESSION['disp_lyrics']=$_POST['disp_lyrics']; // Display lyrics
$_SESSION['hide_icon_text']=$_POST['hide_icon_text']; // 0.8.4
$_SESSION['disp_fav_shares']=$_POST['disp_fav_shares']; // Display shared favorites
$_SESSION['disp_small_images']=$_POST['disp_small_images']; // 0.7.3: Disp. albums/perf.
$_SESSION['browse_albums_by_covers']=$_POST['browse_albums_by_covers']; // 0.7.9: Browse albums by pictures
$_SESSION['browse_performer_by_picture']=$_POST['browse_performer_by_picture']; // 0.7.9: Browse performers by pict.
$_SESSION['ask4favoritelist']=$_POST['ask4favoritelist']; // Always ask for name of fav.list
$_SESSION['ask4favoritelist_disp_suggestion']=$_POST['ask4favoritelist_disp_suggestion']; // Show suggestions when adding to a favorite
$_SESSION['disp_now_playing']=$_POST['disp_now_playing']; // Enable display of "Now playing"
$_SESSION['disp_now_playing_add2favorite']=$_POST['disp_now_playing_add2favorite']; // Enabled option to add something to favorite in "Now playing"
$_SESSION['avoid_duplicate_entries']=$_POST['avoid_duplicate_entries']; // Avoid duplicates
$_SESSION['cssfile']=$_POST['cssfile']; // Theme
$_SESSION['icon_dir']=$_POST['icon_dir']; // Icons
$_SESSION['lang']=$_POST['lang']; // Language
$_SESSION['disp_help']=$_POST['disp_help']; // Display links to AmpJuke FAQ
$_SESSION['welcome_num_items']=$_POST['welcome_num_items']; // # of items on 'welcome' page
$_SESSION['welcome_content_1']=$_POST['box1']; // 'Welcome' page contents, box 1
$_SESSION['welcome_content_2']=$_POST['box2']; // 'Welcome' page contents, box 2
$_SESSION['welcome_content_3']=$_POST['box3']; // 'Welcome' page contents, box 3
$_SESSION['autoplay_last_list']=$_POST['autoplay_last_list']; // 0.7.1
$_SESSION['autoplay_last']=$_POST['autoplay_last']; //0.7.1
$_SESSION['lastfm_active']=$_POST['lastfm_active']; // 0.7.2
$_SESSION['lastfm_username']=$_POST['lastfm_username']; // 0.7.2
$_SESSION['lastfm_password']=$_POST['lastfm_password']; // 0.7.2
$_SESSION['xspf_active']=$_POST['xspf_active']; // 0.8.0
$_SESSION['auto_add2favorite']=$_POST['auto_add2favorite']; // 0.8.2
$_SESSION['auto_add2favorite_create_new']=$_POST['auto_add2favorite_create_new']; // 0.8.3
$_SESSION['auto_add2favorite_prefix']=$_POST['auto_add2favorite_prefix']; // 0.8.2
// 0.8.0: Force some other stuff to "disappear" in the current session, if flash-player is enabled:
if ($_SESSION['xspf_active']=='1') {
	$_SESSION['disp_now_playing']='0';
}	

/* 

		STEP 3: Construct and execute a query in order to update the settings:

*/
function qadd($field,$value) {
	$ret=$field;
	$ret.="='".$value."'";
	return $ret;
}
// Only change stuff in the db if the user<>"anonymous":
if (($_SESSION['login']!="anonymous")) { 	
	$qry="UPDATE user SET";
	$qry.=' '.qadd('enqueue',$_POST['playmethod']).',';
	// 0.8.0: REMEMBER FLASH-STUFF HERE !!!
	$qry.=' '.qadd('count',$_POST['count']).',';
	$qry.=' '.qadd('disp_last_played',$_POST['disp_last_played']).',';
	$qry.=' '.qadd('disp_times_played',$_POST['disp_times_played']).',';
	$qry.=' '.qadd('disp_id_numbers',$_POST['show_ids']).',';
	$qry.=' '.qadd('disp_jump_to',$_POST['show_letters']).',';
	$qry.=' '.qadd('disp_duration',$_POST['disp_duration']).',';
	$qry.=' '.qadd('disp_totals',$_POST['disp_totals']).',';
	$qry.=' '.qadd('disp_related_performers',$_POST['disp_related_performers']).',';
	$qry.=' '.qadd('confirm_delete',$_POST['confirm_delete']).',';
	$qry.=' '.qadd('disp_download',$_POST['disp_download']).',';
	$qry.=' '.qadd('disp_upload',$_POST['disp_upload']).','; // 0.7.4
	$qry.=' '.qadd('disp_lyrics',$_POST['disp_lyrics']).',';
	$qry.=' '.qadd('hide_icon_text',$_POST['hide_icon_text']).','; // 0.8.4
	$qry.=' '.qadd('disp_fav_shares',$_POST['disp_fav_shares']).',';
	$qry.=' '.qadd('disp_small_images',$_POST['disp_small_images']).','; // 0.7.3
	$qry.=' '.qadd('browse_albums_by_covers',$_POST['browse_albums_by_covers']).','; // 0.7.9
	$qry.=' '.qadd('browse_performer_by_picture',$_POST['browse_performer_by_picture']).','; // 0.7.9	
	$qry.=' '.qadd('ask4favoritelist',$_POST['ask4favoritelist']).',';
	$qry.=' '.qadd('ask4favoritelist_disp_suggestion',$_POST['ask4favoritelist_disp_suggestion']).','; // 0.8.2
	$qry.=' '.qadd('disp_now_playing',$_POST['disp_now_playing']).',';
	$qry.=' '.qadd('disp_now_playing_add2favorite',$_POST['disp_now_playing_add2favorite']).','; // 0.8.2
	$qry.=' '.qadd('auto_add2favorite',$_POST['auto_add2favorite']).','; // 0.8.2
	$qry.=' '.qadd('auto_add2favorite_create_new',$_POST['auto_add2favorite_create_new']).','; // 0.8.3
	$qry.=' '.qadd('auto_add2favorite_prefix',$_POST['auto_add2favorite_prefix']).','; // 0.8.2
	$qry.=' '.qadd('avoid_duplicate_entries',$_POST['avoid_duplicate_entries']).',';
	$qry.=' '.qadd('disp_help',$_POST['disp_help']).','; // 0.7.2
	$qry.=' '.qadd('autoplay',$_POST['autoplay']).',';
	$qry.=' '.qadd('autoplay_num_tracks',$_POST['autoplay_num_tracks']).',';
	$qry.=' '.qadd('autoplay_list',$_POST['autoplay_list']).',';
	$qry.=' '.qadd('autoplay_last',$_POST['autoplay_last']).',';
	$qry.=' '.qadd('autoplay_last_list',$_POST['autoplay_last_list']).',';
	$qry.=' '.qadd('cssfile',$_POST['cssfile']).',';
	$qry.=' '.qadd('icon_dir',$_POST['icon_dir']).',';
	$qry.=' '.qadd('lang',$_POST['lang']).',';
	if ($pw_alert==1) {
		// 0.7.8: Don't forget the password SALT...damn:
		$salt=generate_password_salt();
		$pw=md5($salt.$_POST['change_password_1']);			
		$qry.=' '.qadd('password',$pw).',';
		$qry.=' '.qadd('password_salt',$salt).',';
	}
	$qry.=' '.qadd('welcome_num_items',$_POST['welcome_num_items']).',';
	$qry.=' '.qadd('welcome_content_1',$_POST['box1']).',';
	$qry.=' '.qadd('welcome_content_2',$_POST['box2']).',';
	$qry.=' '.qadd('welcome_content_3',$_POST['box3']).','; 
	// 0.8.0: Flash player:
	$qry.=' '.qadd('xspf_active',$_POST['xspf_active']).',';
	// 0.7.2: last.fm stuff:
	$qry.=' '.qadd('lastfm_active',$_POST['lastfm_active']).',';
	$qry.=' '.qadd('lastfm_username',$_POST['lastfm_username']).',';	
	$qry.=' '.qadd('lastfm_password',$_POST['lastfm_password']); // Note: No ',' at the end
	
	$qry.=" WHERE name='".$_SESSION['login']."' LIMIT 1";

	if (!isset($demo)) { // Dont store anything if it's a demo...
		$result=execute_sql($qry,0,-1,$nr);
	}		
	// IF we changed the password (or wasn't successfull), show that in an alert:
	if ($pw_alert=="1") { // = success in pw-change
		echo '<script type="text/javascript" language="javascript">'; 
		echo "alert('Password changed OK.');";
		echo '</script>';
	}	
	if ($pw_alert=="2") { // = no change occured
		echo '<script type="text/javascript" language="javascript">'; 
		echo "alert('ERROR:Password was NOT changed.');";
		echo '</script>';
	}	
}


/*

		STEP 4: Return to TWO pages ago:
	
	
*/

echo '<script type="text/javascript" language="javascript">'; echo "history.go(-2);";
echo '</script>';
?>	
