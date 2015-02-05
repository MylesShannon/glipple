<?php
// This is the (very) old stream-method in AmpJuke.
// Move to parent directory, and rename to "stream.php" if you want to give it a try.
// November 2008 / Michael H. Iversen.

/*
0.6.6: Some of the code below might look as a good 'victim' for consolidation...
...the only difference in the two blocks is the line: update_now_playing($id,$row...
which will be executed depending on the presence of $update_now_playing.
BUT - at least after a number of tests - it appears NOT to work if there's a statement that
checks on the presence of $update_now_playing and then calls update_now_playing($id,$row...
if that is the case, - ie.:
if (isset($update_now_playing)) {
	update_now_playing($id,$row... 
}
won't work, - at least not in AmpJuke.
02-11-2007 / Michael H. Iversen	
*/
die('Sorry...');
parse_str($_SERVER["QUERY_STRING"]);

// check that ID is ok & a number:
if (!isset($id)) {
 	exit;
}
if (!is_numeric($id)) {
	exit;
}
// 0.6.6: Cant have negative ID's in this app.
if ($id<0) {
 	exit;
}	

include("db.php");
include("sql.php");
include("configuration.php");

function update_now_playing($id,$trackname,$pid,$year,$aid,$user_id) {
// 0.6.4: Update the 'now playing':
	require_once("disp.php");
	require_once("db.php");
//	include("db.php");

	$handle=fopen('./tmp/np'.$user_id.'.txt', 'w'); // 'unique' filename
	$h=fopen('./tmp/np'.$user_id.'pop.txt', 'w');

	fwrite($handle,'<table class="ampjuke_now_playing">');
	fwrite($h,'<table class="ampjuke_now_playing">');
	// performer:
	$n=get_performer_name($pid);
	$amazon_string=$n.' - ';
	fwrite($handle,'<tr>'.add_performer_link($n,$pid).'</tr>');
	fwrite($h,'<tr><td class="content">'.$n.'</td></tr>');	
	// track name:
	fwrite($handle,'<tr><td class="content">'.$trackname);
	fwrite($h,'<tr><td class="content">'.$trackname);	
	// if year is there -> show it:
	if ($year!="") {
		$n=add_year_link($year,$year);
		$x=str_replace('<td class="content">','[',$n);
		$n=str_replace('</td>',']',$x);
		fwrite($handle,' '.$n);
		fwrite($h,' ['.$year.']');		
	}
	fwrite($handle,'</td></tr>');
	fwrite($h,'</td></tr>');	

	// 0.6.5: album and mini-cover
	// 0.6.6: Modified...

	$amazon_string=$aid; 
	$n=get_album_name($aid);
	$cover_found=0;		
	if (file_exists('./covers/'.$amazon_string.'.jpg')) {	
		$npw=get_configuration("now_playing_dimension_w");
		$nph=get_configuration("now_playing_dimension_h");
	 	if ($npw!="") {
			$lnk='<img src="./covers/'.$amazon_string.'.jpg" border="0"';
			$lnk.=' width="'.$npw.'" height="'.$nph.'">';
			fwrite($handle,'<tr>'.add_album_link($lnk,$aid).'</tr>');
			fwrite($h,'<tr><td>'.$lnk.'</td></tr>');	
			$cover_found=1;	
		}
	}

	if ($cover_found==1) {	
		fwrite($handle,'<tr>'.add_album_link($n,$aid).'</tr>');
		fwrite($h,'<tr><td>'.$n.'</td></tr>');
	}
	
	fwrite($handle,'<tr><td><a href="./now_playing_popout.php?not_done=1">');
	fwrite($handle,'<img src="./ampjukeicons/popout.gif" ');
	fwrite($handle,'border="0"></a></td></tr>');

// Nothing to see here - move on... Just debugging stuff...
//	fwrite($handle,'./covers/'.$amazon_string.'.jpg<br>cover_found='.$cover_found.'<br>n='.$n);
	fwrite($handle,'user-agent:'.$_SERVER["HTTP_USER_AGENT"]);
	$x=0;
	str_replace("MSIE","",$_SERVER["HTTP_USER_AGENT"],$x);
	fwrite($handle,' X='.$x);

	fwrite($handle,'</table>');
	fwrite($h,'</table><table class="ampjuke_now_playing">');		
	fwrite($h,'<tr><td class="content" align="center">');	
	fwrite($h,'<a href="javascript: self.close ()">AmpJuke</a>');
	fwrite($h,'...and YOUR hits keep on coming !</td></tr>');	
	fclose($handle);	
	fclose($h);	
}

