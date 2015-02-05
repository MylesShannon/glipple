<?php
// 0.7.6: Site-configuration: Used to be in scan.php, but now in own script.
// The code below is mainly a copy of parts from old "scan.php".
// 0.8.3: Changed FAQ-links so they point to "new" site.
// 0.8.8: Added "minimum performer age" = do not stream anything from same performer for X hours 
// Added additional Echonest parameters (which they kept *secret* or at least not told a lot of us "out here" about).
parse_str($_SERVER["QUERY_STRING"]);

$passed=0;
if (isset($uuid)) {
	if (file_exists($uuid)) {
		$passed=1;
		@unlink($uuid);
	}
}	

if ((!file_exists('db_new.sql')) && ($passed==0)) {
	require('logincheck.php');
	if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
		header("Location: logout.php");
		die('Cannot inspect configuration. Not logged in.');
	}	
}

function check_selected($def,$set,$option) {
	$ret='<OPTION VALUE="'.$set.'"';
	if ($def==$set) { 
		$ret.=' SELECTED>';
	} else {
		$ret.='>';
	}
	$ret.=$option.'</OPTION>';
	return $ret;
}		
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>AmpJuke - Site configuration</title>
<?php
if (isset($_SESSION['cssfile'])) {
	echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'" />'; 
} else {	
	echo '<link rel="stylesheet" type="text/css" href="./css/AmpJukeStandard.css">';
}
?>
<script type="text/javascript" src="expand_collapse.js"></script>
<script type="text/javascript" src="rowcols.js"></script>
</head>
<body>
<?php		
require("db.php");
require("sql.php");
require("disp.php");
require('configuration.php');
require('translate.php');

//  Check that we can write to db.php:
if (file_exists('db.php')) {
	if (!is_writable('db.php')) {
		echo 'The file db.php exists, but AmpJuke cannot write to it.<br>';
		echo 'Please correct and try again.';
		die();
	}
}	

//
//
// STORE the configuration
//
//
function write_cfg($h,$cfg,$val,$quotes) {
	if ($quotes==1) {
		fwrite($h,'$'.$cfg.'="'.$val.'";' . chr(13) . chr(10));
	} else {
		fwrite($h,'$'.$cfg.'='.$val.';' . chr(13) . chr(10));
	}
}	

