<?php
// 0.8.6: stream_radio: stripped down version of stream.php.
// Only the most basic stuff is kept from the original.
// In addition, some code for handling/picking/serving requests have been added, - it's NOT a part of the original stream.php
//

$debug='0'; // Set to '1' in order to write debug stuff to ./tmp/debug.txt

// checks/setup:
parse_str($_SERVER["QUERY_STRING"]);

if (!isset($id)) {
 	die();
}
if (!is_numeric($id)) {
	die();
}
if ($id<0) {
 	die();
}	
// 0.7.2: More checks:
if ((!isset($user_id)) || (!is_numeric($user_id))) {
	die();
}

if (isset($update_now_playing)) {
	if (!is_numeric($update_now_playing)) {
		die();
	}
}	
if (isset($update_now_playing_next)) {
	if (!is_numeric($update_now_playing_ext)) {
		die();
	}
}	

// 0.8.5: 
if ((isset($max_last_played)) && (!is_numeric($max_last_played))) {
	die();
}

include("db.php");
include("sql.php");
include("configuration.php");
require_once("disp.php");

// 0.7.3: The "upw" parameter is required AND it must match - unless we allow anonymous access:
if (($allow_anonymous!="1")) { // && ($login!="anonymous")) { // 0.7.9: F*ck - what this $login-stuff anyway..?
	$user=get_username($user_id);
	if ((!isset($upw)) || ($upw!=get_md5_passwd($user))) {
		die('');
	}	
}

// Find out if the media-player used should show "light" updates of "Now playing":
$simple_update_now_playing=0;
if (isset($_SERVER["HTTP_USER_AGENT"])) {
	$user_agent=$_SERVER["HTTP_USER_AGENT"];
} else {
	$user_agent='';
}
$plist=explode("*",$np_light_update);
str_replace($plist,"",$user_agent,$simple_update_now_playing);

/*

			SUPPORTING FUNCTIONS

*/

function update_now_playing_light($user_id,$name,$year) {
	// Uncomment to display user agent (player being used):
	//$display_user_agent=1;
	
	$handle=fopen('./tmp/np'.$user_id.'.txt', 'w'); 
	fwrite($handle,'<table class="ampjuke_now_playing">');
	fwrite($handle,'<tr><td>'.$name.'</td></tr>');
	
	if ($year!='') {
		fwrite($handle,'<tr><td>['.$year.']</td></tr>');
	}	

	if (isset($display_user_agent)) {
		fwrite($handle,'<tr><td>You user-agent (media player) is:<br><b>'.$_SERVER["HTTP_USER_AGENT"]);
		fwrite($handle,'</b></td></tr>');
	}	

	fwrite($handle,'</table>');
	fclose($handle);
	
	$h=fopen('./tmp/np'.$user_id.'pop.txt', 'w'); 
	fwrite($h,'<table class="ampjuke_now_playing">');
	fwrite($h,'<tr><td>'.$name.'</td></tr>');

	if ($year!='') {
		fwrite($h,'<tr><td>['.$year.']</td></tr>');
	}	

	fwrite($h,'</table><table class="ampjuke_now_playing">');		
	fwrite($h,'<tr><td class="content" align="center">');	
	fwrite($h,'<a href="javascript: self.close ()">AmpJuke</a>');
	fwrite($h,'...and YOUR hits keep on coming !</td></tr>');

	fwrite($h,'</table>');
	fclose($h);
	
}	


