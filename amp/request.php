<?php
// 0.8.6: Request: Introduced as the "entry-point" for requests (radio station mode)

require('logincheck.php');
//session_start();
parse_str($_SERVER["QUERY_STRING"]);
require("disp.php");
require("db.php");
require("sql.php");
$now=date('U');


// First+most important: Clean up (remove) old entries:
$before=$now - ($jukebox_mode_selection_limit_minutes * 60);
$qry="SELECT * FROM queue WHERE user_name LIKE '+++%'";
$result=execute_sql($qry,0,10000,$nr);

while ($row=mysql_fetch_array($result)) {
	$a=get_request_array($row['user_name']);
	if ((sizeof($a)>2) && ($a[3]=='1') && ($a[1]<$before)) {
		$qry2="DELETE FROM queue WHERE qid='".$row['qid']."'";
		$result2=execute_sql($qry2,0,-1,$dummy);
	}
}


// *************** SUPPORTING FUCTIONS ****************

function check_request_id($id) { // Check if a track (id) is already pending (haven't been streamed, yet):
    $ok=1; // Everything is a-ok
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%' AND track_id='".$id."'";
	$result=execute_sql($qry,0,10000,$nr);
	while ($row=mysql_fetch_array($result)) {
		$a=get_request_array($row['user_name']);
		if ($a[3]=='0') {
			$ok=0;
		}
	}	
    return $ok;
}    


function check_request_age($id,$last_played,$now,$jukebox_mode_min_age) { // Check age:
    $ok=1;
    // Convert ...min_age to seconds:
    $jukebox_mode_min_age=$jukebox_mode_min_age * 3600; // 3600 secs = 1 hour
    // Calculate timestamp in the past - AFTER this timestamp, nothing can be played:
    $before=$now - $jukebox_mode_min_age;
    // Did we try to add something that's "too new" ?
    if ($last_played>$before) {
        $ok=0;
    }
    return $ok;
}

function check_request_limit_tracks($id,$user,$jukebox_mode_selection_limit_tracks) { // Check user's number of outstanding tracks/requests:
    $ok=1;
	$count=0;
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%'";
	$result=execute_sql($qry,0,1000,$nr);
	while (($row=mysql_fetch_array($result)) && ($ok==1)) {
		$a=get_request_array($row['user_name']);
		if ((sizeof($a)>2) && ($a[2]==$user) && ($a[3]=='0')) {
			$count++;
			if ($count>=$jukebox_mode_selection_limit_tracks) {
				$ok=0;
			}
		}
	}	
    return $ok;
}    

function check_request_limit_tracks_total($id,$user,$jukebox_mode_selection_limit_tracks_total) { // 0.8.8 Check ALL users number of outstanding tracks/requests:
    $ok=1;
	$count=0;
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%'";
	$result=execute_sql($qry,0,1000,$nr);
	while (($row=mysql_fetch_array($result)) && ($ok==1)) {
		$a=get_request_array($row['user_name']);
		if ((sizeof($a)>2) && ($a[3]=='0')) {
			$count++;
			if ($count>=$jukebox_mode_selection_limit_tracks_total) {
				$ok=0;
			}
		}
	}	
    return $ok;
}    
    

// Check if user has requested OR played "too much" within ...limit_minutes:
function check_request_limit_minutes($id,$user,$now,$jukebox_mode_selection_limit_minutes,$jukebox_mode_selection_limit_tracks) { 
    $ok=1;
    // Convert ...limit_minutes to seconds:
    $jukebox_mode_selection_limit_minutes=$jukebox_mode_selection_limit_minutes * 60; // 1 minute = 60 seconds
    // Calculate timestamp in the past:
    $before=$now - $jukebox_mode_selection_limit_minutes;

	$count=0;
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%'";
	$result=execute_sql($qry,0,10000,$nr);
	while (($row=mysql_fetch_array($result)) && ($ok==1)) {
		$a=get_request_array($row['user_name']);
		if ((sizeof($a)>2) && ($a[2]==$user) && ($a[1]>$before)) {
			$count++;
			if ($count>=$jukebox_mode_selection_limit_tracks) {
				$ok=0;
			}
		}
	}
    return $ok;
}


// 0.8.8: Check if this PERFORMER have been played within "..._age_performer":
function check_request_age_performer($request,$now,$jukebox_mode_min_age_performer) {
    $ok=1;
    // Convert "...min_age_..." to seconds:
    $jukebox_mode_min_age_performer=$jukebox_mode_min_age_performer * 3600;
    // Calculate timestamp in the past:
    $before=$now - $jukebox_mode_min_age_performer;
    
    $count=0;
    $qry="SELECT * FROM track WHERE last_played>'".$before."' AND performer_id='".$request['performer_id']."'"; // AND performer_id<>0";
    $result=execute_sql($qry,0,10000,$nr);
    if ($nr<>0) {
        $ok=0;
    }
    
    return $ok;
}    
        

// Replace %p w. "performer", %a w. "album", %t w. track name, %y w. year:
function request_fill_in_the_blanks($i,$track,$performer,$album) {
    include('db.php');
    $ret=$i;
    $ret=str_replace('%t',$track['name'],$ret); // Name of track %t
    $ret=str_replace('%p',$performer,$ret); // Name if performer %p
    $ret=str_replace('%a',$album,$ret); // Name of album %a
    $ret=str_replace('%y',$track['year'],$ret); // Year of track %y
    $ret=str_replace('%limit_tracks',$jukebox_mode_selection_limit_tracks,$ret); // Number of tracks (limit) %limit_tracks
    $ret=str_replace('%limit_all',$jukebox_mode_selection_limit_tracks_total,$ret); // 0.8.8: Same as above but for all
    $ret=str_replace('%limit_minutes',$jukebox_mode_selection_limit_minutes,$ret); // Number of minutes (limit) %limit_minutes
    $ret=str_replace('%min_age',$jukebox_mode_min_age,$ret); // Number of hours before we accept a new request for that track
    $ret=str_replace('%min_performer_age',$jukebox_mode_min_age_performer,$ret); // 0.8.8: Same as aboe, but for ARTISTS/PERFORMERS
    return $ret;
}

