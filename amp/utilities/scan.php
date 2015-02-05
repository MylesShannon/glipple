<?php
die('Sorry...');
// This is the OLD scan-method in AmpJuke.
// It's not maintained anymore.
// Move/copy to parent directory if you want to use it.
// November 2008 / Michael H. Iversen.

if (!file_exists('db_new.sql')) {
	session_start();
	if (!isset($_SESSION['login'])) {
		echo 'Not logged in. <a href="login.php">Click here to login</a>.';
		exit;
	}
}

// 0.6.1: Check that we can write to db.php:
if (file_exists('db.php')) {
	if (!is_writable('db.php')) {
		echo 'The file db.php exists, but AmpJuke cannot write to it.<br>';
		echo 'Please correct and try again.';
		die();
	}
}		

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>AmpJuke - Site&Music management</title>
<meta name="generator" content="PHP Designer 2007">
<link rel="stylesheet" type="text/css" href="./ampstyles.css">
<script type="text/javascript" src="expand_collapse.js"></script>
</head>
<body>
<?php		
require("db.php");
require("sql.php");
require("disp.php");
require("configuration.php"); // 0.7.1
$count=1;
parse_str($_SERVER["QUERY_STRING"]);

/*

					SUPPORT FUNCTIONS FOR function process_files

*/					
	

function report_file($ftype,$artist,$title,$album,$path) {
	$ret='<tr><td>"Dead" track:  Performer: <b>'.$artist.'</b> Name:<b>'.$title.'</b>';
	if ($album!="") {
		$ret.=' Album:<b>'.$album.'</b>';
	}		
	$ret.='</td></tr>';
	return $ret;
}	


function find_key($what,$key) {
// Input: "key" we want to find the corresponding ID for in a given table (="what")
$ret=0;
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
return $ret;
}	



function find_keys($artist,$album,$trk_name) {
$ret=0;
$art_id=find_key('performer',$artist);
$alb_id=find_key('album',$album);
$qry='SELECT * FROM track WHERE ';
if ($album!="") { 
		$qry.='performer_id='.$art_id.' AND album_id='.$alb_id.' AND name="'.$trk_name.'"';
	} else {
		$qry.='performer_id='.$art_id.' AND name="'.$trk_name.'"';
	}	
$result=execute_sql($qry,0,1,$num_rows,'');
if ($num_rows<>0) { $ret=1; }
return $ret;
}


function add_key($what,$key,$f_key) {
if ($what=='performer') {
	$qry='INSERT INTO performer VALUES("","'.$key.'")';
	}
if ($what=='album') {
	$qry='INSERT INTO album VALUES("","'.$f_key.'","'.$key.'")';
	}
$result=execute_sql($qry,0,-1,$num_rows,'');
}	

function get_value($arr) {
	$ret="";
	if (is_array($arr)) {
	foreach ($arr as $k => $v) { $ret=$v; }
	}
	return $ret;
}	

require_once("./getid3/getid3.php");

function use_getid($dir,&$w,&$ws,&$e,&$es,&$art,&$alb,&$tit,&$yea,&$dur,&$pat,&$tra,$print_details,$extension) {
$ws="";
$es="";
$getID3 = new getID3;
$ThisFileInfo = $getID3->analyze($dir);
    getid3_lib::CopyTagsToComments($ThisFileInfo);
//	var_dump($ThisFileInfo); Just used for debugging purposes

	// artist from any/all available tag formats:
    $art=@$ThisFileInfo['comments_html']['artist'][0];
    if ($art=="") { $e++; $es.=" No artist: ".$dir.'<br>'; }

    // title:
    $tit=@$ThisFileInfo['comments_html']['title'][0];
    if ($tit=="") { $e++; $es.=" No title: ".$dir.'<br>'; }

    // album: 
    $alb=@$ThisFileInfo['comments_html']['album'][0];
    if ($alb=="") {
        $w++; $ws.=" No album: ".$dir.'<br>';
    }

    // year: 
    $yea=@$ThisFileInfo['comments_html']['year'][0];
    if ($extension=='ogg') { // 0.3.8: ogg-extension in this array uses DATE, not YEAR...
    	$yea=@$ThisFileInfo['comments_html']['date'][0];
	}    	
    if ($yea=="") {
        $w++; $ws.=" No year: ".$dir.'<br>';
    }

    // track#:
    $tra=@$ThisFileInfo['comments_html']['track_number'][0];
    if ($extension=='ogg') { // 0.3.8: ogg-extension in this array uses "TRACKNUMBER", not TRACK...
    	$tra=@$ThisFileInfo['comments_html']['tracknumber'][0];
	}    
	if ($tra=="") { // 0.6.1: Might be mp3 w. ID3v1 tags...try TRACK...
		$tra=@$ThisFileInfo['comments_html']['track'][0];
	}	
	
	// 0.7.3: If tra is still empty: get/guess a value for tra using DIGITS in FILENAME:
	if ($tra=="") {
		$pa=explode("/", $ThisFileInfo['filenamepath']);
		$pb=$pa[count($pa)-1]; // FILNAME is (must) be the last item in array
		$pc=explode(".",$pb); // Get rid of any extensions (m4a contains a digit...):
		$pd=$pc[0]; // Get the name of file excl. extension
		$tra=preg_replace("/[^0-9]/","", $pd);  // Set/get the digits from name (pd)
	}	
	
    if ($tra=="") {
        $w++; $ws.=" No track number: ".$dir.'<br>';
    }

    // the rest:
    $pat=$ThisFileInfo['filenamepath'];
    $dur=$ThisFileInfo['playtime_string'];
    if (strlen($ThisFileInfo['playtime_string'])<5) { $dur='0'.$dur; }
    if ($print_details==1) {
        if ($ws<>"") {
            echo '<font color="RED"><b>'.$ws.'</b><font color="black">';
        }
    }  
} // function use_getid