function update_now_playing($id,$trackname,$pid,$year,$aid,$user_id,$language) {
	// Uncomment to show user-agant (the player being used):
	// $display_user_agent=1;
	require_once("disp.php");
	require_once("db.php");
	require_once("translate.php");
	$_SESSION['lang']=$language;
	
	// Start/intro.:
	$handle=fopen('./tmp/np'.$user_id.'.txt', 'w'); 
	$h=fopen('./tmp/np'.$user_id.'pop.txt', 'w');
	fwrite($handle,'<table class="ampjuke_now_playing">');
	fwrite($h,'<table class="ampjuke_now_playing">');
	fwrite($handle,'<tr><td class="content">'.xlate("Now playing").':</td></tr>');
	fwrite($h,'<tr><td class="content">'.xlate("Now playing").':</td></tr>');	

	// 0.8.2: Get the user's details (all of 'em) plus get the icon for "Add to favorite...":
	$u=get_user_details($user_id); 
	$icon=get_icon($u['icon_dir'],'favorite_add','');
	
	// performer:
	$n=get_performer_name($pid);
	$amazon_string=$n.' - ';
	// 0.8.2: Show option to add to favorite ?
	$l='';
	if ($u['disp_now_playing_add2favorite']=='1') {
		$l='<a href="./add2fav.php?what=performerid&id='.$pid;
		if ($u['ask4favoritelist']=='1') { // Ask for favoritelist eveytime..?
			$l.='&picker=1';
		}
		$l.='">'.$icon.'</a>';
	} // 0.8.2: Changes ends
	
	fwrite($handle,'<tr><td>'.add_performer_link($n,$pid).$l.'</td></tr>'); // 0.8.2: Added $l
	fwrite($h,'<tr><td class="content">'.$n.$l.'</td></tr>');// 0.8.2: Added $l

	// track name:
	fwrite($handle,'<tr><td class="content">'.$trackname);
	fwrite($h,'<tr><td class="content">'.$trackname);
	// 0.8.2: Show option to add to favorite ?
	if ($u['disp_now_playing_add2favorite']=='1') {
		fwrite($handle,'<a href="./add2fav.php?what=track&id='.$id);
		if ($u['ask4favoritelist']=='1') { // If we're asking for the name of the favorite list each time, add that option to the link:
			fwrite($handle,'&picker=1');
		}
		fwrite($handle,'">'.$icon.'</a>'); 
		fwrite($h,'<a href="./add2fav.php?what=track&id='.$id); 
		if ($u['ask4favoritelist']=='1') { // If we're asking for the name of the favorite list each time, add that option to the link:
			fwrite($h,'&picker=1');
		}	
		fwrite($h,'">'.$icon.'</a>');
	} // 0.8.2: Changes ends
		
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

	// Album & mini-cover:
	$amazon_string=$aid; 
	$n=get_album_name($aid);
	$cover_found=0;		
	if (file_exists('./covers/'.$amazon_string.'.jpg')) {	
		$npw=get_configuration("now_playing_dimension_w");
		$nph=get_configuration("now_playing_dimension_h");
		if ($npw!="") {
			$lnk='<img src="./covers/'.$amazon_string.'.jpg" border="0"';
			$lnk.=' width="'.$npw.'" height="'.$nph.'">';
			// 0.8.2: Show option to add what's playing to a favorite list ?
			$l='';
			if ($u['disp_now_playing_add2favorite']=='1') {
				$l='<a href="./add2fav.php?what=albumid&id='.$aid;
				if ($u['ask4favoritelist']=='1') {
					$l.='&picker=1';
				}	
				$l.='">'.$icon.'</a>';
			} // 0.8.2: Changes ends
			fwrite($handle,'<tr>'.add_album_link($lnk,$aid).'</tr>'); 
			fwrite($h,'<tr><td>'.$lnk.'</td></tr>');	
			fwrite($handle,'<tr>'.add_album_link($n.$l,$aid).'</tr>'); // 0.8.2: Added $l
			fwrite($h,'<tr><td>'.$n.$l.'</td></tr>'); // 0.8.2: Added $l
		}
	}

	// Clean up, finish off, do the rest:
	fwrite($handle,'<tr><td><a href="./now_playing_popout.php?not_done=1">');
	fwrite($handle,'<img src="./ampjukeicons/popout.gif" ');
	fwrite($handle,'border="0"></a></td></tr>');
	if (isset($display_user_agent)) {
		fwrite($handle,'<tr><td>You user-agent (media player) is:<br><b>'.$_SERVER["HTTP_USER_AGENT"]);
		fwrite($handle,'</b></td></tr>');
	}	
	fwrite($handle,'</table>');
	fclose($handle);	

	// 0.7.2: Update what's up next:
	// First, open the users m3u file in ./tmp		
	$handle=fopen('./tmp/'.get_username($user_id).'.m3u', 'r');

	// Second get contents, lookup line w. track-ID of currently playing track as well as
	// set the ID of the NEXT track to be streamed (will be 0 if we're at the end):
	$found=0;
	$currid=0;
	$nextid=-1;
	while ((!feof($handle)) && ($found<2)) {
		$buf=fgets($handle,4096);
		if (substr($buf,0,7)=="http://") {
			$params=explode("&",$buf);
			foreach ($params as $value) {
				if (substr($value,0,3)=="id=") {
					$tmpid=substr($value,3);
					if (($found==0) && ($currid==0) && ($tmpid==$id)) { // Found CURRENT:
						$currid=$tmpid;
						$found++;
					}
					if (($found==1) && ($nextid==-1) && ($tmpid<>$currid)) { // Found NEXT:
						$nextid=$tmpid;
						$found++;
					}	
				}	
			}
		}	
	}		
	fclose($handle);	

	// Third, APPEND whatever we found to the ./tmp/npXX.txt file:
	// 0.7.5: ALSO do this for the ./tmp/npXXpop.txt file (what's up next) - however: text only
	$handle=fopen('./tmp/np'.$user_id.'.txt', 'a');
	$h=fopen('./tmp/np'.$user_id.'pop.txt', 'a'); // 0.7.5
	fwrite($handle,'<table class="ampjuke_now_playing">');
	fwrite($h,'<table class="ampjuke_now_playing">'); // 0.7.5
	if (($found==2) && ($nextid>0)) { // Yes, we found the NEXT id AND it was defined:
		fwrite($handle,'<tr><td>'.xlate("Next track").':</td></tr>'); 
		fwrite($h,'<tr><td>'.xlate("Next track").':</td></tr>'); // 0.7.5
		$nextpname=get_performer_name(get_performer_id($nextid));
		$row=get_track_extras($nextid);		
		fwrite($handle,'<tr><td>'.add_performer_link($nextpname,$row['performer_id']).'</td>');
		fwrite($handle,'</tr><tr><td class="content">'.$row['name']);
		fwrite($h,'<tr><td>'.$nextpname.'</td>'); // 0.7.5
		fwrite($h,'</tr><tr><td class="content">'.$row['name']); // 0.7.5
		if ($row['year']!="") {
			$n=add_year_link($row['year'],$row['year']);
			$x=str_replace('<td class="content">','[',$n);
			$n=str_replace('</td>',']',$x);
			fwrite($handle,' '.$n.'</td></tr>');
			// 0.7.5:
			if ($row['year']<>'') {
				fwrite($h,' ['.$row['year'].']</td></tr>');
			}	
		}
	} 
	if (($found==2) && ($nextid==0)) { // Yes, we found NEXT id - its random play (nextid=0):	
		fwrite($handle,'<tr><td>'.xlate("Next track").':</td></tr>'); 		
		fwrite($handle,'<tr><td class="content">');
		fwrite($handle,xlate("Random play").'</td></tr>');
		// 0.7.5: Do the same for npXX.pop.txt:
		fwrite($h,'<tr><td>'.xlate("Next track").':</td></tr>'); 		
		fwrite($h,'<tr><td class="content">');
		fwrite($h,xlate("Random play").'</td></tr>');
	}	
	fwrite($handle,'</table>');
	fclose($handle);
	// 0.7.5:
	fwrite($h,'</table><table class="ampjuke_now_playing">');		
	fwrite($h,'<tr><td class="content" align="center">');	
	fwrite($h,'<a href="javascript: self.close ()">AmpJuke</a>');
	fwrite($h,'...and YOUR hits keep on coming !</td></tr></table>');
	fclose($h);	
} // update_now_playing

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


