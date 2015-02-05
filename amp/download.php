<?php
// 0.7.5: It's now a stand-alone function (i.e. not included like other things in AmpJuke), SO...
require('logincheck.php');
parse_str($_SERVER["QUERY_STRING"]);
require("disp.php");
require("db.php");
require("sql.php");
$user=$_SESSION['login']; 

// 0.6.0: Entire script re-inspected + rewritten in many places.
// 0.6.7: Not an active session and/or came here by "accident":
if (session_id()=="") { 
 	echo 'Sorry. Session timed out.<br>';
	die('<a href="login.php">Click here to continue</a>.');
}	
if (!isset($_SESSION['login']) || $_SESSION['can_download']!="1") { // was '&&' before -> typical !
	session_destroy();
	include_once("disp.php");
	// 0.6.7: If the session timed out, DO NOT redirect:
 	echo 'Sorry. Session timed out.<br>';
	die('<a href="login.php">Click here to continue</a>.');
}

/* 0.7.5: Already there - see above
require_once("sql.php");
require_once("disp.php");
require_once("db.php");
*/

if (isset($demo)) {
	redir("demo.php");
	die('<a href="./">click here</a>');
}	

function download_track($seq_nr,$track_id,$music_tmp_dir,$base_http_prog_dir,$kext) { 
	$ret="";
	$qry="SELECT * FROM track WHERE id=".$track_id;
	$result=execute_sql($qry,0,1,$nr);
	$row=mysql_fetch_array($result);
	$name=set_name($row['performer_id'],$row['name'],$row['album_id'],$kext,$row['path']); 
	
	//$name=urlencode($name); // 0.6.4
    $name=utf8_encode($name);

	cpy_file_to_tmp($row['path'],$name,'./tmp/',$kext); 
//	return './tmp/'.$name; // 0.6.4: Tweaked/changed to:
	return $name;
}


// Actual start - set up stuff:
$archive_name=$_SESSION['login'].'_'.date("U").'.tar';
$filelist="";
$rm_cmd="";
$ok=0;
$tmpcount=1;

// Determine what we want to download:
if ($type=="track") {
	$filelist.=download_track(1,$download_id,'./tmp',$base_http_prog_dir,$keep_extension).' ';
	$rm_cmd.='rm -f '.download_track(1,$download_id,'./tmp',$base_http_prog_dir,$keep_extension).'; ';
	$ok=1;
	$r=get_track_extras($download_id);
	if ($dont_compress_one_file=="1") {
		$archive_name=download_track(1,$download_id,'./tmp',$base_http_prog_dir,$keep_extension);		
		// strip "./tmp/"
		$f=array("./tmp/");
		$archive_name=str_replace($f,"",$archive_name);
	}	
}

if ($type=="album") { 
	$qry="SELECT * FROM track WHERE album_id=".$download_id;
	$result=execute_sql($qry,0,-1,$nr);
	while ($row=mysql_fetch_array($result)) {
		$filelist.=download_track($tmpcount,$row['id'],'./tmp',$base_http_prog_dir,$keep_extension).' '; 
		$rm_cmd.='rm -f ';
		$rm_cmd.=download_track($tmpcount,$row['id'],'./tmp',$base_http_prog_dir,$keep_extension).'; ';		
		$ok++;
	}
}		

if ($type=="performer") { 
	$qry="SELECT * FROM track WHERE performer_id=".$download_id;
	$result=execute_sql($qry,0,-1,$nr);
	while ($row=mysql_fetch_array($result)) {
		$filelist.=download_track($tmpcount,$row['id'],'./tmp',$base_http_prog_dir,$keep_extension).' '; 
		// 0.7.5: rm_cmd was forgotten somehow - damn...:
		$rm_cmd.='rm -f ';
		$rm_cmd.=download_track($tmpcount,$row['id'],'./tmp',$base_http_prog_dir,$keep_extension).'; ';		
		$ok++;
	}
}