if ((isset($act)) && ($act=='store')) {
//
// STEP 1: Store values in db.php:
//
	$handle=fopen("db.php", "w");
	fwrite($handle, '<?php' . chr(13) . chr(10));
// DB-info.:
	write_cfg($handle,'db_host',$_POST['db_host'],1);
	write_cfg($handle,'db_user',$_POST['db_user'],1);
	write_cfg($handle,'db_password',$_POST['db_password'],1);
	write_cfg($handle,'db_name',$_POST['db_name'],1);
	if ((isset($_POST['ampjuke_tbl_prefix'])) && ($_POST['ampjuke_tbl_prefix']!="")) {
		write_cfg($handle, 'ampjuke_tbl_prefix',$_POST['ampjuke_tbl_prefix'],1);
	}	
// Music:
	write_cfg($handle, 'base_music_dir',$_POST['base_music_dir'],1);
	write_cfg($handle, 'base_http_prog_dir',$_POST['base_http_prog_dir'],1);
// 0.8.6: Charset:
	write_cfg($handle,'charset',$_POST['charset'],1);
// Dateformat:
	write_cfg($handle, 'dateformat',$_POST['dateformat'],1);
// Anonymous settings:
	$val=0;
	if (isset($_POST['allow_anonymous'])) {
		$val=1;
	}
	write_cfg($handle, 'allow_anonymous',$val,0);
	$val=0;
	if (isset($_POST['allow_anonymous_streaming'])) {
		$val=1;
	}
	write_cfg($handle, 'allow_anonymous_streaming',$val,0);
// Keep extension on streamed music:
	$val=0;
	if (isset($_POST['keep_extension'])) {
		$val=1;
	}	
	write_cfg($handle, 'keep_extension',$val,0);
// Compression:
	write_cfg($handle, 'compress_command',$_POST['compress_command'],1);
	$val=0;
	if (isset($_POST['dont_compress_one_file'])) {
		$val=1;
	}
	write_cfg($handle, 'dont_compress_one_file',$val,0);
// External info. about performers:
	$val=0;
	if (isset($_POST['perf_info'])) {
		$val=1;
	}
	write_cfg($handle, 'perf_info',$val,0);
	if (!isset($_POST['perf_info_link'])) { // 0.8.0: Do we actually use this anymore ?
		$_POST['perf_info_link']='0';
	}	
	write_cfg($handle, 'perf_info_link',$_POST['perf_info_link'],1);
// Upload:
	$val=0;
	if (isset($_POST['allow_upload'])) {
		$val=1;
	}	
	if (!is_numeric($_POST['max_upload_files'])) {
		$_POST['max_upload_files']=10;
	}
	write_cfg($handle, 'allow_upload',$val,0);
	write_cfg($handle, 'max_upload_files',$_POST['max_upload_files'],0);
	write_cfg($handle, 'upload_chmod',substr($_POST['upload_chmod'],0,3),1);
// last.fm.  Aka. related performers:
	$val=0;
	if (isset($_POST['lastfm_allow_related'])) {
		$val=1;
	}	
	if (!is_numeric($_POST['lastfm_max_related_artists'])) {
		$_POST['lastfm_max_related_artists']=10;
	}
	if (!is_numeric($_POST['lastfm_min_related_match'])) {
	 	$_POST['lastfm_min_related_match']=50;
	}
	if (!is_numeric($_POST['lastfm_cache_days'])) {
		$_POST['lastfm_cache_days']=30;
	}	
	write_cfg($handle,'lastfm_allow_related',$val,0);
	write_cfg($handle,'lastfm_max_related_artists',$_POST['lastfm_max_related_artists'],0);
	write_cfg($handle,'lastfm_min_related_match',$_POST['lastfm_min_related_match'],0);
	write_cfg($handle,'lastfm_cache_days',$_POST['lastfm_cache_days'],0);
	$val=0;
	if (isset($_POST['lastfm_disp_sample_tracks'])) {
		$val=1;
	}
	write_cfg($handle,'lastfm_disp_sample_tracks',$val,0);
	if (!is_numeric($_POST['lastfm_disp_sample_number'])) {
		$_POST['lastfm_disp_sample_number']=3;
	}
	write_cfg($handle,'lastfm_disp_sample_number',$_POST['lastfm_disp_sample_number'],0);
	write_cfg($handle,'lastfm_disp_sample_priority',$_POST['lastfm_disp_sample_priority'],1);
// "Now playing" stuff - warning - many entries:
	$val=0;
	if (isset($_POST['allow_now_playing'])) {
	 	$val=1;
	}
	write_cfg($handle, 'allow_now_playing',$val,0);
	$val=0;
	if (isset($_POST['now_playing_disp_cover'])) {
	 	$val=1;
	}
	write_cfg($handle, 'now_playing_disp_cover',$val,0);
	write_cfg($handle, 'now_playing_dimension_w',$_POST['now_playing_dimension_w'],1);
	write_cfg($handle, 'now_playing_dimension_h',$_POST['now_playing_dimension_h'],1);
	if (!is_numeric($_POST['now_playing_update_rate'])) {
		$_POST['now_playing_update_rate']=15000;
	}			
	write_cfg($handle, 'now_playing_update_rate',$_POST['now_playing_update_rate'],0);
	if (!is_numeric($_POST['popout_width'])) {
		$_POST['popout_width']=200;
	}			
	write_cfg($handle, 'popout_width',$_POST['popout_width'],0);
	if (!is_numeric($_POST['popout_height'])) {
		$_POST['popout_height']=200;
	}			
	write_cfg($handle, 'popout_height',$_POST['popout_height'],0);
	write_cfg($handle,'np_light_update',$_POST['np_light_update'],1);
	$val=0;
	if (isset($_POST['np_update_automatic_play'])) {
		$val=1;
	}
	write_cfg($handle,'np_update_automatic_play',$val,0);
	write_cfg($handle,'np_light_update_msg',$_POST['np_light_update_msg'],1);
// Array of forbidden characters (WTF...is this still used??):
	$val=$_POST['forbidden_characters'];
	write_cfg($handle,'forbidden_characters',$val,1);
// Downsampling/transcoding:
	$val=0;
	if (isset($_POST['lame_enabled'])) {
		$val=1;
	}
	write_cfg($handle,'lame_enabled',$val,0);
	write_cfg($handle,'lame_path',$_POST['lame_path'],1);
	write_cfg($handle,'lame_parameters',$_POST['lame_parameters'],1);
	// 0.8.2: Dynamic transcoding:
	$val=0;
	if (isset($_POST['lame_dynamic_enabled'])) {
		$val=1;
	}
	write_cfg($handle,'lame_dynamic_enabled',$val,0);
	write_cfg($handle,'lame_dynamic_iplist',$_POST['lame_dynamic_iplist'],1);
// last_scan_date:
	if (isset($_POST['last_scan_date'])) {
		write_cfg($handle,'last_scan_date',$_POST['last_scan_date'],0);
	}	
// last.fm settings:
	$val="0";
	if (isset($_POST['lastfm_allow_submission'])) {
		$val=1;
	}
	write_cfg($handle,'lastfm_allow_submission',$val,1);
	$val="0";
	if (isset($_POST['lastfm_allow_local_users'])) {
		$val=1;
	}
	write_cfg($handle,'lastfm_allow_local_users',$val,1);
	write_cfg($handle,'lastfm_default_username',$_POST['lastfm_default_username'],1);
	write_cfg($handle,'lastfm_default_password',$_POST['lastfm_default_password'],1);
// 0.8.2: Allow peronal setting  "Add tracks to favorite lists automatically":
	$val='0';
	if (isset($_POST['lastfm_allow_auto_add2favorite'])) {
		$val='1';
	}
	write_cfg($handle,'lastfm_allow_auto_add2favorite',$val,1);
// 0.8.2: Allow personal setting "Suggest favorites based on tags":
	$val='0';
	if (isset($_POST['lastfm_allow_favorite_suggestion'])) {
		$val='1';
	}
	write_cfg($handle,'lastfm_allow_favorite_suggestion',$val,1);
// 0.7.7: Ban IP's:
	$val="0";
	if (isset($_POST['max_failed_login_enabled'])) {
		$val="1";
	}
	write_cfg($handle,'max_failed_login_enabled',$val,1);
	write_cfg($handle,'max_failed_login_attempts',$_POST['max_failed_login_attempts'],1);
// 0.7.7: Lyrics
	$val="0";
	if (isset($_POST['lyrics_enabled'])) {
		$val="1";
	}
	write_cfg($handle,'lyrics_enabled',$val,1);
	write_cfg($handle,'lyrics_path',$_POST['lyrics_path'],1);
// Special extensions (m4a, mp4 etc.):
	$val="0";
	if (isset($_POST['special_extensions_enabled'])) {
		$val="1";
	}
	write_cfg($handle,'special_extensions_enabled',$val,1);
	write_cfg($handle,'special_extensions',$_POST['special_extensions'],1);
	$val="0";
	if (isset($_POST['special_extensions_update_playing'])) {
		$val=1;
	}
	write_cfg($handle,'special_extensions_update_playing',$val,1);
	$val="0";
	if (isset($_POST['special_extensions_update_statistics'])) {
		$val=1;
	}
	write_cfg($handle,'special_extensions_update_statistics',$val,1);
// 0.8.0: Flash player configuration:
	$val='0';
	if (isset($_POST['xspf_enabled'])) {
		$val='1';
	}
	write_cfg($handle,'xspf_enabled',$val,1);
	$val='0';
	if (isset($_POST['xspf_only_player'])) {
		$val='1';
	}
	write_cfg($handle,'xspf_only_player',$val,1);
// 0.8.0: Download album covers from last.fm ?
	$val=0;
	if (isset($_POST['lastfm_download_covers'])) {
		$val=1;
	}
	write_cfg($handle,'lastfm_download_covers',$val,1);
// 0.7.8: Add reflections ?
/* 0.8.7: Gone!
   	$val=0;
   	if (isset($_POST['add_reflections'])) {
   		$val=1;
   	}
   	write_cfg($handle,'add_reflections',$val,0);   		
*/
// 0.8.1: Bing stuff:
// 0.8.6: ...abandoned Bing!
/*
	write_cfg($handle,'bing_appid',$_POST['bing_appid'],1);
	$val=200;
	if ((isset($_POST['bing_preferred_size'])) && (is_numeric($_POST['bing_preferred_size']))) {
		$val=$_POST['bing_preferred_size'];
	}
	write_cfg($handle,'bing_preferred_size',$val,1);
*/
// 0.8.1: Hide keep me signed in on login page:
	$val='0';
	if (isset($_POST['login_hide_keep_me_signed_in'])) {
		$val='1';
	}
	write_cfg($handle,'login_hide_keep_me_signed_in',$val,1);
// 0.8.3: Allow shared favorites:
	$val='0';
	if (isset($_POST['shared_favorites_allow'])) {
		$val='1';
	}
	write_cfg($handle,'shared_favorites_allow',$val,1);
// 0.8.4: User-registration:
	$val='0';
	if (isset($_POST['user_reg_enabled'])) {
		$val='1';
	}
	write_cfg($handle,'user_reg_enabled',$val,1);
	write_cfg($handle,'email_sender',$_POST['email_sender'],1);
	write_cfg($handle,'user_reg_display_text',$_POST['user_reg_display_text'],1);
	if (!is_numeric($_POST['user_reg_username_min_length'])) { $_POST['user_reg_username_min_length']=3; }
	write_cfg($handle,'user_reg_username_min_length',$_POST['user_reg_username_min_length'],1);
	if (!is_numeric($_POST['user_reg_username_max_length'])) { $_POST['user_reg_username_max_length']=20; }
	write_cfg($handle,'user_reg_username_max_length',$_POST['user_reg_username_max_length'],1);
	if (!is_numeric($_POST['user_reg_password_min_length'])) { $_POST['user_reg_password_min_length']=3; }
	write_cfg($handle,'user_reg_password_min_length',$_POST['user_reg_password_min_length'],1);
	if (!is_numeric($_POST['user_reg_password_max_length'])) { $_POST['user_reg_password_max_length']=20; }
	write_cfg($handle,'user_reg_password_max_length',$_POST['user_reg_password_max_length'],1);	
	$val='0';
	if (isset($_POST['enable_email_with_lost_password'])) {
		$val='1';
	}
	write_cfg($handle,'enable_email_with_lost_password',$val,1);
	write_cfg($handle,'enable_email_with_lost_password_text',$_POST['enable_email_with_lost_password_text'],1);
// 0.8.4: Delete inactive users:
	$_POST['delete_inactive_users']=only_digits($_POST['delete_inactive_users']);
	write_cfg($handle,'delete_inactive_users',$_POST['delete_inactive_users'],1);
// 0.8.5: Animations stuff:
	$val='0';
	if (isset($_POST['animation_enabled'])) {
		$val='1';
	}
	write_cfg($handle,'animation_enabled',$val,1);
	write_cfg($handle,'animation_delay_timing',$_POST['animation_delay_timing'],1);
	write_cfg($handle,'animation_opacity_timing',$_POST['animation_opacity_timing'],1);
// 0.8.6: Similar tracks / Echonest API:
	$val='0';
	if (isset($_POST['echonest_enabled'])) {
		$val='1';
	}
	write_cfg($handle,'echonest_enabled',$val,1);
	write_cfg($handle,'echonest_api_key',$_POST['echonest_api_key'],1);
	write_cfg($handle,'echonest_api_url',$_POST['echonest_api_url'],1);
	write_cfg($handle,'echonest_max_results',only_digits($_POST['echonest_max_results']),1);
	write_cfg($handle,'echonest_max_diff_duration',only_digits($_POST['echonest_max_diff_duration']),1);
	$val='0';
	if (isset($_POST['echonest_queue_tracks'])) {
		$val='1';
	}
	write_cfg($handle,'echonest_queue_tracks',$val,1);
	// 0.8.7: use echonest parameters in advanced search:
	$val='0';
	if (isset($_POST['echonest_advanced_search'])) {
		$val='1';
	}
	write_cfg($handle,'echonest_advanced_search',$val,1);
// 0.8.6: Echonest "parameters":
	write_cfg($handle,'echonest_limit',$_POST['echonest_limit'],0);
	write_cfg($handle,'echonest_tempo_priority',only_digits($_POST['echonest_tempo_priority']),2);
	write_cfg($handle,'echonest_tempo_factor',only_digits($_POST['echonest_tempo_factor']),2);
	write_cfg($handle,'echonest_danceability_priority',only_digits($_POST['echonest_danceability_priority']),2);
	write_cfg($handle,'echonest_energy_priority',only_digits($_POST['echonest_energy_priority']),2);
	write_cfg($handle,'echonest_key_priority',only_digits($_POST['echonest_key_priority']),2);
	write_cfg($handle,'echonest_key_factor',only_digits($_POST['echonest_key_factor']),2);
	write_cfg($handle,'echonest_loudness_priority',only_digits($_POST['echonest_loudness_priority']),2);
	// 0.8.8: Additional/new echonest "parameters":
	write_cfg($handle,'echonest_liveness_priority',only_digits($_POST['echonest_liveness_priority']),2);
	write_cfg($handle,'echonest_speechiness_priority',only_digits($_POST['echonest_speechiness_priority']),2);	
	write_cfg($handle,'echonest_acousticness_priority',only_digits($_POST['echonest_acousticness_priority']),2);
	write_cfg($handle,'echonest_valence_priority',only_digits($_POST['echonest_valence_priority']),2);
	// 0.8.8: ...end
// 0.8.6: Jukebox configuration:
    $val='0';
    if (isset($_POST['jukebox_mode_enabled'])) {
         $val='1';
    }
    write_cfg($handle,'jukebox_mode_enabled',$val,1);
	write_cfg($handle,'jukebox_mode_welcome_msg',$_POST['jukebox_mode_welcome_msg'],1);
	write_cfg($handle,'jukebox_mode_welcome_link',$_POST['jukebox_mode_welcome_link'],1);
    write_cfg($handle,'jukebox_mode_user',$_POST['jukebox_mode_user'],1);
    write_cfg($handle,'jukebox_mode_selection_limit_tracks',only_digits($_POST['jukebox_mode_selection_limit_tracks']),1);
    write_cfg($handle,'jukebox_mode_selection_limit_minutes',only_digits($_POST['jukebox_mode_selection_limit_minutes']),1);
    // 0.8.8:
    write_cfg($handle,'jukebox_mode_selection_limit_tracks_total',only_digits($_POST['jukebox_mode_selection_limit_tracks_total']),1);

    write_cfg($handle,'jukebox_mode_min_age',only_digits($_POST['jukebox_mode_min_age']),1);
    write_cfg($handle,'jukebox_mode_min_age_performer',only_digits($_POST['jukebox_mode_min_age_performer']),1); // 0.8.8
    write_cfg($handle,'jukebox_mode_selection_identity',$_POST['jukebox_mode_selection_identity'],1);
    write_cfg($handle,'jukebox_mode_request_probability',only_digits($_POST['jukebox_mode_request_probability']),1);
    write_cfg($handle,'jukebox_mode_request_pick',$_POST['jukebox_mode_request_pick'],1);
	$val='0';
    if (isset($_POST['jukebox_mode_msg_popup_enabled'])) {
        $val='1';
    }
    write_cfg($handle,'jukebox_mode_msg_popup_enabled',$val,1);
    write_cfg($handle,'jukebox_mode_msg_add_success',$_POST['jukebox_mode_msg_add_success'],1);
    write_cfg($handle,'jukebox_mode_msg_fail_limit_tracks',$_POST['jukebox_mode_msg_fail_limit_tracks'],1);
    write_cfg($handle,'jukebox_mode_msg_fail_outstanding_tracks_all',$_POST['jukebox_mode_msg_fail_outstanding_tracks_all'],1); // 0.8.8
    write_cfg($handle,'jukebox_mode_msg_fail_outstanding_tracks',$_POST['jukebox_mode_msg_fail_outstanding_tracks'],1);
    write_cfg($handle,'jukebox_mode_msg_fail_age',$_POST['jukebox_mode_msg_fail_age'],1);
    write_cfg($handle,'jukebox_mode_msg_fail_age_performer',$_POST['jukebox_mode_msg_fail_age_performer'],1);
    write_cfg($handle,'jukebox_mode_msg_fail_already_requested',$_POST['jukebox_mode_msg_fail_already_requested'],1);
// 0.8.6: Screensaver:
	$val='0';
	if (isset($_POST['screensaver_enabled'])) {
		$val='1';
	}
	write_cfg($handle,'screensaver_enabled',$val,1);
	write_cfg($handle,'screensaver_start_time',only_digits($_POST['screensaver_start_time']),1);
	write_cfg($handle,'screensaver_reload_time',only_digits($_POST['screensaver_reload_time']),1);
	write_cfg($handle,'screensaver_images',$_POST['screensaver_images'],1);
	write_cfg($handle,'screensaver_preferred_size',only_digits($_POST['screensaver_preferred_size']),1);
	write_cfg($handle,'screensaver_iterations',only_digits($_POST['screensaver_iterations']),1);
	write_cfg($handle,'screensaver_ms_delay_factor',$_POST['screensaver_ms_delay_factor'],1);
	write_cfg($handle,'screensaver_ms_fade_factor',$_POST['screensaver_ms_fade_factor'],1);
// Finish db.php:
	fwrite($handle, "?");
	fwrite($handle, ">" . chr(13) . chr(10));
	fclose($handle);
//
// STEP 2: We may want to create a new database and/or new tables - from SCRATCH:
//
	if ((isset($_POST['createdb'])) || (isset($_POST['createtbl']))) { 
		$connection=mysql_connect($_POST['db_host'],$_POST['db_user'],$_POST['db_password']) or die('Create database: Could not connect.');
		if (isset($_POST['createdb'])) { // we really want to create an empty database:
			$qry="DROP DATABASE IF EXISTS ".$_POST['db_name'];
			$result=mysql_query($qry) or die('Could NOT delete the database: '.$_POST['db_name'].'<br>Most likely, a wrong MySQL-username and/or -password is used.');
			$qry="CREATE DATABASE ".$_POST['db_name'];
			$result=mysql_query($qry, $connection)
				or die('Could NOT create the database: '.$_POST['db_name'].'<br>Most likely, a wrong MySQL-username and/or -password is used.');
		}	
		if (isset($_POST['createtbl'])) { // we want to create empty tables within the database:
			mysql_select_db($_POST['db_name']) or die('You wanted to create empty tables, but the <b>database</b> could not be found.');
			// create the tables (again):
			require("db_new.sql");	
			// Prefix tables ? 0.8.8: Added IF NOT EXISTS
			if (isset($_POST['ampjuke_tbl_prefix'])) {
				$ampjuke_tbl_prefix=$_POST['ampjuke_tbl_prefix'];
				$c_album=str_replace("CREATE TABLE IF NOT EXISTS album (", "CREATE TABLE IF NOT EXISTS ".$ampjuke_tbl_prefix."album (", $c_album);
				$c_fav=str_replace("CREATE TABLE IF NOT EXISTS fav (", "CREATE TABLE IF NOT EXISTS ".$ampjuke_tbl_prefix."fav (", $c_fav);	
				$c_performer=str_replace("CREATE TABLE IF NOT EXISTS performer (", "CREATE TABLE IF NOT EXISTS ".$ampjuke_tbl_prefix."performer (", $c_performer);
				$c_queue=str_replace("CREATE TABLE IF NOT EXISTS queue (", "CREATE TABLE IF NOT EXISTS ".$ampjuke_tbl_prefix."queue (", $c_queue);	
				$c_track=str_replace("CREATE TABLE IF NOT EXISTS `track` (", "CREATE TABLE IF NOT EXISTS `".$ampjuke_tbl_prefix."track` (", $c_track);
				$c_user=str_replace("CREATE TABLE IF NOT EXISTS `user` (", "CREATE TABLE IF NOT EXISTS `".$ampjuke_tbl_prefix."user` (", $c_user);
				$c_fav_shares=str_replace("CREATE TABLE IF NOT EXISTS fav_shares (", "CREATE TABLE IF NOT EXISTS ".$ampjuke_tbl_prefix."fav_shares (", $c_fav_shares);
			} 
			$result=mysql_query($c_album, $connection) or die(mysql_error());
			// c_fav is the new kid on the block. c_favorites was the old...:
			$result=mysql_query($c_fav, $connection) or die(mysql_error());
			$result=mysql_query($c_performer, $connection) or die(mysql_error());
			$result=mysql_query($c_queue, $connection) or die(mysql_error());
			$result=mysql_query($c_track, $connection) or die(mysql_error());
			$result=mysql_query($c_user, $connection) or die(mysql_error());
			// fav_shares table:
			$result=mysql_query($c_fav_shares, $connection) or die(mysql_error());
			// insert the "defaults" in user (so we can login) & performer:
		    // this query ensures we have a place to store albums w. 
			// "various" (ie. multiple) performers:
			$qry="INSERT INTO performer (pid, pname) VALUES ('1','')"; // 0.7.7: Changed qry to: performer (pid,pname)
			$result=execute_sql($qry,0,-1,$nr);
			$qry="INSERT INTO user (name, admin, password, lang, count) ";
			$qry.="VALUES ('admin', '1', 'pass', 'EN', '20')";
			$result=execute_sql($qry,0,-1,$nr);				
		} // if isset post create_tbl...
		echo '<p class="note">Ok. Everything is fine so far !<br>';

		if (isset($_POST['createdb'])) { 
			echo 'The database was created successfully.<br>';
		}
		if (isset($_POST['createtbl'])) {	
			echo 'New tables were created successfully.<br>';
			// Tell 'em we also wanted prefixes:
			if (isset($_POST['ampjuke_tbl_prefix'])) {
				echo 'AmpJuke-tablenames prefixed: <b>'.$_POST['ampjuke_tbl_prefix'].'</b>';
				echo '<br>';
			}	
		}	
		// Instructions:
		echo '<br>Now, do the following:<br>';
		echo '1. Login using username: "<b>admin</b>" and password: "<b>pass</b>"<br>';
		echo '2. Click "<b>Scan music...</b>" in the menu to the left (under "Admins options")<br>';
		echo '3. One the next screen: Click "<b>Scan&import all music...</b>". This step may take ';
		echo 'a LONG time, in case you have many music files and/or AmpJuke is on a slow server<br>';
		echo '<hr width="80%" color="#abcdef" align="center">';
		echo '<p class="note">&nbsp';
		echo '<font color="red"><b>Special notes:</b><br>';
		echo 'Please <b>change the password for "admin"</b> as one of the first things after ';
		echo 'logging in in order not to compromise your system.<br>';
		rename('db_new.sql','db_new.php');
		rename('setup.php','setup_backup.php');
		echo '<a href="login.php">Click here to login.</a>';
		die();
	} // if isset post createdb||createtbl

	if (file_exists('db_new.sql')) {	
		rename('db_new.sql','db_new.php');
	}
	if (file_exists('setup.php')) {
		rename('setup.php','setup_backup.php'); 
	}
 	die('<a href="./">Configuration saved. Click here to go back to the "welcome" page</a>');
} // if act==store
	
	