// Get whatever is specified after "-b" or after "--abr":
function get_lame_bitrate_parameter($param) { 
	$ret=128; // The default, if no "-b" or "--abr" is found.
	$found=0; // Nothing's found, yet !
	
	// First: Look for the -b xxx parameter, if present.
	$i=0;
	$p=explode('-',$param);
	foreach($p as $v) {
		$i++;
		if (substr($v,0,1)=='b') {
			$ret=trim(substr($v,1));
			$found++;
		}	
	}	

	// Second: If no -b xxx parameter was found, look for --abr xxx:
	if ($found==0) { // 0.7.1: Only process if we haven't found the -b xxx stuff above:
		$i=0;
		$p=explode('--',$param);
		foreach($p as $v) {
			$i++;	
			if (substr($v,0,3)=='abr') {
				$ret=trim(substr($v,3));
				$found++;
			}	
		}	
	}	
	return $ret;
}

/* 0.7.0: Transcode using LAME, AND stream the f*cker.
*/
function lame_convert($lame_path,$lame_parameters,$id,$row) {
	header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0'"); 	
	header("Pragma: public");
	header("Content-type: audio"); 

	// Calculate the APPROXIMATE size of Content-Length.
	// The reason ? The media-player might show a "funny" length of the track.
	// This might cause problems in some players - ESPECIALLY Windows Media Player...
	$item=split(":",$row['duration']);
	// Get total seconds:
	$s=$item[1] + ($item[0]*60); 
	// Get wanted bitrate (If none is found $bps will be set to 128):
	$bps=get_lame_bitrate_parameter($lame_parameters) * 1000; 
	// Calculate Content-Length based on the length and required bitrate:
	$cl=($bps*$s)/8; 
	// Send it:
	header('Content-Length: '.$cl);
	
	// An array of 'pipes' for in-/out-put during transcoding:		
	$desc_spec = array(
	0 => array("pipe", "r"),  // 0=stdin
	1 => array("pipe", "w"),  // 1=stdout
	2 => array("file", "./tmp/lame-errors.txt", "a") 
	// 2=stderr. In this case it's a file to write to in AmpJuke's tmp-dir.
	);
		
	// Note the '-' at the end: It's equal to stdout
//	$cmd=$lame_path.' '.$lame_parameters.' "'.$row['path'].'" -';	

	//******************************************************************************
    // 0.8.2: Lame transcoding : ** MODIFIED VERSION TO INCLUDE ID3 TAGS WHILE TRANSCODING **
	$trackname=$row['name'];
	$tracknumber=$row['track_no'];

	$qry="SELECT * FROM performer WHERE performer.pid=".$row['performer_id'];
	$performer_result=execute_sql($qry,0,1,$nr);
	$performer_row=mysql_fetch_array($performer_result);
	$perfomer_name=$performer_row['pname'] ;

	$qry="SELECT * FROM album WHERE album.aid=".$row['album_id'];
	$album_result=execute_sql($qry,0,1,$nr);
	$album_row=mysql_fetch_array($album_result);
	$album_name=$album_row['aname'] ;

	$id3add='--add-id3v2  --tt "'.$trackname.'" --tn "'.$tracknumber.'" --ta "'.$perfomer_name.'" --tl "'.$album_name.'" ' ;
	$cmd=$lame_path.' '.$lame_parameters.' "'.$row['path'].'" - '.$id3add;
    //*******************************************************************************

	$process = proc_open($cmd, $desc_spec, $pipes);	

	// If everything's OK, go on a grab whatever Lame outputs, and send it:		
	if (is_resource($process)) {
		while (!feof($pipes[1]) && !connection_aborted()) {
			echo fgets($pipes[1], 1024);
		}	
		// Clean up:
		flush();
		fclose($pipes[0]);
		fclose($pipes[1]);
		proc_close($process);
	} 
	update_stats($row['id']);	
}	

// Stream one track and update the stats for that track:
function stream_track($id,$row,$user_id,&$first_header = 0) {
	if ($first_header==1) { 
		header("Cache-Control: no-cache, must-revalidate"); 
		header('Content-type: audio'); 
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s T"), date('U'));
		$first_header=0;
	}
	readfile($row['path']);
	update_stats($row['id']);
}

