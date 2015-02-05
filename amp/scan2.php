<?php
// 0.7.7: Comment out  the following four lines in order to allow scan+import to runs as a cron-job. Also remember to uncomment CRON-SETTINGS block below (instructions available): 
// 0.8.6: Added several calls to addslashes() and stripslashes() in order to handle files w. "special" characters ('," etc.)
// 0.8.8: Offers an option to add new music to a specific favorite list.
require('logincheck.php');
if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
	header("Location: logout.php");
	die('Not logged in');
}
	
// 0.7.5: YES! scan2.php is here:
//The *new*, *improved*, *better* SCAN routine for AmpJuke.

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="EN">';
echo '<head>';
require_once('db.php');
echo '<title>Scan+import music [AmpJuke...and YOUR hits keep on coming!]</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />';
//echo '<meta http-equiv="Refresh" content="1" />';
echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'" />'; 
echo '<script type="text/javascript" src="rowcols.js"></script>';
require_once('translate.php');
require_once('disp.php');
require_once('sql.php');
require_once('sql.php');
require_once('configuration.php'); 
require_once('set_td_colors.php');
require_once('tbl_header.php');


// 0.7.7:  CRON-SETTINGS
/*
// Run scan2.php using PHP CLI (AND cron) ?? If "Hell, yeah!", then read on for OPTIONS in relation to this:

// Uncomment if you want to delete "dead" records:
// $_POST['delete_dead_records']='1'; 
// Uncomment if you want the scan to stop when encountering files/folders that cannot be read from:
//$_POST['complain_permissions']='1'; 

// Uncomment to activate "cutoff" in relation to unwanted bitrates. Note: If you enable this, you must also enable next setting:
// $_POST['dont_import_low_bitrate']='1';
//$_POST['low_bitrate_limit']=32000; // Change to a 'suitable' value, if you use 'dont_import_low_bitrate' above

// Uncomment to activate "cutoff date". Note: If you want to do this, you'll also need to uncomment the next and entering a YYYY-MM-DD date:
// $_POST['cutoff_date_active']='1';
// $_POST['cutoff_date']='yyyy-mm-dd'; // Change to a valid date in the past, - f.ex.: 2008-05-20.

// Uncomment if you want to simulate a scan+import (i.e. nothing happens for real to your database)
//$_POST['simulate_import']='1'; 

// Next 4 settings MUST be set if you're running scan using PHP CLI and/or cron. Possible values: 'a warning', 'an error' or 'OK'
$missing_track_no='a warning';
$missing_year='a warning';
$missing_album_name='OK';
$missing_performer='an error';
$missing_track_name='an error';

// Uncomment to allow importing stuff w. warnings (see previous 4 settings to determine what's considered a warning):
//$_POST['import_warnings']='1';
// Uncomment to allow importing stuff w. errors (not recommended). Same "filter" as described in previous setting:
//$_POST['import_errors']='1';
// Uncomment to allow re-processing (sync) of info. found in tags in tracks on filesystem vs. info. found in database.
//$_POST['refresh_tracks']='1';

$details='1'; // MUST be set. Possible values 1,2,3 or 4 (degree of details displayed during scan+import, 1=low, 4=sh*tloads)
$act='scan'; // The *most* *important* *setting* when running scan using PHP CLI and/or cfron: Tell the script to run a scan+import immediately and bypass the "setup" screen...
*/
// END OF CRON-SETTINGS


parse_str($_SERVER["QUERY_STRING"]);
if (!isset($act)) {
	$act='setup';
}
$tdnorm='';
$tdalt='';
$tdhighlight='';
$count=0;
echo '<body>';
/*
******************************
						SCAN (support functions below)
******************************
*/
function update_status($level,$details,$msg) {
 	if ($details>=$level) {
		echo '<tr><td>'.$msg.'</td>';
		print "</tr> \n";
		@flush(); @ob_flush();
	}	
}	

function loc_add_tr($id,$favlist,$all_users,$user_id) { // 0.8.8: A rewritten, stripped down copy of add_tr fro disp.php
    $pid=get_performer_id($id);
	$aid=get_album_id($id);
	$r=get_track_extras($id);
    // Find out of we need to add this track to ALL users or just the current one:
    $qry="SELECT id FROM user WHERE id=".$user_id;
    if ($all_users=='1') {
        $qry="SELECT id FROM user";
    }
    $result=execute_sql($qry,0,100000,$nr);
     // Insert it:
     while ($row=mysql_fetch_array($result)) {
        $uid=$row['id'];
        $qry="INSERT INTO fav (track_id, performer_id, album_id, name, duration,";
        $qry.=" last_played, times_played, year, user_id, fav_name) VALUES";
        $qry.=" ('".$id."', '".$pid."', '".$aid."', ";
        $qry.='"'.$r['name'].'"';
        $qry.=", '".$r['duration']."', ";
        $qry.="'".$r['last_played']."', '".$r['times_played']."', ";
        $qry.="'".$r['year']."', '".$uid."', '".$favlist."')";
        $rr=execute_sql($qry,0,-1,$nr);
    }
}

// 0.8.1: These functions handles the cover-stuff during scan+import:
// 0.8.6: Rewritten:
function find_cover($folder,$details) {
	$ret='';
	$files_list=glob($folder.'*.[jJ][pP][gG]');

	foreach($files_list as $files)	{
		update_status(4,$details,'Found a cover: '.$files);
		$ret=$files;
	}
	return $ret;
}
/* old code:
	$valid = array(
	    'jpg' => 'JPG',
	    'png' => 'PNG'
	);
	$files = array();   
	$dir = new DirectoryIterator($folder);
   	foreach($dir as $file)
   	{
		if($file->isDot() || !$file->isFile()) continue;               // filter out directories
	    $info = pathinfo($file->getPathname());                        // Use pathinfo to get the file extension
    	if(isset($info['extension']) && isset($valid[$info['extension']]))   // Check if there is an extension and it is in the whitelist
      	{
        	$files[] = array(
            'filename' => $file->getFilename();
            'type' => $valid[$info['extension']] // 'JPG' or 'PNG'
         );
      }
   }   
	sort($files);
	return $files[0]['filename'];
}
*/

function handle_cover($ttype,$folder,$id,$simulate_import,$filename_new_stuff,$details,$cwd) {
	if ($ttype=='album') {
		$cover=find_cover($folder,$details);
		update_status(3,$details,'Looked in :'.$folder.'</b> for a cover. Found: <b>'.$cover.'</b>');
		if ($cover<>'') {
			$target=$cwd.'/covers/'.$id.'.jpg';
			$dummy=copy($cover,$target);
			update_status(3,$details,'Copied from <b>'.$cover.'</b> to <b>'.$target.'</b>');
		}
	}
}
//
//
// 0.8.1: End of cover-handling routines
//
//