// Display an image of the performer ("click to close"):
function request_close_link($id,$performer_id) {
	$ret='<a href="./request.php?picker=3&id='.$id.'">';
	if (file_exists('./lastfm/'.$performer_id.'.jpg')) { // Display image of performer (it also closes the window)
		$ret.='<img src="./lastfm/'.$performer_id.'.jpg" border=0"></a>';
	} else {
		$ret.='[X] Close</a>';
	}
    return $ret;
}


    
// *************** END OF SUPPORTING FUCTIONS ****************

// Check: Valid id.
if (!isset($id)) {
    die('ID not valid.');
}
$id=only_digits($id);

if ($jukebox_mode_msg_popup_enabled=='0') { // No: Do not pop-it-up...
	$picker=2;
}

// Step 1: PREPARE the pop-up:
if ($picker==1) {
	if ($jukebox_mode_msg_popup_enabled=='1') { // Yes: pop-it-up...
		$loc=$base_http_prog_dir.'/request.php?picker=2&id='.$id;
		echo '<script type="text/javascript" language="javascript">';	 		 	
		echo "history.go(-1);";
		echo 'var rw = window.open("'.$loc.'","AmpJuke_Request_'.$id.'","width=600,height=300,resizable=yes,scrollbar=yes");';
		echo '</script>';  			
		die();
	} else { // No, - just continue:
		$picker=2;
	}
} 


// Step 2: DISPLAY the pop-up:
if ($picker==2) { 
 	echo '<html><head><title>AmpJuke...and YOUR hits keep on coming!</title>'; 
	echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'">'; 
	echo '</head><body>';
	echo '<table class="ampjuke_content_table"><tr><td>';

	// Get everything we know about this track:
	$request=get_track_extras($id);
	$performer=get_performer_name($request['performer_id']);
	$album='';
	if ($request['album_id']<>'0') {
		$album=get_album_name($request['album_id']);
	}

	// Determine the *identity* of the requester (username or IP-adr.):
	$user=$_SESSION['login'];
	if ($jukebox_mode_selection_identity=='IP-address') {
		$user=$_SERVER['REMOTE_ADDR'];
	}

	// Find out if this track already has been requested:
	$ok=check_request_id($id);
	if ($ok==0) {
		echo request_fill_in_the_blanks($jukebox_mode_msg_fail_already_requested,$request,$performer,$album);
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);
		die('</td></tr></table>');
	}

	// Find out if this track have been streamed within the "...min_age" setting:
	$ok=check_request_age($id,$request['last_played'],$now,$jukebox_mode_min_age);
	if ($ok==0) {
		echo request_fill_in_the_blanks($jukebox_mode_msg_fail_age,$request,$performer,$album);
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);
		die('</td></tr></table>');
	}

	// 0.8.8: Find out if this PERFORMER have been streamed within the "...min_age_performer" setting:
	$ok=check_request_age_performer($request,$now,$jukebox_mode_min_age_performer);
	if ($ok==0) {
		echo request_fill_in_the_blanks($jukebox_mode_msg_fail_age_performer,$request,$performer,$album);
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);
		die('</td></tr></table>');
	}
    

	// Find out if this user already have reached the limit ("...limit_tracks") for outstanding requests:   
	$ok=check_request_limit_tracks($id,$user,$jukebox_mode_selection_limit_tracks);
	if ($ok==0) {
		echo request_fill_in_the_blanks($jukebox_mode_msg_fail_outstanding_tracks,$request,$performer,$album);
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);
		die('</td></tr></table>');
	}


	// 0.8.8: Find out if ALL users have reached the limit ("...limit_tracks_total") for outstanding requests:   
	$ok=check_request_limit_tracks_total($id,$user,$jukebox_mode_selection_limit_tracks_total);
	if ($ok==0) {
		echo request_fill_in_the_blanks($jukebox_mode_msg_fail_outstanding_tracks_all,$request,$performer,$album);
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);
		die('</td></tr></table>');
	}


	// Find out if this user have reached the limit ("...limit_minutes") for outstanding OR played requests:
	$ok=check_request_limit_minutes($id,$user,$now,$jukebox_mode_selection_limit_minutes,$jukebox_mode_selection_limit_tracks);
	if ($ok==0) {
		echo request_fill_in_the_blanks($jukebox_mode_msg_fail_limit_tracks,$request,$performer,$album);
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);         
		die('</td></tr></table>');
	}

	// Finally - FINALLY!! - add the track:
	$qry="INSERT INTO queue (user_name,track_id) VALUES ";
	$qry.="('+++;".$now.";".$user.";0' ,'".$id."')";
	$result=execute_sql($qry,0,-1,$nr);
	echo request_fill_in_the_blanks($jukebox_mode_msg_add_success,$request,$performer,$album);

	if ($jukebox_mode_msg_popup_enabled=='1') { // Yes: it's a pop-up...
		echo '</td></tr><tr><td align="right">';
        echo request_close_link($id,$request['performer_id']);
	}
	echo '</td></tr>';
	echo '</table>';
}


if ($picker==3) {
	echo '<script type="text/javascript" language="javascript">';
	echo 'self.close();';
	echo '</script>';
}				
?>