// 0.7.1: Write the name of the _last_ performer streamed to a temporary text-file (last_perf_XX.txt).
// This is used when the user wants related performers streamed after last track...
// ...aka. "last.fm mode"...see next function.
//
// 0.8.6: ALSO: Write the ID of the TRACK to a temporary text-file (last_track_XX.txt).
// It is used when the user wants related tracks streamed after last track...
// ...aka. "Echonest mode".
function update_last_streamed($user_id,$id) {
	$pid=get_performer_id($id);
	$pname=get_performer_name($pid);
	$handle=fopen('./tmp/last_perf_'.$user_id.'.txt', "w");
// 0.7.7: HA! *REALLY* *REALLY* easy: change $pname to $pid and we're home free with the new way of storing stuff in ./lastfm:
	fwrite($handle,$pid);
	fclose($handle);
// 0.8.6: The echonest-stuff (similar to the track-thing above):
	$handle=fopen('./tmp/last_track_'.$user_id.'.txt', 'w');
	fwrite($handle,$id);
	fclose($handle);
}	


// 0.7.1: Get a random track id based on related performers, - "related" is equal
// to the performer name of the last streamed track.
function get_related_track($user_id,$lastfm_max_related_artists,$lastfm_min_related_match,$debug = '0') {
	if ((isset($debug)) && ($debug=='1')) { mydebug('stream_radio.php','Start: get_related_track function...'); }
	$base_lastfm='./lastfm/';
	$ret=0; // Default is to return a track-id of 0 -> "no related performers found"
	require_once("disp.php");
	// Do we have a txtfile file w. name of last performer that was streamed ?
	if (file_exists('./tmp/last_perf_'.$user_id.'.txt')) { // Yes...go on:
		$handle=fopen('./tmp/last_perf_'.$user_id.'.txt', "r");
		$lp=fread($handle,4096);
		fclose($handle);
		if ((isset($debug)) && ($debug=='1')) { mydebug('stream_radio.php','./tmp/last_perf_'.$user_id.'.txt exists with a value of:'.$lp); }
		
		// First, check if we have a local, cached version of related performers:
		if ((file_exists($base_lastfm.$lp.'.xml')) && (is_readable($base_lastfm.$lp.'.xml'))) {
			// Yes, cached version exists - just use that:
			$file=$base_lastfm.$lp.'.xml';
			// touch($base_lastfm.$lp.'.xml');
 		} else { // No, cached file is not available - ask for related performers from last.fm:
		/*
			$file="http://ws.audioscrobbler.com/1.0/artist/";
			$lp=get_performer_name($lp);
			$file.=urlencode($lp)."/similar.xml";	
			// 0.7.7: Changed to:			
		*/
			$lp=get_performer_name($lp);
			$file='http://ws.audioscrobbler.com/2.0/?method=artist.getsimilar';
			$file.='&artist='.urlencode($lp).'&api_key=b25b959554ed76058ac220b7b2e0a026';
		}
		$xml=retrieve_xml($file,$n,$lastfm_max_related_artists);// Get cached file or ask last.fm (see above)
		if ((isset($debug)) && ($debug=='1')) { mydebug('stream_radio.php','Got XML-contents from '.$file); }
		
		// Second, filter out related performers that doesn't meet our min. match score:
		$count=0;
		$n=0;
		while ($n<$lastfm_max_related_artists) {
			if ((isset($xml->similarartists->artist[$n]->name[0])) 
			&& ($xml->similarartists->artist[$n]->match[0]>=$lastfm_min_related_match)) { // 0.7.7 "similarartists" inserted
				$count++;
			}	
			$n++;
		}	
		
		// Third, filter out related performers we don't have in our AmpJuke database:
		$n=0;
		$i=0; // Array index
		$ia=array(); // The array.		
		while ($n<$count) {		
			// Do we have the performer in the database... ?
			$qry="SELECT * FROM performer WHERE pname='";
			$qry.=$xml->similarartists->artist[$n]->name[0]."'";
			$nr=0;
			$result=execute_sql($qry,0,2,$nr);
			if ($nr>0) { // ...yes - we have the performer. Put it into the array:
				$row=mysql_fetch_array($result);
				$ia[$i]=$row['pid'];
				$i++;
				if ((isset($debug)) && ($debug=='1')) { mydebug('stream_radio.php','Artist: '.$row['pid'].' '.$row['pname'].' exists'); }
			}
			$n++;
		}
		// Pick a random performer-id from the array:
		$i--;
		srand(date("U")+rand());
		if (count($ia)>0) { // 0.8.5
			$victim=$ia[rand(0,$i)]; 
		}
		if ((isset($debug)) && ($debug=='1')) { mydebug('stream_radio.php','Related performer-id (randomly selected)='.$victim); }
		
		// Fourth, get all tracks from the "victim" (the related performer):
		$i=0; // Array index
		$ia=array(); // The array
		$qry="SELECT * FROM track WHERE performer_id='".$victim."'";
		$result=execute_sql($qry,0,100000,$nr);
		while ($row=mysql_fetch_array($result)) {
			$ia[$i]=$row['id'];
			$i++;
			if ((isset($debug)) && ($debug=='1')) { mydebug('stream_radio.php','Track: '.$row['id'].' '.$row['name']); }
		}

		// Fifth, and final step, pick a random track-ID from the array w. performer's tracks:
		$i--;
		srand(date("U")+rand());
		if (count($ia)>0) { // 0.8.5
			$ret=$ia[rand(0,$i)]; 
		}
		if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','END. Get related performer. Will return: '.$ret. ' I='.$i); }
	} // if file_exists...
	return $ret; // Return track-ID (if found), otherwise 0 (no track found)
}	


// 0.7.2: Used to POST a 'just listened' track to last.fm:
function post_request_to_lastfm($url, $data) {
    $params = array('http' => array(
                 'method' => 'POST',
                 'content' => $data
              ));
/*              
    if ($optional_headers <> '') {
       $params['http']['header'] = $optional_headers;
    }
*/    
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
    if (!$fp) {
       throw new Exception("Problem with $url, $php_errormsg");
    }
    $response = @stream_get_contents($fp);
    if ($response === false) {
       throw new Exception("Problem reading data from $url, $php_errormsg");
    }
    return $response;
}


