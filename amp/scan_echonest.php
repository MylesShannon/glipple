<?php
// echonest.php: Scan+update "values" ("danceability", "mode", "key", "energy" etc.etc.) for tracks using The Echonest's API!
// Introduced in AmpJuke 0.8.6 - Michael Iversen.
// Extended functionality version 0.8.7 - Michael Iversen.
// 0.8.8: More detailed status. Added ID, liveness, speechiness, acousticness and valence - Michael Iversen.

// 0.8.8: Comment this out, if running in CRON:
require('logincheck.php');
if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
	header("Location: logout.php");
}

// 0.8.8: Comment this out, if running in CRON:
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="EN">';
echo '<head>';
echo '<title>AmpJuke+Echonest=more value to your music![AmpJuke...and YOUR hits keep on coming!]</title>';

// 0.8.8: If running CRON, you might change this to an absolute path:
require_once('db.php');

// 0.8.8: Comment this out, if running CRON:
echo '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />';
echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'" />'; 
echo '<script type="text/javascript" src="rowcols.js"></script>';

// 0.8.8: If running CRON, you might change these to absolute paths:
require_once('translate.php'); 
require_once('disp.php');
require_once('sql.php');
require_once('sql.php');
require_once('configuration.php'); 
require_once('set_td_colors.php');
require_once('tbl_header.php');
require_once('echonest_lib.php');

// ***** CRON-SETTINGS:
// Want to run this using cron ?
// If yes, read on and uncomment+modify entries below:
/*
$_POST['details']='2'; // 1-4 -> the higher the more will be displayed/printed.
$_POST['number_of_tracks']='10'; // Self-explaining : Number of tracks to be analyzed. Do NOT set this too high or API-calls will be blocked!
//$_POST['upload']='1'; // Uncomment if you want to upload tracks to the Echnoet API for furhter analysis (number_of_tracks should not be >2000).
$_POST['curl_path']='/usr/bin/curl'; // Absolute path to cURL (which, obviously, must be installed on your system in order for this to work).
$_POST['sel_mode']='RAND()'; // possible values: 'RAND()', 'id', 'name'. 
$_POST['lookup_status']='or'; // possible values: 'and', 'or', 'any' -> if set to 'any' the options below can be IGNORED 

// Uncomment each of the options below, if you want to search and analyze a particular parameter
// if you have $_POST['lookup_status'] above set to 'any', the settings below will be IGNORED:
// $_POST['include_identification_never_attempted']='1'; // Lookup tracks that have never been tried any kind of identification
// $_POST['include_identification_attempted']='1'; // Lookup tracks where identification was attempted - but failed - earlier (upload required)
// $_POST['include_tempo']='1'; // Lookup tracks without values for tempo/BPM
// $_POST['include_loudness']='1'; // Lookup tracks without values for loudness
// $_POST['include_danceability']='1'; // Lookup tracks without values for danceability
// $_POST['include_energy']='1'; // Lookup tracks without values for energy
// $_POST['include_mode']='1'; // Lookup tracks without values for mode
// $_POST['include_key']='1'; // Lookup tracks without values for key
// $_POST['include_time_signature']='1'; // Lookup tracks without values for time_signature
// $_POST['include_liveness']='1'; // Lookup tracks without values for liveness - new in 0.8.8
// $_POST['include_speechiness']='1'; // Lookup tracks without values for speechiness - new in 0.8.8
// $_POST['include_acousticness']='1'; // Lookup tracks without values for acousticness - new in 0.8.8
// $_POST['include_valence']='1'; // Lookup tracks without values for valence - new in 0.8.8

$act='scan'; // ...obviously - we cannot fill out forms 'n' stuff in CRON-jobs ;-)
error_reporting(0); // Plain cheatin'
// ALSO - and this is important - change paths from relative to absolute below for 100% error-free operation
*/
// ***** CRON-SETTINGS ends.