if ($type=="year") { 
 	$qry="SELECT * FROM track WHERE year=".$download_id;
 	$result=execute_sql($qry,0,-1,$nr);
 	while ($row=mysql_fetch_array($result)) {
		$filelist.=download_track($tmpcount,$row['id'],'./tmp',$base_http_prog_dir,$keep_extension).' '; 
		// 0.7.5: rm_cmd was forgotten somehow - damn...:
		$rm_cmd.='rm -f ';
		$rm_cmd.=download_track($tmpcount,$row['id'],'./tmp',$base_http_prog_dir,$keep_extension).'; ';				
		$ok++;
	}
}
 		
if ($type=="favorite_list") { 
	$uid=get_user_id($_SESSION['login']); 
	$qry="SELECT * FROM fav WHERE user_id='".$uid."' AND fav_name='".$download_id."'";
	$result=execute_sql($qry,0,100000,$nr);
	if ($nr==0) { // 0 recs.: are we trying to download a shared favorite ?
		$qry="SELECT * FROM fav_shares WHERE fav_name='".$download_id."' AND share_id='".$uid."'";
		$result=execute_sql($qry,0,2,$nr);
		if ($nr<>0) { // yes - we ARE trying to download a shared fav.: get the owner_id:
			while ($row=mysql_fetch_array($result)) {
				$uid=$row['owner_id'];
			}				
			// now: try the same query with the owner_id as uid:
			$qry="SELECT * FROM fav WHERE user_id='".$uid."' AND fav_name='".$download_id."'";
			$result=execute_sql($qry,0,1000000,$nr);
		}
	}		
 	while ($row=mysql_fetch_array($result)) {
 		if ($row['track_id']<>0) {
			$filelist.=download_track($tmpcount,$row['track_id'],'./tmp',
			$base_http_prog_dir,$keep_extension).' ';
			// 0.7.5: rm_cmd was forgotten somehow - damn...:
			$rm_cmd.='rm -f ';
			$rm_cmd.=download_track($tmpcount,$row['track_id'],'/tmp',$base_http_prog_dir,$keep_extension).'; ';
			$ok++;
		}	
	}
}
	
if ($type=="queue") { 
	$qry="SELECT * FROM queue WHERE user_name='".$_SESSION['login']."'";
	$result=execute_sql($qry,0,-1,$nr);
 	while ($row=mysql_fetch_array($result)) {
		$filelist.=download_track($tmpcount,$row['track_id'],'./tmp',$base_http_prog_dir,$keep_extension).' '; 
		// 0.7.5: rm_cmd was forgotten somehow - damn...:
		$rm_cmd.='rm -f ';
		$rm_cmd.=download_track($tmpcount,$row['track_id'],'./tmp',$base_http_prog_dir,$keep_extension).'; ';						
		$ok++;
	}
}

// download >1 track or compress always -> compress first:
if (($ok>1) || ($dont_compress_one_file=="0")) { 
	// 0.6.4: cd tmp; -> cd to tmp first -> no ./tmp/... in archive's filenames
	$e='cd tmp; '.$compress_command.' '.getcwd().'/tmp/'.$archive_name.' '.$filelist;	
	exec($e);
//	exec($rm_cmd);
}

// 0.6.4: Whooops - what if we got EXACTLY one file we don't want to compress ??
if (($ok==1) && ($dont_compress_one_file=="1")) { 
	$f=array("./tmp/", " ");
	$filelist=str_replace($f,"",$filelist);
// 0.7.9: Don't need this:	
// $archive_name=urlencode($filelist); 
	$archive_name=$filelist;
}

if ($ok<>0) { // found something to download, now go ahead and send archive/track:
/*
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.open("./tmp/'.$archive_name.'");';
	echo '</script>';  	

	0.7.5: The above 3 lines replaced with this (should prevent pop-up blockers from complaining)...
// ...basically implementing wolf's hint/suggestion:
*/
	header('Cache-Control: no-cache, no-store, must-revalidate'); 
	header('Expires: Tue, 20 May 2008 00:00:00 GMT'); 
	header('Pragma: no-cache'); 
	header('Content-Type: application/octet-stream');

	header('Content-Disposition: attachment; filename="'.$archive_name.'"');
	readfile('./tmp/'.$archive_name); 
	
}	
/*
0.7.5: Not needed anymore:
echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
echo '</script>';  
*/
?>	