function get_setting($txt,$setting1,$setting2) {
	$ret=$txt;
	if ($setting1=='1') {
		$ret.='<font color="green"><b>Yes</b><font color="black">';
	} 
	if ($setting1=='0') {
		$ret.='<font color="red"><b>No</b><font color="black">';
	}
	if ($setting2<>'') {
		$ret.=' <b>'.$setting2.'</b>';
	}
	return $ret;
}				

function report_file($fn,$msg) {
	$ha=fopen($fn,'a');
	fwrite($ha,'<tr><td>');
	fwrite($ha,$msg.'</td></tr>');
	fclose($ha);
}	

function append_report($file1,$file2) {
	$inhandle=fopen($file2, 'r');
	$outhandle=fopen($file1, 'a');
	while (!feof($inhandle)) {
		$buf=fread($inhandle,8192);
		fwrite($outhandle,$buf);
	}
	fclose($indhandle);
	fclose($outhandle);
}	

function check_all_folders($dir,&$folders,&$total_folders_not_read,$complain_permissions,
&$folders_not_read,&$details) { 
    $dir_files = $dir_subdirs = array(); 
	// Add missing '/':
	if (substr($dir,strlen($dir)-1,1)<>'/') {
		$dir.'/';
	}	
	
	// Check we have permissions:
	if ((is_dir($dir)) && (!is_readable($dir))) {
		$total_folders_not_read++;
		update_status(3,$details,'<font color="red">Cannot read from: '.$dir);
		$folders_not_read.=$dir.'/'.$entry.'||'; // The extra / is not needed here
		if ($complain_permissions=='1') { // We DIE her (missing permissions):
			update_status(1,$details,'You have "Complain about permissions..." set: <font color="red"><b>Stop. Cannot read from '.$dir.'/'.$entry.'<br>Fix permissions and try again');
			die();
		}		
	} 
	if ((is_dir($dir)) && (is_readable($dir))) {
	// ...process the dir.:
		chdir($dir);
		update_status(4,$details,'Check folder: '.$dir);
		if ($handle = @opendir($dir)) {
			while($entry = readdir($handle)) {    	
				if (is_dir($entry) && $entry !=  ".." && $entry !=  ".") { 
					$dir_subdirs[] = $entry; 
					$folders.=$dir.'/'.$entry.'/||';
				} 
				elseif($entry !=  ".." && $entry !=  ".") {    
					$dir_files[] = $entry; 
					$count++;
				} 
			} // while... 

			sort($dir_files); 
			sort($dir_subdirs); 
			// Traverse sub directories 
			for($i=0; $i<count($dir_subdirs); $i++) { 
			 	update_status(4,$details,'Found one or more sub-folders...(recursion needed)');
				check_all_folders("$dir/$dir_subdirs[$i]",$folders,$total_folders_not_read,
				$complain_permissions,$folders_not_read,$details); 
			} 
			closedir($handle); 
		}	
	} // -> We have permissions
	return $folders;
} 

// Used in step 3:
require_once("./getid3/getid3.php");

// Get+return tags from a music file:
function use_getid($dir,$extension,$details) { 
	$ret=array();
	$ret['performer']='';
	$ret['title']='';
	$ret['album']='';
	$ret['year']='';
	$ret['track_number']='';
	$getID3 = new getID3;
	$ThisFileInfo = $getID3->analyze($dir);
    getid3_lib::CopyTagsToComments($ThisFileInfo);
	update_status(4,$details,'Call to GetID3() completed for: '.$dir);
	
	// perfomer from any/all available tag formats:
    $ret['performer']=@$ThisFileInfo['comments_html']['artist'][0];
	update_status(4,$details,'Performer='.$ret['performer']);
	
    // title:
    $ret['title']=@$ThisFileInfo['comments_html']['title'][0];
	update_status(4,$details,'Title='.$ret['title']);

    // album: 
    $ret['album']=@$ThisFileInfo['comments_html']['album'][0];
	update_status(4,$details,'Album='.$ret['album']);

    // year: 
    $ret['year']=@$ThisFileInfo['comments_html']['year'][0];
    if ($extension=='ogg') { // ogg-extension in this array uses DATE, not YEAR...
    	$ret['year']=@$ThisFileInfo['comments_html']['date'][0];
	}    	
	update_status(4,$details,'Year='.$ret['year']);

    // track#:
    $ret['track_number']=@$ThisFileInfo['comments_html']['track_number'][0];
    if ($extension=='ogg') { // ogg-extension in this array uses "TRACKNUMBER", not TRACK...
    	$ret['track_number']=@$ThisFileInfo['comments_html']['tracknumber'][0];
	}    
	if ($ret['track_number']=="") { // Might be mp3 w. ID3v1 tags...try TRACK...
		$ret['track_number']=@$ThisFileInfo['comments_html']['track'][0];
	}	
	if ($ret['track_number']=="") { // Still empty: GUESS value using DIGITS in FILENAME:
		$pa=explode("/", $ThisFileInfo['filenamepath']);
		$pb=$pa[count($pa)-1]; // FILNAME is (must) be the last item in array
		$pc=explode(".",$pb); // Get rid of any extensions (m4a contains a digit...):
		$pd=$pc[0]; // Get the name of file excl. extension
		$ret['track_number']=preg_replace("/[^0-9]/","", $pd);  // Get digits from name (pd)
	}	
	update_status(4,$details,'Track_number='.$ret['track_number']);
	
    // the rest:
    $ret['path']=$ThisFileInfo['filenamepath'];
    $ret['duration']=$ThisFileInfo['playtime_string'];
    if (strlen($ThisFileInfo['playtime_string'])<5) { $ret['duration']='0'.$ret['duration']; }
    update_status(4,$details,'Duration='.$ret['duration']);
	$ret['bitrate']=$ThisFileInfo['bitrate']; 
	update_status(4,$details,'Bitrate='.$ret['bitrate']);
	
	return $ret;
} // function use_getid


function handle_empty_tag($file,$missing_tag,$import_warnings,
$import_errors,$filename_warnings,$filename_errors,&$total_warnings,
&$total_errors,$errmsg,$details,$edit_errors=0) { // 0.8.5: Added: edit_errors: Link to editing tags
	$ret=1;
	if (($missing_tag=='an error') && ($import_errors=='0')) {
		$ret=0;
		// 0.8.5: New: Add link to edit right away:
		$extra='';
		if ($edit_errors=='1') {
			if (!isset($base_http_prog_dir)) {
				require('db.php');
			}
			$extra=' <a href="'.$base_http_prog_dir.'/id3tag/?filename='.$file.'" target="_blank">Edit tags</a>';
		}
		// 0.8.5: ...ends
		report_file($filename_errors,$errmsg.': '.$file.$extra);
		update_status(3,$details,$errmsg.': '.$file.$extra);
		$total_errors++;
	}
	if (($missing_tag=='a warning') && ($import_warnings=='0')) {
		$ret=0;
		// 0.8.5:
		$extra='';
		if ($edit_errors=='1') {
			if (!isset($base_http_prog_dir)) {
				require('db.php');
			}		
			$extra=' <a href="'.$base_http_prog_dir.'/id3tag/?filename='.urlencode($file).'" target="_blank">Edit tags</a>'; // 0.8.6: urlencode
		}
		//
		report_file($filename_warnings,$errmsg.': '.$file.$extra);
		update_status(3,$details,$errmsg,': '.$file.$extra);
		$total_warnings++;
	}
	return $ret;
}	
		

