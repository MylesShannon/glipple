<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>AmpJuke - Installation/Upgrade</title>
<meta name="generator" content="PHP Designer 2007">
<link rel="stylesheet" type="text/css" href="./ampstyles.css">
</head>

<body><table border="1" cellspacing="0" cellpadding="0" rules="none">
<tr><td align="center"><img src="./ampjukeicons/ampjuke_welcome.gif" border="0">
</td></tr>
<tr><td>
<?php
die('Sorry...');
/* 0.7.7: NOT USED ANYMORE.
This is the old installation procedure.
the new installation/upgrade procedure is found in setup.php (or setup_backup.php).
// Michael.
*/
require("configuration.php");


function inst_error($dir,$entity) { // prints out errors about various directories:
	echo '<font color="red"><b>Error:</b> Cannot write to <b>'.$dir.'</b> and/or the '.$entity.' does not exist.<br>';
	echo 'You should either create the '.$entity.' within <b>'.getcwd().'</b><br>';
	echo 'or - if the '.$entity.' is already there - change the permissions to 777 (rwxrwxrwx).<br>';
	echo 'Please adjust, then rerun install.php.<font color="black">';
	exit;
}

function get_and_write($oh,$item,$default) {
	$val=get_configuration($item);
	if ($val=="") {
		$item='$'.$item.'="'.$default.'";';
		fwrite($oh,$item . chr(13) . chr(10));
		echo "Writing NEW configuration: <b>".$item.'</b><br>';
	} else {
		$item='$'.$item.'="'.$val.'";';
		fwrite($oh,$item . chr(13) . chr(10));
		echo "Writing EXISTING configuration: <b>".$item.'</b><br>';
	}	
}		

parse_str($_SERVER["QUERY_STRING"]);
clearstatcache();

// 0.6.4: coming from the right page ? If not, intruct to make checks in relation to permissions:
if (!isset($checks)) { 
	echo '<b>Warning</b>: Did you go directly to this page to do an installation ?<br>';
	echo 'Please check permissions on various files/folders first...<br><br>';
	echo '<a href="./">Click here to continue</a>';
	die();
}	

if (!isset($act)) { // we assume it's the first time we're running the script.
// i.e.: present some OPTIONS.
// First, check stuff:
	$ok=1;
	echo '<hr width="80%" color="#abcdef"><b>--- Checks ---</b><br><br>';
	if (is_writable('./tmp/')) {
		echo '"tmp". Ok: Can write to "tmp" !<br><br>';
	} else {
		$ok=0;
		inst_error('tmp','directory');
	}
	echo '</td></tr><tr><td>';	
	if (is_writable('./covers/')) {
		echo '"covers". Ok: Can write to "covers" !<br><br>';
	} else {
		$ok=0;
		inst_error('covers','directory');
	}	
	echo '</td></tr><tr><td>';	
	if (is_writable('./lastfm/')) {
		echo '"lastfm". Ok: Can write to "lastfm" !<br><br>';
	} else {
		$ok=0;
		inst_error('lastfm','directory');
	}	
	echo '</td></tr><tr><td>';		
// Second, if we made it so far, the user must choose what to do:		
	if ($ok!=0) { 
		echo '<hr width="80%" color="#abcdef"><br>';
    	echo '<b>--- Options ---</b><br><br>';
    	echo '<a href="install.php?act=install"><b>Install</b> AmpJuke from <b>scratch</b>.</a><br><br>';
	    echo '<a href="install.php?act=upgrade"><b>Upgrade</b> AmpJuke.</a><br>';
    	// 0.6.1: Warn about upgrading from somethin thats too old:
    	echo '<b>Important:</b> If youre <i>not</i> running AmpJuke version <i>0.5.5</i> or above ';
		echo 'then you should <i>install</i> - NOT upgrade.';    
		echo '<br><br>';
	}    
} // act not set...



