<?php
/*
Michael Iversen (michael@ampjuke.org):

echonest_lib.php: "Library" of functions used with/against the Echonet API. 
The library was written to be used with AmpJuke (http://www.ampjuke.org).
Feel free to include whatever functions that are deemed useful. This block of comments must stay intact.


Version/date description:
1.0/Aug 2011: Draft.

1.1/Sep 2011: Basic, stable, mature version.

1.2/Apr 2013: Echonest have introduced "2 step identification": 1:track.upload 2:track.profile in order to 
get audio_summary (the fingerprint for the track).
Up until recently, it was only necessary to upload the track in order to get audio_summary. 
A new function, echonest_track_profile, deals with the extra step needed.

1.3/Nov 2013: Added echonest_id. Also added liveness,speechiness,acousticness and valence. WTF, Echonest: ADVICE, please!!!

*/

/*
in audio_summary a number of different "identifiers" will be returned by the Echonest API:
tempo: aka. BPM for old DJ's like me :) (Range: 0-500).
danceability: can we dance to this one ? Range 0-1. Typical values around 0.65
energy: the "energy" (I assume) for a track. Range 0-1. Varies. A lot!
loudness: the "gain factor" in db. Range -100 - +100, typical value for my mp3's: around -7
key: (c, c-sharp, d, e-flat, e, f, f-sharp, g, a-flat, a, b-flat, b). Range: 0 - 11 (integer).
time_signature: ranges from 3 to 7 indicating 3/4 to 7/4. The value -1 is returned if no time signature was detected, and +1 if the meter was too confusing. 
mode: minor/major. Two possibilities: 0 or 1.
liveness: No doc. about this from The Echonest...
speechiness: 0-1. The closer to 1 the more "speak" a track contains.
acousticness: 0-1. Close to 0=More 'electric' sounds. Close to 1=Un-altered/real instruments: Voice, guitar, piano etc.
valence: 0-1: "Happy/sadness" factor. The closer to 0.5 the more "neutral" the track is.
*/


// Construct a url used in a query against the echonest api. This query is used when searching for tracks:
function echonest_search_track($performer,$title,$echonest_api_key,$echonest_api_url,$echonest_max_results,$duration,$max_diff,$debug = '0') {
	$ret='';
	// Construct URL to retrieve XML from the Echonest API:
	$url=$echonest_api_url.'song/search?api_key='.$echonest_api_key.'&artist='.urlencode($performer).'&title='.urlencode($title);
	$url.='&results='.$echonest_max_results.'&format=xml&bucket=audio_summary';
	$url.='&min_duration='.($duration-$max_diff).'&max_duration='.($duration+$max_diff);
	if ((isset($debug)) && (($debug=='1'))) {
		mydebug('echonest','URL='.$url);
	}	
	$x=0;
	$ret=retrieve_xml($url,$x,1000);
	return $ret; // Returns XML-results or an empty string if nothing was found
}


// Find the best "match" in the xml-content (returned by echonest API) and, if found, returns the index:
function echonest_get_best_xml_match($performer,$title,$duration,$max_diff,$xml) { 
	$ret=-1;
	$x=0;
	$highscore_duration=99999; // The duration is - besides track name and artist/performer - the ONLY 'key' we have. This is a "highscore" for that.
	// Aww...filter out "unwanted" chars here:
	$unwanted=array("'",'"');
	$performer=str_replace($unwanted,'',$performer);
	$title=str_replace($unwanted,'',$title);
	
	while ($x<count($xml->songs->song)) {
		if ((strtoupper($xml->songs->song[$x]->artist_name)==strtoupper($performer)) && (strtoupper($xml->songs->song[$x]->title)==strtoupper($title))) { // Match on performer + title ?
			if ((abs($duration-$xml->songs->song[$x]->audio_summary->duration)<$highscore_duration) && // Duration is better compared to what we have ?
			(abs($duration-$xml->songs->song[$x]->audio_summary->duration)<=$max_diff))	{ // Duration is within orignial track's duration +/-"max_diff" ?
				$ret=$x;
				$highscore_duration=abs($duration-$xml->songs->song[$x]->audio_summary->duration); // Compute new "highscore".
			}
		}
		$x++;
	}
	return $ret;
}