//
//
// INSPECT/set site-wide configuration
//
//
if (!isset($what)) { 
	$what='';
}	
echo headline($what,'','');
print "</td></tr> \n\n\n <!-- Actual CONTENT comes here: --> \n\n\n <tr><td>";
echo '<FORM NAME="sitecfg" method="POST" action="sitecfg.php?act=store">';
echo '<table class="ampjuke_headline_table"><tr><td colspan="5" align="center">';
echo 'AmpJuke site configuration</td></tr><tr><td>';
?>
	Expand all:
	<img src="./ampjukeicons/expandall.gif" id="exp" onclick="cfg_expand_collapse_all('1')">
	<br>Collapse all:
	<img src="./ampjukeicons/collapseall.gif" id="exp" onclick="cfg_expand_collapse_all('0')">

<?php	
//
//	
// DATABASE STUFF:
//
//

?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif1" onclick="handleClick('to_col1','gif1')">	
Database options
<div id="to_col1" style="display:none"></b>
<?php
echo std_table("ampjuke_content_table","ampjuke_content");
echo '<tr><td>Database host:</td>';
echo '<td>'.add_textinput('db_host',get_configuration('db_host'),40).'</td></tr>';

echo '<tr><td>Database user:</td>';
echo '<td>'.add_textinput('db_user',get_configuration("db_user"),40).'</td></tr>';