parse_str($_SERVER["QUERY_STRING"]);
if (!isset($act)) {
	$act='setup';
}
$tdnorm='';
$tdalt='';
$tdhighlight='';
$count=0;

/*
function local_update_status($level,$details,$msg) {
 	if ($details>=$level) {
		echo '<tr><td>'.$msg.'</td>';
		print "</tr> \n";
		@flush(); @ob_flush();
	}	
}	
*/

function loc_get_echonest_parameter_total($field,$criteria) {
    $ret=0;
    $qry="SELECT id,".$field." FROM track WHERE ".$field.$criteria;
    $result=execute_sql($qry,0,10000000,$ret);
    return $ret;
}


function loc_print_parameter_status($total_tracks,$nr,$name,$checkbox_name) {
	$ret='<td>...'.$name.':</td><td><b>'.($total_tracks-$nr).'</b> ('.round(100-($nr/$total_tracks)*100,2).'%) ';
    $checked='0';
    if ($nr<>0) {
        $checked='1';
     }
	$ret.=' '.add_checkbox($checkbox_name,$checked).'</td>';
	$ret.="</tr> \n\n";
    return $ret;
}

function loc_get_api_ratelimit($details,$upload) {
    $ret=20; // Or whatever makes Echonest API happy...
    if ($upload=='1') { // ...if set: get the ratelimit the fancy way (by asking Echonest):
        $filename='./tmp/api.txt'; // *************** IMPORTANT: Change this if you f.ex. want to point to an absolute location
        loc_update_status(4,$details,'Temporary filename for API rate limit details: '.$filename);
        include('db.php');
        $cmd=$_POST['curl_path']." -i '".$echonest_api_url."artist/profile?api_key=".$echonest_api_key."&name=Erasure'";
        $cmd.=' > '.$filename;
        loc_update_status(4,$details,'Will execute: '.$cmd);    
        $res=exec($cmd);
        sleep(1);
        $found=0;
        $handle=fopen($filename, 'r');
        while (($found==0) && (!feof($handle))) {
            $linje=fgets($handle);
            loc_update_status(4,$details,'LINJE='.$linje);
            loc_update_status(4,$details,'SUBSTR=['.substr($linje,0,22).']');
            if (substr($linje,0,22)=='X-Ratelimit-Remaining:') { // We're close...
                $ret=substr($linje,23,strlen($linje)); // ...to get the 'real' api ratelimit
                loc_update_status(3,$details,'Echonest API ratelimit remaining: '.$ret);
                $found=1;
            }
        }
        fclose($handle);
        @unlink($filename);
    }
    loc_update_status(3,$details,'Will return '.$ret.' as new, remaining API ratelimit');
    return $ret;
}