// Get+return specific "tag" from xml (returned by echonest API) using a specific key (returned by echonest_get_best_xml_match):
function echonest_get_tag($x,$xml,$tag) { 
	$ret=-1;
	if (isset($xml->songs->song[$x])) {
		$ret=$xml->songs->song[$x]->audio_summary->$tag;
	}
	return $ret;
}


// Returns a specific tag found in the XML-response from Echonest API after uploading & analyzing:
function echonest_get_tag_after_upload($xml,$tag) {
	$ret=-1;
	if (isset($xml->track->audio_summary->$tag)) {
		$ret=$xml->track->audio_summary->$tag;
	}
	return $ret;
}

function loc_update_status($level,$details,$msg) {
 	if ($details>=$level) {
		echo '<tr><td>'.$msg.'</td>';
		print "</tr> \n";
		@flush(); @ob_flush();
	}	
}	
			
			
// 0.8.7: Ask the API (several times) using track.profile (using an ID). Also return stuff in $xml:
function echonest_track_profile($echonest_api_key,$echonest_id,$details,&$xml,$echonest_api_url,$debug='0') {
	$ret=0;
	$attempts=5; // Max. # of times we want to repeat the query
	$url=$echonest_api_url.'track/profile?api_key='.$echonest_api_key.'&format=xml&id='.$echonest_id.'&bucket=audio_summary';
	$dummy=0;	
	loc_update_status(4,$details,'<br>track.profile URL='.$url);
	loc_update_status(3,$details,'echonest_track_profile: Echonest API processing track: '.$echonest_id.'...');
	sleep(5); // Give the API a little time to (at least) TRY to process the uploaded track also to avoid ERROR 429 from Echonest
	
	while (($ret==0) && ($attempts>0)) { // Loop
		$xml=retrieve_xml($url,$dummy,100);
		if ((isset($xml->track->status)) && ($xml->track->status=='complete')) {
		    loc_update_status(4,$details,'Raw XML received...');
			$ret=1;
		} else {
			loc_update_status(3,$details,'Status<>complete. Waiting '. $attempts*2 .' secs.');
			sleep($attempts*2);
			$attempts--;
		}
	}
	loc_update_status(2,$details,'echonest_track_profile: API finished processing. Returning: '.$ret.' (0=not completed/error, 1=completed/OK)');
	return $ret;
}
	
