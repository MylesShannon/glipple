<?php


require_once('disp.php');
parse_str($_SERVER["QUERY_STRING"]);
if (!isset($act)) {
	$act='setup';
}
$tdnorm='';
$tdalt='';
$tdhighlight='';
$count=0;

/*
******************************
						SCAN (support functions below)
******************************
*/
function update_status($level,$details,$msg) {
 	if ($details>=$level) {
		echo '<tr><td>'.$msg.'</td>';
		print "</tr> \n";
		flush(); ob_flush();
	}	
}	

// 0.8.1: These functions handles the cover-stuff during scan+import:
// find_cover function is courtesy Wurlitzer:
function find_cover($folder) {
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
            'filename' => $file->getFilename()
            //'type' => $valid[$info['extension']] // 'JPG' or 'PNG'
         );
      }
   }   
	sort($files);
	return $files[0]['filename'];
}

function handle_cover($ttype,$folder,$id,$simulate_import,$filename_new_stuff,$details,$cwd) {
	if ($ttype=='album') {
		$cover=find_cover($folder);
		update_status(3,$details,'Looked in :'.$folder.'</b> for a cover. Found: <b>'.$cover.'</b>');
		if ($cover<>'') {
			$d=file_get_contents($folder.$cover);
			$h=fopen($cwd.'/covers/'.$id.'.jpg', 'w');
			fwrite($h,$d);
			fclose($h);
			update_status(3,$details,'Copied from <b>'.$folder.$cover.'</b> to <b>./covers/'.$id.'.jpg</b>');
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
			$extra=' <a href="'.$base_http_prog_dir.'/id3tag/?filename='.$file.'" target="_blank">Edit tags</a>';
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



?> 