/*


							ACTUAL / REAL STUFF HERE:

							

*/
function process_files($dir,&$ok,&$warnings,&$errors,&$act,$print_details,$cutoff_date) {
 // 0.7.1: cutoff_date introduced
$errors=0;
$ok=0;
$warnings=0;
$errors=0;
$total=0;
$first_file=1;
$handle=opendir($dir);
$error_buf="<tr><td>Dir=".$dir."</td>";

while ($file=readdir($handle)) {
	$is_music="1";
	$extension=get_file_extension($file);
    // 0.3.1: We now allow these extenstions: "mp3", "ogg", "wma" and "ape":
	// 0.7.3: Added: m4a:
	if (($extension!="mp3") && ($extension!="ogg") && ($extension!="wma") && 
	($extension!="ape") && ($extension!="m4a")) { 
		$is_music="0";
		$error_flag=4;
	}

	// 0.6.1: Disp. error, if file cannot be read (permissions):
	if (($is_music=="1") && (!is_readable($file))) {
		echo '<br>Error: Cannot read <b>'.$file.'</b> (missing permissions)';
	}

	// 0.7.1: Treat files w. modification time BEFORE cutoff_date as "non_music":
	if (($file!="." && $file!="..") && (filemtime($file)<$cutoff_date)) {
	//  Uncomment 2 lines beneath this one, if you REALLY want to see what's considered 'too old':
	//	echo $file.'<br>is too old (ctime='.filectime($file).' '.date('Y-m-d',filemtime($file));
	//	echo '<br> cutoff_date='.$cutoff_date.' '.date('Y-m-d',$cutoff_date).'<br><br>';
		$is_music=0; // ...not music (even though we know it is), but makes scan happen *faster*...
	}	

// Skip ".", ".." & non-MUSIC-files
	if (($file!="." && $file!="..") && ($is_music=="1")) { 


// Step 0: Read a file:
		$error_flag=0;
		use_getid($dir.$file,$warnings,$ws,$errors,$es,$artist,$album,$title,$year,
		$duration,$path,$track_no,$print_details,$extension);

// Step 0.5: 0.6.0: Check that the FILE doesn't exist already in the database:
		$qry='SELECT * FROM track WHERE path="'.$dir.$file.'"';
		$result=execute_sql($qry,0,1,$num_rows);
		if ($num_rows>0) { // The filename is already in the db...
			$error_flag=5;
		}	
		
// Step 1: Check that title/artist contains something:
		if (($artist=="" || $title=="") && ($error_flag==0)) { // 0.6.0: Added check for error_flag
			$error_flag=1;
		}	
// Step 2: If album isn't empty then find out if title, album & artist exists:
		if (($album!="") && ($error_flag==0)) { // 0.6.0: added check for error_flag
			$title_exists=find_keys($artist,$album,$title);
			if (($title_exists==1) && ($act!="analyze")) {
				$error_flag=2;
			}
		}	
// Step 3: If album is empty then find out if title & artist exists:
		if ($album=="" && $error_flag==0) {
			$title_exists=find_keys($artist,$album,$title);
			if (($title_exists==1) && ($act!="analyze")) {
				$error_flag=3;
			}
		}
// Step 4: We can now start to add some data...
// If we cannot find the artist, then add it:
		if ($error_flag==0) {
			$artist_id=find_key('performer',$artist);
			if (($artist_id==0) && ($act!="analyze")) {
				add_key('performer',$artist,'');
				$artist_id=find_key('performer',$artist);
			}
		}
// Step 5: We can now add some more data...
// If we cannot find the album, then add it:
		if ($error_flag==0 && $album!="") {
		// First, store the previous performer's id (used later):
			if ($first_file!=1) {
				$qry="SELECT * FROM album WHERE aid=".$album_id;
				$result=execute_sql($qry,0,1,$num_rows,'');
				$row=mysql_fetch_array($result);
				$previous_performer_id=$row['aperformer_id'];
			}	
		// Second, add the album:
			$album_id=find_key('album',$album);
			if (($album_id==0) && ($act!="analyze")) {
				add_key('album',$album,$artist_id); // we have artist_id from previous step
				$album_id=find_key('album',$album);
			}	
		}		
		// Optional, Third: IF the album IS empty (ie. bought/"borrowed"/standalone track, set the album_id to 0
		if ($error_flag==0 && $album=="") {
			$album_id=0;
		}	
// Step 6: This is a tricky part...
// If the album exists and the album's artist!=current artist and we're @ 2nd file or above, 
// THEN set the album's artist (aperformer_id) to 1 ("Various"):
		if ($error_flag==0 && $album!="") {
			if ($first_file==0 && $artist_id!=$previous_performer_id) {
				$qry="SELECT * FROM album WHERE aid=".$album_id;
				$result=execute_sql($qry,0,-1,$num_rows,'');
				$row=mysql_fetch_array($result);
				if ($act!="analyze") {
					$qry="UPDATE album SET aperformer_id=1 WHERE aid=".$row['aid'];
					$result=execute_sql($qry,0,-1,$num_rows);
				}	
			}
		}

// Step 7: If no errors: Add the TRACK and update counters
		if ($error_flag==0) {
			$now=date("U");
			if ($act!="analyze") {
				// 0.6.0: FINAL check: Do we have title, artist_id and album_id already ?
				$qry="SELECT * FROM track WHERE performer_id='".$artist_id."'";
				$qry.=" AND album_id='".$album_id."'";
				$qry.=" AND name='".$title."'";
				$result=execute_sql($qry,0,10,$num_rows);
				if ($num_rows==0) {
					$qry='INSERT INTO track VALUES("","'.$artist_id.'","'.$album_id.'","'.$track_no.'","'.$title.'",';
					$qry.='"'.$duration.'","'.$now.'","0","'.$year.'","'.$dir.$file.'")';
					$result=execute_sql($qry,0,-1,$num_rows);
				}	
			}	
			$ok++;	
			$first_file=0;
//			echo 'New: '.$dir.$file.'<br>';
		}	
// Step 8: If errors: Print them.				 
		if (($error_flag!=0) && ($error_flag!=5) && ($print_details=="1")) {
			if ($error_flag==1) {
				$errmsg="Title and/or artist is empty: ";
			}
			if ($error_flag==2) {
				$errmsg="Title, album and artist already exists in the database:";
			}
			if ($error_flag==3) {
				$errmsg="Track already in the database:";
			}
			$errmsg.="<br>Track#=$track_no";
			$errmsg.="<br> Artist=$artist";
			$errmsg.="<br>Title=$title";
			$errmsg.="<br>Album=$album";
			$errmsg.="<br>Year=$year";
			$errmsg.="<br>Duration=$duration";
			$errmsg.="<br>Path&file=$dir$file<br>";
			print '<p class="note"><font color="red">Error:<font color="black"><br>'.$errmsg.'</p>';
			$errors++;
		}			
// Step 9: Update counters:
		$total++;
		}
	} // if file!=. & ..	
} // while file=readdir...		


function listall($dir,&$mastercount,&$act,$print_details,$cutoff_date) { 
    $dir_files = $dir_subdirs = array(); 
    chdir($dir); 
    $handle = @opendir($dir) or die( "Directory \"$dir\"not found."); 
    $count=0; 
     // Loop through all directory entries, construct   
     // two temporary arrays containing files and sub directories 
    while($entry = readdir($handle)) { 
        if(is_dir($entry) && $entry !=  ".." && $entry !=  ".") { 
        	$dir_subdirs[] = $entry; 
        } 
        elseif($entry !=  ".." && $entry !=  ".") {    
            $dir_files[] = $entry; 
            $count++;
        } 
    } // while... 

    sort($dir_files); 
    sort($dir_subdirs); 

	if ($count>0) {  
		print "<tr>";
		print '<td valign="top">'.$mastercount.'</td>';
		print '<td valign="top"><b>'.$dir.'</b>';
		print '<td valign="top">'.$count.' files</td><td>';
		process_files($dir,$ok,$warnings,$errors,$act,$print_details,$cutoff_date); // 0.7.1: cutoff...
		print '</td><td valign="top">New:';
		if ($ok<>0) { echo '<b class="note"><font color="blue">'; }
		echo $ok.'</b><font color="black"> Warnings:'.$warnings.' Errors:'.$errors;
        // 0.3.1: adds a link in case of warnings/errors
/*        
        if ((($warnings<>0) || ($errors<>0)) && ($print_details==0)) {
            print '<br><a href="scan.php?act=analyze&clickdir='.$dir.'" target="_blank">';
            print 'Click here for more information.</a>';
        }
*/        
		print "</td></tr> \n";	
		if (isset($_POST['show_progress'])) {
			flush(); ob_flush();
		}
		$mastercount++;
	}
     // Traverse sub directories 
    for($i=0; $i<count($dir_subdirs); $i++) { 
        listall( "$dir$dir_subdirs[$i]/",$mastercount,$act,$print_details,$cutoff_date); 
    } 
    closedir($handle); 
} 



