<?php
// This is the old stream-method in AmpJuke.
// Move to parent directory, and rename to "stream.php" if you want to give it a try.
// November 2008 / Michael H. Iversen.

// AmpJuke tweaks:
//$display_user_agent=1; // Uncomment to see what your media player identifies itself as:
die('Sorry...');
$debug=1; // Uncomment to write debug stuff to ./tmp/debug.txt

// checks/setup:
parse_str($_SERVER["QUERY_STRING"]);

if (!isset($id)) {
 	exit;
}
if (!is_numeric($id)) {
	exit;
}
if ($id<0) {
 	exit;
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

include("db.php");
include("sql.php");
include("configuration.php");
require_once("disp.php");

// 0.7.3: The "upw" parameter is required AND it must match - unless we allow anonymous access:
if (($allow_anonymous!="1") && ($login!="anonymous")) {
	$user=get_username($user_id);
	if ((!isset($upw)) || ($upw!=get_md5_passwd($user))) {
		die();
	}	
}

// Find out if the media-player used should show "light" updates of "Now playing":
$simple_update_now_playing=0;
$user_agent=$_SERVER["HTTP_USER_AGENT"];
$plist=explode("*",$np_light_update);
str_replace($plist,"",$user_agent,$simple_update_now_playing);

/*

			SUPPORTING FUNCTIONS

*/

function update_now_playing_light($user_id,$name,$year) {
	global $display_user_agent;
	
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
	global $display_user_agent;
	require_once("disp.php");
	require_once("db.php");
	require_once("translate.php");
	$_SESSION['lang']=$language;
	

		$handle=fopen('./tmp/np'.$user_id.'.txt', 'w'); 
		$h=fopen('./tmp/np'.$user_id.'pop.txt', 'w');
		fwrite($handle,'<table class="ampjuke_now_playing">');
		fwrite($h,'<table class="ampjuke_now_playing">');
		fwrite($handle,'<tr><td class="content">'.xlate("Now playing").':</td></tr>');
		fwrite($h,'<tr><td class="content">'.xlate("Now playing").':</td></tr>');	
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
				fwrite($handle,'<tr>'.add_album_link($lnk,$aid).'</tr>');
				fwrite($h,'<tr><td>'.$lnk.'</td></tr>');	
				fwrite($handle,'<tr>'.add_album_link($n,$aid).'</tr>');
				fwrite($h,'<tr><td>'.$n.'</td></tr>');
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
		fwrite($h,'</table><table class="ampjuke_now_playing">');		
		fwrite($h,'<tr><td class="content" align="center">');	
		fwrite($h,'<a href="javascript: self.close ()">AmpJuke</a>');
		fwrite($h,'...and YOUR hits keep on coming !</td></tr></table>');
		fclose($h);	


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
			fwrite($h,'buf='.$buf);
			if (substr($buf,0,7)=="http://") {
				$params=explode("&",$buf);
				foreach ($params as $value) {
					fwrite($h,'value='.$value.chr(13).chr(10));					
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
		$handle=fopen('./tmp/np'.$user_id.'.txt', 'a');
		fwrite($handle,'<table class="ampjuke_now_playing">');
		if (($found==2) && ($nextid>0)) { // Yes, we found the NEXT id AND it was defined:
			fwrite($handle,'<tr><td>'.xlate("Next track").':</td></tr>'); 
			$nextpname=get_performer_name(get_performer_id($nextid));
			$row=get_track_extras($nextid);		
			fwrite($handle,'<tr>'.add_performer_link($nextpname,$row['performer_id']));
			fwrite($handle,'</tr><tr><td class="content">'.$row['name']);
			if ($row['year']!="") {
				$n=add_year_link($row['year'],$row['year']);
				$x=str_replace('<td class="content">','[',$n);
				$n=str_replace('</td>',']',$x);
				fwrite($handle,' '.$n.'</td></tr>');
			}
		} 
		if (($found==2) && ($nextid==0)) { // Yes, we found NEXT id - its random play (nextid=0):	
			fwrite($handle,'<tr><td>'.xlate("Next track").':</td></tr>'); 		
			fwrite($handle,'<tr><td class="content">');
			fwrite($handle,xlate("Random play").'</td></tr>');
		}	
		fwrite($handle,'</table>');
		fclose($handle);
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
	$cmd=$lame_path.' '.$lame_parameters.' "'.$row['path'].'" -';	
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

// 0.7.1: Write the name of the _last_ performer streamed to a temporary text-file.
// This is used when the user wants related performers streamed after last track...
// ...aka. "last.fm mode"...see next function
function update_last_streamed($user_id,$id) {
	$pid=get_performer_id($id);
	$pname=get_performer_name($pid);
	$handle=fopen('./tmp/last_perf_'.$user_id.'.txt', "w");
	fwrite($handle,$pname);
	fclose($handle);
}	


// 0.7.1: Get a random track id based on related performers, - "related" is equal
// to the performer name of the last streamed track.
function get_related_track($user_id,$lastfm_max_related_artists,$lastfm_min_related_match) {
// in case you suspect foul play from last.fm - uncomment to see log in ./tmp/debug.txt: 
// 	$debug=1; 
	if (isset($debug)) { mydebug('stream.php','Start: get_related_track function...'); }
	$base_lastfm='./lastfm/';
	$ret=0; // Default is to return a track-id of 0 -> "no related performers found"
	require_once("disp.php");
	// Do we have a txtfile file w. name of last performer that was streamed ?
	if (file_exists('./tmp/last_perf_'.$user_id.'.txt')) { // Yes...go on:
		$handle=fopen('./tmp/last_perf_'.$user_id.'.txt', "r");
		$lp=fread($handle,4096);
		fclose($handle);
		if (isset($debug)) { mydebug('stream.php','./tmp/last_perf_'.$user_id.'.txt exists with a value of:'.$lp); }
		
		// First, check if we have a local, cached version of related performers:
		if ((file_exists($base_lastfm.$lp.'.xml')) && (is_readable($base_lastfm.$lp.'.xml'))) {
			// Yes, cached version exists - just use that:
			$file=$base_lastfm.$lp.'.xml';
			touch($base_lastfm.$lp.'.xml');
 		} else { // No, cached file is not available - ask for related performers from last.fm:
			$file="http://ws.audioscrobbler.com/1.0/artist/";
			$file.=urlencode($lp)."/similar.xml";	
		}
		$xml=retrieve_xml($file,$n,$lastfm_max_related_artists);// Get cached file or ask last.fm (see above)
		if (isset($debug)) { mydebug('stream.php','Got XML-contents from '.$file); }
		
		// Second, filter out related performers that doesn't meet our min. match score:
		$count=0;
		$n=0;
		while ($n<$lastfm_max_related_artists) {
			if ((isset($xml->artist[$n]->name[0])) 
			&& ($xml->artist[$n]->match[0]>=$lastfm_min_related_match)) {
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
			$qry.=$xml->artist[$n]->name[0]."'";
			$nr=0;
			$result=execute_sql($qry,0,2,$nr);
			if ($nr>0) { // ...yes - we have the performer. Put it into the array:
				$row=mysql_fetch_array($result);
				$ia[$i]=$row['pid'];
				$i++;
if (isset($debug)) { mydebug('stream.php','Artist: '.$row['pid'].' '.$row['pname'].' exists'); }
			}
			$n++;
		}
		// Pick a random performer-id from the array:
		$i--;
		srand(date("U")+rand());
		$victim=$ia[rand(0,$i)];		
		if (isset($debug)) { mydebug('stream.php','Related performer-id (randomly selected)='.$victim); }
		
		// Fourth, get all tracks from the "victim" (the related performer):
		$i=0; // Array index
		$ia=array(); // The array
		$qry="SELECT * FROM track WHERE performer_id='".$victim."'";
		$result=execute_sql($qry,0,100000,$nr);
		while ($row=mysql_fetch_array($result)) {
			$ia[$i]=$row['id'];
			$i++;
			if (isset($debug)) { mydebug('stream.php','Track: '.$row['id'].' '.$row['name']); }
		}

		// Fifth, and final step, pick a random track-ID from the array w. performer's tracks:
		$i--;
		srand(date("U")+rand());
		$ret=$ia[rand(0,$i)];
		if (isset($debug)) { mydebug('stream.php','END. Get related performer. Will return: '.$ret. ' I='.$i); }
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
function update_last_fm($title,$performer,$dur,$lastfm_user,$lastfm_password) {
 	// uncomment following line to see some details in ./tmp/debug.txt:
 	// $debug=1;
 	
	if (isset($debug)) { mydebug('stream.php','start: update_last_fm function...'); }
 	
	// Setup some stuff - do NOT change ANYTHING of this (last.fm requuirements):
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
	if (isset($debug)) { 
		mydebug('stream.php','Submitted '.$performer.' '.$title.' ('.$duration.' secs.) to last.fm'); 
		mydebug('stream.php','Last.fm responded: '.$ret); 
		mydebug('stream.php','The function will return: '.$ret);
	} 		
	return $ret;
}

function get_lastfm_settings($uid,&$username,&$password) {
	// debug=1;
	if (isset($debug)) { mydebug('stream.php','start: get_lastfm_settings function...'); }	
 	include("db.php");
 	require_once("sql.php");
	$ret=0;
	// Get the defaults (from db.php), if set:
	if ((isset($lastfm_default_username)) && (isset($lastfm_default_password)) 
	&& ($lastfm_default_username<>'') && ($lastfm_default_password<>'')) {
		$ret=1;
		$username=$lastfm_default_username;
		$password=$lastfm_default_password;
		if (isset($debug)) { mydebug('stream.php','last.fm user='.$username.' (the default)'); } 		 		
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
				if (isset($debug)) { mydebug('stream.php','last.fm user='.$username.' (uid='.$uid.')'); }					
			}
		}
	}
	if (isset($debug)) { mydebug('stream.php','end: get_lastfm_settings function...return:'.$ret); }
	return $ret;
}				
		



// ID is set & 0: Stream forever based on random preferences:
if ($id==0) {
	@ini_set('output_buffering', 0);
	require_once("disp.php");
// 	$md5pw=get_md5_passwd($user); 0.7.3
 	$first_header=1; // avoid "headers already sent..."
	if (!isset($preference)) { 
		$preference="";
	}	
	$first_header=1;
	if (isset($update_now_playing)) {
		update_now_playing_light($user_id,$np_light_update_msg,'');
	}	
	// 0.7.3: Get special extensions, if defined:
	if ((isset($special_extensions_enabled)) && ($special_extensions_enabled=="1")) {
		$special_extensions=explode(',',$special_extensions);
	} else {
		$special_extensions=array();
	}	

	while ((true) && (!connection_aborted())) { // do forever or until client quits
		if ($what=="Tracks") {
			$qry="SELECT * FROM track ".get_random_preference($preference,"Tracks");			
		}	
		if ($what=="Related") {
			$track_id=get_related_track($user_id,$lastfm_max_related_artists,$lastfm_min_related_match);
			if ($track_id==0) {
				$qry="SELECT * FROM track ".get_random_preference($preference,"Tracks");
			} else {
				$qry="SELECT * FROM track WHERE id='".$track_id."'";
			}	
		}
		if (($what!="Tracks") && ($what!="Related")) {
			$qry="SELECT * FROM fav WHERE user_id='".$user_id."'";
			$qry.=" AND fav_name='".rawurldecode($what)."' AND track_id>0 ";
			$qry.=get_random_preference($preference,"Fav");
		}	

		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
	
		if (($what=="Tracks") || ($what=="Related")) {
			$qry="SELECT * FROM track WHERE id=".$row['id'];
		} else {
		 	$qry="SELECT * FROM track WHERE id=".$row['track_id'];
		}	 			
			
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		update_last_streamed($user_id,$row['id']); // 0.7.1		
		
		if ((isset($np_update_automatic_play)) && ($np_update_automatic_play==1)) {
		 	if ((isset($update_now_playing)) && (isset($allow_now_playing)) && ($allow_now_playing==1)) {
				if ($simple_update_now_playing==0) {
					update_now_playing($id,$row['name'],$row['performer_id'],$row['year'],$row['album_id'],$user_id,$language); 
				} else {
				update_now_playing_light($user_id,get_performer_name($row['performer_id']).
				'<br>'.$row['name'],$row['year']);
				}
			}				
		}	
		// 0.7.0: Lame enabled ??
		if ((isset($lame_enabled) && ($lame_enabled==1)) && (get_local_lame($user_id,$lle)==1)) {
			if ($lle<>'') { $lame_parameters=$lle; }
			lame_convert($lame_path,$lame_parameters,$id,$row);
		} else {
			stream_track($id,$row,$user_id,$simple_update_now_playing);
		}	
		// 0.7.2: Submit track to last.fm...
		if ($lastfm_allow_submission=="1") { // Are we allowed to ?
			$ok=get_lastfm_settings($user_id,$l_user,$l_pass);
			if ($ok==1) {
				$d=update_last_fm($row['name'],get_performer_name($row['performer_id']),$row['duration'],$l_user,$l_pass);
			}	
		}	
	}	

}	


// ID is set & not 0: Stream one track

@ini_set('output_buffering', 0);
$qry="SELECT * FROM track WHERE id='".$id."'";
$result=execute_sql($qry,0,1,$nr); // 0.7.2: Changed from ...,0,10000,...
$row=mysql_fetch_array($result);

if (isset($debug)) {
	mydebug('stream.php','Stream ONE track. The media player is:'.$_SERVER["HTTP_USER_AGENT"]);
}	

update_last_streamed($user_id,$row['id']); 

if ((isset($update_now_playing)) && (isset($allow_now_playing)) && ($allow_now_playing==1)) {
	if ($simple_update_now_playing==0) {
		update_now_playing($id,$row['name'],$row['performer_id'],$row['year'],$row['album_id'],$user_id,$language); 
	} else {
		update_now_playing_light($user_id,get_performer_name($row['performer_id']).
		'<br>'.$row['name'],$row['year']);
	}
	// 0.7.3: Is dummy_update set & =2 ? Yes: Exit
	if ((isset($dummy_update)) && ($dummy_update==2)) {
		die();
	}	
}		

// 0.7.3: Is dummy_update NOT set ? If yes: Stream as usual:
if (!isset($dummy_update)) {
	// 0.7.0: Lame enabled ??
	if ((isset($lame_enabled) && ($lame_enabled==1)) 
	&& (get_local_lame($user_id,$lle)==1)) {
		if ($lle<>'') { $lame_parameters=$lle; }
		lame_convert($lame_path,$lame_parameters,$id,$row);
	} else {
		stream_track($id,$row,$user_id,$simple_update_now_playing);
	}
}		

// 0.7.3: Is dummy_update set & =1 ? If yes: Just update stats:
if ((isset($dummy_update)) && ($dummy_update==1)) {
	update_stats($row['id']);
}	

// 0.7.2: Submit track to last.fm...
if ($lastfm_allow_submission=="1") { // Are we allowed to ?
	$ok=get_lastfm_settings($user_id,$l_user,$l_pass);
	if ($ok==1) {
		$d=update_last_fm($row['name'],get_performer_name($row['performer_id']),$row['duration'],$l_user,$l_pass);
	}	
}
die(); 
?>		