echo "<tr><td>Database user's password:</td>";
echo '<td>'.add_textinput_password('db_password',get_configuration("db_password"),40).'</td></tr>'; // 0.8.3

echo '<tr><td>Database name:</td>';
echo '<td>'.add_textinput('db_name',get_configuration("db_name"),40).'</td></tr>';

if ((!file_exists("db_new.sql")) && (get_configuration("ampjuke_tbl_prefix")<>'')) {
	echo '<input type="hidden" name="ampjuke_tbl_prefix" value="';
	echo get_configuration("ampjuke_tbl_prefix").'">';
}	

if (get_configuration("last_scan_date")!='') {
	echo '<input type="hidden" name="last_scan_date" value="';
	echo get_configuration("last_scan_date").'">';
}
	
if (file_exists("db_new.sql")) {
	echo "<tr><td>Create an empty database:</td>";
	echo '<td>'.add_checkbox('createdb',1);
	echo '<b>Warning:</b> If you select an existing database, <b>everything</b> within ';
	echo 'it will be <b>deleted</b> !<br>';
	echo '</td></tr>';
	echo "<tr><td>Create empty tables within the database:</td>";		
	echo '<td>'.add_checkbox('createtbl',1).'</td></tr>';
	echo "<tr><td>Prefix tablenames:</td>";		
	echo '<td>'.add_textinput('ampjuke_tbl_prefix','ampjuke_',20).'</td></tr>';	
} else {
	echo '<tr><td colspan="2">Rename "<b>db_new.php</b>" to "<b>db_new.sql</b>" ';
	echo 'if you want ';
	echo 'the option to create a new database and/or new tables from scratch<br>';
	echo 'If your database & tables already exists you might as well delete "';
	echo '<b>db_new.php</b>"';
	echo '</td></tr>';
}	
echo '</table></div>';


//
//
// DIR. STUFF:	
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif2" onclick="handleClick('to_col2','gif2')">
Location of program files & your music</b>
<div id="to_col2" style="display:none"></b>
<?php	
echo std_table("ampjuke_content_table","ampjuke_content2");
$table2=1;
echo '<tr><td valign="top">"Base" folder where your music files are located:</td>';
echo '<td>'.add_textinput('base_music_dir',get_configuration('base_music_dir'),80);
echo '<br><b>'.add_faq(12,'Click here for more information about this setting',1);
echo '</b>.';    
echo '<br>Note: the current/absolute directory is: ';
echo '<b><font color="red">'.getcwd().'</b><font color="black">';
echo ' (might be useful if you install on an ISP server thats not your own...)';
echo '<br>Remember: <b><u>No</u></b> trailing slash';
echo ' & Absolute path. F.ex.: <b>/home/michael/my_music</b>';
echo '</td></tr>';
echo '<tr><td valign="top">HTTP-Location of program files:</td>';
$d=get_configuration("base_http_prog_dir");
if ($d=="") { // 0.8.3: Changed compared to previous versions:
	$d='http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$s=explode('/sitecfg.php',$d);
	$d=$s[0];
}	
echo '<td>'.add_textinput('base_http_prog_dir',$d,80);
echo '<br><b>Examples:</b>';
echo 'http://www.yourhost.com/location-of-ampjuke, ';
echo 'http://www.somehost.com/ampjuke</td></tr>';
echo '<tr><td valign="top">Use this charset:</td>';
echo '<td>'.add_textinput('charset',get_configuration('charset'),30);
echo '<br>Examples: ISO-8859-1, utf-8';
echo '</td></tr></table></div>';


//
//
// DOWNLOAD&COMPRESS OPTIONS: 
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif3" onclick="handleClick('to_col3','gif3')">
Download & Upload options
<div id="to_col3" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content3");
$table3=1;
// Keep extension on files ?
echo '<tr><td valign="top"><br>Keep extension on downloaded/streamed music:</td>';
echo '<td><b>'.add_faq(24,'Click here for more information about download & compression settings',1).'.</b>.';
echo '<br>'.add_checkbox('keep_extension',get_configuration('keep_extension')).'</td></tr>';
// Compress multiple files using:
echo '<tr><td valign="top">Location of "tar" incl. compression parameters:</td>';
echo '<td>'.add_textinput('compress_command',get_configuration('compress_command'),25).'</td></tr>';
// Compress one file when downloading ?
echo '<tr><td valign="top">When downloading one track, do not compress:</td>';
echo '<td>'.add_checkbox('dont_compress_one_file',get_configuration('dont_compress_one_file')).'</td></tr>';
//  
//  UPLOAD section:   
//
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
// Allow upload whatsoever ?
echo '<tr><td valign="top"><br>Allow upload:</td>';
echo '<td>';
echo '<b>'.add_faq(37,'Click here for more information about upload settings',1).'</b>.<br>';
echo add_checkbox('allow_upload',get_configuration('allow_upload')).'</td></tr>';
// Max. number of files to upload in one go:
echo '<tr><td valign="top">Max. number of files to upload each time:</td>';
echo '<td>'.add_textinput('max_upload_files',get_configuration('max_upload_files'),4).'</td></tr>';
// CHMOD uploaded files to...
echo '<tr><td valign="top">After upload, CHMOD files to:</td>';
echo '<td>'.add_textinput('upload_chmod',get_configuration('upload_chmod'),4).'</td></tr>';
echo '</table></div>';



//
//
// LAST.FM section (aka. related performers):
//
//
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif4" onclick="handleClick('to_col4','gif4')">
	Related performers
	<div id="to_col4" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content4");