// Lookup one track (id from track-table) using "quick" search (attempt to find similar/identical tracks):
function echonest_lookup_track($id, $debug = '0', $details='0') { // 0.8.8: Added details
	mydebug('echonest_lib.php','Lookup track id='.$id);
	require('db.php');
	$ret=0; // 0=nothing found->should be queued for upload to Echonest, 1=found+analyzed ok
	// Get the track details (name, performer, duration, path etc.etc.)	
	$track=get_track_extras($id); 
	$title=$track['name'];
	$performer=stripslashes((get_performer_name($track['performer_id'])));
	$item=explode(":",$track['duration']);
	$duration=$item[1] + ($item[0]*60);
	// Search for the track @ echonest using title, artist/performer and duration:
	$xml=echonest_search_track($performer,$title,$echonest_api_key,$echonest_api_url,$echonest_max_results,$duration,$echonest_max_diff_duration,$debug); 
	// We got some songs from the API: Attempt to find a match:
	if (isset($xml->songs)) { 
		$match_id=echonest_get_best_xml_match($performer,$title,$duration,$echonest_max_diff_duration,$xml);
		if ($match_id<>-1) { // something WAS found:
			$danceability=echonest_get_tag($match_id,$xml,'danceability');
			$loudness=echonest_get_tag($match_id,$xml,'loudness');
			$energy=echonest_get_tag($match_id,$xml,'energy');
			$tempo=echonest_get_tag($match_id,$xml,'tempo');
			$key=echonest_get_tag($match_id,$xml,'key');
			$mode=echonest_get_tag($match_id,$xml,'mode');
			$time_signature=echonest_get_tag($match_id,$xml,'time_signature');
			//$echonest_id=echonest_get_tag($match_id,$xml,'id'); // Well - this one has been there always...but it's wrong... 
            $echonest_id=$xml->songs->song[$match_id]->id; // ...since the id is located elsewhere in the XML-tree (bummer!) - 0.8.8
            // 0.8.8: New Echonest parameters:            
			$liveness=echonest_get_tag($match_id,$xml,'liveness');
			$speechiness=echonest_get_tag($match_id,$xml,'speechiness');
			$acousticness=echonest_get_tag($match_id,$xml,'acousticness');
			$valence=echonest_get_tag($match_id,$xml,'valence');
			// 0.8.8: ...end
			$ret=1; // uuuh...VERY important :)
			// Insert values into TRACK:
			$qry="UPDATE track SET ";
			$qry.="echonest_tempo='".$tempo."', ";
			$qry.="echonest_loudness='".$loudness."', ";
			$qry.="echonest_danceability='".$danceability."', ";
			$qry.="echonest_energy='".$energy."', ";
			$qry.="echonest_mode='".$mode."', ";
			$qry.="echonest_key='".$key."', ";
			$qry.="echonest_time_signature='".$time_signature."', ";
			// 0.8.8: New echonest parameters:
			$qry.="echonest_id='".$echonest_id."', ";
			$qry.="echonest_liveness='".$liveness."', ";
			$qry.="echonest_speechiness='".$speechiness."', ";
			$qry.="echonest_acousticness='".$acousticness."', ";
			$qry.="echonest_valence='".$valence."', ";
			// 0.8.8: ...end
			$qry.="echonest_status='".date('U')."' WHERE id=".$id;
			$result=execute_sql($qry,0,-1,$dummy);
		    $mc='XML-element '.$match_id.'  matched : EchonestID='.$echonest_id;
            $md=' Danceability='.$danceability.' Loudness='.$loudness;
		    $md.=' Energy='.$energy.' Tempo='.$tempo.' Key='.$key.' Mode='.$mode.' Time_signature='.$time_signature;
		    $md.=' Liveness='.$liveness.' Speechiness='.$speechiness.' Acousticness='.$acousticness.' Valence='.$valence;
			if ((isset($debug)) && ($debug=='1')) {
				mydebug('echonest_lib.php: echonest_lookup_track',$md);
				mydebug('echonest_lib.php: echonest_lookup_track','QRY='.$qry);
			}
            loc_update_status(2,$details,$mc);
            loc_update_status(3,$details,$md);
            loc_update_status(4,$details,$qry);
		} else { // match_id=-1: nothing found
			if ((isset($debug)) && ($debug=='1')) {
				mydebug('echonest','No mathces for track-id '.$id);
			}
			
			// Mark tracks not identified the "quick" way (eventually uploading them later)
			$qry="UPDATE track SET echonest_status='0' WHERE id=".$id;
			$result=execute_sql($qry,0,-1,$dummy);
			$ret=0;
            loc_update_status(3,$details,'Not identified. Mark trackID '.$id.' with echonest_status=0 (upload required)');
		}
	} 
	if (!isset($xml->songs)) { //  NOTHING found, - ie. XML error:
		if ((isset($debug)) && ($debug=='1')) {
			mydebug('echonest','Nothing (literally, really) found for id='.$id.': XML not valid or empty');
		}
		$ret=0;
	}
	return $ret;
}


// Find out if we have analyzed a track already (ie. avoid calling echonest api a 2nd w. same track):
function echonest_get_track_status($id,$debug='0') {
	$track=get_track_extras($id);
	if ((isset($debug)) && ($debug=='1')) {
		mydebug('echonest','The track w. id='.$id.' has echonest_status set to: '.$track['echonest_status']);
	}
	return $track['echonest_status']; // -1=not analyzed previously, 0=analyzed, but nothing found, "timestamp"=analyzed & ok
}