// 0.5.3: Actually a copy (almost) of same function in random:
function get_random_preference($pref,$what) {
	$ret="ORDER BY rand()";

	if ($pref=="most_played") { $ret="ORDER BY rand()*(times_played+1) DESC"; }
	if ($pref=="least_played") { $ret="ORDER BY rand()*(times_played+1) ASC"; }
	if ($pref=="oldest") { 
	 	$now=date("U");
		$ret="ORDER BY rand()*(".$now."-last_played) DESC"; 
	}
	if ($pref=="newest") { 
	 	$now=date("U");
		$ret="ORDER BY rand()*(".$now."-last_played) ASC"; 
	}
	return $ret;
}	
//
// 0.6.6: re-introduced
// 
if (($id==0) && (!isset($update_now_playing))) { // 0.6.6: update_now...
 	if (!isset($preference)) { $preference=""; } // 0.5.4: Just so we HAVE it defined...
	require("disp.php");
 	// check against uid <-> password in user-table (simple: yes. But what the f*ck...):
 	$md5pw=get_md5_passwd($user);
 	$first_header=1; // 0.5.1: to avoid "headers already..."
 	while (true) { // continue forever...
		if ($what=="Tracks") {
			$qry="SELECT * FROM track ";
			$qry.=get_random_preference($preference,"Tracks");
		} else {
			$qry="SELECT * FROM fav WHERE user_id='".$user_id."'";
			$qry.=" AND fav_name='".rawurldecode($what)."' AND track_id>0 ";
			$qry.=get_random_preference($preference,"Fav");
		}	
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		
		if ($what=="Tracks") {
			$qry="SELECT * FROM track WHERE id=".$row['id'];
		} else {
		 	$qry="SELECT * FROM track WHERE id=".$row['track_id'];
		}	 			
			
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		
		if ($first_header==1) { // 0.5.1: send this once: first time
			header("Cache-Control: no-cache, must-revalidate"); 
			header('Content-type: audio'); 
			header('Last-Modified: ' . gmdate("D, d M Y H:i:s T"), date('U'));
			$first_header=0;
		}
			
		$fp = fopen($row['path'], 'r');
		fpassthru($fp);
		fclose($fp);
		update_stats($row['id']);
	}	
}


// 0.6.6: Modified version of the above - see explanation...
if (($id==0) && (isset($update_now_playing))) { // 0.6.6: update_now...
 	if (!isset($preference)) { $preference=""; } // 0.5.4: Just so we HAVE it defined...
	require("disp.php");
 	// check against uid <-> password in user-table (simple: yes. But what the f*ck...):
 	$md5pw=get_md5_passwd($user);
 	$first_header=1; // 0.5.1: to avoid "headers already..."
 	while (true) { // continue forever...
		if ($what=="Tracks") {
			$qry="SELECT * FROM track ";
			$qry.=get_random_preference($preference,"Tracks");
		} else {
			$qry="SELECT * FROM fav WHERE user_id='".$user_id."'";
			$qry.=" AND fav_name='".rawurldecode($what)."' AND track_id>0 ";
			$qry.=get_random_preference($preference,"Fav");
		}	
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		
		if ($what=="Tracks") {
			$qry="SELECT * FROM track WHERE id=".$row['id'];
		} else {
		 	$qry="SELECT * FROM track WHERE id=".$row['track_id'];
		}	 			
			
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);

		update_now_playing($id,$row['name'],$row['performer_id'],$row['year'],$row['album_id'],$user_id); 			

		if ($first_header==1) { // 0.5.1: send this once: first time
			header("Cache-Control: no-cache, must-revalidate"); 
			header('Content-type: audio'); 
			header('Last-Modified: ' . gmdate("D, d M Y H:i:s T"), date('U'));
			$first_header=0;
		}
		
		$fp = fopen($row['path'], 'r');
		fpassthru($fp);
		fclose($fp);
		update_stats($row['id']);
	}	
}



// All set - let's DO something !
if (!isset($update_now_playing)) {
	$qry="SELECT * FROM track WHERE id='".$id."'";
	$result=execute_sql($qry,0,100000,$nr);
	$row=mysql_fetch_array($result);

	header("Cache-Control: no-cache, must-revalidate"); 
	header('Content-type: audio'); 
	header('Last-Modified: ' . gmdate("D, d M Y H:i:s T"), date('U'));

	$fp = fopen($row['path'], 'r');
	fpassthru($fp);
	fclose($fp); // 0.5.1: Added just to "clean up".

	update_stats($row['id']);
	die(); 

} else { // update_now_playing IS set:
	$x=0;
	str_replace("MSIE","",$_SERVER["HTTP_USER_AGENT"],$x);
	if ($x==0) {
		str_replace("Windows-Media-Player","",$_SERVER["HTTP_USER_AGENT"],$x);
	}	
	if ($x==0) {
		str_replace("NSPlayer","",$_SERVER["HTTP_USER_AGENT"],$x);
	}	

	$qry="SELECT * FROM track WHERE id='".$id."'";
	$result=execute_sql($qry,0,100000,$nr);
	$row=mysql_fetch_array($result);

	if ($x!=1) {
	update_now_playing($id,$row['name'],$row['performer_id'],$row['year'],$row['album_id'],$user_id); 
	} 
	 
	header("Cache-Control: no-cache, must-revalidate"); 
	header('Content-type: audio'); 
	header('Last-Modified: ' . gmdate("D, d M Y H:i:s T"), date('U'));

	$fp = fopen($row['path'], 'r');
	fpassthru($fp);
	fclose($fp); // 0.5.1: Added just to "clean up".
	
	update_stats($row['id']);
	die(); 
}		
	
?>		