function loc_calculate_api_ratelimit($details,&$api_old_minute,$api_initial_ratelimit,$api_ratelimit,$upload) {
    // LOCAL housekeeping of API-ratelimit + ask for a new limit if we're at 0:
    $api_ratelimit=$api_ratelimit-1; // Subtract 1 from current...
    $ret=$api_ratelimit; // ...set that as default returnvalue
    
    if ($ret==0) { // We're all out of shots for this minute:
        if (date('i')<>$api_old_minute) { // ...but a minute has passed
            $ret=loc_get_api_ratelimit($details,$upload);
            $api_old_minute=date('i');
        } else { // ...uuuh...it's NOT been a minute since last "reset" of api-ratelimit:
            loc_update_status(3,$details,'Echonet API-rate exceeded for this minute ('.date('i').'). Will wait 60 secs...');
            sleep(60);
            $ret=loc_get_api_ratelimit($details,$upload);
            $api_old_minute=date('i');
            loc_update_status(4,$details,'Finished waiting, - new ratelimit: '.$ret.' for this minute ('.$api_old_minute.')');
        }
    }
    return $ret;
}
/* 
**************************************************************************

						SCAN 
						
**************************************************************************
*/
if ($act=='scan') {
	set_time_limit(0); // We want this to run forever......or at least until finished.
	// Basic validation+setup:
	$_POST['details']=only_digits($_POST['details']);
	$_POST['number_of_tracks']=only_digits($_POST['number_of_tracks']);

	// Obey the limits:
	if ($_POST['number_of_tracks']>4000) {
		$_POST['number_of_tracks']=4000;
	}
	if (isset($_POST['upload'])) {
		if ($_POST['number_of_tracks']>2000) {
			$_POST['number_of_tracks']=2000;
		}
	}
	
	$starttimer = time()+microtime(); // Used to calc. the total duration

    $upload='';
    if (isset($_POST['upload'])) {
        $upload='1';
    }

	// ****** Construct query:
	$qry="SELECT * FROM track";
	// 
	$condition='';
	if ($_POST['lookup_status']<>'any') {
	    $condition=' '.strtoupper($_POST['lookup_status']); // Use OR or AND - leave blank if "any" was selected
	    $qry.=' WHERE';
	}
    // Collect all the POST'ed checkboxes & use 'em to construct the qry:
    $cqry='';
    if ($condition<>'') {
        if (isset($_POST['include_identification_never_attempted'])) { // identification never attempted -> echonest_status=-1
            $cqry.=" echonest_status='-1'".$condition;
        }
        if (isset($_POST['include_identification_attempted'])) { // Identification failed earlier -> echonest_status=0
            $cqry.=" echonest_status='0'".$condition;
        }
        if (isset($_POST['include_tempo'])) { // Tempo/BPM -> echonest_tempo
            $cqry.=" echonest_tempo='-1'".$condition;
        }
        if (isset($_POST['include_loudness'])) { // Loudness -> echonest_loudness
            $cqry.=" echonest_loudness='-1'".$condition;
        }
        if (isset($_POST['include_danceability'])) { // Danceability -> echonest_danceability
            $cqry.=" echonest_danceability='-1'".$condition;
        }
        if (isset($_POST['include_energy'])) { // Energy -> echonest_energy
            $cqry.=" echonest_energy='-1'".$condition;
        }
        if (isset($_POST['include_mode'])) { // Mode -> echonest_mode
            $cqry.=" echonest_mode='-1'".$condition;
        }
        if (isset($_POST['include_key'])) { // Key -> echonest_key
            $cqry.=" echonest_key='-1'".$condition;
        }
        if (isset($_POST['include_time_signature'])) { // Time_sginature -> echonest_time_signature
            $cqry.=" echonest_time_signature='-1'".$condition;
        }
        if (isset($_POST['include_liveness'])) { // Liveness -> echonest_liveness
            $cqry.=" echonest_liveness='-1'".$condition;
        }
        if (isset($_POST['include_speechiness'])) { // Speechiness -> echonest_speechiness
            $cqry.=" echonest_speechiness='-1'".$condition;
        }
        if (isset($_POST['include_acousticness'])) { // Acousticness -> echonest_acousticness
            $cqry.=" echonest_acousticness='-1'".$condition;
        }
        if (isset($_POST['include_valence'])) { // Valence -> echonest_valence
            $cqry.=" echonest_valence='-1'".$condition; // Yes! It's still added although we dont have more to add...
        }
        $cqry=substr($cqry,0,(strlen($cqry)-strlen($condition))); // ...'cause we're getting rid of the last $condition here!
    }
	$qry.=$cqry; // Add what's generated from above
	
    $qry.=" ORDER BY ".$_POST['sel_mode'];
	$count=1;
	$result=execute_sql($qry,0,$_POST['number_of_tracks'],$nr);

	// Show what we're processing:
	echo std_table("ampjuke_content_table","ampjuke_content");
    loc_update_status(3,$_POST['details'],'Initial query: '.$qry);
    loc_update_status(1,$_POST['details'],'*** Number of tracks found to be analyzed:'.$nr.' ***');
    

    // Ratelimit for echonest API:
    $api_ratelimit=20; // per minut
    $api_old_minute=date('i'); // this/current minute
    // Do we have cURL enabled/checked ?
    if ($upload=='1') { // Yes: THEN we can get a "real" ratelimit:
        loc_update_status(4,$_POST['details'],'Upload checked: Obtain real API ratelimit from Echonest...');
        $api_ratelimit=loc_get_api_ratelimit($_POST['details'],$upload);
    }
    $api_initial_ratelimit=$api_ratelimit;
    //loc_update_status(2,$_POST['details'],'Echonest API ratelimit: '.$api_initial_ratelimit.' calls/minute.');

    // Actual processing:
	while ($row=mysql_fetch_array($result)) {
        loc_update_status(2,$_POST['details'],'Remaining Echonest API-calls: '.$api_ratelimit.' for this minute ('.$api_old_minute.')'); 
        loc_update_status(1,$_POST['details'],' '); // This line intentionally left blank :-)
		$id=$row['id'];
		$track=get_track_extras($id); // Get the track details (name, path etc.etc.) - encode to utf8 (req. by The Echonest API):
		$title=utf8_encode($track['name']);
		$performer=utf8_encode(get_performer_name($track['performer_id']));
		$item=explode(":",$track['duration']);
		$duration=$item[1] + ($item[0]*60);
		loc_update_status(1,$_POST['details'],' <strong>Track '.$count.' of '.$nr.': '.$performer.' - '.$title.'</strong> ');
		$ok=echonest_lookup_track($row['id'],'0',$_POST['details']); // Try LAZY approach first: See echonest_lib.php
		$api_ratelimit=loc_calculate_api_ratelimit($_POST['details'],$api_old_minute,$api_initial_ratelimit,$api_ratelimit,$upload);
		if ($ok==1) {
			loc_update_status(2,$_POST['details'],'Found using "quick" lookup.');
		} 
		if ($ok==0) {
			loc_update_status(2,$_POST['details'],'Not found using "quick" lookup.');
			if ($upload=='1') {
				loc_update_status(2,$_POST['details'],'Upload option checked -> use cURL...');
				loc_update_status(3,$_POST['details'],' Will upload & process using cURL...this might take a while...');
				$echonest_parameters='api_key='.$echonest_api_key.'&format=xml&wait=true&bucket=audio_summary&url=';
				if (!file_exists('./tmp/'.$id.'.mp3')) {
					copy($track['path'],'./tmp/'.$id.'.mp3');
					loc_update_status(4,$_POST['details'],'Copied '.$track['path'].' to ./tmp/'.$id.'.mp3');
				}
				$filename=$base_http_prog_dir.'/tmp/'.$id.'.mp3';

				$cmd=$_POST['curl_path'].' -X POST "'.$echonest_api_url.'track/upload" -d "'.$echonest_parameters.$filename.'"';

				loc_update_status(4,$_POST['details'],'Will use CURL: '.$cmd);
				$res=exec($cmd);
				$xml=simplexml_load_string($res);
				loc_update_status(3,$_POST['details'],'Echonest response code: '.$xml->status->code.'='.$xml->status->message);
        		
        		$api_ratelimit=loc_calculate_api_ratelimit($_POST['details'],$api_old_minute,$api_initial_ratelimit,$api_ratelimit,$upload);

				// 0.8.7: For some reason, Echonest changed the response from "immediate" to several possible statusses...
				// SO: Check the status - if "pending" then ask echonest api again using track.profile using the ID:
				$track_analyzed_ok=0; 
				$eid=$xml->track->id; // get the ID
				loc_update_status(3,$_POST['details'],'Echonest ID='.$eid.' Status='.$xml->track->status);
				if ($xml->track->status=='pending') { // No "immediate" response from the API: Ask again using ID:
					loc_update_status(2,$_POST['details'],'Waiting for track w. id '.$id.' to be analyzed by the Echonest API...');
					$track_analyzed_ok=echonest_track_profile($echonest_api_key,$eid,$_POST['details'],$xml,$echonest_api_url);
				}
				
				// Note: See: http://developer.echonest.com/docs/v4/index.html#response-codes
				if ($track_analyzed_ok==1) {
					$danceability=echonest_get_tag_after_upload($xml,'danceability');
					$loudness=echonest_get_tag_after_upload($xml,'loudness');
					$energy=echonest_get_tag_after_upload($xml,'energy');
					$tempo=echonest_get_tag_after_upload($xml,'tempo');
					$key=echonest_get_tag_after_upload($xml,'key');
					$mode=echonest_get_tag_after_upload($xml,'mode');
					$time_signature=echonest_get_tag_after_upload($xml,'time_signature');
					$liveness=echonest_get_tag_after_upload($xml,'liveness'); // 0.8.8: This is new
					$speechiness=echonest_get_tag_after_upload($xml,'speechiness'); // 0.8.8: This is new
					$acousticness=echonest_get_tag_after_upload($xml,'acousticness'); // 0.8.8: This is new
					$valence=echonest_get_tag_after_upload($xml,'valence'); // 0.8.8: This is new
					// Insert values into TRACK:
					$q="UPDATE track SET ";
					$q.="echonest_id='".$eid."', "; // 0.8.8: This is new
					$q.="echonest_tempo='".$tempo."', ";
					$q.="echonest_loudness='".$loudness."', ";
					$q.="echonest_danceability='".$danceability."', ";
					$q.="echonest_energy='".$energy."', ";
					$q.="echonest_mode='".$mode."', ";
					$q.="echonest_key='".$key."', ";
					$q.="echonest_time_signature='".$time_signature."', ";
					$q.="echonest_status='".date('U')."', ";
					$q.="echonest_liveness='".$liveness."', "; // 0.8.8: This is new
					$q.="echonest_speechiness='".$speechiness."', "; // 0.8.8: This is new
					$q.="echonest_acousticness='".$acousticness."', "; // 0.8.8: This is new					
					$q.="echonest_valence='".$valence."' "; // 0.8.8: This is new
					$q.="WHERE id=".$id;
					$re=execute_sql($q,0,-1,$dummy);	
					loc_update_status(3,$_POST['details'],'Identified OK! Values found: ID='.$eid.' Tempo='.$tempo.' Loudness='.$loudness
					.' Danceability='.$danceability.' Energy='.$energy.' Mode='.$mode.' Key='.$key.' Time_signature='.$time_signature
					.' Liveness='.$liveness.' Speechiness='.$speechiness.' Acousticness='.$acousticness.' Valence='.$valence);
					loc_update_status(4,$_POST['details'],$q);
				} else {
					$q="UPDATE track SET echonest_status='0' WHERE id=".$id;
					$re=execute_sql($q,0,-1,$dummy);
					loc_update_status(4,$_POST['details'],'Unknown echonest response (may be pending in status). Mark this track for processing later');
				}
			} else { // Upload<>1: Do not upload, but set echonest_status to 0 (looked up, not found):
				$q="UPDATE track SET echonest_status='0' WHERE id=".$id;
				$re=execute_sql($q,0,-1,$dummy);
				loc_update_status(4,$_POST['details'],'Setting status to 0 for id:'.$id);
			}
		}
		@ob_flush();
		@flush();
		@ob_end_flush();	
		$count++;
		loc_update_status(1,$_POST['details'],'&nbsp');
	}
	loc_update_status(1,$_POST['details'],'Done.');
	$stoptimer = time()+microtime();
	$timer = round($stoptimer-$starttimer,2);
	loc_update_status(1,$_POST['details'],'Processing time: '.$timer.' seconds');
	loc_update_status(1,$_POST['details'],'<a href="./">Click here to go back to the "welcome" page</a>');
	echo '</table>';
}