$table4=1;
// Allow disp. related performers ?
echo '<tr><td valign="top"><br>Display related performers:</td>';
echo '<td><b>'.add_faq(41,'Click here for more information about settings for related performers',1).'.</b><br>';
echo add_checkbox('lastfm_allow_related',get_configuration('lastfm_allow_related')).'</td></tr>';
// Max. number of related performers to display:
echo '<tr><td valign="top">Max. number of related performers:</td>';
echo '<td>'.add_textinput('lastfm_max_related_artists',get_configuration("lastfm_max_related_artists"),4).'</td></tr>';
// Threshold level:
echo '<tr><td valign="top">Minimum match score:</td>';
echo '<td>'.add_textinput('lastfm_min_related_match',get_configuration("lastfm_min_related_match"),4).'</td></tr>';
// Days to cache:
echo '<tr><td valign="top">Cache related performers locally for:</td>';
$d=get_configuration("lastfm_cache_days");
if (!is_numeric($d)) {
	$d=30;
}
echo '<td>'.add_textinput('lastfm_cache_days',$d,3).' (days)</td></tr>';
// 0.7.8: Display tracks/samples from related performers ? (3 new configuration values):
echo '<tr><td valign="top">Display some tracks/samples from related performers:</td>';
echo '<td>'.add_checkbox('lastfm_disp_sample_tracks',get_configuration('lastfm_disp_sample_tracks')).'</td></tr>';
// 0.7.8: Number of tracks/samples to display:
echo '<tr><td>Number of tracks/samples to display:</td>';
echo '<td>'.add_textinput('lastfm_disp_sample_number',get_configuration('lastfm_disp_sample_number'),3).'</td></tr>';
// 0.7.8: Give priority to these tracks/samples:
echo '<tr><td>Give priority to these tracks/samples:</td>';
echo '<td><SELECT NAME="lastfm_disp_sample_priority" class="tfield">';
$c=get_configuration('lastfm_disp_sample_priority');
echo check_selected($c,'nothing','Nothing (completely random)');
echo check_selected($c,'least_played','Least played tracks');
echo check_selected($c,'most_played','Most played tracks');
echo check_selected($c,'oldest','Tracks not played recently');
echo check_selected($c,'newest','Tracks played recently');
echo '</SELECT></td></tr>';
echo '</table></div>';



//
//
// 0.8.0: FLASH PLAYER SETTINGS:
//
//
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif10" onclick="handleClick('to_col10','gif10')">
	Flash player
	<div id="to_col10" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content10");
$table10=1;
// Enable flash player ?
echo '<tr><td valign="top"><br>Enable flash player:</td>';
echo '<td><b>'.add_faq(73,'Click here for more information about settings for flash player',1).'.</b><br>';
echo add_checkbox('xspf_enabled',get_configuration("xspf_enabled")).'</td></tr>';
// Is flash the ONLY player on this system ?
echo '<tr><td valign="top">Exclusively use flash player to play music:</td>';
echo '<td>'.add_checkbox('xspf_only_player',get_configuration("xspf_only_player")).'</td></tr>';
echo '</table></div>';

	

//
//
// LAST.FM SECTION	
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif5" onclick="handleClick('to_col5','gif5')">
last.fm settings
<div id="to_col5" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content5");
$table5=1;
// Allow submission ?
echo '<tr><td valign="top"><br>Allow streamed tracks to be submitted:</td>';
echo '<td><b>'.add_faq(51,'Click here for more information about submission of streamed music to last.fm',1).'.</b><br>';
echo add_checkbox('lastfm_allow_submission',get_configuration("lastfm_allow_submission")).'</td></tr>';
// Allow local users ?
echo '<tr><td valign="top">Allow last.fm username/password in personal settings:</td>';
echo '<td>'.add_checkbox('lastfm_allow_local_users',get_configuration("lastfm_allow_local_users")).'</td></tr>';
// Default last.fm username & password:
echo '<tr><td valign="top">Default last.fm username:</td>';
echo '<td>'.add_textinput('lastfm_default_username',get_configuration("lastfm_default_username"),20).'</td></tr>';
echo '<tr><td valign="top">Default last.fm password:</td>';
echo '<td>'.add_textinput('lastfm_default_password',get_configuration("lastfm_default_password"),20).'</td></tr>';		
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
// 0.8.2: Allow personal setting "Suggest favorite lists based on tags":
echo '<tr><td valign="top">Allow personal setting "Suggest favorites based on tags":</td>';
echo '<td>'.add_checkbox('lastfm_allow_favorite_suggestion',get_configuration("lastfm_allow_favorite_suggestion"));
echo ' <b>'.add_faq(79,'Click here for more information',1).'</b>.</td></tr>';
// 0.8.2: Allow "Add tracks to favorite lists automatically":
echo '<tr><td valign="top">Allow personal setting "Add tracks to favorite lists automatically":</td>';
echo '<td>'.add_checkbox('lastfm_allow_auto_add2favorite',get_configuration("lastfm_allow_auto_add2favorite"));
echo ' <b>'.add_faq(80,'Click here for more information',1).'</b>.</td></tr>';
echo '</table></div>';


//
//
// NOW PLAYING section
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif6" onclick="handleClick('to_col6','gif6')">
"Now playing"
<div id="to_col6" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content6");
$table6=1;
// Allow disp. of "now playing" ?
echo '<tr><td valign="top"><br>Allow display of "Now playing":</td>';
echo '<td><b>'.add_faq(42,'Want to get this right ? The FAQ has detailed information about "Now playing"',1).'.</b><br>';
echo add_checkbox('allow_now_playing',get_configuration("allow_now_playing")).'</td></tr>';
// Display mini-cover ?
echo '<tr><td valign="top">Display album cover:</td>';
echo '<td>'.add_checkbox('now_playing_disp_cover',get_configuration("now_playing_disp_cover")).'</td></tr>';
// Reduce size of album images:
echo '<tr><td valign="top">Reduce size of album images to:</td>';
echo '<td>Width:'.add_textinput('now_playing_dimension_w',get_configuration("now_playing_dimension_w"),5);
echo 'Height:'.add_textinput('now_playing_dimension_h',get_configuration("now_playing_dimension_h"),5);
echo ' </td></tr>';
// Update/refresh rate:
echo '<tr><td valign="top">Update interval :</td>';
echo '<td>'.add_textinput('now_playing_update_rate',get_configuration("now_playing_update_rate"),5);
echo ' (Note: value is entered as <i>milliseconds</i>. 1000=1sec.)</td></tr>';	
// popout window:
echo '<tr><td valign="top">"Popout" window dimensions:</td>';
echo '<td>Width:'.add_textinput('popout_width',get_configuration("popout_width"),5);
echo 'Height:'.add_textinput('popout_height',get_configuration("popout_height"),5).'</td></tr>';
// Light update:
echo '<tr><td valign="top">Use "light update" on these mediaplayers :</td>';
echo '<td>'.add_textinput('np_light_update',get_configuration("np_light_update"),60);
echo ' (Note: Seperate each entry with an asterisk: *)</td></tr>';	
// Enable updates OR Display message when playing automatically:
echo '<tr><td valign="top">During Automatic play:</td>';
echo '<td valign="top">Continue updating "Now playing": ';
echo add_checkbox('np_update_automatic_play',get_configuration("np_update_automatic_play"));
echo ' <b>OR</b> display this message:';
echo add_textinput('np_light_update_msg',get_configuration("np_light_update_msg"),40).'</td></tr>';
echo '<tr><td colspan="3" align="center"><i>';
echo 'Note: If you change anything in "Now playing" (this section) you must also ';
echo 'select/play some music in order to have the changes applied.';
echo '</i></td></tr>';		
echo '</table></div>';


//
//
//	LAME / TRANSCODING SECTION
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif7" onclick="handleClick('to_col7','gif7')">
Transcoding/downsampling
<div id="to_col7" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content7");
$table7=1;
// Enabled ?
echo '<tr><td valign="top"><br>Enable transcoding/downsampling:</td>';
echo '<td><b>'.add_faq(48,'Click here for more information about downsampling/transcoding',1).'.</b><br>';
echo add_checkbox('lame_enabled',get_configuration("lame_enabled")).'</td></tr>';
// Path (to LAME):
echo '<tr><td valign="top">Absolute path to program (i.e. to lame):</td>';
echo '<td>'.add_textinput('lame_path',get_configuration("lame_path"),20).'</td></tr>';
// Downsample parameters, default:
echo '<tr><td valign="top">Default parameters :</td>';
echo '<td>'.add_textinput('lame_parameters',get_configuration("lame_parameters"),40).'</td></tr>';
// 0.8.2: Dynamic downsampling:
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td valign="top">Enable downsampling based on IP-addresses:</td>';
echo '<td>'.add_checkbox('lame_dynamic_enabled',get_configuration("lame_dynamic_enabled"));
echo ' for these IP-addresses/-ranges: '.add_textinput('lame_dynamic_iplist',get_configuration("lame_dynamic_iplist"),50).'</td></tr>';	
echo '</table></div>';