if (isset($act) && ($act=="install")) { // install from scratch:
    // check that we have what we need:
    if (!file_exists('./db_dist.php')) {
        echo 'The file <b>db_dist.php</b> does not seem to exist and/or is renamed.<br>';
        echo 'Please fix it, then run install.php again.<br>';
        echo 'Tip: You can copy the file <b>db.php</b> to <b>db_dist.php</b>...';
        die();
    }
    if (file_exists('./db.php')) {
        if (!is_writable('./db.php')) {
            echo 'The file <b>db.php</b> exists, but cannot be written to.<br>';
            echo 'Please fix it (f.ex. using the command: CHMOD 777 db.php) then run install.php again.<br>';
            die();
        }
    }
    if (!file_exists('./db_new.sql')) {
        echo 'The file <b>db_new.sql</b> does not seem to exist and/or is renamed.<br>';
        echo 'Please fix it, then run install.php again.<br>';
        die();
    }
	// checks ends...now DO something:

    $in_handle=fopen("db_dist.php", "r");
    $out_handle=fopen("db.php", "w");
    while (!feof($in_handle)) {
        $line=fgets($in_handle);
        fwrite($out_handle, $line);
    }
    // 0.4.4: Write current value of HTTP_HOST and any sub-dir.
	// in order to find out where the hell we are in the directory-structure:
    $line='$'.'base_http_prog_dir="http://'.$_SERVER["HTTP_HOST"];
    $path=substr($_SERVER["SCRIPT_NAME"],0,strlen($_SERVER["SCRIPT_NAME"])-12);
	$line.=$path.'";'.chr(13).chr(10);
	$line.='?>';
    fwrite($out_handle, $line);
    //
    fclose($in_handle);
	fclose($out_handle);
    
    echo '<b>Ok !</b><br>Now click on the link below to proceed with the installation.<br>';
    echo '<a href="scan.php?act=configure"><b>Click here to continue</b></a>.';
    // 0.4.3: Offer link to detailed installation example:
    echo '<br><br><br>Nervous ? Afraid something might go wrong ? Need an example ?<br>';
    echo '<a href="http://www.ampjuke.org/install_ex.php" target="_blank">';
    echo '<b>Click here for a step-by-step (incl. screen dumps) guide on how to install AmpJuke</b></a>';
    echo '.<br>The guide will open in a new window.<br>';
} // act=install