/* 
**************************************************************************

						SETUP 
						
**************************************************************************
*/
if ($act=='setup') {
//	Check we have an API key, - if not, break away from normal flow, offer option to get one and punch in the f*cker:
	if ((!isset($echonest_api_key)) || ($echonest_enabled<>'1')) {
		echo '<h3>Uh...oh... This functionality requires access to The Echonest API.</h3>';
		echo 'In other words: You do not have an API key from The Echonest stored in the configuration (or you have not enabled access to Echonest).<br>';
		echo 'You can get an API-key here: <a href="http://developer.echonest.com/account/register" target="_blank">';
		echo '<img src="./ampjukeicons/popout.gif" border="0">http://developer.echonest.com/account/register</a><br>';
		echo 'It only takes a couple of seconds to apply for the key.<br>';
		echo '<br><a href="./">Click here to go back to the "Welcome" page</a><br>';
		echo '<br><a href="http://www.ampjuke.org/?id=faq87">More info. here</a>'; // 0.8.7: Corrected FAQ-entry
		die();
	}	

 	echo '<FORM NAME="scanform" method="POST" action="scan_echonest.php?act=scan">';
	echo std_table("ampjuke_content_table","ampjuke_content");
	echo '<th colspan="2" valign="top">AmpJuke+Echonest=More value to your music!<br>';
	echo '<a href="http://the.echonest.com/" target="_blank"><img src="./ampjukeicons/echonest.gif" border="0"></a></th>';
	echo '<tr><td align="left" valign="top">';
	echo add_faq(91,' Click here to read more about the options presented below');
	echo '<td align="right" valign="top"> </td></tr>';

// OVERALL STATUS (INDEXING):
// 0.8.8: Improved - A LOT! More details about *individual* echonest parameters - see comments below
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center"><b>Status and options</b></td></tr>';
	// Total tracks:
	$total_tracks=get_num_rows('track','id');
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
    echo '<td>There'."'s".' a total of <b>'.$total_tracks.' tracks</b> in the music collection, here is what'."'s <b>missing</b>:</td>";
    echo '<td><i>Note: Check/select at least one of the checkboxes below</i></td>';
    echo "</tr>";

	// 0.8.8: Tracks NEVER identified:
	$nr=loc_get_echonest_parameter_total('echonest_status',"='-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
  	echo loc_print_parameter_status($total_tracks,($total_tracks-$nr),'identification (never attempted)','include_identification_never_attempted');

	// 0.8.8: Tracks w.o. identification (upload required):
	$nr=loc_get_echonest_parameter_total('echonest_status',"='0'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
  	echo loc_print_parameter_status($total_tracks,($total_tracks-$nr),'identification (attempted earlier - upload required)','include_identification_attempted');

	// 0.8.8: Tracks w.o. tempo/BPM identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_tempo',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'tempo/BPM','include_tempo');

	// 0.8.8: Tracks w.o. loudness identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_loudness',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'loudness','include_loudness');

	// 0.8.8: Tracks w.o. danceability identified ok:
    $nr=loc_get_echonest_parameter_total('echonest_danceability',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'danceability','include_danceability');
	
	// 0.8.8: Tracks w.o. energy identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_energy',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'energy','include_energy');

	// 0.8.8: Tracks w.o. mode identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_mode',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'mode','include_mode');

	// 0.8.8: Tracks w.o. key identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_key',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'key','include_key');

	// 0.8.8: Tracks w.o. time_signature identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_time_signature',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'time signature','include_time_signature');

	// 0.8.8: Tracks w.o. liveness identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_liveness',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'liveness','include_liveness');

	// 0.8.8: Tracks w.o. speechiness identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_speechiness',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'speechiness','include_speechiness');

	// 0.8.8: Tracks w.o. acousticness identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_acousticness',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'acousticness','include_acousticness');

	// 0.8.8: Tracks w.o. valence identified ok:
	$nr=loc_get_echonest_parameter_total('echonest_valence',"<>'-1'");
  	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo loc_print_parameter_status($total_tracks,$nr,'valence','include_valence');
	
	
	
	
/* OLD CODE (<0.8.8):
	// Tracks identified:
	$qry="SELECT id,echonest_status FROM track WHERE echonest_status<>'-1' AND echonest_status<>'0'";
	$result=execute_sql($qry,0,10000000,$tracks_identified);
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>...Identified with success:</td><td><b>'.$tracks_identified.'</b>';
	echo ' ('.round(($tracks_identified/$total_tracks)*100,2);
	echo '%)</td></tr>';
	// Tracks w. failed identification (but attempt was made earlier):
	$qry="SELECT id,echonest_status FROM track WHERE echonest_status='0'";
	$result=execute_sql($qry,0,10000000,$tracks_identified_attempt);
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>...Failed identification:</td><td><b>'.$tracks_identified_attempt.'</b>';
	echo ' ('.round(($tracks_identified_attempt/$total_tracks)*100,2);
	echo '%)</td></tr>';
	// Number of tracks that haven't been processed:
	$qry="SELECT id,echonest_status FROM track WHERE echonest_status='-1'";
	$result=execute_sql($qry,0,10000000,$tracks_never_identified);
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>...Never identified:</td><td><b>'.$tracks_never_identified.'</b>';
	echo ' ('.round(($tracks_never_identified/$total_tracks)*100,2);
	echo '%)</td></tr>';
*/
	
	

// OPTIONS:
	//fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<tr><td colspan="2" align="center"><hr></td></tr>';

	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center"><b>Additional options</b></td></tr>';

	// Lookup status:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Process tracks where...</td><td>';
	echo '<SELECT NAME="lookup_status" class="tfield">';
	echo add_select_option('and','ALL selection criterias above are true (AND)','');
	echo add_select_option('or','ANY selection criteria above is true (OR)','1');
	echo add_select_option('any','Does not matter (completely ignore what is checked above)','');
	echo '</SELECT></td></tr>';
	
	// Number of tracks to lookup:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Number of tracks to process:</td>';
	echo '<td>'.add_textinput('number_of_tracks',10,'8');
	echo ' <i>Maximum allowed: 4000. With upload enabled the limit is 2000.</td></tr>';

	// Write status messages to screen:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Show details during processing:</td><td>';
	echo '<SELECT NAME="details" class="tfield">';
	echo add_select_option('1','Minimal','');
	echo add_select_option('2','Normal','1');
	echo add_select_option('3','Detailed','');
	echo add_select_option('4','Very detailed','');
	echo '</SELECT></td></tr>';
	
	// 0.8.7: Selection mode:
    fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
    echo '<td>Order tracks by:</td><td>';
    echo '<SELECT NAME="sel_mode" class="tfield">';
    echo add_select_option('RAND()','Random','1');
    echo add_select_option('id','ID 0->x (ASC)','');
    echo add_select_option('id DESC','ID x->0 (DESC)','');
    echo add_select_option('name','Name (ASC)','');
    echo add_select_option('name DESC','Name (DESC)','');
    echo '</SELECT></td></tr>';

// UPLOAD:	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center"><b>Upload tracks for identification</b></td></tr>';
	
	// If nothing was found, perform upload:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Upload track(s) to The Echonest for processing:</td>';
	echo '<td>'.add_checkbox('upload','').' <i>Note: Must be selected if you checked "...attempted earlier - upload required" above - see the FAQ</i></td>';
	echo '</tr>';	

	// Path to cURL:	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Path to cURL:</td>';
	echo '<td>'.add_textinput('curl_path','/usr/bin/curl','20');
	echo '</td></tr>';
	
// Wrap it up:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center">';
	echo '<input type="submit" value="Start processing"></td></tr>';
	// Jump back -> "Welcome":
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2"><a href="index.php?what=welcome">';
	echo '<img src="./ampjukeicons/mnu_arr.gif" border="0"> ';
	echo 'Do not do anything, just step back to the "Welcome" page</a>';
	echo '</td></tr>';
	echo '</table></form>';		
}



?>
<script type="text/javascript">
addTableRolloverEffect('ampjuke_content','tableRollOverEffect','');
</script>
</body></html>