// 0.7.2: Submit track to last.fm:
function update_last_fm($title,$performer,$dur,$lastfm_user,$lastfm_password,$debug = '0') {
 	
	if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','start: update_last_fm function...'); }
 	
	// Setup some stuff - do NOT change ANYTHING of this (last.fm requirements):
	$lastfm_audioscrobbler='http://post.audioscrobbler.com/?hs=true';
	$lastfm_protocol='1.2';
	$lastfm_client_id='amj';
	$lastfm_client_ver='0.1';
	$lastfm_timestamp=time();
	$lastfm_token = md5(md5($lastfm_password).$lastfm_timestamp);

	// Build the handshake URL:
	$url=$lastfm_audioscrobbler.'&p='.$lastfm_protocol;
	$url.='&c='.$lastfm_client_id.'&v='.$lastfm_client_ver;
	$url.='&u='.$lastfm_user;
	$url.='&t='.$lastfm_timestamp;
	$url.='&a='.$lastfm_token;

	if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','The url is: '.$url); }	

	// Get the handshake's SessionID and submission URL:
	$handle=fopen($url, "r");
	$line=0;
	while (!feof($handle)) {
		$buf=fgets($handle,4096);
		if ($line==1) { $md5tmp=trim($buf); } // Last.fm's handshake SessionID
		if ($line==3) { $newurl=trim($buf); } // Last.fm's submission URL
		$line++;
	}
	fclose($handle);	

	// Convert dur (mm:ss) to seconds:
	$item=split(":",$dur);
	$duration=$item[1] + ($item[0]*60);	

	// Build the query string to be POST'ed:
	$n='s='.$md5tmp; // SessionID returned by handshake
	$n.='&a[0]='.rawurlencode($performer); // Artist
	$n.='&t[0]='.rawurlencode($title); // Title
	$n.='&i[0]='.$lastfm_timestamp; // Time (UNIX-style) the track started playing
	$n.='&o[0]=P'; // Source: PRELU - only P & L are supported
	$n.='&r[0]='; // Rating
	$n.='&l[0]='.$duration; // Length , seconds
	$n.='&b[0]='; // rawurlencode...Album name, or empty if unknown
	$n.='&n[0]='; // Track number, or empty if unknown
	$n.='&m[0]='; // MusicBrainz ID

	$ret=post_request_to_lastfm($newurl,$n); // POST it to last.fm
	if ((isset($debug)) && ($debug=='1')) { 
		mydebug('stream.php','Submitted '.$performer.' '.$title.' ('.$duration.' secs.) to last.fm'); 
		mydebug('stream.php','Last.fm responded: '.$ret); 
	} 		
	return $ret;
}

function get_lastfm_settings($uid,&$username,&$password,$debug = '0') {
	if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','start: get_lastfm_settings function...'); }	
 	include("db.php");
 	require_once("sql.php");
	$ret=0;
	// Get the defaults (from db.php), if set:
	if ((isset($lastfm_default_username)) && (isset($lastfm_default_password)) 
	&& ($lastfm_default_username<>'') && ($lastfm_default_password<>'')) {
		$ret=1;
		$username=$lastfm_default_username;
		$password=$lastfm_default_password;
		if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','last.fm user='.$username.' (the default)'); } 		 		
	}
	// Do we allow users own last.fm username/password to be used ?
	if (($lastfm_allow_local_users=="1")) {
		$qry="SELECT * FROM user WHERE id=".$uid;
		$result=execute_sql($qry,0,1,$nr);
		if ($nr==1) {
			$row=mysql_fetch_array($result);
			if ($row['lastfm_active']=="1") { // Yes, the user wants to submit to last.fm using own credtent.:
				$username=$row['lastfm_username'];
				$password=$row['lastfm_password'];
				$ret=1;
				if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','last.fm user='.$username.' (uid='.$uid.')'); }					
			}
		}
	}
	if ((isset($debug)) && ($debug=='1')) { mydebug('stream.php','end: get_lastfm_settings function...return:'.$ret); }
	return $ret;
}				

// 0.8.6: Pick a track from the requests, return the ID, set the ID to '1' (streamed):
function pick_request($max_last_played,$jukebox_mode_request_pick, $debug = '0') {
    // 0.8.7: Configurable options:
//    $avoid_same_perf_enabled=1;
//    $avoid_same_perf_num=5;
// ...0.8.8: YES, they are from now on: jukebox_mode_min_age_performer
    
    $debug='0';
    $ret=0;
    if ((isset($debug)) && ($debug=='1')) {
        mydebug('stream_radio.php','jukebox_mode_request_pick is: '.$jukebox_mode_request_pick);
    }
 	include("db.php");
 	require_once("sql.php");
	require_once('disp.php');
	
	// Get the request queue:
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%' ";
	if ($jukebox_mode_request_pick=='First-come-first-serve') { // pick from 1st entry within the queue:
		$qry.="ORDER BY qid ASC";
	} else { // pick random
		$qry.="ORDER BY RAND()";
	}
	$result=execute_sql($qry,0,100,$nr);

	if ((isset($debug)) && ($debug=='1')) {
        mydebug('stream_radio.php','qry='.$qry.' nr='.$nr.' rsrp='.$jukebox_mode_request_pick);
    }
	
	// Find an entry we haven't streamed, yet:
	$found=0;
	while (($row=mysql_fetch_array($result)) && ($found==0)) {
		$a=get_request_array($row['user_name']);
		if ($a[3]=='0') { // we haven't streamed this request, yet:
		    // 0.8.7: Also check against last "age" of performers streamed:
		    $found_recent=0;
		    if ($jukebox_mode_min_age_performer>0) {
		        $performer_id=get_performer_id($row['track_id']);
		        $found_recent=check_recently_played_performer($performer_id,$jukebox_mode_min_age_performer);
		    }
		    if ($found_recent==0) {
       			$found=$row['qid'];
    			$ret=$row['track_id'];
   	    		$l=$a[0].';'.$a[1].';'.$a[2].';1';
   	    	}
		}
	}
	
	// Update the queue, so we don't stream the same request again:
	if ($found<>0) {
		$qry="UPDATE queue SET user_name='".$l."' WHERE qid='".$found."'";
		$result=execute_sql($qry,0,-1,$dummy);
	}
	
    if ((isset($debug)) && ($debug=='1')) {
        mydebug('stream_radio.php','Picked a track. The ID that will be returned is: '.$ret);
    }
    return $ret;
}