// Construct+return a query based on "related" tracks (using echonest parameters from the database):
function echonest_construct_related_track_query($prow,$max_last_played,$debug='0') {
	$attempts=1;
	$nr=0;
	require('db.php');	
    $statistics_enabled='0'; // Set to 1 to write "statistics" to echonest_stat.txt (semicolon seperated textfile)
    
    // 0.8.7: Configurable options:
    //$avoid_same_perf_enabled=1;
    //$avoid_same_perf_num=5;	
	// 0.8.8: Changed to:
	if ((!isset($jukebox_mode_enabled)) || ($jukebox_mode_enabled=='0')) {
	    $jukebox_mode_min_age_performer=1; // set it to 1 hour (avoid streaming tracks from same performer)
	}
	    
	
	
	while (($nr==0) && ($attempts<10)) { // As long as we don't have any tracks that qualify to be "related":
		$l='Attempt #'.$attempts.':';
		$qry="SELECT * FROM track WHERE id<>'".$prow['id']."'"; // We do not want to play the same track again
        $qry.=get_recently_played_performers($jukebox_mode_min_age_performer,'Fav'); // 0.8.7 + 0.8.8
		// 0.8.7 $qry.=" AND performer_id<>'".$prow['performer_id']."'"; // ...and also avoid the same performer being played twice
		// Check "loudness":
		if ($attempts<=$echonest_loudness_priority) {
			$qry.=" AND echonest_loudness>='".($prow['echonest_loudness']-$echonest_limit)."'";
			$qry.=" AND echonest_loudness<='".($prow['echonest_loudness']+$echonest_limit)."'";
			$l.='<br> Loudness:'.($prow['echonest_loudness']-$echonest_limit).'-'.($prow['echonest_loudness']+$echonest_limit);			
		}
		// Check "key":
		if ($attempts<=$echonest_key_priority) {
			$qry.=" AND echonest_key>='".($prow['echonest_key']-$echonest_key_factor)."'";
			$qry.=" AND echonest_key<='".($prow['echonest_key']+$echonest_key_factor)."'";
			$l.='<br> Key:'.($prow['echonest_key']-$echonest_key_factor).'-'.($prow['echonest_key']+$echonest_key_factor);
		}
		// Check "energy":
		if ($attempts<=$echonest_energy_priority) {
			$qry.=" AND echonest_energy>='".($prow['echonest_energy']-($echonest_limit))."'";
			$qry.=" AND echonest_energy<='".($prow['echonest_energy']+($echonest_limit))."'";
			$l.='<br> Energy:'.($prow['echonest_energy']-$echonest_limit).'-'.($prow['echonest_energy']+$echonest_limit);
		}
		// Check "danceability":
		if ($attempts<=$echonest_danceability_priority) {
			$qry.=" AND echonest_danceability>='".($prow['echonest_danceability']-$echonest_limit)."'";
			$qry.=" AND echonest_danceability<='".($prow['echonest_danceability']+$echonest_limit)."'";
			$l.='<br> Danceability:'.($prow['echonest_danceability']-$echonest_limit).'-'.($prow['echonest_danceability']+$echonest_limit);
		}
		// Check "tempo" (aka. BPM):
		if ($attempts<=$echonest_tempo_priority) {
			$qry.=" AND echonest_tempo>='".($prow['echonest_tempo']-($echonest_limit*$echonest_tempo_factor))."'";
			$qry.=" AND echonest_tempo<='".($prow['echonest_tempo']+($echonest_limit*$echonest_tempo_factor))."'";
			$l.='<br> Tempo:'.($prow['echonest_tempo']-($echonest_limit*$echonest_tempo_factor)).'-'.($prow['echonest_tempo']+($echonest_limit*$echonest_tempo_factor));
		}
        // 0.8.8: Check "liveness":
		if ($attempts<=$echonest_liveness_priority) {
			$qry.=" AND echonest_liveness>='".($prow['echonest_liveness']-$echonest_limit)."'";
			$qry.=" AND echonest_liveness<='".($prow['echonest_liveness']+$echonest_limit)."'";
			$l.='<br> Liveness:'.($prow['echonest_liveness']-$echonest_limit).'-'.($prow['echonest_liveness']+$echonest_limit);
		}
        // 0.8.8: Check "speechiness":
		if ($attempts<=$echonest_speechiness_priority) {
			$qry.=" AND echonest_speechiness>='".($prow['echonest_speechiness']-$echonest_limit)."'";
			$qry.=" AND echonest_speechiness<='".($prow['echonest_speechiness']+$echonest_limit)."'";
			$l.='<br> Speechiness:'.($prow['echonest_speechiness']-$echonest_limit).'-'.($prow['echonest_speechiness']+$echonest_limit);
		}
        // 0.8.8: Check "acousticness":
		if ($attempts<=$echonest_acousticness_priority) {
			$qry.=" AND echonest_acousticness>='".($prow['echonest_acousticness']-$echonest_limit)."'";
			$qry.=" AND echonest_acousticness<='".($prow['echonest_acousticness']+$echonest_limit)."'";
			$l.='<br> Acousticness:'.($prow['echonest_acousticness']-$echonest_limit).'-'.($prow['echonest_acousticness']+$echonest_limit);
		}
        // 0.8.8: Check "valence":
		if ($attempts<=$echonest_valence_priority) {
			$qry.=" AND echonest_valence>='".($prow['echonest_valence']-$echonest_limit)."'";
			$qry.=" AND echonest_valence<='".($prow['echonest_valence']+$echonest_limit)."'";
			$l.='<br> Valence:'.($prow['echonest_valence']-$echonest_limit).'-'.($prow['echonest_valence']+$echonest_limit);
		}

		// The rest (always there):
		$qry.=" AND last_played<'".$max_last_played."'";
		$qry.=" ORDER BY rand()";
		// Execute it and see if one row is returned:
		$result=execute_sql($qry,0,1,$nr);
		if ((isset($debug)) && ($debug=='1')) {
		    mydebug('echonest','...');
		    mydebug('echonest','...');
			mydebug('echonest',$l.'<br> -> Hits: '.$nr);
			//mydebug('echonest','Attempt #'.$attempts.': '.$qry.' -> NR='.$nr);
			//mydebug('echonest',' ');
		}
	    // 0.8.7: Statistics:
	    // if stat-enabled...
	    if (($statistics_enabled=='1') && ($nr==1)) {
    	    $stat_row=mysql_fetch_array($result);
    	    if (!file_exists('./tmp/echonest_stat.txt')) {
    	        $handle=fopen('./tmp/echonest_stat.txt', 'w');
    	        fwrite($handle,'Date;Time;TrackID;Attempts;Tempo;Loudness;Danceability;Energy;Mode;Key;Time_signature;Liveness;Speechiness;Acousticness;Valence;Performer;Trackname;Year;Qry');
    	        fwrite($handle,chr(13));
    	        fclose($handle);
    	    }
    	    $handle=fopen('./tmp/echonest_stat.txt', 'a');
    	    fwrite($handle,date('Y-m-d').';'.date('H:i:s').';'.$stat_row['id'].';'.$attempts.';');
    	    fwrite($handle,$stat_row['echonest_tempo'].';'.$stat_row['echonest_loudness'].';');
    	    fwrite($handle,$stat_row['echonest_danceability'].';'.$stat_row['echonest_energy'].';'.$stat_row['echonest_mode'].';');
    	    fwrite($handle,$stat_row['echonest_key'].';'.$stat_row['echonest_time_signature'].';');
            // 0.8.8: NEW:
            fwrite($handle,$stat_row['echonest_liveness'].';');
            fwrite($handle,$stat_row['echonest_speechiness'].';');
            fwrite($handle,$stat_row['echonest_acousticness'].';');
            fwrite($handle,$stat_row['echonest_valence'].';');
            // 0.8.8: ends
    	    fwrite($handle,get_performer_name($stat_row['performer_id']).';'.$stat_row['name'].';'.$stat_row['year'].';'.$qry);
    	    fwrite($handle,chr(13));
    	    fclose($handle);		
	    }
	        
		if ($nr<>1) {
			$attempts++;
		}
	}
	return $qry;
}