function find_key($what,$key,$details) {
// Input: "key" we want to find the corresponding ID for in a given table (="what")
$ret=0;
update_status(4,$details,'Lookup a '.$what.': '.$key);
if ($what=='performer') {
	$qry='SELECT * FROM performer WHERE pname="'.$key.'"';
	$result=execute_sql($qry,0,1,$num_rows,'');
	}

if ($what=='album') {
	$qry='SELECT * FROM album WHERE aname="'.$key.'"';
	$result=execute_sql($qry,0,1,$num_rows,'');
	}

if ($what=='track') {
	$qry='SELECT * FROM track WHERE name="'.$key.'"';
	$result=execute_sql($qry,0,1,$num_rows,'');
	}

if ($num_rows>=1) {
	$row=mysql_fetch_array($result);
	if ($what=='performer') { $ret=$row['pid']; }
	if ($what=='album') { $ret=$row['aid']; }
	if ($what=='track') { $ret=$row['id']; }
	}
update_status(4,$details,'Find_key. Return-value='.$ret);
return $ret;
}	



function find_keys($artist,$album,$trk_name,$details) {
$ret=0;
update_status(4,$details,'Find_keys...');
$art_id=find_key('performer',$artist,$details);
$alb_id=find_key('album',$album,$details);
$qry='SELECT * FROM track WHERE ';
if ($album!="") { 
		$qry.='performer_id='.$art_id.' AND album_id='.$alb_id.' AND name="'.$trk_name.'"';
	} else {
		$qry.='performer_id='.$art_id.' AND name="'.$trk_name.'"';
	}	
$result=execute_sql($qry,0,1,$num_rows,'');
if ($num_rows<>0) { $ret=1; }
update_status(4,$details,'Return-value='.$ret);
return $ret;
}


function add_key($what,$key,$f_key,$simulate_import,$filename_new_stuff,$details) {
	if ($what=='performer') {
		$qry='INSERT INTO performer VALUES("","'.$key.'","","")';
	}
	if ($what=='album') {
		$qry='INSERT INTO album VALUES("","'.$f_key.'","'.$key.'","","")';
	}
	if ($simulate_import==0) {
		$num_rows=0;
		$result=execute_sql($qry,0,-1,$num_rows);
	} 
	report_file($filename_new_stuff,'New <b>'.$what.'</b> discovered: <b>'.$key.'</b>');
	update_status(2,$details,'New '.$what.' discovered: '.$key);
}	

function get_value($arr) {
	$ret="";
	if (is_array($arr)) {
		foreach ($arr as $k => $v) { $ret=$v; }
	}
	return $ret;
}	