function checkall($dir) { 
// 0.6.1: Basically a (reduced) copy of "listall" above with the exception that we just traverse
// the directory structure and reporting if we can read from a dir. or not:

// 0.6.1: Damn....HAVE to do this -> recursion. Not good practice - I know :-(
	global $err_count; 
	global $folder_count;

    $dir_files = $dir_subdirs = array(); 
    chdir($dir); 
    if ($handle = @opendir($dir)) {
    	while($entry = readdir($handle)) {    	
	        if (is_dir($entry) && $entry !=  ".." && $entry !=  ".") { 
    	    	$dir_subdirs[] = $entry; 
        	} 
	        elseif($entry !=  ".." && $entry !=  ".") {    
    	        $dir_files[] = $entry; 
        	    $count++;
	        } 

	    } // while... 

	    sort($dir_files); 
    	sort($dir_subdirs); 

	 	echo '<tr><td colspan="5">';
 		if ((is_dir($dir)) && (!is_readable($dir))) {
 			echo '<b class="note">Error reading: '.$dir.'</b>';
 			$err_count++;
		} else {
		 	echo 'OK: '.$dir;
		}	 	
		print "</td></tr> \n";	
		if (isset($_POST['show_progress'])) {
			flush(); ob_flush();
		}
		$folder_count++;
    	 // Traverse sub directories 
	    for($i=0; $i<count($dir_subdirs); $i++) { 
    	    checkall("$dir$dir_subdirs[$i]/"); 
	    } 
	    closedir($handle); 
	} else { 
		echo '<tr><td colspan="5"> <br><b class="note">Error reading: '.$dir.'</b><br> </td></tr>';
		$err_count++;
	}	
} 


/*



        DETERMINE WHAT SHOULD BE DONE:



*/
if (($act=="rebuild") || ($act=="analyze")) {
	// rebuild: we want to re-build the database (f.ex.: new music was added).
	// analyze: we want to have a closer look at a directory.
	// We'll have to allow "inifinite" execution time of scripts, due to possible large amounts of data 
	//(that is: your music collection is BIG and/or you're on a slow server), furhtermore we want only
	// to have "real" errors printed out:
	set_time_limit(0); // Believe me: you want this !
	error_reporting(1); // Believe me: you also want this !
	$starttimer = time()+microtime(); // 0.7.1: Used to calc. the total duration
	echo '<div class="note">Note:Be very, very patient, - this might take some time !</div>';
	// check if we want to look WITHIN the base_music_dir:
	if (isset($_POST['subdir'])) {
		$base_music_dir.=$_POST['subdir'];
	}	

    $print_details=0;
    if (isset($_POST['print_details'])) {
    	$print_details=1;
    }
		
    if (isset($clickdir)) {
        $base_music_dir=substr($clickdir,0,strlen($clickdir)-1);
        $print_details=1;
    }
    
    // 0.7.1: Do we have a cutoff date ?
    $cutoff_date=0;
    if (isset($_POST['cutoff_date_active'])) {
		$cutoff_date=strtotime($_POST['cutoff_date']);
   	 	$disp_cutoff_date=mydate($cutoff_date);		
	}	
    
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// 0.6.1: CHECK we can at least READ from the directories, BEFORE further processing:
	echo '<tr><td colspan="5" align="center"><b>Check folders</b></td></tr>';
	$err_count=0;
	$folder_count=0;
	checkall($base_music_dir.'/'); 
	echo '<tr><td colspan="5">Finished checking folders. A total of '.$folder_count.' folders was checked.';
	echo '</td></tr>';
	if ($err_count<>0) { // Some folders cannot be read: Halt the processing
		echo '<tr><td colspan="5"><b class="note">';
		echo 'Cannot read from one or more folders (see above).<br>';
		echo "This is most likely because your web-user doesn't have permission to read from";
		echo ' the folders listed (with an error) above.<br>';
		echo 'Please fix it, and then <a href="./scan.php?act=rescan">';
		echo ' click here to try again</a>.<br><br>';
		echo 'More information can be found in <a href="http://www.ampjuke.org/faq.php?q_id=36" target="_blank">';
		echo 'this FAQ-entry</a>.<br>';
		die('</td></tr>');
	}	

	// 0.6.3: MOVED in order to get rid of "dead" stuff happens BEFORE looking for new music:
    if (isset($_POST['delete_dead_records'])) { // yes, go on with it:
		echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
		echo '<tr><td colspan="5"><b>Delete "dead" records</b></td></tr>';
		$qry="SELECT * FROM track";
		$result=execute_sql($qry,0,100000000,$num_rows);
		$count=0;
		$total_dead=0;
		while ($row=mysql_fetch_array($result)) {
			if (!file_exists($row['path'])) { // do we have a "dead" record: deal with it...
				echo report_file('"Dead"',
				get_performer_name($row['performer_id']),$row['name'],
				get_album_name($row['album_id']),$row['path']);
				$total_dead++;
			 	// First, delete from TRACK-table:
				$delqry='DELETE FROM track WHERE id='.$row['id'];
				$delresult=execute_sql($delqry,0,-1,$nr);
				// Second, delete the id from tha FAV-table as well (if it exists):
				$delqry="DELETE FROM fav WHERE track_id=".$row['id'];
				$delresult=execute_sql($delqry,0,-1,$nr);
				// Third, get rid of it in the queue (if it exists). Note: Added in 0.7.4
				$delqry="DELETE FROM queue WHERE track_id=".$row['id'];
				$delresult=execute_sql($delqry,0,-1,$nr);
			}	
			$count++;
		}			
		echo '<tr><td colspan="5">Finished. <b>'.$count.'</b> tracks processed. ';
		echo '<b>'.$total_dead.'</b> "dead" tracks deleted.';
		// next step: get rid of "empty" albums:
		$qry="SELECT * FROM album";
		$result=execute_sql($qry,0,10000000,$num_rows);
		$count=0;
		$total_dead=0;
		while ($row=mysql_fetch_array($result)) {
			$chkqry="SELECT * FROM track WHERE album_id='".$row['aid']."'";
			$chkresult=execute_sql($chkqry,0,10000000,$nr);
			if ($nr==0) { // there are no tracks for this album: delete it:
				$p=get_performer_name($row['aperformer_id']);
				echo '<tr><td colspan="3">Album: <b>'.$row['aname'].'</b> ('.$p.') deleted</td></tr>';
				$delqry="DELETE FROM album WHERE aid='".$row['aid']."'";
				$delresult=execute_sql($delqry,0,-1,$n);
				$total_dead++;
			}
			$count++;
		}
		echo '<tr><td colspan="5">Finished. <b>'.$count.'</b> albums processed. ';
		echo '<b>'.$total_dead.'</b> "dead" albums deleted.';
		// final step: get rid of "empty" performers:
		$qry="SELECT * FROM performer";
		$result=execute_sql($qry,0,100000000,$num_rows);
		$count=0;
		$total_dead=0;
		while ($row=mysql_fetch_array($result)) {
			$chkqry="SELECT * FROM track WHERE performer_id='".$row['pid']."'";
			$chkresult=execute_sql($chkqry,0,10000000,$nr);
			if ($nr==0) { // this performer does not have any tracks: delete the performer:
				echo '<tr><td colspan="3">Performer: <b>'.$row['pname'].'</b> deleted</td></tr>';
				$delqry="DELETE FROM performer WHERE pid='".$row['pid']."'";
				$delresult=execute_sql($delqry,0,-1,$n);
				$total_dead++;
			}
			$count++;
		}
		echo '<tr><td colspan="5">Finished. <b>'.$count.'</b> performers processed. ';
		echo '<b>'.$total_dead.'</b> "dead" performers deleted.';
    	echo '</table>';
    }	
    // End of deletion of "dead" records

	$count=1;
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">'; // 0.6.3
	echo '<tr><td colspan="5" align="center"><b>Scan+import music</b></td></tr>';
	// 0.7.1: If cutoff_date is active: display it:
	if (isset($disp_cutoff_date)) {
		echo '<tr><td colspan="5">&nbsp<br><b class="note">Note: Tracks added/modified before ';
		echo $disp_cutoff_date.' will be ignored.</b><br>&nbsp</td></tr>';
	}
	listall($base_music_dir.'/',$count,$act,$print_details,$cutoff_date); // 0.7.1: cutoff_date
	echo '</table><br>';
    $count--;

	// 0.7.1: Calc. the total duration:
	$stoptimer = time()+microtime();
	$timer = round($stoptimer-$starttimer,2);
   	echo '<div class="note">'.$count." folders scanned in ".$timer.' seconds<br></div>';
    echo '<a href="'.$base_http_prog_dir.'/index.php?what=last_scan_date';
	echo '&unix_timestamp='.date("U");
	echo '">Click here to continue.</a>';
}