//
//
//	SPECIAL EXTENSIONS (M4A,MP4 ETC.)
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif8" onclick="handleClick('to_col8','gif8')">
Special extensions
<div id="to_col8" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content8");
$table8=1;
// Enabled ?
echo '<tr><td valign="top"><br>Enable handling of special extensions (m4a, mp4 etc.):</td>';
echo '<td><b>'.add_faq(54,'Click here for more information about special extensions (f.ex. m4a, mp4)',1).'.</b><br>';
echo add_checkbox('special_extensions_enabled',get_configuration("special_extensions_enabled")).'</td></tr>';
// Array of special extensions:
echo '<tr><td valign="top">Special extensions:</td>';
echo '<td>'.add_textinput('special_extensions',get_configuration("special_extensions"),20).'</td></tr>';
// Update now playing:
echo '<tr><td valign="top">Update "now playing":</td>';
echo '<td>'.add_checkbox('special_extensions_update_playing',get_configuration("special_extensions_update_playing")).'</td></tr>';
// Update statistics:
echo '<tr><td valign="top">Update statistics:</td>';
echo '<td>'.add_checkbox('special_extensions_update_statistics',get_configuration("special_extensions_update_statistics"));
echo '</td></tr></table></div>';	



//
//	
// 0.8.1: MICRO$OFT BING API
// 0.8.6: Abandoned Bing!
//
?>
<!--
0.8.6: Abandoned Bing!
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif11" onclick="handleClick('to_col11','gif11')">
Microsoft Bing API
<div id="to_col11" style="display:none;">
-->
<?php
/*
echo std_table("ampjuke_content_table","ampjuke_content11");
$table11=1;
// Bing AppId:
echo '<tr><td valign="top"><br>Bing AppId:</td>';	
echo '<td><b>'.add_faq(75,'Click here for more information about integration of the Microsoft Bing API',1).'.</b><br>'; 
echo add_textinput('bing_appid',get_configuration('bing_appid'),60).'</td></tr>';
// Preferred size:
echo '<tr><td>Default preferred size:</td>';	
echo '<td>'.add_textinput('bing_preferred_size',get_configuration('bing_preferred_size'),10).'</td></tr>';

echo '</table></div>';

*/

//
//	
// 0.8.4: USER REGISTRATION AND LOGIN OPTIONS
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif12" onclick="handleClick('to_col12','gif12')">
User registration and login options
<div id="to_col12" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content12");
$table12=1;
// Enabled?
echo '<tr><td valign="top"><br>Enable self-registration of users:</td>';	
echo '<td><b>'.add_faq(85,'Click here for more information about the options below',1).'.</b><br>'; 
echo add_checkbox('user_reg_enabled',get_configuration('user_reg_enabled')).'</td></tr>';
// Use this email address as sender:
echo '<tr><td>Use this email address as sender:</td><td>'.add_textinput('email_sender',get_configuration('email_sender'),60).'</td></tr>';
// Text to display:
echo '<tr><td>Display this text (f.ex. "Click here to register"):</td><td>';
echo add_textinput('user_reg_display_text',get_configuration('user_reg_display_text'),60).'</td></tr>';
// Username min/max chars:
echo '<tr><td>Username length:</td>';
echo '<td>Minimum:'.add_textinput('user_reg_username_min_length',get_configuration('user_reg_username_min_length'),3);
echo ' Maximum:'.add_textinput('user_reg_username_max_length',get_configuration('user_reg_username_max_length'),3);
echo 'characters</td></tr>';
// Passwd min/max chars:
echo '<tr><td>Password length:</td>';
echo '<td>Minimum:'.add_textinput('user_reg_password_min_length',get_configuration('user_reg_password_min_length'),3);
echo ' Maximum:'.add_textinput('user_reg_password_max_length',get_configuration('user_reg_password_max_length'),3);
echo 'characters</td></tr>';
// Enable sending e-mail with lost/forgotten passwords:
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td>Enable sending emails with lost/forgotten password:</td>';
echo '<td>'.add_checkbox('enable_email_with_lost_password',get_configuration('enable_email_with_lost_password'));
echo '</td></tr>';
echo '<tr><td>Display this text (f.ex. "Forgot your password ? Click here"):</td><td>';
echo add_textinput('enable_email_with_lost_password_text',get_configuration('enable_email_with_lost_password_text'),60);
echo '</td></tr>';
// Automatically delete inactive users ?
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td>Automatically delete users that have not logged in after:</td>';
echo '<td>'.add_textinput('delete_inactive_users',get_configuration('delete_inactive_users'),4).' days. ';
echo '(Note: If you set this value to 0 it will disable automatic deletion)';
echo '</td></tr>';
// 0.8.1: Hide "keep me signed in" on login page. 0.8.4: Moved here from "misc" section below.
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td valign="top">Hide "Keep me signed in" option on login page:</td>';
echo '<td>'.add_checkbox('login_hide_keep_me_signed_in',get_configuration("login_hide_keep_me_signed_in"));
//echo ' <b>'.add_faq(78,'Click here for more information',1).'</b>.';    
echo '</td></tr>';
// Allow anonymous users ? 0.8.4: Moved here from "misc" section below:
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
// Allow anonymous users (aka. guests) to use AmpJuke:
echo '<tr><td valign="top">Allow anonymous users:</td>';
echo '<td>'.add_checkbox('allow_anonymous',get_configuration("allow_anonymous"));
// Also allow anonymous streaming:
echo ' Also allow anonymous users to stream music: ';
echo add_checkbox('allow_anonymous_streaming',get_configuration("allow_anonymous_streaming")).'</td></tr>';

echo '</table></div>';


//
//	
// 0.8.5: ANIMATIONS
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif13" onclick="handleClick('to_col13','gif13')">
Visual effects
<div id="to_col13" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content13");
$table13=1;
// Enabled?
echo '<tr><td valign="top"><br>Enable visual effects for album and performer images:</td>';	
echo '<td><b>'.add_faq(86,'Click here for more information about animation possibilities',1).'.</b><br>'; 
echo add_checkbox('animation_enabled',get_configuration('animation_enabled')).'</td></tr>';
// Animation delay timing (might be a formula/calculation):
echo '<tr><td>Timing of delay:</td><td>'.add_textinput('animation_delay_timing',get_configuration('animation_delay_timing'),12).'</td></tr>';
// Animation opacity timing (might be a formula/calculation):
echo '<tr><td>Timing of opacity:</td><td>';
echo add_textinput('animation_opacity_timing',get_configuration('animation_opacity_timing'),12).'</td></tr>';

echo '</table></div>';


//
//	
// 0.8.6: Similar/related tracks aka. ECHONEST API
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif14" onclick="handleClick('to_col14','gif14')">
Similar/related tracks (Echonest API)
<div id="to_col14" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content14");
$table14=1;
// Enabled?
echo '<tr><td valign="top"><br>Enable calls to the Echonest API:</td>';	
echo '<td><b>'.add_faq(87,'Click here for more information about the Echonest API',1).'.</b><br>'; 
echo add_checkbox('echonest_enabled',get_configuration('echonest_enabled')).'</td></tr>';
// Echonest API key:
echo '<tr><td>Echonest API key:</td><td>'.add_textinput('echonest_api_key',get_configuration('echonest_api_key'),25).'</td></tr>';
// Echonest API URL:
echo '<tr><td>Echonest API "base" URL:</td>';
echo '<td>'.add_textinput('echonest_api_url',get_configuration('echonest_api_url'),50).'</td></tr>';
// Max results to return:
echo '<tr><td>Maximum number of results returned by API-calls:</td>';
echo '<td>'.add_textinput('echonest_max_results',get_configuration('echonest_max_results'),5).'</td></tr>';
// Max. diff. duration:
echo '<tr><td>Maximum difference in duration:</td>';
echo '<td>'.add_textinput('echonest_max_diff_duration',get_configuration('echonest_max_diff_duration'),5).' seconds</td></tr>';
// Mark tracks not identified ?
echo '<tr><td>Mark tracks not identified:</td>';
echo '<td>'.add_checkbox('echonest_queue_tracks',get_configuration('echonest_queue_tracks')).'</td></tr>';
// 0.8.7: Use echonest parameters in advanced search ?
echo '<tr><td>Use echonest parameters in advanced search:</td>';
echo '<td>'.add_checkbox('echonest_advanced_search',get_configuration('echonest_advanced_search')).'</td></tr>';


echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td colspan="5" align="center">Adjust track selection parameters (higher value in parameter=higher priority; a value of 0 ignores the parameter")</td></tr>';
// Limit:
echo '<tr><td>Limit factor:</td>';
echo '<td>'.add_textinput('echonest_limit',get_configuration('echonest_limit'),5).'</td></tr>';
// Priority of echonest_tempo:
echo '<tr><td>BPM/tempo priority:</td>';
echo '<td>'.add_textinput('echonest_tempo_priority',get_configuration('echonest_tempo_priority'),2).' (Integer only, 0-x)';
echo ' BPM/tempo factor: '.add_textinput('echonest_tempo_factor',get_configuration('echonest_tempo_factor'),4).' (will be multiplied with "Limit factor")';
echo '</td></tr>';
// Priority of echonest_danceability:
echo '<tr><td>Danceability priority:</td>';
echo '<td>'.add_textinput('echonest_danceability_priority',get_configuration('echonest_danceability_priority'),2).' (Integer only, 0-x)</td></tr>';
// Priority of echonest_energy:
echo '<tr><td>Energy priority:</td>';
echo '<td>'.add_textinput('echonest_energy_priority',get_configuration('echonest_energy_priority'),2).' (Integer only, 0-x)</td></tr>';
// Priority of echonest_key:
echo '<tr><td>Key priority:</td>';
echo '<td>'.add_textinput('echonest_key_priority',get_configuration('echonest_key_priority'),2).' (Integer only, 0-x)';
echo ' Key factor: '.add_textinput('echonest_key_factor',get_configuration('echonest_key_factor'),4).' (will be added/subtracted to/from the "key" from previously played track)';
echo '</td></tr>';
// Priority of echonest_loudness:
echo '<tr><td>Loudness priority:</td>';
echo '<td>'.add_textinput('echonest_loudness_priority',get_configuration('echonest_loudness_priority'),2).' (Integer only, 0-x)</td></tr>';
// 0.8.8: ******************* NEW/ADDITIONAL ECHONEST PARAMETERS:
// Priority of echonest_liveness:
echo '<tr><td>Liveness priority:</td>';
echo '<td>'.add_textinput('echonest_liveness_priority',get_configuration('echonest_liveness_priority'),2).' (Integer only, 0-x)</td></tr>';
// Priority of echonest_speechiness:
echo '<tr><td>Speechiness priority:</td>';
echo '<td>'.add_textinput('echonest_speechiness_priority',get_configuration('echonest_speechiness_priority'),2).' (Integer only, 0-x)</td></tr>';
// Priority of echonest_acousticness:
echo '<tr><td>Acousticness priority:</td>';
echo '<td>'.add_textinput('echonest_acousticness_priority',get_configuration('echonest_acousticness_priority'),2).' (Integer only, 0-x)</td></tr>';
// Priority of echonest_valence:
echo '<tr><td>Valence priority:</td>';
echo '<td>'.add_textinput('echonest_valence_priority',get_configuration('echonest_valence_priority'),2).' (Integer only, 0-x)</td></tr>';
// 0.8.8: ******************* ...end
echo '</table></div>';

//
//	
// 0.8.6: Jukebox configuration
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif15" onclick="handleClick('to_col15','gif15')">
Jukebox configuration
<div id="to_col15" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content15");
$table15=1;
// Enabled?
echo '<tr><td valign="top"><br>Enable jukebox mode:</td>';	
echo '<td><b>'.add_faq(93,'Click here for more information about running AmpJuke in jukebox mode',1).'.</b><br>'; 
echo add_checkbox('jukebox_mode_enabled',get_configuration('jukebox_mode_enabled')).'</td></tr>';
// Msg. for the "welcome" page:
echo '<tr><td valign="top">Display this message on the "Welcome" page:</td>';
echo '<td>'.add_textinput('jukebox_mode_welcome_msg',get_configuration('jukebox_mode_welcome_msg'),80);
// Link for the "welcome" page:
echo '<tr><td valign="top">Use this URL for the message above (on the "Welcome" page):</td>';
echo '<td>'.add_textinput('jukebox_mode_welcome_link',get_configuration('jukebox_mode_welcome_link'),80);
// User-name:
echo '<tr><td valign="top">Jukebox user name:</td>';
echo '<td>'.add_textinput('jukebox_mode_user',get_configuration('jukebox_mode_user'),10);
echo ' (some settings from this user will be used in the jukebox mode)</td></tr>';
// How do we determine who's selecting tracks ?
echo '<tr><td valign="top">What determines the <b>identity</b> of who requests a track:</td>';
$ch1='';
$ch2='';
$def=get_configuration('jukebox_mode_selection_identity');
if ((!isset($def)) || (($def<>'IP-address') && ($def<>'Username'))) {
    $def='IP-address';
    $ch1='1';
}
if ($def=='IP-address') {
    $ch1='1';
}
echo '<td>';
echo 'IP-address:'.add_radio('jukebox_mode_selection_identity','IP-address',$ch1);
if ($def=='Username') {
    $ch2='1';
}
echo '<br>Username:'.add_radio('jukebox_mode_selection_identity','Username',$ch2);
echo '</td></tr>';
// Probability of streaming a request:
echo '<tr><td>Probability of streaming a request (in pct.: 0-100):</td>';
echo '<td>'.add_textinput('jukebox_mode_request_probability',get_configuration('jukebox_mode_request_probability'),2);
echo '% (lower value = more "laziness")</td></tr>';
// Serve requests on a first-come-first-serve or random basis:
echo '<tr><td valign="top">Requests will be picked as:</td>';
$ch1='';
$ch2='';
$def=get_configuration('jukebox_mode_request_pick');
if ((!isset($def)) || (($def<>'First-come-first-serve') && ($def<>'Random'))) {
    $def='First-come-first-serve';
    $ch1='1';
}
if ($def=='First-come-first-serve') {
    $ch1='1';
}
if ($def=='Random') {
    $ch2='1';
}
echo '<td>First-come-first-serve:'.add_radio('jukebox_mode_request_pick','First-come-first-serve',$ch1);
echo '<br>Random:'.add_radio('jukebox_mode_request_pick','Random',$ch2);
echo '</td></tr>';
// Limitations:
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td colspan="5" align="center">Limitations:</td></tr>';
// Limit: Max. number of tracks a "user" (IP or real user) can select/wish:
echo '<tr><td valign="top">Maximum number of tracks that can be requested (for each user):</td>';
echo '<td>'.add_textinput('jukebox_mode_selection_limit_tracks',get_configuration('jukebox_mode_selection_limit_tracks'),2);
echo ' every '.add_textinput('jukebox_mode_selection_limit_minutes',get_configuration('jukebox_mode_selection_limit_minutes'),3);
echo ' minutes</td></tr>';
// 0.8.8: Limit: Max. number of requests, total:
echo '<tr><td valign="top">Maximum number of outstanding tracks (for all users):</td>';
echo '<td>'.add_textinput('jukebox_mode_selection_limit_tracks_total',get_configuration('jukebox_mode_selection_limit_tracks_total'),2);
// Limit: Minimum age (since last played), TRACKS:
echo '<tr><td valign="top">Minimum "age" (pr. track) since last streamed:</td>';
echo '<td>'.add_textinput('jukebox_mode_min_age',get_configuration('jukebox_mode_min_age'),2);
echo ' hours</td></tr>';
// 0.8.8: Limit: Minimum age (since last played), PERFORMERS:
echo '<tr><td valign="top">Minimum "age" (pr. performer/artist) since last streamed:</td>';
echo '<td>'.add_textinput('jukebox_mode_min_age_performer',get_configuration('jukebox_mode_min_age_performer'),2);
echo ' hours</td></tr>';
// ********* Messages:
echo '<tr bgcolor="#abcdef"><td colspan="5" align="center"></td></tr>';
echo '<tr><td colspan="5" align="center">Messages (use %t, %p, %a, %y, %limit_tracks, %limit_all, %limit_minutes, %min_performer_age and %min_age below):</td></tr>';
// Pop-up enabled:
echo '<tr><td>Show messages in a pop-up window:</td>';
echo '<td>'.add_checkbox('jukebox_mode_msg_popup_enabled',get_configuration('jukebox_mode_msg_popup_enabled'));
echo '</td></tr>';
// Msg. when added successfully:
echo '<tr><td>Display this message when adding new track (SUCCESS):</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_add_success',get_configuration('jukebox_mode_msg_add_success'),80);
echo '</td></tr>';
// Msg. when limit of tracks reached:
echo '<tr><td>Display this message when limit of tracks is reached:</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_fail_limit_tracks',get_configuration('jukebox_mode_msg_fail_limit_tracks'),80);
echo '</td></tr>';
// Msg. when limit of outstanding (=not played) tracks reached:
echo '<tr><td>Display this message when limit of outstanding (not played) tracks is reached (for each user):</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_fail_outstanding_tracks',get_configuration('jukebox_mode_msg_fail_outstanding_tracks'),80);
echo '</td></tr>';
// 0.8.8: Msg. when limit of outstanding (=not played) tracks reached, total:
echo '<tr><td>Display this message when limit of outstanding (not played) tracks is reached (for all users):</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_fail_outstanding_tracks_all',get_configuration('jukebox_mode_msg_fail_outstanding_tracks_all'),80);
echo '</td></tr>';
// Msg. when trying to add something that was played recently - TRACKS:
echo '<tr><td>Display this message when adding a track that was played "recently":</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_fail_age',get_configuration('jukebox_mode_msg_fail_age'),80);
echo '</td></tr>';
// Msg. when trying to add something that was played recently - PERFORMERS:
echo '<tr><td>Display this message when adding an artist/performer that was played "recently":</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_fail_age_performer',get_configuration('jukebox_mode_msg_fail_age_performer'),80);
echo '</td></tr>';
// Msg. when trying to add something that's already requested:
echo '<tr><td>Display this message when adding a track that is already in the queue:</td>';
echo '<td>'.add_textinput('jukebox_mode_msg_fail_already_requested',get_configuration('jukebox_mode_msg_fail_already_requested'),80);
echo '</td></tr>';