// ****************************************************************
//
// 		ID is set & 0: Stream FOREVER based on random preferences:
//
// ****************************************************************
if ($id==0) {
    // 0.8.7: Configurable options:
    //$avoid_same_perf_enabled=1;
    //$avoid_same_perf_num=5;
	// 0.8.8: Commented out - it's in db.php as jukebox_mode_min_age_performer=x (x=hours)
    /*
	if ((!isset($jukebox_mode_enabled)) || ($jukebox_mode_enabled=='0')) {
	    $jukebox_mode_min_age_performer=1; // set it to 1 hour (avoid streaming tracks from same performer)
	}
	*/

	@ini_set('output_buffering', 0);
	require_once("disp.php");
	require_once('lastfm_lib.php'); // 0.8.2
	require_once('echonest_lib.php'); // 0.8.6
 	$first_header=1; // avoid "headers already sent..."

	if (!isset($preference)) { 
		$preference="";
	}	
	if (isset($update_now_playing)) {
		update_now_playing_light($user_id,$np_light_update_msg,'');
	}	
	// 0.7.3: Get special extensions, if defined:
	if ((isset($special_extensions_enabled)) && ($special_extensions_enabled=="1")) {
		$special_extensions=explode(',',$special_extensions);
	} else {
		$special_extensions=array();
	}	
	$u=get_user_details($user_id); // 0.8.2: Used later, - see 0.8.2-comments below

	while ((true) && (!connection_aborted())) { // do forever or until client quits
		// 0.8.5: Do we have $max_last_played ("Avoid selection of tracks...") set ?
		if (!isset($max_last_played)) { // No, we don't - set it in the past:
			$max_last_played=date('U')-($jukebox_mode_min_age * 3600); // 0.8.8: jukebox_mode_min_age (hours) is set in db.php
			if ((isset($debug)) && ($debug=='1')) { 
				mydebug('stream_radio.php','max_last_played not set. Setting it to: '.$max_last_played.' ('.date('Y-m-d H:i:s',$max_last_played).')');
				mydebug('stream_radio.php',' now:'.date('U').' ('.date('Y-m-d H:i:s').')'); 
				mydebug('stream_radio.php',' jukebox_mode_min_age_performer='.$jukebox_mode_min_age_performer.' HOURS');
			}			
		}
		
        // ****** Find out what selection mode we have (based on the jukebox user's preferences: 
        // Settings->Automatic play->After last track):
		if ($what=="Tracks") { // tracks (general), not related or anything:
			$qry="SELECT * FROM track ";
			$qry.="WHERE last_played<".$max_last_played." "; // 0.8.5
			$qry.=get_recently_played_performers($jukebox_mode_min_age_performer,'Fav'); // 0.8.7
			$qry.=get_random_preference($preference,"Tracks");			
		}	
		if ($what=="Related") { // Related PERFORMERS/ARTISTS:
			$track_id=get_related_track($user_id,$lastfm_max_related_artists,$lastfm_min_related_match,$debug);
			if ($track_id==0) {
				$qry="SELECT * FROM track ";
				$qry.="WHERE last_played<".$max_last_played." "; // 0.8.5
				$qry.=get_recently_played_performers($jukebox_mode_min_age_performer,'Fav'); // 0.8.7
				$qry.=get_random_preference($preference,"Tracks");
			} else {
				$qry="SELECT * FROM track WHERE id='".$track_id."'";
				$qry.=get_recently_played_performers($jukebox_mode_min_age_performer,'Fav'); // 0.8.7
				$qry.=" AND last_played<".$max_last_played; // 0.8.5
			}	
		}
    	// 0.8.6: Echonest: Stream based on "parameters"/related tracks:
		if ($what=='Echonest') { // Related TRACKS:
			// First, get the ID of the previous track that was streamed:
            // 0.8.6: ...IF there's a .../last_track....txt available:
            //
            if (file_exists('./tmp/last_track_'.$user_id.'.txt')) {
    			$prev_id=only_digits(file_get_contents('./tmp/last_track_'.$user_id.'.txt'));
	    		if ((isset($debug)) && ($debug=='1')) {
	    			mydebug('stream_radio.php','Echonest -> find something similar to PREVIOUS track: '.$prev_id);
	    		}
    			// Second, get the echonest STATUS for the previously played track:
    			$prev_status=echonest_get_track_status($prev_id);
            } else { // 0.8.6: ...else just set prev_id=0 (completely random track will be selected):
                $prev_status=0;
            }
            //
			// Second: If we do have a "valid" prev_status, we have the opportunity to execute a query based on "related" tracks:
            //
			if (($prev_status<>'0') && ($prev_status<>'-1')) { // Yup: Something useful:
				$prev_track=get_track_extras($prev_id);
				$qry=echonest_construct_related_track_query($prev_track,$max_last_played,$debug);
			} else { // We don't have a "valid" status for the last track: Select by random:
				$qry="SELECT * FROM track WHERE id<>'".$prev_id."'";
				$qry.=get_recently_played_performers($jukebox_mode_min_age_performer,'Fav'); // Avoid same performer for X hours...
				$qry.=" AND last_played<'".$max_last_played."' ORDER BY rand()"; // ...and also avoid playing same track for Y hours
			}
		}

       	if (($what!="Tracks") && ($what!="Related") && ($what!='Echonest')) { // Stream from a specific favorite list:
            // Check scheduler & change accordingly to new favorite list
            $newwhat=schedule_change($what,$debug); 
            if (strlen($newwhat)>2) {
                $what=$newwhat;
            }
            
            $qry="SELECT * FROM fav WHERE user_id='".$user_id."'";
            $qry.=" AND fav_name='".rawurldecode($what)."' AND track_id>0 ";
            $qry.=" AND last_played<".$max_last_played." "; // 0.8.5
            $qry.=get_recently_played_performers($jukebox_mode_min_age_performer,'Fav'); // 0.8.8
            $qry.=get_random_preference($preference,"Fav");
            if ((isset($debug)) && ($debug=='1')) {
                mydebug('stream_radio.php','Select from specific favorite list: '.$what.'<br> '.$qry);
            }
            // ***
            // *** 0.8.8: ATTEMPT to narrow down the selection by also including echonest's "fingerprint" in the query:
            // ***
            // First, save the previous query, setup some stuff etc.:
            $oldqry=$qry; // saved!
            $found=0; // success - or not.
            // Filter out the order by clause:
            $qry=str_replace('ORDER BY rand()','AND (',$qry);
            // Second, see if there's details about the previously streamed track (same method as above):
            if (file_exists('./tmp/last_track_'.$user_id.'.txt')) {
    			$prev_id=only_digits(file_get_contents('./tmp/last_track_'.$user_id.'.txt'));
    			$prev_status=echonest_get_track_status($prev_id);
            } else { 
                $prev_status=0;
            }
            // Third - if we have a previously streamed track's ID: fetch it & use it to get related tracks
            if (($prev_status<>'0') && ($prev_status<>'-1')) {
        		$prev_track=get_track_extras($prev_id);
                $listqry=echonest_construct_related_track_query($prev_track,$max_last_played,'0');
                // Fourth - we're still here - loop through the $listqry and check to see if each track is in the $what (favorite)...
                // ...if it is then add it to the $qry:
                $listresult=execute_sql($listqry,0,20,$listnr); // Note: a hardcoded limit of 20 exists for this, currently (0.8.8)
                //mydebug('SR','***** LISTQRY='.$listqry.'<br>Hits='.$listnr);
                if ($listnr>0) {
                    $first=1;
                    while ($listrow=mysql_fetch_array($listresult)) {
                        if ($first==1) {
                            $qry.="track_id='".$listrow['id']."' ";
                            $first=0;
                        } else {
                            $qry.="OR track_id='".$listrow['id']."' ";
                        }
                    }
                    $qry.=") ORDER BY RAND()";
                    // Test it:
                    $testresult=execute_sql($qry,0,1,$found);                  
                    //mydebug('SR','***** NEW QRY='.$qry.'<br>Hits='.$found);
                }
            }
            // Last: Did we get anything 
            if ($found<>1) { // No:                 
                $qry=$oldqry;
                //mydebug('SR','***** Nothing found -> fallback to old qry:<br>'.$qry);
            } 
            // ***
            // *** 0.8.8: ends
            // ***              
 		}	

        // 0.8.6: Do we "care" and do we have a pending request ?
        // Finally - and with the highest priority - check outstanding user-requests from the queue:
		$request_taken=0;
        if (rand(1,100)<=$jukebox_mode_request_probability) { // Yes - we do "care"...
            // Pick a track from the list:
            $new_id=pick_request($max_last_played,$jukebox_mode_request_pick);
            if ($new_id<>0) { // Yes - we had a pending request
				//mydebug('stream_radio.php','Picked a request - trackID: '.$new_id);
                $qry="SELECT * FROM track WHERE id='".$new_id."'";
				$request_taken=1;
            }
        }    
		
		$result=execute_sql($qry,0,1,$nr);
        
		/*
		if ((isset($debug)) && ($debug=='1')) { 
			mydebug('stream_radio.php','The initial query: '.$qry.' NR='.$nr); 
		}
		*/
		// 0.8.5: Due to the "limit" introduced above ($max_last_played), it might NOT have been possible to get a track outside the limit.
		// If not, just pick *any* track by random.
        // This is the default choice, if anything else fails:
		if ($nr<>1) { // We didn't get anything - pick by random (completely) without $max_last_played as the limit:
			$qry="SELECT * FROM track ".get_random_preference($preference,'Tracks');
			$result=execute_sql($qry,0,1,$nr);
			// 0.8.8: Fake it & fall back:
            $row=mysql_fetch_array($result);
            $new_id=$row['id'];
            $request_taken=1;
			if ((isset($debug)) && ($debug=='1')) { 
				mydebug('stream_radio.php','Could NOT find anything - select a track COMPLETELY RANDOMIZED:');
				mydebug('stream_radio.php',$qry.' NR='.$nr.'<br>&nbsp<br>'); 
			}
						
		}

		// Get the track based on the outcome of the selection above:
		$row=mysql_fetch_array($result);
	
		if (($what=="Tracks") || ($what=="Related") || ($what=='Echonest')) { // 0.8.6: Added 'Echonest'
			$qry="SELECT * FROM track WHERE id=".$row['id'];
		} else {
			if ($request_taken==0) { // we didn't take a request: do as usual
				$qry="SELECT * FROM track WHERE id=".$row['track_id'];
			}
			if ($request_taken==1) { // we took a request, - change the qry:
				$qry="SELECT * FROM track WHERE id=".$new_id;
			}
		}	 			

        // Prepare to stream the track:	
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		update_last_streamed($user_id,$row['id']); // 0.7.1		
		
		if ((isset($np_update_automatic_play)) && ($np_update_automatic_play==1)) {
		 	if ((isset($update_now_playing)) && (isset($allow_now_playing)) && ($allow_now_playing==1)) {
				if ($simple_update_now_playing==0) {
					update_now_playing($row['id'],$row['name'],$row['performer_id'],$row['year'],$row['album_id'],$user_id,$language); // 0.8.2: $row['id'] rather than just $id....
				} else {
				update_now_playing_light($user_id,get_performer_name($row['performer_id']).
				'<br>'.$row['name'],$row['year']);
				}
			}				
		}	
		
		// 0.8.2: Downsample if we're from a specific IP-address ?
		if ((isset($lame_dynamic_enabled)) && ($lame_dynamic_enabled==1) && (strlen($lame_dynamic_iplist)>4)) {
			if ((isset($debug)) && ($debug=='1')) {
				mydebug('stream_radio.php','LAME_DYNAMIC_ENABLED='.$lame_dynamic_enabled. ' IPs'.$lame_dynamic_iplist);
			}	
			$lame_dynamic_iplist.=',';
			$ip_range=explode(',',$lame_dynamic_iplist);
			foreach($ip_range as $range) {
				$range = str_replace('*','(.*)', $range);
				$range=trim($range);
				if ((isset($debug)) && ($debug=='1')) {
					mydebug('stream_radio.php','ITEM='.$range);
				}	
				if ((preg_match('/'.$range.'/', $_SERVER["REMOTE_ADDR"])) && (strlen($range)>4)) {
					if ((isset($debug)) && ($debug=='1')) {
						mydebug('stream_radio.php',$range.' is in the list!');
					}	
					lame_convert($lame_path,$lame_parameters,$id,$row);	
					$lame_enabled=2; // Just to avoid 2nd play of track.
				}
			}
		}			
		
		// 0.7.0: Lame enabled ??
		if ((isset($lame_enabled) && ($lame_enabled==1)) && (get_local_lame($user_id,$lle)==1)) {
			if ($lle<>'') { $lame_parameters=$lle; }
			lame_convert($lame_path,$lame_parameters,$id,$row); // STREAM IT (conversion to different bitrate)
		} else {
			if ($lame_enabled<>2) { 
			    if ((isset($debug)) && ($debug=='1')) {
			        mydebug('stream_radio.php',' ');
			        mydebug('stream_radio.php',' ');
			        mydebug('stream_radio.php','*****');
  			        mydebug('stream_radio.php','***** Stream track: '.get_performer_name($row['performer_id']).' - '.$row['name']);
			        mydebug('stream_radio.php','*****');
			        mydebug('stream_radio.php',' ');
			        mydebug('stream_radio.php',' ');
                }  			        			        			        
			    stream_track($id,$row,$user_id,$simple_update_now_playing); // STREAM IT
			} // 0.8.2
		}	
		// 0.7.2: Submit track to last.fm...?
		if ($lastfm_allow_submission=="1") { // Are we allowed to ?
			$ok=get_lastfm_settings($user_id,$l_user,$l_pass,$debug);
			if ($ok==1) {
				$d=update_last_fm($row['name'],get_performer_name($row['performer_id']),$row['duration'],$l_user,$l_pass,'0'); // 0 -> $debug
			}	
		}
		// 0.8.2: Automatically add to a favorite list ?
		if ((isset($lastfm_allow_auto_add2favorite)) && ($lastfm_allow_auto_add2favorite=='1') && ($u['auto_add2favorite']=='1')) { 
			$perf=get_performer_name($row['performer_id']);
			$toptags=lastfm_get_toptags('track',$row['id'],$perf,$row['name']);
			$x=0;
			if ((is_array($toptags)) && (count($toptags)>0)) {
				while ($x<count($toptags)) {
					add_tr($row['id'],$user_id,$u['auto_add2favorite_prefix'].$toptags[$x]);
					$x++;
				}	
			}
		}
		unset($max_last_played); // 0.8.5
		// 0.8.6: Echonest stuff:
		if ((isset($echonest_enabled)) && ($echonest_enabled=='1')) {
			$already_analyzed=echonest_get_track_status($row['id'],$debug);
			if ($already_analyzed=='-1') { // we have not looked at this track, yet:
				$ok=echonest_lookup_track($row['id'],$debug); 
			}
		}
		if ((isset($debug)) && ($debug=='1')) {
			mydebug('stream_radio.php',' ');
			mydebug('stream_radio.php',' ');
			mydebug('stream_radio.php',' ');			
		}
	} // while...FOREVER!

}	



die(); 
?>		