if ($act=='scan') {
/*
******************************
						SCAN: STEP 1: INITIALIZE
******************************
*/
	set_time_limit(0); // Believe me: you want this !
	error_reporting(0); // Believe me: you also want this ! Unfortunately...
	$starttimer = time()+microtime(); // Used to calc. the total duration

	echo headline('','Scan+import','');
	echo std_table("ampjuke_content_table","ampjuke_content");
	?>
	<th colspan="3">Status</th>
	<tr><td width="50%" valign="top"><p class="note">
	Note: Bery very, very patient ! This might take a long time.</p>
	<?php
	// Setup report FILENAMES:
	$filename_scan_report=getcwd().'/tmp/scan_report_'.date('U').'.htm';
	@unlink($filename_scan_report);
	$h=fopen($filename_scan_report,'w');
	fwrite($h,'<html><head><title>AmpJuke Scan Report</title></head><body>');
	fwrite($h,'<table border="1" rules="rows">');
	fclose($h);
	report_file($filename_scan_report,'<h3 align="center">*** SCAN REPORT ***<a name="top"></a></h3>'); // 0.8.5: basic navigation
	
	$filename_new_stuff=getcwd().'/tmp/scan_report_new_stuff_'.date('U').'.htm';
	@unlink($filename_new_stuff);
	report_file($filename_new_stuff,'<h4>*** New tracks ***</h4>');
	
	$filename_folders_not_read=getcwd().'/tmp/folders_not_read.txt'; 	
	@unlink($filename_folders_not_read);
	report_file($filename_folders_not_read,'<a href="#top">Go to the top</a><h4>*** Folders not read ***</h4><a name="folders_not_read"></a>'); // 0.8.5
	
	$filename_dead_stuff=getcwd().'/tmp/scan_report_dead_stuff_'.date('U').'.htm';
	@unlink($filename_dead_stuff);	
	report_file($filename_dead_stuff,'<a href="#top">Go to the top</a><h4>"Dead" tracks, albums and performers</h4><a name="dead"></a>'); // 0.8.5
	
	$filename_warnings=getcwd().'/tmp/scan_warnings_'.date('U').'.htm';
	@unlink($filename_warnings);
	report_file($filename_warnings,'<a href="#top">Go to the top</a><h4>*** Warnings ***</h4><a name="warnings"></a>'); // 0.8.5
	
	$filename_errors=getcwd().'/tmp/scan_errors_'.date('U').'.htm';
	@unlink($filename_errors);
	report_file($filename_errors,'<a href="#top">Go to the top</a><h4>*** Errors ***</h4><a name="errors"></a>'); // 0.8.5

	$filename_low_bitrate=getcwd().'/tmp/scan_low_bitrate_'.date('U').'.htm';
	@unlink($filename_low_bitrate);
	report_file($filename_low_bitrate,'<a href="#top">Go to the top</a><h4>*** Tracks below bit rate limit ***</h4><a name="low_bitrate"></a>'); // 0.8.5

	$filename_refresh_stuff=getcwd().'/tmp/scan_refreshed_stuff_'.date('U').'.htm';
	@unlink($filename_refresh_stuff);
	report_file($filename_refresh_stuff,'<a href="#top">Go to the top</a><h4>*** Reprocessed tracks ***</h4><a name="reprocessed"></a>'); // 0.8.5

	// 0.8.1: In case we need to lookup covers in folders, get the "starting folder" (ie. parent to "covers" folder):
	$cwd=getcwd();

	// Setup COUNTERS:
	$total_folders=0;
	$total_folders_not_read=0;
	$total_dead_tracks=0;
	$total_dead_performers=0;
	$total_dead_albums=0;
	$total_tracks=0;
	$total_tracks_not_read=0;	
	$total_new_tracks=0;
	$total_warnings=0;
	$total_errors=0;
	$total_tracks_below_bitrate_limit=0;
	$total_refreshed_tracks=0;

	// Setup what's POSTed:
	$delete_dead_records=0;
	if (isset($_POST['delete_dead_records'])) { $delete_dead_records=1; }
	$complain_permissions=0;
	if (isset($_POST['complain_permissions'])) { $complain_permissions=1; }
	$cutoff_date_active=0;
	if (isset($_POST['cutoff_date_active'])) { 
		$cutoff_date_active=1;
		$cutoff_date=strtotime($_POST['cutoff_date']);
	}
	$dont_import_low_bitrate=0;
	if (isset($_POST['dont_import_low_bitrate'])) {
			$dont_import_low_bitrate=1;
			$low_bitrate_limit=$_POST['low_bitrate_limit'];
	}
	$simulate_import=0;
	if (isset($_POST['simulate_import'])) { $simulate_import=1; }
	$missing_track_no=$_POST['missing_track_no'];
	$missing_year=$_POST['missing_year'];
	$missing_album_name=$_POST['missing_album_name'];
	$missing_performer=$_POST['missing_performer'];
	$missing_track_name=$_POST['missing_track_name'];
	$edit_errors=0; // 0.8.5
	if (isset($_POST['edit_errors'])) { $edit_errors=1; } //  0.8.5
	$import_warnings=0;
	if (isset($_POST['import_warnings'])) { $import_warnings=1; }
	$import_errors=0;
	if (isset($_POST['import_errors'])) { $import_errors=1; }
	$refresh_tracks=0;
	if (isset($_POST['refresh_tracks'])) { $refresh_tracks=1; }
	$details=$_POST['details'];
    
    // 0.8.8: Get user-id in case we need it later (and the session times out):
    $userid=get_user_id($_SESSION['login']);
    
	// 0.8.1: Look for covers in folders ?
	$lookup_covers_enabled=0;
	if (isset($_POST['lookup_covers_enabled'])) {
		$lookup_covers_enabled=1;
	}
	// Build list of folders:
	update_status('1',$details,'Build list of folders...');	
	$folder=check_all_folders($base_music_dir,$folders,$total_folders_not_read,$complain_permissions,$folders_not_read,$details);
	$folders.=$base_music_dir.'/||'; // 0.8.0: Heyy - don't forget the BASE folder!!
	$folder=explode('||',$folders);
	$total_folders=count($folder);
	report_file($filename_folders_not_read,'Folders that cannot be read: <b>'.$total_folders_not_read.'</b>');
	$not_read=explode('||',$folders_not_read);
	$x=0;
	while ($x<=count($not_read)) {
		report_file($filename_folders_not_read,$not_read[$x]);
		update_status(4,$details,'Not able to read from: '.$not_read[$x]);
		$x++;
	}
/*
******************************
						SCAN: STEP 2: DELETE DEAD RECORDS
******************************
*/
// (Optionally) delete "dead" stuff :
	if ((isset($delete_dead_records)) && ($delete_dead_records=='1')) {
		update_status(1,$details,'Delete "dead" records...'); 
		// 1.Dead tracks:
		$qry="SELECT * FROM track";
		$result=execute_sql($qry,0,100000000,$num_rows);
		while ($row=mysql_fetch_array($result)) {
			if (!file_exists(stripslashes($row['path']))) { // 0.8.6 do we have a "dead" record: deal with it...
				report_file($filename_dead_stuff,
				'"Dead" track: '.get_performer_name(stripslashes($row['performer_id'])).' - '.stripslashes($row['name']).
				' ['.get_album_name(stripslashes($row['album_id'])).'] Path='.stripslashes($row['path'])); // 0.8.6
				$total_dead_tracks++;
				update_status(3,$details,'Found a "dead" track: '.$row['path']);
			 	if (($simulate_import=='0') && ($delete_dead_records=='1')) { // This is for real:
				 	// First, delete from TRACK-table:			 	
					$delqry='DELETE FROM track WHERE id='.$row['id'];
					$delresult=execute_sql($delqry,0,-1,$nr);
					// Second, delete the id from tha FAV-table as well (if it exists):
					$delqry="DELETE FROM fav WHERE track_id=".$row['id'];
					$delresult=execute_sql($delqry,0,-1,$nr);
					// Third, get rid of it in the queue (if it exists). Note: Added in 0.7.4
					$delqry="DELETE FROM queue WHERE track_id=".$row['id'];
					$delresult=execute_sql($delqry,0,-1,$nr);
					update_status(4,$details,'Deleted the "dead" track');
				}
			}	
		}
		// 2.Dead albums:
		$qry="SELECT * FROM album";
		$result=execute_sql($qry,0,10000000,$num_rows);
		while ($row=mysql_fetch_array($result)) {
			$chkqry="SELECT * FROM track WHERE album_id='".$row['aid']."'";
			$chkresult=execute_sql($chkqry,0,10000000,$nr);
			if ($nr==0) { // there are no tracks for this album: delete it:
				$total_dead_albums++;			
				$p=get_performer_name($row['aperformer_id']);
				report_file($filename_dead_stuff,'"Dead" album: '.$p);
				update_status(3,$details,'"Dead" album: '.$p);
			 	if (($simulate_import=='0') && ($delete_dead_records=='1')) { // This is for real:
					$delqry="DELETE FROM album WHERE aid='".$row['aid']."'";
					$delresult=execute_sql($delqry,0,-1,$n);
					update_status(4,$details,'Deleted the "dead" album');
				}	
			}
		}					
		// 3.Dead performers:
		$qry="SELECT * FROM performer";
		$result=execute_sql($qry,0,100000000,$num_rows);
		while ($row=mysql_fetch_array($result)) {
			$chkqry="SELECT * FROM track WHERE performer_id='".$row['pid']."'";
			$chkresult=execute_sql($chkqry,0,10000000,$nr);
			if (($nr==0) && ($row['pid']<>'1')) { // this performer does not have any tracks AND is not the "various" performer, - deal with it:
				$total_dead_performers++;
				report_file($filename_dead_stuff,'"Dead" performer: '.$row['pname']);
				update_status(3,$details,'"Dead" performer: '.$row['pname']);
			 	if (($simulate_import=='0') && ($delete_dead_records=='1')) { // This is for real:
					$delqry="DELETE FROM performer WHERE pid='".$row['pid']."'";
					$delresult=execute_sql($delqry,0,-1,$n);
					update_status(4,$details,'Deleted the "dead" performer');
				}
			}
		}
		report_file($filename_dead_stuff,'Total "dead" tracks: <b>'.$total_dead_tracks.'</b>');
		report_file($filename_dead_stuff,'Total "dead" albums: <b>'.$total_dead_albums.'</b>');
		report_file($filename_dead_stuff,'Total "dead" performers: <b>'.$total_dead_performers.'</b>');
	} // if isset delete_dead_records...
/*
******************************
						SCAN: STEP 3: PROCESS ALL FOLDERS
******************************
*/
	$x=0;
	sort($folder);
	while ($x<count($folder)) { // Process each folder we actually can read from:
		$first_file=1;
		$previous_performer_id=0;
		// Skip scanning folders w.o. permissions as well as '.' and '..':
		if ((!in_array($folder[$x],$not_read)) && ($folder[$x].$file<>'.') && 
		($folder[$x].$file<>'..')) { 
			$handle=opendir($folder[$x]);
			update_status(1,$details,'Scan+import tracks in folder: '.$folder[$x]);;
			while ($file=readdir($handle)) { 
				$is_music=1;
				$is_folder=0; // 0.8.6
				// 0.8.6: Check if we're trying to process a *folder* (. or ..):
				if (is_dir($file)) {
					$is_music=0;
					$is_folder=1;
				}
				// Check extension is what we want:
				$extension=get_file_extension($folder[$x].$file);
	    		if (($extension!="mp3") && ($extension!="ogg") && ($extension!="wma") && 
				($extension!="ape") && ($extension!="m4a")) { 
					$is_music=0;
					update_status(4,$details,'Not a valid extension: '.$folder[$x].$file);
				}	
				// Check we can READ from the f*cker:
				if (!is_readable($folder[$x].$file)) {
					update_status(3,$details,'Error: Cannot read: <b>'.$folder[$x].$file.'</b> (missing permissions). Scan+import will continue...');
					report_file($filename_errors,'Error. Cannot read: <b>'.$folder[$x].$file.'</b> (missing permissions)</td></tr>');
					$total_errors++;
					$is_music=0; // ...well...it MIGHT be, but we cannot read from it
				}				
				// Check cutoff-date:
				if (($cutoff_date_active=='1') && ($is_music==1)) {
					if ((filemtime($folder[$x].$file)<$cutoff_date)) {
						$is_music=0; // ...not music (even though we know it is)
						update_status(4,$details,'Cutoff-date active. Do not scan: '.$folder[$x].$file);
					}	
				}
				// Made it so far, now get our hands REAL dirty: Read the TAGS:
				if (($is_music==1) && ($folder[$x].file!=".") && ($folder[$x].file!="..")) { 
					$track=use_getid($folder[$x].$file,$extension,$details);
				}	
				// Check for low bitrate:
				if ($dont_import_low_bitrate==1) {
					if (($is_music==1) && ($folder[$x].$file!=".") && ($folder[$x].$file!="..")) { 
						if ($track['bitrate']<$low_bitrate_limit) {
							report_file($filename_low_bitrate,'Bitrate too low: <b>'.$folder[$x].$file);
							$total_tracks_below_low_bitrate++;
							update_status(4,$details,'Bitrate too low: '.$folder[$x].$file);
							$is_music=0;
						}
					}
				}	
				// Do we want to re-process... ?
				$reprocessed_track=0;
				if (($is_music==1) && ($refresh_tracks==0)) { // ...no, we dont:
					$qry='SELECT * FROM track WHERE path="'.addslashes($folder[$x].$file).'"'; // 0.8.6
					$result=execute_sql($qry,0,1,$num_rows);
					if ($num_rows>0) { 
						$is_music=0;
					}
				}
				if (($is_music==1) && ($refresh_tracks==1)) { // ...yes, we do:
					$qry='SELECT * FROM track WHERE path="'.$folder[$x].$file.'"';
					$result=execute_sql($qry,0,1,$num_rows);
					if (($num_rows>0) && ($simulate_import==0)) { 
						$row=mysql_fetch_array($result);
						// Delete it from TRACK table:
						$qry="DELETE FROM track WHERE id='".$row['id']."'"; // 0.8.1
						$result=execute_sql($qry,0,-1,$nr); // 0.8.1
						// Delete it from FAVORITES:
						$qry="DELETE FROM fav WHERE track_id=".$row['id'];
						$result=execute_sql($qry,0,-1,$nr);
						// Delete it from QUEUE:
						$qry="DELETE FROM queue WHERE track_id=".$row['id'];
						$result=execute_sql($qry,0,-1,$nr);							
					}	
					if ($num_rows>0) {
						$total_refreshed_tracks++;
						report_file($filename_refresh_stuff,'Re-processed track: <b>'.$folder[$x].$file);
						update_status(4,$details,'Re-process track: '.$folder[$x].$file);						
						$reprocessed_track=1;
					}	
				} // end of check: reprocess.
				// Check for conditions that's been POSTed, and report them:
				if ($is_music==1) { 
					// Missing PERFORMER ?
					if ($track['performer']=='') { 
						$is_music=handle_empty_tag($folder[$x].$file,$missing_performer,
						$import_warnings,$import_errors,$filename_warnings,$filename_errors,
						$total_warnings,$total_errors,'No performer',$details,$edit_errors); // 0.8.5: edit...
					}	
					// Missing TITLE ?
					if ($track['title']=='') {
						$is_music=handle_empty_tag($folder[$x].$file,$missing_track_name,
						$import_warnings,$import_errors,$filename_warnings,$filename_errors,
						$total_warnings,$total_errors,'No track name',$details,$edit_errors); // 0.8.5: edit...
					}
					// Missing ALBUM ?
					if ($track['album']=='') {
						$is_music=handle_empty_tag($folder[$x].$file,$missing_album_name,
						$import_warnings,$import_errors,$filename_warnings,$filename_errors,
						$total_warnings,$total_errors,'No album name',$details,$edit_errors); // 0.8.5: edit...
					}				
					// Missing YEAR ?
					if ($track['year']=='') {
						$is_music=handle_empty_tag($folder[$x].$file,$missing_year,
						$import_warnings,$import_errors,$filename_warnings,$filename_errors,
						$total_warnings,$total_errors,'No year',$details,$edit_errors); // 0.8.5: edit...
					}				
					// Missing TRACKNUMBER ?
					if ($track['track_number']=='') {
						$is_music=handle_empty_tag($folder[$x].$file,$missing_track_no,
						$import_warnings,$import_errors,$filename_warnings,$filename_errors,
						$total_warnings,$total_errors,'No track number',$details,$edit_errors); // 0.8.5: edit...
					}	
				}
				// Survived the above checks...still here...really WANT to do this, right ?
				// Hell yeah ! Go ahead and use modified copy of old scan-routine:
				if (($is_music==1) && ($track['album']<>'')) { 
					$title_exists=find_keys(stripslashes($track['performer']),stripslashes($track['album']),stripslashes($track['title']),$details); // 0.8.6
					if ($title_exists==1) {
						$is_music=0;
					}	
				}
				if (($is_music==1) && ($track['album']=='')) { 
					$title_exists=find_keys($track['performer'],$track['album'],$track['title'],$details);
					if ($title_exists==1) {
						$is_music=0;
					}	
				}
				// Made it so far. Start to add some data:
				if ($is_music==1) {
					$artist_id=find_key('performer',$track['performer'],$details);
					if ($artist_id==0) {
						add_key('performer',addslashes($track['performer']),'',$simulate_import,$filename_new_stuff,$details); //0.8.6
						$artist_id=find_key('performer',$track['performer'],$details);
					}
				}
				if (($is_music==1) && ($track['album']!="")) {
					// First, store the previous performer's id (used later):
					if ($first_file!=1) {
						$qry="SELECT * FROM album WHERE aid=".$album_id;
						$result=execute_sql($qry,0,1,$num_rows,'');
						$row=mysql_fetch_array($result);
						$previous_performer_id=$row['aperformer_id'];
						update_status(4,$details,'Not first file. Prev.perf.id='.$previous_performer_id);
					}		
					// Second, add the album:
					$album_id=find_key('album',$track['album'],$details);
					if ($album_id==0) {
						add_key('album',$track['album'],$artist_id,$simulate_import,$filename_new_stuff,$details); 
						$album_id=find_key('album',$track['album'],$details);
						// 0.8.1: Check if we also want to look for covers:
						if ($lookup_covers_enabled==1) {
							handle_cover('album',$folder[$x],$album_id,$simulate_import,$filename_new_stuff,$details,$cwd);
						}
					}	
				}		
				// IF the album is empty, just set the album_id to 0:
				if (($is_music==1) && ($track['album']=="")) {
					$album_id=0;
					update_status(4,$details,$folder[$x].$file.': No album (it is OK!)');
				}
				// This is a tricky part...
				// If the album exists and the album's artist!=current artist...
				// ...AND we're @ 2nd file or above, 
				// THEN set the album's artist (aperformer_id) to 1 ("Various"):
				if (($is_music==1) && ($track['album']!="")) {
					if ($first_file==0 && $artist_id!=$previous_performer_id) {
						$qry="SELECT * FROM album WHERE aid=".$album_id;
						$result=execute_sql($qry,0,-1,$num_rows,'');
						$row=mysql_fetch_array($result);
						if ($simulate_import==0) {
							$qry="UPDATE album SET aperformer_id=1 WHERE aid=".$row['aid'];
							$result=execute_sql($qry,0,-1,$num_rows);
							update_status(4,$details,'Album: '.$row['aname'].'-> "various" performers');
						}	
					}
				}
				// If no errors: Add the TRACK and update counters
				if ($is_music==1) {
					$now=date("U");
					// 0.6.0: FINAL check: Do we have title, artist_id and album_id already ?
					$qry="SELECT * FROM track WHERE performer_id='".$artist_id."'";
					if ($album_id<>0) {
						$qry.=" AND album_id='".$album_id."'";
					}	
					$qry.=' AND name="'.$track['title'].'"';
					$result=execute_sql($qry,0,10,$num_rows);
					if (($num_rows==0) && ($simulate_import==0)) {
						$qry="INSERT INTO track (performer_id,album_id,track_no,name,duration,last_played,times_played,year,path) ";
						$qry.="VALUES ('".$artist_id."', ";
						$qry.="'".$album_id."', '".$track['track_number']."', '";
						$qry.=addslashes($track['title'])."', "; // 0.8.6
						$qry.="'".$track['duration']."' , '".$now."' ,'0', '";
						$qry.=$track['year']."' , '".addslashes($folder[$x].$file)."')"; // 0.8.6
						$result=execute_sql($qry,0,-1,$num_rows);
						// 0.8.8: Add new music ?
                        if (isset($_POST['add_new_music_automatically'])) { // YES: Add new music automatically to a favorite list:
                            // First, we need the ID of the last track that was added (above):
                            $qry="SELECT id FROM track ORDER BY id DESC";
                            $result=execute_sql($qry,0,1,$nr);
                            if ($nr==1) {
                                $row=mysql_fetch_array($result);
                                $trackid=$row['id']; // The ID
                                $all_users='0'; // Does this apply to all users ?
                                if (isset($_POST['add_new_music_all_users'])) {
                                    $all_users='1';
                                }
                                loc_add_tr($trackid,$_POST['add_new_music_favorite_list'],$all_users,$userid);
                            }
                        }
                        // 0.8.8: ...ends.
					}	
					report_file($filename_new_stuff,'New track: <b>'.$folder[$x].$file.'</b>');
					update_status(2,$details,'New track: '.$folder[$x].$file);
					$total_new_tracks++;	
					$first_file=0;
				}	
				// 0.8.6:
				if ($is_folder==0) {
					$total_tracks++;
				}
				if ($is_music==0) {
					// 0.8.6:
					if ($is_folder==0) {
						$total_tracks_not_read++;
						update_status(4,$details,'Not read: '.$folder[$x].$file);
					} else {
						update_status(4,$details,'Not processed (it is a folder): '.$folder[$x].$file);
					}
				}		
			}	// while file=readdir	
		} //  !is(in_array..)	
		$x++;
	}	// while x<=count(folder...	
	report_file($filename_warnings,'Total warnings: <b>'.$total_warnings.'</b>');
	report_file($filename_errors,'Total errors: <b>'. $total_errors.'</b>');	
	report_file($filename_low_bitrate,'Total tracks with below desired bitrate: <b>'.
	$total_tracks_below_low_bitrate.'</b>');
	report_file($filename_new_stuff,'Number of new tracks discovered: <b>'.$total_new_tracks.'</b>');
	report_file($filename_refresh_stuff,'Number of tracks re-processed: <b>'.$total_refreshed_tracks.'</b>');
	update_status(1,$details,'<b>Finished !</b>');	
/*
******************************
						SCAN: STEP 4: BUILD + DISPLAY REPORT
******************************
*/
	update_status(1,$details,'Build+display report...');
	report_file($filename_scan_report,'<h4>*** Settings ***</h4>');
	// Complain about permissions:
	$r=get_setting('Complain about missing permissions on folders: ',$complain_permissions,'');
	report_file($filename_scan_report,$r);	
	// Delete "dead" reacords:
	$r=get_setting('Delete "dead" records:',$delete_dead_records,'');
	report_file($filename_scan_report,$r);
	// Only scan+import after:
	$r=get_setting('Active cutoff date:',$cutoff_date_active,'');
	if ($cutoff_date_active==1) {
		$r.='. Only scan+import tracks added after <b>'.date('Y-m-d',$cutoff_date).'</b>';
	}
	report_file($filename_scan_report,$r);
	// Don't import low bitrate:
	$r=get_setting('Skip tracks w. low bitrate:',$dont_import_low_bitrate,'');
	if ($dont_import_low_bitrate==1) {
			$r.='. Tracks with bitrate below <b>'.$low_bitrate_limit.'</b> bps. are ignored.';
	}	
	report_file($filename_scan_report,$r);
	// Re-process tracks:
	$r=get_setting('Re-process tracks found in database <b>and</b> on filesystem:',$refresh_tracks,'');
	report_file($filename_scan_report,$r);
	// Simulate import:
	$r=get_setting('Simulate import: ',$simulate_import,'');
	report_file($filename_scan_report,$r);
	// Missing tags - track:
	$r=get_setting('Missing tag in <b>track number</b> is: ','-',$missing_track_no);
	report_file($filename_scan_report,$r);
	// Missing tags - year:
	$r=get_setting('Missing tag in <b>year</b> is: ','-',$missing_year);
	report_file($filename_scan_report,$r);
	// Missing tags - album name:
	$r=get_setting('Missing tag in <b>album name</b> is: ','-',$missing_album_name);
	report_file($filename_scan_report,$r);
	// Missing tags - performer:
	$r=get_setting('Missing tag in <b>performer name</b> is: ','-',$missing_performer);
	report_file($filename_scan_report,$r);
	// Missing tags - track name:
	$r=get_setting('Missing tag in <b>track name</b> is: ','-',$missing_track_name);
	report_file($filename_scan_report,$r);
	// Import warnings:
	$r=get_setting('Import warnings: ',$import_warnings,'');
	report_file($filename_scan_report,$r);
	// Import errors:
	$r=get_setting('Import errors: ',$import_errors,'');
	report_file($filename_scan_report,$r);
	// 
	update_status(4,$details,'Reports finished. Add totals...');
	
	// TOTALS: 0.8.5: Added a href...
	report_file($filename_scan_report,'<h4>*** Totals ***</h4>');
	report_file($filename_scan_report,'Number of folders: <b>'.$total_folders.'</b>');
	report_file($filename_scan_report,'Number of folders not read: <b>'.$total_folders_not_read.'</b> <a href="#folders_not_read">View</a>'); // 0.8.5
	report_file($filename_scan_report,'Number of tracks: <b>'.$total_tracks.'</b>');
	report_file($filename_scan_report,'Number of new tracks: <b>'.$total_new_tracks.'</b>');	
	report_file($filename_scan_report,'Number of tracks not read: <b>'.$total_tracks_not_read.'</b>'); 
	report_file($filename_scan_report,'Warnings: <b>'.$total_warnings.'</b> <a href="#warnings">View</a>'); // 0.8.5
	report_file($filename_scan_report,'Errors: <b>'.$total_errors.'</b> <a href="#errors">View</a>'); // 0.8.5
	if ($delete_dead_records==1) {
		report_file($filename_scan_report,'"Dead" tracks: <b>'.$total_dead_tracks.'</b> <a href="#dead">View</a>'); // 0.8.5
		report_file($filename_scan_report,'"Dead" performers: <b>'.$total_dead_performers.'</b>');
		report_file($filename_scan_report,'"Dead" albums: <b>'.$total_dead_albums.'</b>');
	}	
	if ($dont_import_low_bitrate==1) {
		report_file($filename_scan_report,'Tracks below bitrate limit: <b>'.$total_tracks_below_low_bitrate.'</b> <a href="#low_bitrate">View</a>'); // 0.8.5
	}	
	report_file($filename_scan_report,'Number of tracks re-processed: <b>'.$total_refreshed_tracks.'</b> <a href="#reprocessed">View</a>'); //0.8.5
	update_status(3,$details,'Get contents of each "sub-report". Include in main report');
	// EACH SEPERATE FILE APPENDED BELOW, AS FOLLOWS:
	// Folders not read:
	//report_file($filename_scan_report,file_get_contents($filename_folders_not_read));
	update_status(3,$details,'Report: Folders not read...');	
	append_report($filename_scan_report,$filename_folders_not_read);	
	// "Dead" stuff:
	if ($delete_dead_records==1) {
//		report_file($filename_scan_report,file_get_contents($filename_dead_stuff));
		update_status(3,$details,'Report: "Dead stuff"...');	
		append_report($filename_scan_report,$filename_dead_stuff); // 0.8.1: Speling eror.... :-|
	}
	// New stuff:
//	report_file($filename_scan_report,file_get_contents($filename_new_stuff));	
	update_status(3,$details,'Report: New tracks found...');
	append_report($filename_scan_report,$filename_new_stuff);
	// Low bitrate:
	if ($dont_import_low_bitrate==1) {
//		report_file($filename_scan_report,file_get_contents($filename_low_bitrate));
		update_status(3,$details,'Report: Tracks w. low bitrate...');			
		append_report($filename_scan_report,$filename_low_bitrate);
	}	
	// Warnings:
//	report_file($filename_scan_report,file_get_contents($filename_warnings));
	update_status(3,$details,'Report: Warnings...');		
	append_report($filename_scan_report,$filename_warnings);
	// Errors:
//	report_file($filename_scan_report,file_get_contents($filename_errors));
	update_status(3,$details,'Report: Errors...');		
	append_report($filename_scan_report,$filename_errors);
	// Re-processed tracks:
//	report_file($filename_scan_report,file_get_contents($filename_refresh_stuff));
	update_status(3,$details,'Report: Re-processed tracks...');		
	append_report($filename_scan_report,$filename_refresh_stuff);

	// Clean up:
	update_status(3,$details,'Clean up...');
	@unlink($filename_new_stuff);
	@unlink($filename_folders_not_read);
	@unlink($filename_dead_stuff);	
	@unlink($filename_warnings);
	@unlink($filename_errors);
	@unlink($filename_low_bitrate);
	@unlink($filename_refresh_stuff);
	
	// Show it, - point to right location of the scan_report:
	$fn=explode('/',$filename_scan_report);
	$msg='Scan+import finished. There is a report as well in: <b>'.$filename_scan_report;
	$msg.='</b><br><a href="./tmp/'.$fn[count($fn)-1].'" target="_blank">';
	$msg.='Click here to view (it will open in a new window/tab)</a>';
	echo $msg; // Show this ON TOP of page
	update_status(1,$details,$msg); // Show on BOTTOM of page
/*
******************************
						SCAN: LAST STEP - TIMERS
******************************
*/
	$stoptimer = time()+microtime();
	$timer = round($stoptimer-$starttimer,2);
    $msg='<br><p>'.$total_folders." folders scanned in ".$timer.' seconds<br>';
    $msg.='<a href="'.$base_http_prog_dir.'/index.php?what=last_scan_date';
	$msg.='&unix_timestamp='.date("U");
	$msg.='">Click here to go back to the "Welcome" page.</a>';
	echo $msg;
	
} // if act=scan