if ($act=="configure") { // we want to display options:
//	require("configuration.php"); 0.7.1
// 0.7.5: Changed all sections to expand/collapse weh displaying/editing the configuration
	echo '<p class="note" align="center">AmpJuke configuration</p>';
?>
	<p>Expand all: <img src="./ampjukeicons/expandall.gif" id="exp" onclick="cfg_expand_collapse_all('1')">
	<br>Collapse all: 
	<img src="./ampjukeicons/collapseall.gif" id="exp" onclick="cfg_expand_collapse_all('0')">
	
	

<?php	
	echo '<FORM NAME="cfgform" method="POST" action="scan.php?act=write">';

//
//	
// DATABASE STUFF:
//
//
// 0.7.5: Expand/collapse:
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif1" onclick="handleClick('to_col1','gif1')">
	Database options
	<div id="to_col1" style="display:none">
<?php

	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	echo '<tr><td>Database host:</td>';
	echo '<td><input type="text" name="db_host" class="tfield" value="';
	echo get_configuration("db_host").'" size="40">';
	echo '</td></tr>';

	echo '<tr><td>Database user:</td>';
	echo '<td><input type="text" name="db_user" class="tfield" value="';
	echo get_configuration("db_user").'" size="40">';
	echo '</td></tr>';

	echo "<tr><td>Database user's password:</td>";
	echo '<td><input type="text" name="db_password" class="tfield" value="';
	echo get_configuration("db_password");
    echo '" size="40">';
	echo '</td></tr>';

	echo '<tr><td>Database name:</td>';
	echo '<td><input type="text" name="db_name" class="tfield" value="';
	echo get_configuration("db_name").'" size="40">';
	echo '</td></tr>';

	// 0.7.0: In order to AVOID the prefix-setting gets lost after editing
	// and saving the configuration, just populate whatever ampjuke_tbl_prefix is:
	if ((!file_exists("db_new.sql")) && (get_configuration("ampjuke_tbl_prefix")<>'')) {
		echo '<input type="hidden" name="ampjuke_tbl_prefix" value="';
		echo get_configuration("ampjuke_tbl_prefix").'">';
	}	
	// 0.7.1: To avoid losing last_scan_date (like the prefix-situation above), the
	// same "trick" is applied to last_scan_date:
	if (get_configuration("last_scan_date")!='') {
		echo '<input type="hidden" name="last_scan_date" value="';
		echo get_configuration("last_scan_date").'">';
	}
	
	if (file_exists("db_new.sql")) {
		echo "<tr><td>Create an empty database:</td>";
		echo '<td><input type="checkbox" name="createdb" value="1" class="tfield">';
		echo '<b>Warning:</b> If you select an existing database, <b>everything</b> within ';
        echo 'it will be <b>deleted</b> !<br>';
		echo '</td></tr>';
		echo "<tr><td>Create empty tables within the database:</td>";		
		echo '<td><input type="checkbox" name="createtbl" value="1" class="tfield">';
		echo '</td></tr>';
		// 0.6.7: Prefix tables:
		echo "<tr><td>Prefix tablenames:</td>";		
		echo '<td><input type="text" name="ampjuke_tbl_prefix" value="ampjuke_" class="tfield">';
		echo '</td></tr>';	
	} else {
		echo '<tr><td colspan="2">Rename "<b>db_new.php</b>" to "<b>db_new.sql</b>" ';
		echo 'if you want ';
        echo 'the option to create a new database and/or new tables from scratch<br>';
		echo 'If your database & tables already exists you might as well delete "';
		echo '<b>db_new.php</b>"';
		echo '</td></tr>';
	}	


//
//
// DIR. STUFF:	
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif2" onclick="handleClick('to_col2','gif2')">
	Location of program files & your music
	<div id="to_col2" style="display:none">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
// Location of music:
	echo '<tr><td valign="top">"Base" directory where your music files are located:</td>';
	echo '<td><input type="text" name="base_music_dir" class="tfield" ';
    echo 'value="'.get_configuration("base_music_dir").'" size="80">';
	echo '<br><b><a href="http://www.ampjuke.org/faq.php?q_id=12" target="_blank">';
	echo 'Click here for more information about this setting</a></b>.';    
    echo '<br>Note: the current/absolute directory is: ';
	echo '<b><font color="red">'.getcwd().'</b><font color="black">';
	echo ' (might be useful if you install on an ISP server thats not your own...)';
	echo '<br>Remember: <b>No</b> trailing slash';
	echo ' & Absolute path. F.ex.: /home/michael/my_music';
	echo '</td></tr>';
	echo '<tr><td valign="top">HTTP-Location of program files:</td>';
	echo '<td><input type="text" name="base_http_prog_dir" class="tfield" ';
	$d=get_configuration("base_http_prog_dir");
	if ($d=="") {
		$d='http://'.$_SERVER["HTTP_HOST"].str_replace("/scan.php?act=configure","",$_SERVER["REQUEST_URI"]);
	}	
    echo 'value="'.$d.'" size="80">';
	echo '<br><b>Examples:</b>';
	echo 'http://www.yourhost.com/location-of-ampjuke, ';
	echo 'http://www.somehost.com/ampjuke';
	echo '</td></tr>';


//
//
// 0.6.0: DOWNLOAD&COMPRESS OPTIONS: (actually a mix of old/new options w. their own section now)
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif3" onclick="handleClick('to_col3','gif3')">
	Download & Upload options
	<div id="to_col3" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// 0.4.2: Keep extension on files ?
    echo '<tr><td valign="top"><br>Keep extension on downloaded/streamed music:</td>';
	echo '<td><b><a href="http://www.ampjuke.org/faq.php?q_id=24" target="_blank">';
	echo 'Click here for more information about download & compression settings.</a></b>.';
    echo '<br><input type="checkbox" name="keep_extension" class="tfield" ';
    $c=get_configuration("keep_extension");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';
	// 0.6.0: Compress multiple files using:
    echo '<tr><td valign="top">Location of "tar" incl. compression parameters:</td>';
    echo '<td><input type="text" name="compress_command" class="tfield" value="';
    echo get_configuration("compress_command");
	echo '" size="25">';    	
	echo '</td></tr>';
	// 0.6.0: Compress one file when downloading ?
    echo '<tr><td valign="top">When downloading one track, do not compress:</td>';
    echo '<td><input type="checkbox" name="dont_compress_one_file" class="tfield" ';
    $c=get_configuration("dont_compress_one_file");
    if ($c=="1") {
	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';
//
//    
// 0.6.1: UPLOAD section:   
//
//
	echo '<tr bgcolor="#abcdef"><td colspan="5" align="center">';
	echo '</td></tr>';	    
	// Allow upload whatsoever ?
	echo '<tr><td valign="top"><br>Allow upload:</td>';
	echo '<td>';
	echo '<b><a href="http://www.ampjuke.org/faq.php?q_id=37" target="_blank">';
	echo 'Click here for more information about upload settings</a></b>.<br>';
	echo '<input type="checkbox" name="allow_upload" class="tfield" ';
	$c=get_configuration("allow_upload");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';
	// Max. number of files to upload in one go:
    echo '<tr><td valign="top">Max. number of files to upload each time:</td>';
    echo '<td><input type="text" name="max_upload_files" class="tfield" value="';
    echo get_configuration("max_upload_files");
	echo '" size="4">';
	echo '</td></tr>';
	// CHMOD uploaded files to...
    echo '<tr><td valign="top">After upload, CHMOD files to:</td>';
    echo '<td><input type="text" name="upload_chmod" class="tfield" value="';
    echo get_configuration("upload_chmod");
	echo '" size="4">';    	
	echo '</td></tr>';
//
//
// 0.6.1: LAST.FM section (aka. related performers):
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif4" onclick="handleClick('to_col4','gif4')">
	Related performers
	<div id="to_col4" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// Allow disp. related performers ?
	echo '<tr><td valign="top"><br>Display related performers:</td>';
    echo '<td><b><a href="http://www.ampjuke.org/faq.php?q_id=41" target="_blank">';
    echo 'Click here for more information about settings for related performers.</a></b><br>';
	echo '<input type="checkbox" name="lastfm_allow_related" class="tfield" ';
	$c=get_configuration("lastfm_allow_related");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Max. number of related performers to display:
    echo '<tr><td valign="top">Max. number of related performers:</td>';
    echo '<td><input type="text" name="lastfm_max_related_artists" class="tfield" value="';
    echo get_configuration("lastfm_max_related_artists");
	echo '" size="4">';
	echo ' </td></tr>';
	// Threshold level:
    echo '<tr><td valign="top">Minimum match score:</td>';
    echo '<td><input type="text" name="lastfm_min_related_match" class="tfield" value="';
    echo get_configuration("lastfm_min_related_match");
	echo '" size="4">';
	// Days to cache:
    echo '<tr><td valign="top">Cache related performers locally for:</td>';
    echo '<td><input type="text" name="lastfm_cache_days" class="tfield" value="';
	$d=get_configuration("lastfm_cache_days");
	if (!is_numeric($d)) {
		$d=30;
	}
	echo $d;
	echo '" size="2"> (days)';	
	echo '</td></tr>';
	

//
//
// 0.7.2: Submit streamed tracks to last.fm	
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif5" onclick="handleClick('to_col5','gif5')">
	Submit tracks to last.fm
	<div id="to_col5" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	
	// Allow submission ?
	echo '<tr><td valign="top"><br>Allow streamed tracks to be submitted:</td>';
    echo '<td><b><a href="http://www.ampjuke.org/faq.php?q_id=51" target="_blank">';
    echo 'Click here for more information about submission of streamed music to last.fm</a></b><br>';
	echo '<input type="checkbox" name="lastfm_allow_submission" class="tfield" ';
	$c=get_configuration("lastfm_allow_submission");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Allow local users ?
	echo '<tr><td valign="top">Allow last.fm username/password in personal settings:</td>';
	echo '<td><input type="checkbox" name="lastfm_allow_local_users" class="tfield" ';	
	$c=get_configuration("lastfm_allow_local_users");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Default last.fm username & password:
	echo '<tr><td valign="top">Default last.fm username:</td>';
	echo '<td><input type="text" name="lastfm_default_username" class="tfield" value="';
	echo get_configuration("lastfm_default_username").'"></td></tr>';
	echo '<tr><td valign="top">Default last.fm password:</td>';
	echo '<td><input type="text" name="lastfm_default_password" class="tfield" value="';
	echo get_configuration("lastfm_default_password").'"></td></tr>';		


//
//
// 0.6.4: NOW PLAYING section
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif6" onclick="handleClick('to_col6','gif6')">
	"Now playing"
	<div id="to_col6" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// Allow disp. of "now playing" ?
	echo '<tr><td valign="top"><br>Allow display of "Now playing":</td>';
    echo '<td><b><a href="http://www.ampjuke.org/faq.php?q_id=42" target="_blank">';
    echo 'Want to get this right ? The FAQ has detailed information about "Now playing".</a></b><br>';
	echo '<input type="checkbox" name="allow_now_playing" class="tfield" ';
	$c=get_configuration("allow_now_playing");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Display mini-cover ?
    echo '<tr><td valign="top">Display album cover:</td>';
    echo '<td><input type="checkbox" name="now_playing_disp_cover" class="tfield" ';
	$c=get_configuration("now_playing_disp_cover");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Reduce size of album images:
    echo '<tr><td valign="top">Reduce size of album images to:</td>';
    echo '<td>Width:<input type="text" name="now_playing_dimension_w" class="tfield" value="';
    echo get_configuration("now_playing_dimension_w");
	echo '" size="5">  ';
	echo 'Height:<input type="text" name="now_playing_dimension_h" class="tfield" value="';
    echo get_configuration("now_playing_dimension_h");
	echo '" size="5">';    
	echo ' </td></tr>';
	// Update/refresh rate:
    echo '<tr><td valign="top">Update interval :</td>';
    echo '<td><input type="text" name="now_playing_update_rate" class="tfield" value="';
    echo get_configuration("now_playing_update_rate");
	echo '" size="5">';
	echo ' (Note: value is entered as <i>milliseconds</i>. 1000=1sec.)</td></tr>';	
	// popout window:
    echo '<tr><td valign="top">"Popout" window dimensions:</td>';
    echo '<td>Width:<input type="text" name="popout_width" class="tfield" value="';
    echo get_configuration("popout_width");
	echo '" size="5"> pixels.  ';
	echo 'Height:<input type="text" name="popout_height" class="tfield" value="';
    echo get_configuration("popout_height");
	echo '" size="5">';	
	echo ' pixels</td></tr>';
	// 0.6.6: Light update:
    echo '<tr><td valign="top">Use "light update" on these mediaplayers :</td>';
    echo '<td><input type="text" name="np_light_update" class="tfield" value="';
    echo get_configuration("np_light_update");
	echo '" size="60">';
	echo ' (Note: Seperate each entry with an asterisk: *)</td></tr>';	
	// 0.6.6: Enable updates OR Display message when playing automatically:
    echo '<tr><td valign="top">During Automatic play:</td>';
    echo '<td valign="top">Continue updating "Now playing": ';
    echo '<input type="checkbox" name="np_update_automatic_play" class="tfield" ';
    $c=get_configuration("np_update_automatic_play");
    if ($c=="1") {
    	echo 'checked';
    }
	echo '> <b>OR</b> display this message:';	
    echo '<input type="text" name="np_light_update_msg" class="tfield" value="';
    echo get_configuration("np_light_update_msg");
	echo '" size="40"></td></tr><tr><td colspan="4" align="center"><i>';
	echo 'Note: If you change anything in "Now playing" (this section) you must also ';
	echo 'select/play some music in order to have the changes applied.';
	echo '</i></td></tr>';		

//
//
//	0.7.0: LAME / TRANSCODING SECTION
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif7" onclick="handleClick('to_col7','gif7')">
	Transcoding/downsampling
	<div id="to_col7" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// Enabled ?
	echo '<tr><td valign="top"><br>Enable transcoding/downsampling:</td>';
    echo '<td><b><a href="http://www.ampjuke.org/faq.php?q_id=48" target="_blank">';
    echo 'Click here for more information about downsampling/transcoding.</a></b><br>';
	echo '<input type="checkbox" name="lame_enabled" class="tfield" ';
	$c=get_configuration("lame_enabled");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Path (to LAME):
    echo '<tr><td valign="top">Absolute path to program (i.e. to lame):</td>';
    echo '<td><input type="text" name="lame_path" class="tfield" value="';
    echo get_configuration("lame_path");
	echo '" size="20"></td></tr>';
	// Downsample parameters, default:
    echo '<tr><td valign="top">Default parameters :</td>';
    echo '<td><input type="text" name="lame_parameters" class="tfield" value="';
    echo get_configuration("lame_parameters");
	echo '" size="40"></td></tr>';

//
//
//	0.7.3: SPECIAL EXTENSIONS (M4A,MP4 ETC.)
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif8" onclick="handleClick('to_col8','gif8')">
	Special extensions
	<div id="to_col8" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// Enabled ?
	echo '<tr><td valign="top"><br>Enable handling of special extensions (m4a, mp4 etc.):</td>';
    echo '<td><b><a href="http://www.ampjuke.org/faq.php?q_id=54" target="_blank">';
    echo 'Click here for more information about special extensions (f.ex. m4a, mp4).</a></b><br>';
	echo '<input type="checkbox" name="special_extensions_enabled" class="tfield" ';
	$c=get_configuration("special_extensions_enabled");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Array of special extensions:
    echo '<tr><td valign="top">Special extensions:</td>';
    echo '<td><input type="text" name="special_extensions" class="tfield" value="';
    echo get_configuration("special_extensions");
	echo '" size="20"></td></tr>';
	// Update now playing:
    echo '<tr><td valign="top">Update "now playing":</td>';
    echo '<td><input type="checkbox" name="special_extensions_update_playing" class="tfield" ';
	$c=get_configuration("special_extensions_update_playing");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	
	// Update statistics:
    echo '<tr><td valign="top">Update statistics:</td>';
    echo '<td><input type="checkbox" name="special_extensions_update_statistics" class="tfield" ';
	$c=get_configuration("special_extensions_update_statistics");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';	

//
//	
// MISC STUFF:
//
//
// 0.7.5: Expand/collapse:
	echo '</table></div>';
?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif9" onclick="handleClick('to_col9','gif9')">
	Miscellaneous options
	<div id="to_col9" style="display:none;">
<?php
	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	// date/time format:
	echo '<tr><td valign="top">Date/time format to display:</td>';
	echo '<td><input type="text" name="dateformat" class="tfield" ';
    echo 'value="'.get_configuration("dateformat").'" size="20">';
	echo 'If set to <b>Y-m-d H:m:s</b>, will display something like: 2005-05-20 20:05:55. ';
	echo 'Visit <a href="http://www.php.net/manual/en/function.date.php" target="_blank">';
    echo 'the PHP manual</a> for other examples.';
	echo '</td></tr>';
	// Amazon developer key:
    echo '<tr><td valign="top">Amazon Web Services (AWS) key:</td>';
    echo '<td><input type="text" name="amazon_key" class="tfield" ';
    echo 'value="'.get_configuration("amazon_key").'" size="25"><br>';
	echo ' <b><a href="http://www.ampjuke.org/faq.php?q_id=10" target="_blank">';
	echo 'Click here for more information about this setting (and how to obtain an AWS key)</a></b>.';    
    echo '</td></tr>';

	// 0.3.7: Allow anonymous users (aka. guests) to use AmpJuke:
    echo '<tr><td valign="top">Allow anonymous users:</td>';
    echo '<td><input type="checkbox" name="allow_anonymous" class="tfield" ';
    $c=get_configuration("allow_anonymous");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo 'If checked: "Anonymous users" (as in: "anybody who happens to come by your site") ';
	echo 'will be allowed to access AmpJuke.';
	// 0.6.4: Allow anonymous streaming:
    echo '<tr><td valign="top">Allow anonymous users to stream music:</td>';
    echo '<td><input type="checkbox" name="allow_anonymous_streaming" class="tfield" ';
    $c=get_configuration("allow_anonymous_streaming");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '</td></tr>';

	// 0.6.4: Array of forbidden characters:
    echo '<tr><td valign="top">"Forbidden" characters (stream/download):</td>';
    echo '<td><input type="text" name="forbidden_characters" class="tfield" value="';
    echo get_configuration("forbidden_characters");
	echo '" size="25"> ';   
 	echo ' <b><a href="http://www.ampjuke.org/faq.php?q_id=43" target="_blank">';
	echo 'Click here for more information about "forbidden" characters</a></b>.';    
	echo '</td></tr>';

	// 0.5.1: External link for performer info.:
    echo '<tr><td valign="top">Offer more information about performers:</td>';
    echo '<td><input type="checkbox" name="perf_info" class="tfield" ';
    $c=get_configuration("perf_info");
    if ($c=="1") {
    	echo "checked";
	}
	echo '>';    	
	echo '  Path:';
	$c=get_configuration("perf_info_link");
	echo '<input type="text" name="perf_info_link" class="tfield" value="'.$c.'" size="50">';
	echo ' <b><a href="http://www.ampjuke.org/faq.php?q_id=28" target="_blank">';
	echo 'Click here for more information about this setting</a></b>.';
	echo '</td></tr>';
	echo '</table></div>';

	echo '<table width="100%" border="1" cellspacing="0" cellpadding="0" rules="rows">';
	echo '<tr><td colspan="5" align="center">';
	echo '<input type="submit" value="Save & continue" class="tfield">';
	echo '</td></tr>';
	echo '</table></form>';
	

} // if act=configure



// Rescan or (new) options: write 'em to the db.php file:
if (($act=="write") || ($act=="rescan")) { 
//	require("configuration.php"); 0.7.1
	if ($act=="write")  {
		$handle=fopen("db.php", "w");
		fwrite($handle, '<?php' . chr(13) . chr(10));
		fwrite($handle, '$db_host="'.$_POST['db_host'].'";' . chr(13) . chr(10));
		fwrite($handle, '$db_user="'.$_POST['db_user'].'";' . chr(13) . chr(10));
		fwrite($handle, '$db_password="'.$_POST['db_password'].'";' . chr(13) . chr(10));
		fwrite($handle, '$db_name="'.$_POST['db_name'].'";' . chr(13) . chr(10));
		// 0.6.7: Prefix tables ?
		if ((isset($_POST['ampjuke_tbl_prefix'])) && ($_POST['ampjuke_tbl_prefix']!="")) {
			fwrite($handle, '$ampjuke_tbl_prefix="'.$_POST['ampjuke_tbl_prefix'].'";' . chr(13) . chr(10));
		}	
		fwrite($handle, '$base_music_dir="'.$_POST['base_music_dir'].'";' . chr(13) . chr(10));
		fwrite($handle, '$base_http_prog_dir="'.$_POST['base_http_prog_dir'].'";' . chr(13) . chr(10));
		fwrite($handle, '$dateformat="'.$_POST['dateformat'].'";' . chr(13) . chr(10));
        fwrite($handle, '$amazon_key="'.$_POST['amazon_key'].'";' . chr(13) . chr(10));
		$val=0;
		if (isset($_POST['allow_anonymous'])) {
			$val=1;
		}
		fwrite($handle, '$allow_anonymous='.$val.';' . chr(13) . chr(10));
		// 0.6.4: Allow anonymous to stream:
		$val=0;
		if (isset($_POST['allow_anonymous_streaming'])) {
			$val=1;
		}
		fwrite($handle, '$allow_anonymous_streaming='.$val.';' . chr(13) . chr(10));

		$val=0;
		if (isset($_POST['keep_extension'])) {
			$val=1;
		}	
		fwrite($handle, '$keep_extension='.$val.';' . chr(13) . chr(10));
		// 0.6.0: Compress command / location of "tar":
		fwrite($handle, '$compress_command="'.$_POST['compress_command'].'";' . chr(13) . chr(10));
		// 0.6.0: Dont compress one file:
		$val=0;
		if (isset($_POST['dont_compress_one_file'])) {
			$val=1;
		}
		fwrite($handle, '$dont_compress_one_file='.$val.';' . chr(13) . chr(10));			
		// 0.5.1: offer more info. about performers: 
		$val=0;
		if (isset($_POST['perf_info'])) {
			$val=1;
		}
		fwrite($handle, '$perf_info='.$val.';' . chr(13) . chr(10));
		fwrite($handle, '$perf_info_link="'.$_POST['perf_info_link'].'";' . chr(13) . chr(10));
		// 0.6.1: upload-stuff (3 items), allow_upload, max_upload_files, upload_chmod:
		$val=0;
		if (isset($_POST['allow_upload'])) {
			$val=1;
		}	
		if (!is_numeric($_POST['max_upload_files'])) {
			$_POST['max_upload_files']=10;
		}
		fwrite($handle, '$allow_upload='.$val.';' . chr(13) . chr(10));		
		fwrite($handle, '$max_upload_files='.$_POST['max_upload_files'].';' . chr(13) . chr(10));
		fwrite($handle, '$upload_chmod="'.substr($_POST['upload_chmod'],0,3).'";' . chr(13) . chr(10));
		// 0.6.1: last.fm stuff (aka. related performers settings):
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
		fwrite($handle,'$lastfm_allow_related='.$val.';' . chr(13) . chr(10));
		fwrite($handle,
		'$lastfm_max_related_artists='.$_POST['lastfm_max_related_artists'].';' . chr(13) . chr(10));
		fwrite($handle,'$lastfm_min_related_match='.$_POST['lastfm_min_related_match'].';' . chr(13) . chr(10));
		fwrite($handle,'$lastfm_cache_days='.$_POST['lastfm_cache_days'].';' . chr(13) . chr(10));
		// 0.6.4: "now playing" stuff:
		$val=0;
		if (isset($_POST['allow_now_playing'])) {
		 	$val=1;
		}
		fwrite($handle, '$allow_now_playing='.$val.';' . chr(13) . chr(10));
		$val=0;
		if (isset($_POST['now_playing_disp_cover'])) {
		 	$val=1;
		}
		fwrite($handle, '$now_playing_disp_cover='.$val.';' . chr(13) . chr(10));
		fwrite($handle, '$now_playing_dimension_w="'.$_POST['now_playing_dimension_w'].'";' . chr(13) . chr(10));	
		fwrite($handle, '$now_playing_dimension_h="'.$_POST['now_playing_dimension_h'].'";' . chr(13) . chr(10));			
		if (!is_numeric($_POST['now_playing_update_rate'])) {
			$_POST['now_playing_update_rate']=15000;
		}			
		fwrite($handle, '$now_playing_update_rate='.$_POST['now_playing_update_rate'].';' . chr(13) . chr(10));
		if (!is_numeric($_POST['popout_width'])) {
			$_POST['popout_width']=200;
		}			
		fwrite($handle, '$popout_width='.$_POST['popout_width'].';' . chr(13) . chr(10));
		if (!is_numeric($_POST['popout_height'])) {
			$_POST['popout_height']=200;
		}			
		fwrite($handle, '$popout_height='.$_POST['popout_height'].';' . chr(13) . chr(10));
		// 0.6.6: "Light" update these mediaplayers:
		fwrite($handle,'$np_light_update="'.$_POST['np_light_update'].'";' . chr(13) . chr(10));
		// 0.6.6: Continue update "Now playing" durting auto.play or dsp.msg.:
		$val=0;
		if (isset($_POST['np_update_automatic_play'])) {
			$val=1;
		}
		fwrite($handle,'$np_update_automatic_play='.$val.';' . chr(13) . chr(10));
		fwrite($handle,'$np_light_update_msg="'.$_POST['np_light_update_msg'].'";' . chr(13) . chr(10));
		// 0.6.4: Array of forbidden characters:
		$val=$_POST['forbidden_characters'];
		fwrite($handle,'$forbidden_characters="'.$val . '";'.chr(13) . chr(10));		

// 0.7.0: Downsampling/transcoding:
		$val=0;
		if (isset($_POST['lame_enabled'])) {
			$val=1;
		}
		fwrite($handle,'$lame_enabled='.$val . ';'.chr(13) . chr(10));
		fwrite($handle,'$lame_path="'.$_POST['lame_path'].'";'.chr(13) . chr(10));
		fwrite($handle,'$lame_parameters="'.$_POST['lame_parameters'].'";'.chr(13) . chr(10));

// 0.7.1: last_scan_date:
		if (isset($_POST['last_scan_date'])) {
			fwrite($handle,'$last_scan_date='.$_POST['last_scan_date'].';'.chr(13) . chr(10));
		}	

// 0.7.2: submission/"Scrobbling" tracks to last.fm:
		$val="0";
		if (isset($_POST['lastfm_allow_submission'])) {
			$val=1;
		}
		fwrite($handle,'$lastfm_allow_submission="'.$val.'";'.chr(13) . chr(10));
		$val="0";
		if (isset($_POST['lastfm_allow_local_users'])) {
			$val=1;
		}
		fwrite($handle,'$lastfm_allow_local_users="'.$val.'";'.chr(13) . chr(10));
		fwrite($handle,'$lastfm_default_username="'.$_POST['lastfm_default_username'].'";'.chr(13) . chr(10));
		fwrite($handle,'$lastfm_default_password="'.$_POST['lastfm_default_password'].'";'.chr(13) . chr(10));	
// 0.7.3: special extensions (m4a, mp4 etc.):
		$val="0";
		if (isset($_POST['special_extensions_enabled'])) {
			$val="1";
		}
		fwrite($handle,'$special_extensions_enabled="'.$val.'";'.chr(13) . chr(10));
		fwrite($handle,'$special_extensions="'.$_POST['special_extensions'].'";'.chr(13).chr(10));
		$val="0";
		if (isset($_POST['special_extensions_update_playing'])) {
			$val=1;
		}
		fwrite($handle,'$special_extensions_update_playing="'.$val.'";'.chr(13) . chr(10));
		$val="0";
		if (isset($_POST['special_extensions_update_statistics'])) {
			$val=1;
		}
		fwrite($handle,'$special_extensions_update_statistics="'.$val.'";'.chr(13) . chr(10));
			
		fwrite($handle, "?");
		fwrite($handle, ">" . chr(13) . chr(10));
		fclose($handle);
		
// we want to create a new database and/or new tables - from SCRATCH:
		if ((isset($_POST['createdb'])) || (isset($_POST['createtbl']))) { 
			$connection=mysql_connect($_POST['db_host'],$_POST['db_user'],$_POST['db_password']) 
				or die('Create database: Could not connect.');
			if (isset($_POST['createdb'])) { // we really want to create an empty database:
				$qry="DROP DATABASE IF EXISTS ".$_POST['db_name'];
				$result=mysql_query($qry) or die('Could NOT delete the database: '.$_POST['db_name'].'<br>Most likely, a wrong MySQL-username and/or -password is used.');
				$qry="CREATE DATABASE ".$_POST['db_name'];
				$result=mysql_query($qry, $connection)
					or die('Could NOT create the database: '.$_POST['db_name'].'<br>Most likely, a wrong MySQL-username and/or -password is used.');
			}	
			if (isset($_POST['createtbl'])) { // we want to create empty tables within the database:
				mysql_select_db($_POST['db_name']) or die('You wanted to create empty tables, but the <b>database</b> could not be found.');
				// First, drop existing tables (if they're there):
				$qry="DROP TABLE IF EXISTS album";
				$result=mysql_query($qry, $connection) or die(mysql_error());
				$qry="DROP TABLE IF EXISTS favorites";
				$result=mysql_query($qry, $connection) or die(mysql_error());
				$qry="DROP TABLE IF EXISTS performer";
				$result=mysql_query($qry, $connection) or die(mysql_error());
				$qry="DROP TABLE IF EXISTS queue";
				$result=mysql_query($qry, $connection) or die(mysql_error());
				$qry="DROP TABLE IF EXISTS track";
				$result=mysql_query($qry, $connection) or die(mysql_error());
				$qry="DROP TABLE IF EXISTS user";
				$result=mysql_query($qry, $connection) or die(mysql_error());
				// Second, create the tables (again):
				require("db_new.sql");
				
				// 0.6.7: Prefix tables ?
				if (isset($_POST['ampjuke_tbl_prefix'])) {
					$ampjuke_tbl_prefix=$_POST['ampjuke_tbl_prefix'];
					$c_album=str_replace("CREATE TABLE album (", "CREATE TABLE ".$ampjuke_tbl_prefix."album (", $c_album);
					$c_fav=str_replace("CREATE TABLE fav (", "CREATE TABLE ".$ampjuke_tbl_prefix."fav (", $c_fav);	
					$c_performer=str_replace("CREATE TABLE performer (", "CREATE TABLE ".$ampjuke_tbl_prefix."performer (", $c_performer);
					$c_queue=str_replace("CREATE TABLE queue (", "CREATE TABLE ".$ampjuke_tbl_prefix."queue (", $c_queue);	
					$c_track=str_replace("CREATE TABLE track (", "CREATE TABLE ".$ampjuke_tbl_prefix."track (", $c_track);
					$c_user=str_replace("CREATE TABLE `user` (", "CREATE TABLE `".$ampjuke_tbl_prefix."user` (", $c_user);
					$c_fav_shares=str_replace("CREATE TABLE fav_shares (", "CREATE TABLE ".$ampjuke_tbl_prefix."fav_shares (", $c_fav_shares);
				} // 0.6.7: Prefix tables ends
				
				$result=mysql_query($c_album, $connection) or die(mysql_error());
				// 0.5.0: c_fav is the new kid on the block. c_favorites was the old...:
				$result=mysql_query($c_fav, $connection) or die(mysql_error());
				$result=mysql_query($c_performer, $connection) or die(mysql_error());
				$result=mysql_query($c_queue, $connection) or die(mysql_error());
				$result=mysql_query($c_track, $connection) or die(mysql_error());
				$result=mysql_query($c_user, $connection) or die(mysql_error());
				// 0.5.2: introduce the fav_shares table:
				$result=mysql_query($c_fav_shares, $connection) or die(mysql_error());
				// Third, insert the "defaults" in user (so we can login) & performer:
		                // this query ensures we have a place to store albums w. 
				// "various" (ie. multiple) performers:
				$qry="INSERT INTO performer VALUES ('1','')"; 
				$result=execute_sql($qry,0,-1,$nr);
				$qry="INSERT INTO user (name, admin, password, lang, count) ";
				$qry.="VALUES ('admin', '1', 'pass', 'EN', '20')";
				$result=execute_sql($qry,0,-1,$nr);				
			}			
			echo '<p class="note">Ok. Everything is fine so far !<br>';

			if (isset($_POST['createdb'])) { 
				echo 'The database was created successfully.<br>';
			}
			if (isset($_POST['createtbl'])) {	
				echo 'New tables were created successfully.<br>';
				// 0.6.7: Tell 'em we also wanted prefixes:
				if (isset($_POST['ampjuke_tbl_prefix'])) {
					echo 'AmpJuke-tablenames prefixed: <b>'.$_POST['ampjuke_tbl_prefix'].'</b>';
					echo '<br>';
				}	
			}	

			// 0.2.5: instructions changed:
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
			echo 'For improved security, you should also consider renaming/deleting';
			echo ' the files "<b>db_new.php</b>" as well as "<b>install.php</b>".<br>';
			echo '<a href="login.php">Click here to login.</a>';
			// 0.3.0: just rename "new_db.sql" & exit script:
			exec("mv db_new.sql db_new.php");
			exit;
		} // _POST[createdb]...
	} // act==write. Version 0.2.4

	// 0.7.5: Point to OLD and NEW scan method:
	if (!isset($notified)) {
		echo '<img src="./ampjukeicons/ampjuke_top.gif" border="0"><br>';
		echo 'OK. You now have <b>two options</b> to setup and run a scan+import of your music:<br><br>';
		echo '<a href="scan2.php?act=setup">Click here to use the <b>new</b> method.</a>';
		echo '<br>';
		echo '<a href="scan.php?act=rescan&notified=1"><font color="red">Click here to use the <b>old</b> method.</a>';
		echo '<br><br><font color="black">I could go on about a <font color="blue"><b>blue pill</b> ';
		echo '<font color="black">and a <font color="red"><b>red pill</b>';
		echo '<font color="black"> and mentioning something about a "Matrix" of some kind...<br>';
		echo 'But in <b>my opinion</b> (and I <i>programmed</i> AmpJuke, remember?) I think you should';
		echo ' go for the <font color="blue"><b>blue</b><font color="black">...<br><br>';
		echo 'With that in mind, please be aware that the new method is - well - new in AmpJuke, and the old method has been';
		echo ' around for years as a well-proven, reliable way to scan+import music.<br>';
		die();
	}	
	echo '<table border="1" cellspacing="0" cellpadding="0" rules="rows">';
	echo '<tr><td colspan="5" bgcolor="#abcdef"><b>Scan entire collection</b></td></tr>';


	// 0.6.0: Offer help+more options:
	echo '<form name="newscan" method="POST" action="scan.php?act=rebuild">';	
	echo '<tr><td valign="top">';
	echo '<br><a href="http://www.ampjuke.org/faq.php?q_id=34" target="_blank">';
	echo '<b>Click here for more information about the options below</b></a><br>';
	echo 'Show errors/warnings: <input type="checkbox" name="print_details" class="tfield"><br>';
	echo 'Delete "dead" records: <input type="checkbox" name="delete_dead_records"';
	echo ' class="tfield"><br>';
	// 0.7.1: Offer a "cutoff" date:
	$coff=get_configuration("last_scan_date");
	$ch='';
	if ($coff<>'') {
		$coff=date('Y-m-d',$coff);
		$ch=' checked';		
	}	
	echo '<input type="checkbox" name="cutoff_date_active"'.$ch.'> ';
	echo 'Only scan+import tracks added after:';
	echo '<input type="text" name="cutoff_date" value="'.$coff.'" class="tfield">';
	echo '<i> Note: Entered as YYYY-MM-DD, f.ex.: 2008-05-20.</i><br>';
	
	echo 'Show progress immediately: <input type="checkbox" name="show_progress"';
	echo ' class="tfield" checked><br><br>';
	echo '<input type="submit" value="Scan & import all music available"><br><br>';

	echo '</td><td>';
	echo '</td></tr>';

	echo '<tr><td colspan="5" bgcolor="#abcdef"><b>Scan a subdirectory</b></td></tr>';	
	echo '<tr><td valign="top">&nbsp<br><form name="newdir" method="POST" action="scan.php?act=rebuild">';
	echo 'Only scan & import all (new) within:<b>'.$base_music_dir.'</b>';
	echo '<input type="text" name="subdir" value="" size="80" class="tfield"></td>';
	echo '<td>Enter a subdirectory within <b>'.$base_music_dir.'</b> that you want to import music from.<br>';
	echo 'If you f.ex. enter <b>/my_new_music</b>, the program will look for and import new music ';
	echo 'from <b>'.$base_music_dir.'/my_new_music</b> and any directories below.<br>';
	echo '<b>Note</b>: You will need to put in a leading / (just like in the example)';
	echo '</td></tr></form>';
/*
	echo '<tr><td valign="top"><form name="newdir" method="POST" action="scan.php?act=analyze">';
	echo 'Scan&<b>analyze</b> music within:<b>'.$base_music_dir.'</b>';
	echo '<input type="text" name="subdir" value="" size="80" class="tfield"></td>';
	echo '<td>Enter a subdirectory within <b>'.$base_music_dir.'</b> that you want <b>analyze</b> (no music will be imported).';
	echo '</td></tr></form>';
*/	

	echo '<tr><td colspan="5" bgcolor="#abcdef">&nbsp</td></tr>';
	echo '<tr><td colspan="5"><a href="index.php?what=welcome"><img src="./ampjukeicons/mnu_arr.gif" border="0"> Go back to the "Welcome" page</a>';
	echo '</td></tr>';
	echo '</table>';
} // act=write | rescan

?>

</body>
</html>

