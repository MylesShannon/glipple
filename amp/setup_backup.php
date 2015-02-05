<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>AmpJuke - Installation/Upgrade</title>
	<meta name="generator" content="gedit">
	<link rel="stylesheet" type="text/css" href="./ampstyles.css">
</head>
<body>
<table class="ampjuke_content_table"><tr><td>
<?php
/*
0.7.6: Introduced the new setup.php
0.7.7: NOTE: This file is renamed to setup_backup.php after (successful) installation/upgrade.
*/

parse_str($_SERVER["QUERY_STRING"]);
require("configuration.php");

// PHP-version check - mandatory
if (substr(phpversion(),0,1)<=4) {
	echo '<font color="RED">';
	echo "<b>Warning:</b> You're using PHP version <b>".phpversion().'</b>.<br>';
	echo 'The <b>recommended</b> version is 5.2 or above.<font color="BLACK"><br>';
	echo 'The script might <i>not</i> work as expected.<br>';
} 


function make_uuid($prefix) {
    $chars = md5(uniqid(rand()));
    $uuid  = substr($chars,0,8) . '-';
    $uuid .= substr($chars,8,4) . '-';
    $uuid .= substr($chars,12,4) . '-';
    $uuid .= substr($chars,16,4) . '-';
    $uuid .= substr($chars,20,12);
    return $prefix . $uuid;
}


function check_rw($dir,$f,$both,$etype) { 
	$ok=1;
	$etxt='<font color="red"><b>Error:</b><font color="black">';
	if ($etype=="Warning") {
		$etxt='<font color="red"><b>Warning:</b><font color="black">';	 
	}	
	if (($both=='w') && (!is_writable($dir))) { 
		echo $etxt.' Can not write to: <b>'.$dir.'</b><br>';
		$f++;
		$ok=0;
	}
	if (!is_readable($dir)) {
		echo $etxt.' Can not read from: <b>'.$dir.'</b><br>';
		$f++;
		$ok=0;
	}
	if ($ok==1) {
	 	$txt='read from';
	 	if ($both=='w') { $txt.=' <i>and</i> write to'; }
		echo '<font color="green"><b>OK</b><font color="black"> can '.$txt.': <b>'.$dir.'</b><br>';
	}
	return $f;
}


if ((isset($act)) && ($act=='install')) { // We clicked the link -> continue INSTALLATION:
// This is a complete copy from the old install.php:
    // check that we have what we need:
    if (!file_exists('./db_dist.php')) {
        echo 'The file <b>db_dist.php</b> does not seem to exist and/or is renamed.<br>';
        echo 'Please fix it and try again.<br>';
        echo 'Tip: You can copy the file <b>db.php</b> to <b>db_dist.php</b>...<br>';
		echo '<br><br><a href="./scan.php?act=install">Click here to try again</a>.';
        die();
    }
    if (file_exists('./db.php')) {
        if (!is_writable('./db.php')) {
            echo 'The file <b>db.php</b> exists, but cannot be written to.<br>';
            echo 'Please fix it (f.ex. using the command: CHMOD 777 db.php) and try again.<br>';
			echo '<br><br><a href="./scan.php?act=install">Click here to try again</a>.';			
            die();
        }
    }
    if (!file_exists('./db_new.sql')) {
        echo 'The file <b>db_new.sql</b> does not seem to exist and/or is renamed.<br>';
        echo 'Please fix it and try again.<br>';
		echo '<br><br><a href="./scan.php?act=install">Click here to try again</a>.';		
        die();
    }
	// checks ends...now DO something:

    $in_handle=fopen("db_dist.php", "r");
    $out_handle=fopen("db.php", "w");
    while (!feof($in_handle)) {
        $line=fgets($in_handle);
        fwrite($out_handle, $line);
    }
    // Write current value of HTTP_HOST and any sub-dir.
	// in order to find out where the hell we are in the directory-structure:
    $line='$'.'base_http_prog_dir="http://'.$_SERVER["HTTP_HOST"];
    $path=substr($_SERVER["SCRIPT_NAME"],0,strlen($_SERVER["SCRIPT_NAME"])-10);
	$line.=$path.'";'.chr(13).chr(10);
	$line.='?>';
    fwrite($out_handle, $line);

	fclose($in_handle);
	fclose($out_handle);
    
	$uuid  = './tmp/'.make_uuid('').'.tmp';
    touch($uuid);
	
    echo '<b>Ok !</b><br>';
	echo '<a href="sitecfg.php?uuid='.$uuid.'">';	
	echo 'Click here to proceed with the installation</a>.<br>';
    // Offer link to detailed installation example:
    echo '<br><br><br>Nervous ? Afraid something might go wrong ? Need an example ?<br>';
    echo '<a href="http://www.ampjuke.org/?id=installation" target="_blank">';
    echo '<b>Click here for a step-by-step (incl. screen dumps) guide on how to install AmpJuke</b></a>';
    echo '.<br>The guide will open in a new window.<br>';
	die();
} // act=install