if (isset($act) && ($act=="upgrade")) { // UPGRADE from a previous version, as follows:
	// check we can use db.php & it exists:
    if (file_exists('./db.php')) {
        if (!is_writable('./db.php')) {
            echo 'The file <b>db.php</b> exists, but cannot be written to.<br>';
            echo 'Please fix it (f.ex. using the command: CHMOD 777 db.php) then <a href="./">try again</a>.<br>';
            die();
        }
    }
	if (!file_exists('./db.php')) {
		echo 'The file <b>db.php</b> does not seem to exist.<br>';
		echo 'Cannot upgrade.<br>';
		die();
	}			
	// ok. let's upgrade: We will start off with what MUST exist from previous versions:
	$out_handle=fopen("db.new", "w");
	fwrite($out_handle,"<?php" . chr(13) . chr(10));
	$d=get_configuration("db_host");
	get_and_write($out_handle,"db_host",$d);
	
	$d=get_configuration("db_user");
	get_and_write($out_handle,"db_user",$d);
	
	$d=get_configuration("db_password");
	get_and_write($out_handle,"db_password",$d);
	
	$d=get_configuration("db_name");
	get_and_write($out_handle,"db_name",$d);

	// 0.7.0: table prefix:
	$d=get_configuration("ampjuke_tbl_prefix");
	if ($d<>"") {
		get_and_write($out_handle,"ampjuke_tbl_prefix",$d);
	}	

	$d=get_configuration("base_music_dir");
	get_and_write($out_handle,"base_music_dir",$d);
	
	$d=get_configuration("base_http_prog_dir");
	get_and_write($out_handle,"base_http_prog_dir",$d);
		
	$d=get_configuration("dateformat");
	get_and_write($out_handle,"dateformat",$d);	

	$d=get_configuration("amazon_key");
	get_and_write($out_handle,"amazon_key",$d);
	
	// 0.3.7: Anonymous access:
	$d=get_configuration("allow_anonymous");
	if ($d=="") { $d=0; }
	get_and_write($out_handle,"allow_anonymous",$d);

	// 0.6.4: Allow anonymous streaming:
	$d=get_configuration("allow_anonymous_streaming");
	if ($d=="") { $d="0"; }
	get_and_write($out_handle,"allow_anonymous_streaming",$d);

	// 0.5.1: Two new settings:
	$d=get_configuration("perf_info");
	get_and_write($out_handle,"perf_info",$d);
	$d=get_configuration("perf_info_link",$d);
	get_and_write($out_handle,"perf_info_link",$d);

	// 0.6.4: All the other "missing" upgradeable entries in db.php:
	$d=get_configuration("keep_extension");
	if ($d=="") { $d="1"; }
	get_and_write($out_handle,"keep_extension",$d);

	$d=get_configuration("compress_command");
	if ($d=="") { $d="/bin/tar -cf"; }
	get_and_write($out_handle,"compress_command",$d);

	$d=get_configuration("dont_compress_one_file");
	if ($d=="") { $d="1"; }
	get_and_write($out_handle,"dont_compress_one_file",$d);

	$d=get_configuration("allow_upload");
	if ($d=="") { $d="1"; }
	get_and_write($out_handle,"allow_upload",$d);

	$d=get_configuration("max_upload_files");
	if ($d=="") { $d="15"; }
	get_and_write($out_handle,"max_upload_files",$d);

	$d=get_configuration("upload_chmod");
	if ($d=="") { $d="777"; }
	get_and_write($out_handle,"upload_chmod",$d);
	
	$d=get_configuration("lastfm_allow_related");
	if ($d=="")  { $d="1"; }
	get_and_write($out_handle,"lastfm_allow_related",$d);

	$d=get_configuration("lastfm_max_related_artists");
	if ($d=="") { $d="10"; }
	get_and_write($out_handle,"lastfm_max_related_artists",$d);

	$d=get_configuration("lastfm_min_related_match");
	if ($d=="") { $d="50"; }
	get_and_write($out_handle,"lastfm_min_related_match",$d);

	$d=get_configuration("lastfm_cache_days");
	if ($d=="") { $d="30"; }
	get_and_write($out_handle,"lastfm_cache_days",$d);
	
	$d=get_configuration("allow_now_playing");
	if ($d=="") { $d=1; }
	get_and_write($out_handle,"allow_now_playing",$d);
	
	$d=get_configuration("now_playing_disp_cover");
	if ($d=="") { $d=1; }
	get_and_write($out_handle,"now_playing_disp_cover",$d);

	$d=get_configuration("now_playing_dimension_w");
	if ($d=="") { $d="100px"; }
	get_and_write($out_handle,"now_playing_dimension_w",$d);

	$d=get_configuration("now_playing_dimension_h");
	if ($d=="") { $d="100px"; }
	get_and_write($out_handle,"now_playing_dimension_h",$d);

	$d=get_configuration("now_playing_update_rate");
	if ($d=="") { $d=12000; }
	get_and_write($out_handle,"now_playing_update_rate",$d);

	$d=get_configuration("popout_width");
	if ($d=="") { $d=150; }
	get_and_write($out_handle,"popout_width",$d);

	$d=get_configuration("popout_height");
	if ($d=="")  { $d=150; }
	get_and_write($out_handle,"popout_height",$d);

	$d=get_configuration("forbidden_characters");
	get_and_write($out_handle,"forbidden_characters",$d);

	// 0.7.0: Heres the new stuff (LAME / transcoding):
	$d=get_configuration("lame_enabled");
	if ($d=="") { $d="0"; }
	get_and_write($out_handle,"lame_enabled",$d);
	
	$d=get_configuration("lame_path");
	if ($d=="") { $d="/usr/bin/lame"; }
	get_and_write($out_handle,"lame_path",$d);
	
	$d=get_configuration("lame_parameters");
	if ($d=="") { $d="--silent --nohist -b 96 -q 7"; }
	get_and_write($out_handle,"lame_parameters",$d);	

	// 0.7.7: NEW: Bin IP settings:
	$d=get_configuration("max_failed_login_enabled");
	if ($d=="") { $d="0"; }
	get_and_write($out_handle,"max_failed_login_enabled",$d);
	$d=get_configuration("max_failed_login_attempts",$d);
	if ($d=="") { $d="7"; }
	get_and_write($out_handle,"max_failed_login_attempts",$d);
	
	// 0.7.7: NEW: Lyrics configuration:
	$d=get_configuration("lyrics_enabled");
	if ($d=="") { $d="1"; }
	get_and_write($out_handle,"lyrics_enabled",$d);
	$d=get_configuration("lyrics_path");
	if ($d=="") { $d="http://lyricwiki.org/%PERFORMER%:%TRACK%"; }
	get_and_write($out_handle,"lyrics_path",$d);
	
	fwrite($out_handle,"?");
	fwrite($out_handle,">" . chr(13) . chr(10));
	fclose($out_handle);
		
	// Final step in upgrading: renaming db_new.sql to db_new.php
	// and renaming db.new to db.php.
	// This is done using EXEC's, since I have observed a lot of "buggy" features with rename():
	echo '<br><br><br><p class="note"><b>Notes:</b><br>';
	exec("mv db_new.sql db_new.php");
	echo 'The file db_new.sql was renamed to <b>db_new.php</b>.<br>';
	echo 'You should move the file outside the doc-root, but keep it in a safe place, in case you want to<br>';
	echo 're-install everything later.<br><br>';
	echo 'You should also consider to move the file <b>install.php</b> outside the doc-root.<br><br>';
	exec("rm -f db.php");
	exec("mv db.new db.php");
	include_once("sql.php"); // we just want that...

/* Commented out. Assume everyone is running AT LEAST 0.5.5:	
	// 0.5.0: User-table upgrade:
	$qry="ALTER TABLE user ADD autoplay_last CHAR( 1 ) NULL DEFAULT '0', ";
	$qry.="ADD autoplay_last_list VARCHAR( 80 );";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.5.0)...<br>';

	// 0.5.0: Introduce fav-table:
	$qry="CREATE TABLE IF NOT EXIST fav (
  id int(11) NOT NULL auto_increment,
  track_id int(11) NOT NULL,
  performer_id int(11) default NULL,
  album_id int(11) default NULL,
  name text NOT NULL,
  duration varchar(6) default NULL,
  last_played varchar(20) default NULL,
  times_played int(11) NOT NULL default '0',
  year varchar(4) default NULL,
  user_id int(11) NOT NULL,
  fav_name varchar(80) NOT NULL,
  PRIMARY KEY  (`id`)
)";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'FAV-table created (0.5.0)...<br>';

	// 0.5.5: User-table upgrade:
	$qry="ALTER TABLE user ADD ask4favoritelist CHAR( 1 ) NULL DEFAULT '0';";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.5.5)...<br>';
*/

	// 0.6.1: Upgrade user-table:
	$qry="ALTER TABLE user ADD can_upload CHAR( 1 ) NULL DEFAULT '0' AFTER can_download ;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.6.1)...<br>';
	
	// 0.6.3: Upgrade user-table:
	$qry="ALTER TABLE user ADD welcome_num_items SMALLINT NOT NULL DEFAULT '10', ";
	$qry.="ADD welcome_content_1 VARCHAR( 80 ) NULL DEFAULT 'Recently played tracks' , ";
	$qry.="ADD welcome_content_2 VARCHAR( 80 ) NULL , ";
	$qry.="ADD welcome_content_3 VARCHAR( 80 ) NULL ; ";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.6.3)...<br>';

	// 0.6.4: Upgrade user-table:
	$qry="ALTER TABLE user ADD disp_now_playing CHAR( 1 ) NULL DEFAULT '0' AFTER ask4favoritelist;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.6.4)...<br>';

	// 0.6.5: Upgrade user-table:
	// 0.6.6: Not needed anymore, but kepy "open" to avoid confusion below:
	$qry="ALTER TABLE user ADD update_now_playing_delay CHAR( 1 ) NULL DEFAULT '0' AFTER disp_now_playing;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.6.5)...<br>';
	
	// 0.6.5: Upgrade user-table:
	$qry="ALTER TABLE user ADD disp_help CHAR( 1 ) NULL DEFAULT '0' AFTER update_now_playing_delay;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.6.5)...<br>';

	// 0.6.6: Upgrade user-table:
	$qry="ALTER TABLE user ADD avoid_duplicate_entries CHAR( 1 ) NULL DEFAULT '1' AFTER update_now_playing_delay;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.6.6)...<br>';

	// 0.7.0: Upgrade user-table:
	$qry="ALTER TABLE user ADD lame_local_enabled CHAR( 1 ) NULL DEFAULT '1' AFTER welcome_content_3;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD lame_local_parameters VARCHAR( 100 ) NULL ;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD icon_dir VARCHAR( 80 ) NULL AFTER cssfile;";
	$result=execute_sql($qry,0,-1,$dummy);	
	echo 'USER-table upgraded (0.7.0)...<br>';
	
	// 0.7.2: Upgrade user-table:
	$qry="ALTER TABLE user ADD lastfm_active CHAR( 1 ) NULL DEFAULT '0' AFTER lame_local_parameters;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD lastfm_username VARCHAR( 80 ) NULL AFTER lastfm_active;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD lastfm_password VARCHAR( 80 ) NULL AFTER lastfm_username;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.7.2)...<br>';

	// 0.7.3: Upgrade user-table:
	$qry="ALTER TABLE user ADD disp_small_images CHAR( 1 ) NULL DEFAULT '1' AFTER disp_fav_shares;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.7.3)...<br>';

	// 0.7.4: Upgrade user-table:
	$qry="ALTER TABLE user ADD disp_upload CHAR( 1 ) NULL DEFAULT '0' AFTER disp_download;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD password_salt VARCHAR( 40 ) NOT NULL DEFAULT '0' AFTER `password`;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.7.4)...<br>';

	// 0.7.7: Upgrade performer-table:
	$qry="ALTER TABLE `performer` ADD `bio_short` TEXT NULL , ADD `bio_long` TEXT NULL ;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'PERFORMER-table upgraded (0.7.7)...<br>';
	
	// 0.7.7: Upgrade album-table:
	$qry="ALTER TABLE `album` ADD `bio_short` TEXT NULL , ADD `bio_long` TEXT NULL ;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'ALBUM-table upgraded (0.7.7)...<br>';
	
    echo '<br><b>Upgrade OK</b>!<br>Now click on the link below to login & enjoy some music !<br>';
    echo 'But please do not forget the two security issues stated above.<br>';
    echo '<a href="login.php">Click here to login</a>';
}	

?>
</td></tr></table></body></html>