// 0.8.7: New: 
// Returns a list of track-ID's that contains "related" tracks for the "reference" track ($prow).
// Basically, this is a modified version of echonest_contrsuct_related_track_query above.
function echonest_get_related_tracks($prow,$num_wanted,$max_last_played='',$debug='0') {
	if ($max_last_played=='') {
		$max_last_played=date('U');
	}
	$attempts=1;
	$nr=0;
	require('db.php');	
	$ret='';
	
	while (($nr==0) && ($nr<$num_wanted) && ($attempts<10)) { // As long as we don't have any tracks that qualify to be "related":
		$l='Attempt #'.$attempts.':';
		$qry="SELECT * FROM track WHERE id<>'".$prow['id']."'"; // We do not want to play the same track again
		$qry.=" AND performer_id<>'".$prow['performer_id']."'"; // ...and also avoid the same performer being played twice
		// Check "loudness":
		if ($attempts<=$echonest_loudness_priority) {
			$qry.=" AND echonest_loudness>='".($prow['echonest_loudness']-$echonest_limit)."'";
			$qry.=" AND echonest_loudness<='".($prow['echonest_loudness']+$echonest_limit)."'";
			$l.='<br> Loudness:'.($prow['echonest_loudness']-$echonest_limit).'-'.($prow['echonest_loudness']+$echonest_limit);			
		}
		// Check "key":
		if ($attempts<=$echonest_key_priority) {
			$qry.=" AND echonest_key>='".($prow['echonest_key']-$echonest_key_factor)."'";
			$qry.=" AND echonest_key<='".($prow['echonest_key']+$echonest_key_factor)."'";
			$l.='<br> Key:'.($prow['echonest_key']-$echonest_key_factor).'-'.($prow['echonest_key']+$echonest_key_factor);
		}
		// Check "energy":
		if ($attempts<=$echonest_energy_priority) {
			$qry.=" AND echonest_energy>='".($prow['echonest_energy']-($echonest_limit))."'";
			$qry.=" AND echonest_energy<='".($prow['echonest_energy']+($echonest_limit))."'";
			$l.='<br> Energy:'.($prow['echonest_energy']-$echonest_limit).'-'.($prow['echonest_energy']+$echonest_limit);
		}
		// Check "danceability":
		if ($attempts<=$echonest_danceability_priority) {
			$qry.=" AND echonest_danceability>='".($prow['echonest_danceability']-$echonest_limit)."'";
			$qry.=" AND echonest_danceability<='".($prow['echonest_danceability']+$echonest_limit)."'";
			$l.='<br> Danceability:'.($prow['echonest_danceability']-$echonest_limit).'-'.($prow['echonest_danceability']+$echonest_limit);
		}
		// Check "tempo" (aka. BPM):
		if ($attempts<=$echonest_tempo_priority) {
			$qry.=" AND echonest_tempo>='".($prow['echonest_tempo']-($echonest_limit*$echonest_tempo_factor))."'";
			$qry.=" AND echonest_tempo<='".($prow['echonest_tempo']+($echonest_limit*$echonest_tempo_factor))."'";
			$l.='<br> Tempo:'.($prow['echonest_tempo']-($echonest_limit*$echonest_tempo_factor)).'-'.($prow['echonest_tempo']+($echonest_limit*$echonest_tempo_factor));
		}
        // 0.8.8: Check "liveness":
		if ($attempts<=$echonest_liveness_priority) {
			$qry.=" AND echonest_liveness>='".($prow['echonest_liveness']-$echonest_limit)."'";
			$qry.=" AND echonest_liveness<='".($prow['echonest_liveness']+$echonest_limit)."'";
			$l.='<br> Liveness:'.($prow['echonest_liveness']-$echonest_limit).'-'.($prow['echonest_liveness']+$echonest_limit);
		}
        // 0.8.8: Check "speechiness":
		if ($attempts<=$echonest_speechiness_priority) {
			$qry.=" AND echonest_speechiness>='".($prow['echonest_speechiness']-$echonest_limit)."'";
			$qry.=" AND echonest_speechiness<='".($prow['echonest_speechiness']+$echonest_limit)."'";
			$l.='<br> Speechiness:'.($prow['echonest_speechiness']-$echonest_limit).'-'.($prow['echonest_speechiness']+$echonest_limit);
		}
        // 0.8.8: Check "acousticness":
		if ($attempts<=$echonest_acousticness_priority) {
			$qry.=" AND echonest_acousticness>='".($prow['echonest_acousticness']-$echonest_limit)."'";
			$qry.=" AND echonest_acousticness<='".($prow['echonest_acousticness']+$echonest_limit)."'";
			$l.='<br> Acousticness:'.($prow['echonest_acousticness']-$echonest_limit).'-'.($prow['echonest_acousticness']+$echonest_limit);
		}
        // 0.8.8: Check "valence":
		if ($attempts<=$echonest_valence_priority) {
			$qry.=" AND echonest_valence>='".($prow['echonest_valence']-$echonest_limit)."'";
			$qry.=" AND echonest_valence<='".($prow['echonest_valence']+$echonest_limit)."'";
			$l.='<br> Valence:'.($prow['echonest_valence']-$echonest_limit).'-'.($prow['echonest_valence']+$echonest_limit);
		}
		// The rest (always there):
		$qry.=" AND last_played<'".$max_last_played."'";
		$qry.=" ORDER BY last_played ASC";
		// Execute it and see if one row is returned:
		$result=execute_sql($qry,0,$num_wanted,$nr);
		if ((isset($debug)) && ($debug=='1')) {
		    mydebug('echonest','...');
		    mydebug('echonest','...');
			mydebug('echonest',$l.' -> Hits: '.$nr);
			//mydebug('echonest','Attempt #'.$attempts.': '.$qry.' -> NR='.$nr);
		}
		$attempts++;
		if ($nr<>0) {
		    $l='Candidates (trackIDs): ';
			while ($row=mysql_fetch_array($result)) {
				$ret.=$row['id'].',';
				$l.=$row['id'].',';
				//mydebug('echonest','qry='.$qry.' -> $nr='.$nr);
			}
			if ((isset($debug)) && ($debug=='1')) {
			    mydebug('echonest',$l);
			}
		}
	}
	return $ret;
}
/*
in audio_summary a number of different "identifiers" will be returned by the Echonest API:
tempo: aka. BPM for old DJ's like me :) (Range: 0-500).
danceability: can we dance to this one ? Range 0-1. Typical values around 0.65
energy: the "energy" (I assume) for a track. Range 0-1. Varies. A lot!
loudness: the "gain factor" in db. Range -100 - +100, typical value for my mp3's: around -7
key: (c, c-sharp, d, e-flat, e, f, f-sharp, g, a-flat, a, b-flat, b). Range: 0 - 11 (integer).
time_signature: ranges from 3 to 7 indicating 3/4 to 7/4. The value -1 is returned if no time signature was detected, and +1 if the meter was too confusing. 
mode: minor/major. Two possibilities: 0 or 1.
liveness: No doc. about this from The Echonest...

speechiness: 0-1. The closer to 1 the more "speak" a track contains.
acousticness: 0-1. Close to 0=More 'electric' sounds. Close to 1=Un-altered/real instruments: Voice, guitar, piano etc.
valence: 0-1: "Happy/sadness" factor. The closer to 0.5 the more "neutral" the track is.
*/
?>