echo '</table></div>';


//
//	
// 0.8.6: Screensaver configuration
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif16" onclick="handleClick('to_col16','gif16')">
Screensaver
<div id="to_col16" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content16");
$table16=1;
// Enabled?
echo '<tr><td valign="top"><br>Enable screensaver:</td>';	
echo '<td><b>'.add_faq(98,'Click here for more information about the screensaver',1).'.</b><br>'; 
echo add_checkbox('screensaver_enabled',get_configuration('screensaver_enabled')).'</td></tr>';
// Secs. to wait before activating the screensaver:
echo '<tr><td valign="top">Number of seconds to wait before activating:</td>';
echo '<td>'.add_textinput('screensaver_start_time',get_configuration('screensaver_start_time'),5);
echo '</td></tr>';
// Secs. between reloading screensaver images:
echo '<tr><td valign="top">Number of seconds between loading new images:</td>';
echo '<td>'.add_textinput('screensaver_reload_time',get_configuration('screensaver_reload_time'),5);
echo '</td></tr>';
// Use images from:
echo '<tr><td valign="top">Pick screensaver images from:</td>';
echo '<td><SELECT NAME="screensaver_images">';
$img=get_configuration('screensaver_images');
echo '<OPTION VALUE="Albums"';
if ($img=='Albums') { echo ' selected'; }
echo '>Albums</OPTION>';
echo '<OPTION VALUE="Performers"';
if ($img=='Performers') { echo ' selected'; }
echo '>Performers</OPTION>';
echo '<OPTION VALUE="Random"';
if ($img=='Random') { echo ' selected'; }
echo '>Random (artists or performers)</OPTION>';
echo '</SELECT></td></tr>';
// Preferred size (pixels):
echo '<tr><td valign="top">Preferred size:</td>';
echo '<td>'.add_textinput('screensaver_preferred_size',get_configuration('screensaver_preferred_size'),5);
echo ' pixels (x and y)';
echo '</td></tr>';
// Number of iterations:
echo '<tr><td valign="top">Number of iterations:</td>';
echo '<td>'.add_textinput('screensaver_iterations',get_configuration('screensaver_iterations'),5);
echo '</td></tr>';
// Delay factor:
echo '<tr><td valign="top">Delay factor:</td>';
echo '<td>'.add_textinput('screensaver_ms_delay_factor',get_configuration('screensaver_ms_delay_factor'),8);
echo '</td></tr>';
// Fade factor:
echo '<tr><td valign="top">"Fade" factor:</td>';
echo '<td>'.add_textinput('screensaver_ms_fade_factor',get_configuration('screensaver_ms_fade_factor'),8);
echo '</td></tr>';


echo '</table></div>';
//
//	
// MISCELLANEOUS SECTION
//
//
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif9" onclick="handleClick('to_col9','gif9')">
Miscellaneous options
<div id="to_col9" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content9");
$table9=1;
// date/time format:
echo '<tr><td valign="top">Date/time format to display:</td>';
echo '<td>'.add_textinput('dateformat',get_configuration("dateformat"),20);
echo 'If set to <b>Y-m-d H:i:s</b>, will display something like: 2012-05-20 20:25:55. ';
echo 'Visit <a href="http://www.php.net/manual/en/function.date.php" target="_blank">';
echo 'the PHP manual</a> for other examples.</td></tr>';
// 0.8.0: Download+display covers from last.fm ?
echo '<tr><td valign="top">Download & display album covers:</td>';
echo '<td>'.add_checkbox('lastfm_download_covers',get_configuration('lastfm_download_covers'));
// 0.7.8: Add reflections ?
/* 0.8.7: Gone!
echo ' Also add reflections: '.add_checkbox('add_reflections',get_configuration("add_reflections"));
*/
echo ' <b>'.add_faq(74,'Click here for more information about this setting',1).'</b>.';    
echo '</td></tr>';
// Array of forbidden characters:
echo '<tr><td valign="top">"Forbidden" characters (stream/download):</td>';
echo '<td>'.add_textinput('forbidden_characters',get_configuration("forbidden_characters"),25);
echo ' <b>'.add_faq(43,'Click here for more information about "forbidden" characters',1).'</b>.';    
echo '</td></tr>';
// 0.7.7: Enable display of bio's:
echo '<tr><td valign="top">Retrieve and display biographies:</td>';
echo '<td>'.add_checkbox('perf_info',get_configuration("perf_info"));
echo ' <b>'.add_faq(57,' Click here for more information about biographies',1).'</b>.';
echo '</td></tr>';
// 0.7.7: Enable lyrics + path:
echo '<tr><td valign="top">Enable lyrics:</td>';
echo '<td>'.add_checkbox('lyrics_enabled',get_configuration("lyrics_enabled"));
echo ' Path: ';
echo add_textinput('lyrics_path',get_configuration("lyrics_path",'1'),60);
echo '<b>'.add_faq(56,'Visit the FAQ for examples',1).'</b>.</td></tr>';
// 0.7.7: Ban IP's w. too many login attempts:
echo '<tr><td valign="top">Enable banning of IP-addresses:</td>';
echo '<td>'.add_checkbox('max_failed_login_enabled',get_configuration("max_failed_login_enabled"));
echo ' Maximum number of attempts:';
echo add_textinput('max_failed_login_attempts',get_configuration('max_failed_login_attempts'),3);
echo ' <b>'.add_faq(58,'Click here for more information',1).'</b>.';    
echo '</td></tr>';	
// 0.8.3: Allow shared favorites:
echo '<tr><td valign="top">Allow shared favorites:</td>';
echo '<td>'.add_checkbox('shared_favorites_allow',get_configuration('shared_favorites_allow'));
echo ' <b>'.add_faq(83,'Click here for more information',1).'</b></td></tr>';
echo '</table></div>';

echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" rules="none">';
echo '<tr><td colspan="5" align="center">';
echo '<input type="submit" value="Save & continue" class="tfield">';
echo '</td></tr>';
echo '<tr><td colspan="5"><a href="./">Do not save anything. Go back to the "welcome" page</a></td></tr>';

echo '</FORM>';
?>
<script type="text/javascript">
addTableRolloverEffect('ampjuke_content','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content2','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content3','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content4','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content5','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content6','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content7','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content8','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content9','tableRollOverEffect',''); 
addTableRolloverEffect('ampjuke_content10','tableRollOverEffect',''); 
addTableRolloverEffect('ampjuke_content11','tableRollOverEffect',''); 
addTableRolloverEffect('ampjuke_content12','tableRollOverEffect',''); 
addTableRolloverEffect('ampjuke_content13','tableRollOverEffect','');
addTableRolloverEffect('ampjuke_content14','tableRollOverEffect','');  
addTableRolloverEffect('ampjuke_content15','tableRollOverEffect','');  
addTableRolloverEffect('ampjuke_content16','tableRollOverEffect','');  
</script>
</table>
</table>