if ((isset($act)) && ($act=='upgrade')) { // We cliked the link -> continue UPGRADE
	include_once("sql.php"); 
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
	$qry="ALTER TABLE performer ADD `bio_short` TEXT NULL , ADD `bio_long` TEXT NULL ;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'PERFORMER-table upgraded (0.7.7)...<br>';
	
	// 0.7.7: Upgrade album-table:
	$qry="ALTER TABLE album ADD `bio_short` TEXT NULL , ADD `bio_long` TEXT NULL ;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'ALBUM-table upgraded (0.7.7)...<br>';
	
	// 0.7.9: Upgrade user-table:
	$qry="ALTER TABLE user ADD browse_albums_by_covers CHAR( 1 ) NULL DEFAULT '0' AFTER `disp_small_images`;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD browse_performer_by_picture CHAR( 1 ) NULL DEFAULT '0' AFTER `browse_album_by_cover`;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.7.9)...<br>';
	
	// 0.8.0: Upgrade user-table:
	$qry="ALTER TABLE user ADD xspf_active CHAR( 1 ) NOT NULL DEFAULT '0';";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.8.0)...<br>';
	
	// 0.8.2: Upgrade user-table:
	$qry="ALTER TABLE user ADD disp_now_playing_add2favorite CHAR( 1 ) NOT NULL DEFAULT '0' AFTER `disp_now_playing`;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE `user` ADD `ask4favoritelist_disp_suggestion` CHAR( 1 ) NOT NULL DEFAULT '0' AFTER `ask4favoritelist`;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.8.2)...<br>';

	// 0.8.3: Upgrade user-table:
	$qry="ALTER TABLE user ADD auto_add2favorite CHAR( 1 ) NOT NULL DEFAULT '1' AFTER `avoid_duplicate_entries`;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD auto_add2favorite_create_new CHAR( 1 ) NOT NULL DEFAULT '1' AFTER `auto_add2favorite`;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE user ADD auto_add2favorite_prefix VARCHAR( 80 ) NOT NULL DEFAULT 'AmpJuke_automatically_added' AFTER `auto_add2favorite_create_new`;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE `user` ADD `auto_add2favorite_create_new` CHAR( 1 ) NOT NULL DEFAULT '1' AFTER `auto_add2favorite`;";
	$result=execute_sql($qry,0,-1,$dummy);	
	echo 'USER-table upgraded (0.8.3)...<br>';
	
	// 0.8.4: Upgrade user-table:
	$qry="ALTER TABLE user ADD hide_icon_text CHAR( 1 ) NOT NULL DEFAULT '0' AFTER disp_lyrics;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE  user ADD  email VARCHAR( 80 ) NOT NULL DEFAULT  '0' AFTER name;";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'USER-table upgraded (0.8.4)...<br>';	

	// 0.8.6: Upgrade track-table:
	$qry="ALTER TABLE track ADD echonest_tempo VARCHAR( 8 ) NOT NULL DEFAULT '-1' AFTER path;";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_loudness VARCHAR( 8 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_danceability VARCHAR( 8 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_energy VARCHAR( 8 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_mode VARCHAR( 2 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_key VARCHAR( 2 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_time_signature VARCHAR( 2 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	$qry="ALTER TABLE track ADD echonest_status VARCHAR( 2 ) NOT NULL DEFAULT '-1';";
	$result=execute_sql($qry,0,-1,$dummy);
	echo 'TRACK-table upgraded (0.8.6)...<br>';
	
	$uuid  = './tmp/'.make_uuid('').'.tmp';
	touch($uuid);
	
    echo '<b>Ok ! Upgrade almost complete.</b><br>';
	echo '<a href="sitecfg.php?uuid='.$uuid.'">';	
	echo 'Last step is to check/inspect/verify the configuration</a>.<br>';

}	
	


// 1. db.php don't exist: Offer to INSTALL
if (!file_exists('db.php')) {
	echo ' <br>It looks like you have not <b>installed</b> AmpJuke, yet.<br>';
	echo 'If this is <b>not correct</b>, please remove or rename the files <b>db_new.sql</b> and <b>setup.php</b>.<br>';
	echo '<br>Checking permissions are OK:<br>';
	$failures=0;
	$failures=check_rw(getcwd(),$failures,'w','');
	$failures=check_rw(getcwd().'/tmp',$failures,'w','');
	$failures=check_rw(getcwd().'/covers',$failures,'w','');
	$failures=check_rw(getcwd().'/lastfm',$failures,'w','');
	$failures=check_rw(getcwd().'/db_new.sql',$failures,'w','');
	$failures=check_rw(getcwd().'/db_dist.php',$failures,'w','');
	$failures=check_rw(getcwd().'/setup.php',$failures,'w','');	
	$failures=check_rw(getcwd().'/toptags',$failures,'w',''); // 0.8.2
    $failures=check_rw(getcwd().'/getid3',$failures,'w',''); // 0.8.8
//    $failures=check_rw(getcwd().'/id3tag/modules/class/id3',$failures,'w',''); // 0.8.8 - experimental    
	if ($failures!=0) {
		echo '<br>'.$failures.' error';
		if ($failures>1) { echo 's'; }
		echo ' found. Can not install.<br>';
		echo '<br>Fix permissions, and <a href="./">try to install again</a>.';
		echo '<br><br>For more information, please see <a href="http://www.ampjuke.org/?id=faq40"';
		echo ' target="_blank">this FAQ-entry</a> (will open in a new window/tab).<br> ';
		die();
	}
	echo '<br><br><font color="blue"><b>OK</b><font color="black">. ';
	echo 'To <b>install</b> AmpJuke, <a href="setup.php?act=install&checks=done">click here</a>.';
}

// 2. db.php exists: Offer to UPGRADE
if (file_exists('db.php')) {
	echo ' <br>It looks like you ant to <b>upgrade</b> AmpJuke.<br>';
	echo 'If this is <b>not correct</b>, please remove or rename the files <b>db_new.sql</b> and <b>setup.php</b>.<br>';
	$failures=0;
	$failures=check_rw(getcwd(),$failures,'w','');
	$failures=check_rw(getcwd().'/tmp',$failures,'w','');
	$failures=check_rw(getcwd().'/covers',$failures,'w','');
	$failures=check_rw(getcwd().'/lastfm',$failures,'w','');
	$failures=check_rw(getcwd().'/db_new.sql',$failures,'w','');
	$failures=check_rw(getcwd().'/db_dist.php',$failures,'w','');
	$failures=check_rw(getcwd().'/setup.php',$failures,'w','');	
    $failures=check_rw(getcwd().'/getid3',$failures,'w',''); // 0.8.8
    $failures=check_rw(getcwd().'/id3tag/modules/class/id3',$failures,'w',''); // 0.8.8
	if ($failures!=0) {
		echo '<br>'.$failures.' error';
		if ($failures>1) { echo 's'; }
		echo ' found. Can not upgrade.<br>';
		echo '<br>Fix permissions, and <a href="./">try to upgrade again</a>.';
		echo '<br><br>For more information, please see <a href="http://www.ampjuke.org/faq.php?q_id=40"';
		echo ' target="_blank">this FAQ-entry</a> (will open in a new window/tab).<br> ';
		die();
	}
	echo '<br><br><font color="blue"><b>OK</b><font color="black">. ';
	echo 'To <b>upgrade</b> AmpJuke, <a href="setup.php?act=upgrade&checks=done">click here</a>.';
}
	

?>
</td></tr></table></body></html>