/*
******************************
						SET UP
******************************						
*/
if ($act=='setup') {
//	echo headline('','Setup: Scan+import',''); 
 
 	echo '<FORM NAME="scanform" method="POST" action="scan2.php?act=scan">';

// Setup: Options
	echo std_table("ampjuke_content_table","ampjuke_content");
	echo '<th colspan="2">Scan+import options</th>';
	echo '<tr><td colspan="2" align="left">';
	echo '<a href="http://www.ampjuke.org/?id=faq55" target="_blank">'; // 0.8.4: Whoops...an old link had survived.
	echo 'Click here to see the FAQ-entry explaining this (will open in a new window)</a></td><tr>';
	// Complain about permissions:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Complain about missing permissions on folders:</td>';
	echo '<td>'.add_checkbox('complain_permissions','0').'</td></tr>';	// 0.8.5: Used to be "on" (1) by default.
	// Delete dead records:	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Delete "dead" records:</td>';
	echo '<td>'.add_checkbox('delete_dead_records','').'</td></tr>';
	// Cutoff date:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	$coff=get_configuration("last_scan_date");
	$ch='';
	if ($coff<>'') {
		$coff=date('Y-m-d',$coff);
		$ch=' checked';		
	}	
	echo '<td>'.add_checkbox('cutoff_date_active',$ch);
	echo 'Only scan+import tracks added after:</td>';
	echo '<td>'.add_textinput('cutoff_date',$coff,12);
	echo '<i>(YYYY-MM-DD, f.ex.: 2009-05-20)</i></td></tr>';
	// Dont import if bitrate is below xxxxxx bps.:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('dont_import_low_bitrate','');
	echo "Don't import a track if the bitrate is below:</td>";
	echo '<td>'.add_textinput('low_bitrate_limit','32000',6).' bps. <i>(Note: Integer only.';
	echo ' F.ex.: 64000 = 64kbps.)</i></td></tr>';
	// Update/refresh db w. info. from tags:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Re-process tracks found in database <b>and</b> on the filesystem:</td>';
	echo '<td>'.add_checkbox('refresh_tracks','');
	echo '<i>(Note: "Only scan+import..." and bitrate setting above overrides)';
	echo '</i></td></tr>';
	// 0.8.1: Lookup covers in folders:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Look for covers in folders:</td>';
	echo '<td>'.add_checkbox('lookup_covers_enabled','');
	echo '</td></tr>';	
	// Simulate import aka. the sissy setting:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>Simulate import (don't change database or DO anything):</td>";
	echo '<td>'.add_checkbox('simulate_import','').'</td></tr>';
    // 0.8.8: Automatically add new music to a specific favorite list:
    fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
    echo '<td>'.add_checkbox('add_new_music_automatically','1').'Automatically add new music to favorite list:</td>';
    echo '<td>'.add_textinput('add_new_music_favorite_list','new_music',40).' ';
    echo add_checkbox('add_new_music_all_users','1').'Apply to ALL users';
    echo '</td></tr>';
	// Write status messages to scrren:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Show details during scan+import:</td><td>';
	echo '<SELECT NAME="details" class="tfield">';
	echo add_select_option('1','Minimal: Very little ','1');
	echo add_select_option('2','Normal: Status on folders and new tracks,albums,performers','');
	echo add_select_option('3','Detailed: Like "normal" plus errors and warnings','');
	echo add_select_option('4','Very detailed: A LOT of things will be shown. Really.','');
	echo '</SELECT></td></tr>';
// Handle missing tags:
	$table2=1;
	echo '</table></td></tr><tr><td>';
	echo std_table("ampjuke_content_table","ampjuke_content2");
	echo '<th colspan="2" align="left">If/when tags are missing...</th>';
	// Missing track#:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td valign="top" width=30%">...a missing <b>track-number</b> is:</td><td>';
	echo '<SELECT NAME="missing_track_no" class="tfield">';
	echo add_select_option('a warning','a warning','1');
	echo add_select_option('an error','an error','');
	echo add_select_option('OK','OK','');
	echo '</SELECT></td></tr>';
	// Missing year:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td valign="top">...a missing <b>year</b> is:</td><td>';
	echo '<SELECT NAME="missing_year" class="tfield">';
	echo add_select_option('a warning','a warning','1');
	echo add_select_option('an error','an error','');
	echo add_select_option('OK','OK','');
	echo '</SELECT></td></tr>';
	// Missing album name:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td valign="top">...a missing <b>album name</b> is:</td><td>';
	echo '<SELECT NAME="missing_album_name" class="tfield">';
	echo add_select_option('a warning','a warning','');
	echo add_select_option('an error','an error','');
	echo add_select_option('OK','OK','1');
	echo '</SELECT></td></tr>';
	// Missing performer:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td valign="top">...a missing <b>performer</b> is:</td><td>';
	echo '<SELECT NAME="missing_performer" class="tfield">';
	echo add_select_option('a warning','a warning','');
	echo add_select_option('an error','an error','1');
	echo add_select_option('OK','OK','');
	echo '</SELECT></td></tr>';
	// Missing track name:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td valign="top">...a missing <b>track name</b> is:</td><td>';
	echo '<SELECT NAME="missing_track_name" class="tfield">';
	echo add_select_option('a warning','a warning','');
	echo add_select_option('an error','an error','1');
	echo add_select_option('OK','OK','');
	echo '</SELECT></td></tr>';
	// 0.8.5: Offer to edit tags right away, if possible:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>...display link to edit tags:</td><td>';
	echo add_checkbox('edit_errors','1').'</td></tr>';
	
	// Import warnings no matter what:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2"> </td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Import <b>warnings</b> to the database:</td><td>';
	echo add_checkbox('import_warnings','').' <i>("Simulate import" overrides)</i></td></tr>';
	// Import errors no matter what:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Import <b>errors</b> to the database:</td><td>';
	echo add_checkbox('import_errors','').' <i>("Simulate import" overrides)</i></td></tr>';
	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center">';
	echo '<input type="submit" value="Start scan+import"></td></tr>';
	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2"><a href="index.php?what=welcome">';
	echo '<img src="./ampjukeicons/mnu_arr.gif" border="0"> ';
	echo 'Do not do anything, just step back to the "Welcome" page</a>';
	echo '</td></tr>';


	echo '</table>';
}
?>

<script type="text/javascript">
addTableRolloverEffect('ampjuke_content','tableRollOverEffect','');
<?php
if (isset($table2)) {
?>
addTableRolloverEffect('ampjuke_content2','tableRollOverEffect','');
<?php
}
?> 
<?php
if (isset($table3)) {
?>
addTableRolloverEffect('ampjuke_content3','tableRollOverEffect','');
<?php
} 
if (isset($table4)) {
?>
addTableRolloverEffect('ampjuke_content4','tableRollOverEffect','');
<?php
}
if (isset($table5)) {
?>
addTableRolloverEffect('ampjuke_content5','tableRollOverEffect','');
<?php
}
if (isset($table6)) {
?>
addTableRolloverEffect('ampjuke_content6','tableRollOverEffect','');
<?php
}
if (isset($table7)) {
?>
addTableRolloverEffect('ampjuke_content7','tableRollOverEffect','');
<?php
}
?> 
</script>
