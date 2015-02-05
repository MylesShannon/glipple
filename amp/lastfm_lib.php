<?php
/*
		LAST.FM LIBRARY: Functions that supports last.fm's webservices and retriveval of data vs. AmpJuke.
		
		Version 1 / Dec. 2008 / Michael H. Iversen
		Version 2 / Jun. 2009 / Michael: Added a (hardcoded!) setting to adjust size of image returned
		Version 3 / Sep. 2009 / Michael: Added functions for album cover management
		Version 4 / Nov. 2009 / Michael: Added function for suggestion of images (album/performer)
		Version 5 / Feb. 2010 / Michael: Added function for dealing with track.getTopTracks, artist.getTopTags and album.getInfo  (They're all used in relation to  suggestion of favorite lists)
		Version 6 / Apr. 2011 / Michael: Small improvements (look/search for "0.8.5" below) + cleaned up in comments.
		Version 7 / Apr. 2013 / Michael: ONE API-key in only ONE place makes things A LOT easier...(*cough*). Plus: One URL.
*/
// Some definitions:
function get_lastfm_api_key() {
	return '2b39d48ae842a3809afc237220f20634'; // Go ahead, punk. Make your move, borrow mine...
}

function get_lastfm_cover_path() {	
	return './covers'; // The path where covers are stored.
}

function get_lastfm_api_url() { // 0.8.7: Makes things a bit easier if (when..?) lastfm changes URL for API
	return 'http://ws.audioscrobbler.com/2.0/';
}

// 0.8.2: This function is used to get "toptags" for a track, a performer/artist or an album.
// Note that the (hardcoded) setting $lastfm_toptag_min_score is used in order to avoid too much "noise" when getting the tags. Or: try to lower it yourself...
function lastfm_get_toptags($what,$id,$perf,$name,$lastfm_toptag_min_score=65) {
// 1. set the right prefix + set URL to last.fm's API (initially):
	$prefix=substr($what,0,1); // prefix is one char: t,a or p
	switch ($what) {
		case 'track': 
			$url=get_lastfm_api_url().'?method=track.gettoptags&artist='.urlencode($perf).'&track='.urlencode($name);
		break;
		case 'performer':
			$url=get_lastfm_api_url().'?method=artist.gettoptags&artist='.urlencode($perf);
		break;
		case 'album':
			$url=get_lastfm_api_url().'?method=album.getinfo&artist='.urlencode($perf).'&album='.urlencode($name);
		break;
	}
	$key=get_lastfm_api_key();
	$url.='&api_key='.$key;
// 2. Check if we have a cached (on the server) version of the toptag-xml-file. If we do, then use that instead. If we don't then ask last.fm:
	if (file_exists('./toptags/'.$prefix.$id.'.xml')) {
		$url='./toptags/'.$prefix.$id.'.xml';
//		echo '* cache hit *'; // Just to verify. Uncomment, if you're curious..
		touch($url); // Extend lifetime 1 year
		$xml=retrieve_xml($url,$dummy,1000);
	} else { // ..we do NOT have a cached version: ask last.fm:
		if ($hf=@fopen($url,'r')) {
			for ($sfile='';$buf=fread($hf,8192);) {  
				$sfile.=$buf;
			}
    		// Customize a bit before storing:
	    	$sfile=str_replace('<lfm status="ok">','<AmpJuke_toptags>',$sfile);
    		$sfile=str_replace('</lfm>','</AmpJuke_toptags>',$sfile);
	    	// Store it:
	    	$handle=fopen('./toptags/'.$prefix.$id.'.xml', 'w');
	    	fwrite($handle,$sfile);
	    	fclose($handle);
    		$xml=retrieve_xml($url,$dummy,1000);
		}
	}	
// 3. Process & return an array w. toptags:
	$ret=array();
    if (isset($xml)) {
    	$x=0;
    	while ($x<5) { // max. 5 tags will be returned. For tracks+performers, a tag will only be returned if the 'score' is higher than $lastfm_toptag_min_score
	    	if (($what=='track') || ($what=='performer')) {
	    		if ((isset($xml->toptags[0]->tag[$x]->name[0])) && ($xml->toptags[0]->tag[$x]->count[0]>$lastfm_toptag_min_score)) {
	    			$ret[$x]=$xml->toptags[0]->tag[$x]->name[0];
	    		}
	    	}
	    	if ($what=='album') {
	    		if ((isset($xml->album[0]->toptags[0]->tag[$x]->name[0]))) {
	    			$ret[$x]=$xml->album[0]->toptags[0]->tag[$x]->name[0];
	    		}
    		}
	    	$x++;
    	}
    }
	return $ret;
}
		
// 0.8.0: We're now using last.fm rather than Amazon to handle covers.
// Get a cover + return it:
function lastfm_get_cover($album_row) {
	$ret=get_lastfm_cover_path().'/_blank.jpg'; // This is the default -> "Blank" cover is returned
	$found=0;
	
	// First, let's check to see if we already have a cover stored locally:
	if (file_exists(get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg')) {
		$ret=get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg';
		touch(get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg'); // 0.8.5: Extend lifetime 1 year
		$found=1;
	} 
	
	// Second, if no local cover is there - ask last.fm:
	if ($found==0) {
		$url=get_lastfm_api_url().'?method=album.getinfo&api_key='.get_lastfm_api_key();
		$url.='&artist='.urlencode(get_performer_name($album_row['aperformer_id']));
		$url.='&album='.urlencode($album_row['aname']);
		$xml=retrieve_xml($url,$dummy,$dummy); 
		if (isset($xml->album[0]->image[2])) { // 2="large" images. Use 0-3 as appropriate value, where 0=extremely large.
		// ...anyway, we found something: Store it:
		    $data=file_get_contents($xml->album[0]->image[2]);
			$out_handle=fopen(get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg', "w");
			fwrite($out_handle,$data);
			fclose($out_handle);
			$ret=get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg';
			$found=1;
			// 0.8.4: As suggested by Ben:
			// Add extra check to see if the filesize is < "x bytes" (10 in this case) or the file exists.
			// If not (=something went wrong during download), set $found to 0 (=use '_blank.jpg' instead).
			if ((!is_readable($ret)) || (filesize($ret)<10)) {
				$found=0;
			}
		}
	}

	// Nothing found: Store "_blank.jpg" as this albums' cover (prevents another trip to last.fm):
	if ($found==0) { 
		copy(get_lastfm_cover_path().'/_blank.jpg',get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg');
		$ret=get_lastfm_cover_path().'/'.$album_row['aid'].'.jpg';
	}
	
	// Finally, return whatever we found:
	return $ret;
}		

// Format & return a bio from last.fm, with:
// Hard breaks: <br> after min. 100 chars and when a '.' is found
// Strip tags.
// mysql_escape_string
function lastfm_reformat_bio($s) {
	// 0.7.9: Find out if it's necessary at all to reformat the bio:
	// This is determined by locating if any <p>'s are within the bio (if there is, the bio does not need to be reformatted):
	$replaced=0;
	$test=str_replace('<p>','<p>',$s,$replaced);
	$ret=$s;
	
	if ($replaced==0) { // No <p>'s found: Reformat based on simple charcount:
		$ret='';
		$charcount=0;
		$n=0;
		while ($n<=strlen($s)) {
			$ret.=substr($s,$n,1);
			if ((substr($s,$n,1)=='.') && ($charcount>100)) { // We're past the 100 char mark -> insert a hard break (<br>):
				$ret.='<br>'; 
				$charcount=0;
			}
			$charcount++;
			$n++;
		}
		//Strip tags:
		$ret=strip_tags($ret,'<br>');
		// mysql-escape...
	}	
	$ret=mysql_escape_string($ret);
	return $ret;
}

// Get an album's biography. First try the database, if none is found, try last.fm (and the store the result in db):
function lastfm_get_album_bio($aid,$pname,$aname,$full_bio,$refresh_bio) {
// Is refresh_bio=1 ? Yes: Clear bio in db and continue:
	if ($refresh_bio==1) {
		$q="UPDATE album SET bio_short='' WHERE aid='".$aid."' LIMIT 1";
		$re=execute_sql($q,0,-1,$nr);
	}	
// Try db first:
	$ret="";
	$found=0;
	$q="SELECT * FROM album";
	$q.=" WHERE aid='".$aid."'";
	$re=execute_sql($q,0,1,$nr);
	if ($nr==1) {
		$r=mysql_fetch_array($re);
		if (strlen($r['bio_short'])>2) { // Found something in db - remember that:
			$found=1;
		}
	}
// If we cannot find anything in db, ask last.fm for a bio:
	if ($found==0) {
		$biourl=get_lastfm_api_url().'?method=album.getinfo&artist='.urlencode($pname);
		$biourl.='&album='.urlencode($aname).'&api_key='.get_lastfm_api_key();
        // 0.8.6:
        if (!isset($lastfm_max_related_artists)) {
            $lastfm_max_related_artists=5;
        }
        // ...ends
		$xml=retrieve_xml($biourl,$n,$lastfm_max_related_artists);
		if (isset($xml->album[0]->wiki[0]->summary[0]) && (strlen($xml->album[0]->wiki[0]->summary[0])>2)) { // Store summary of bio in db:
			$q="UPDATE album SET ";
			$q.="bio_short='".lastfm_reformat_bio($xml->album[0]->wiki[0]->summary[0])."'";
			$q.=" WHERE aid='".$aid."' LIMIT 1";
			$re=execute_sql($q,0,-1,$nr);
			if (isset($xml->album[0]->wiki[0]->content[0])) { // Also store full bio in db:
				$q="UPDATE album SET ";
				$q.="bio_long='".lastfm_reformat_bio($xml->album[0]->wiki[0]->content[0])."'";
				$q.=" WHERE aid='".$aid."' LIMIT 1";
				$re=execute_sql($q,0,-1,$nr);
			}
			$found=1;
		} else { // Didn't find a bio ? Then put 'n/a' in db to avoid repeating requests for bio for this performer over and over again:
			$q="UPDATE album SET bio_short='n/a' WHERE aid='".$aid."' LIMIT 1";
			$re=execute_sql($q,0,-1,$nr);
			$q="UPDATE album SET bio_long='n/a' WHERE aid='".$aid."' LIMIT 1";
			$re=execute_sql($q,0,-1,$nr);
			$found=1;
		}	
	}
// Finally, return what we have, - IF we found anything:
	if ($found==1) {
		$q="SELECT * FROM album";
		$q.=" WHERE aid='".$aid."'";
		$re=execute_sql($q,0,1,$nr);
		$r=mysql_fetch_array($re);
		if ($full_bio==0) { // Return summary:
			$ret=$r['bio_short'];
		} else { // Return full bio:
			$ret=$r['bio_long'];
		}
	}	
	return $ret;
}	


// Get performer's biography. First try the database, if none is found, try last.fm (and store in db):
function lastfm_get_bio($pid,$pname,$full_bio,$refresh_bio) {
// Is refresh_bio=1 ? Yes: Clear bio in db and continue:
	if ($refresh_bio==1) {
		$q="UPDATE performer SET bio_short='' WHERE pid='".$pid."' LIMIT 1";
		$re=execute_sql($q,0,-1,$nr);
	}	
// Try db first:
	$ret="";
	$found=0;
	$q="SELECT * FROM performer";
	$q.=" WHERE pid='".$pid."'";
	$re=execute_sql($q,0,1,$nr);
	if ($nr==1) {
		$r=mysql_fetch_array($re);
		if (strlen($r['bio_short'])>2) { // Found something in db - remember that:
			$found=1;
		}
	}
// If we cannot find anything in db, try last.fm:	
	if ($found==0) {
		$biourl=get_lastfm_api_url().'?method=artist.getinfo&artist='.$pname;
		$biourl.='&api_key='.get_lastfm_api_key();
		$xml=retrieve_xml($biourl,$n,$n);
		if ((isset($xml->artist[0]->bio[0]->summary[0])) && (strlen($xml->artist[0]->bio[0]->summary[0])>2)) { // Store summary of bio in db:
			$q="UPDATE performer SET ";
			$q.="bio_short='".lastfm_reformat_bio($xml->artist[0]->bio[0]->summary[0])."'";
			$q.=" WHERE pid='".$pid."' LIMIT 1";
			$re=execute_sql($q,0,-1,$nr);
			if (isset($xml->artist[0]->bio[0]->content[0])) { // Also store full bio in db:
				$q="UPDATE performer SET ";
				$q.="bio_long='".lastfm_reformat_bio($xml->artist[0]->bio[0]->content[0])."'";
				$q.=" WHERE pid='".$pid."' LIMIT 1";
				$re=execute_sql($q,0,-1,$nr);
			}
			$found=1;
		} else { // Didn't find a bio, but put 'n/a' in db to avoid repeating requesting bio for this performer over and over again:
			$q="UPDATE performer SET bio_short='n/a' WHERE pid='".$pid."' LIMIT 1";
			$re=execute_sql($q,0,-1,$nr);
			$found=1;
		}	
	}
// Finally, return what we have, if we found anything:
	if ($found==1) {
		$q="SELECT * FROM performer";
		$q.=" WHERE pid='".$pid."'";
		$re=execute_sql($q,0,1,$nr);
		$r=mysql_fetch_array($re);
		if ($full_bio==0) { // Return summary:
			$ret=$r['bio_short'];
		} else { // Return full bio:
			$ret=$r['bio_long'];
		}
	}	
	return $ret;
}	

	
// Get number of related performers.
// If none found and/or all pics doesn't exist returns 0
function lastfm_get_number_of_related_performers($pid,$pname,$lastfm_min_related_match,$lastfm_max_related_artists) {
	$ret=0;
	$n=0;
	$target_filename='./lastfm/'.$pid.'.xml'; // just to ease things a bit...
	if ((file_exists($target_filename)) && (is_readable($target_filename))) { // Found a file...
		touch($target_filename); // ...so it stays a little longer.
		$xml=retrieve_xml($target_filename,$n,$lastfm_max_related_artists); // Get contents (from disp.php)
		$all_pics_ok=1; // Positive approach: Assume all pics exists up front
		$n=0;		
		while (($n<=$lastfm_max_related_artists) && ($all_pics_ok==1)) {
			if (isset($xml->similarartists->artist[$n]->name[0]) 
			&& ($xml->similarartists->artist[$n]->match[0]>=$lastfm_min_related_match)) {
				$rel_pid=get_performer_id_by_name($xml->similarartists->artist[$n]->name[0]);
				$rel_file='./lastfm/'.$rel_pid.'.jpg';
				if (!file_exists($rel_file) || (!is_readable($rel_file))) {
				// No: We dont have a picture and/or the picture cannot be read
					$all_pics_ok=0;
					$n=$lastfm_max_related_artists;
				} else {
				// Yes: We have a picture, - touch it so it stays in cache a little longer
					touch($rel_file);
					$ret++;
				}	
			}
			$n++;
		}	
		if ($all_pics_ok==0) {
			$ret=0;
		}	
	} 	
	return $ret;
}	

// Ask last.fm, store in local cache & return # of matches found:
// 0.7.9: MODIFIED, as follows:
/*
Rather than just returning whatever, lastfm's artist.getimages method is used in order to store
the "largesquare" (2nd in array) image of a given performer.
If not found, then an image from the "similarartists" array will be used instead.
*/
// 0.8.8: Changed preferred size of images returned+stored (see 0.8.8 entries below)
function lastfm_update_related_performers($pid,$pname,$lastfm_min_related_match,$lastfm_max_related_artists) {
	$ret=0;
	// Prepare to ask last.fm:
	$target_filename='./lastfm/'.$pid.'.xml';
	$lastfm_url=get_lastfm_api_url().'?method=artist.getsimilar';
	$lastfm_url.='&artist='.$pname.'&api_key='.get_lastfm_api_key();
	$xml=retrieve_xml($lastfm_url,$n,$lastfm_max_related_artists); // Get XML
	// echo $lastfm_url.'<br>'; // Just for debugging purposes
	// Open cached version of related performers for this particular performer:
	$cache_handle=fopen($target_filename, 'w');
	fwrite($cache_handle,'<?xml version="1.0" encoding="UTF-8"?>'.chr(10));
	fwrite($cache_handle,'<AmpJuke_Related>'.chr(10));
	fwrite($cache_handle,'<similarartists>'.chr(10));

	// Retrieve & display some images:
	$n=0;
	while ($n<=$lastfm_max_related_artists) {
		// Is the name of the related performer in the array AND is the 'match-score' above what we've configured ?
	 	if (isset($xml->similarartists->artist[$n]->name[0]) 
		&& ($xml->similarartists->artist[$n]->match[0]>=$lastfm_min_related_match)) { // ..Yes, it is..
			// Do we have that performer in the AmpJuke database ?
		 	$qry="SELECT * FROM performer WHERE pname='";
		 	$qry.=addslashes($xml->similarartists->artist[$n]->name[0])."'";
		 	$numr=0;
	 		$result=execute_sql($qry,0,1,$numr);
		 	if ($numr>0) { // ..Yes we do..
//			  	$row=mysql_fetch_array($result);

		 		// 0.7.9: Retrieve images of the related performer from last.fm:
		 		$lastfm_img_url=get_lastfm_api_url().'?method=artist.getimages';
		 		$lastfm_img_url.='&artist='.str_replace(' ','+',$xml->similarartists->artist[$n]->name[0]);
		 		$lastfm_img_url.='&api_key='.get_lastfm_api_key();
	  			$img_xml=retrieve_xml($lastfm_img_url,$n,$n);

			  	// By default, use an image from "similarartists". If found, use the "largesquare" image from
			  	// the previous call (see above):
			  	$img_url='';
				if (isset($xml->similarartists->artist[$n]->image[2])) { // 0.8.8: Changed from [1]
					$img_url=$xml->similarartists->artist[$n]->image[2];
				}	
				if (isset($img_xml->images->image[0]->sizes->size[3])) { // 0.8.8: Changed from [2]
					$img_url=$img_xml->images->image[0]->sizes->size[3];
				}  

				if ($img_url<>'') { // If we have an image of a related performer...
				 	// ...store the image in local cache, if we the performername in the database:
					$rel_pid=get_performer_id_by_name($xml->similarartists->artist[$n]->name[0]);

					if ($rel_pid>0) { // 0.7.7: Found it! Write everything to the local cache:
				 		fwrite($cache_handle,'<artist>'.chr(10));
			 			fwrite($cache_handle,'<name>'.$xml->similarartists->artist[$n]->name[0].'</name>'.chr(10));
		 				fwrite($cache_handle,'<match>'.$xml->similarartists->artist[$n]->match[0].'</match>'.chr(10));		
						$rel_filename='./lastfm/'.$rel_pid.'.jpg'; // 0.7.7
						// 0.8.0: Only write lastfm-image to disk if we haven't got anything locally:
						if (!file_exists($rel_filename)) {
							$chin=fopen($img_url, 'r');						
							$chout=fopen($rel_filename, 'w');
							while (!feof($chin)) {
								$buf=fread($chin,8192);
								fwrite($chout,$buf);
							}	
							fclose($chout);
							fclose($chin);							
						} else { // 0.8.0: "touch" it -> expand the time the image stays on the local drive:
							touch($rel_filename);
						}	
				 		fwrite($cache_handle,'<image>'.$rel_filename);
				 		fwrite($cache_handle,'</image>'.chr(10));
				 		fwrite($cache_handle,'</artist>'.chr(10));
					}
			 	}	
			}	
		}
		$n++;
	}
	fwrite($cache_handle,'</similarartists>'.chr(10));	
	fwrite($cache_handle,'</AmpJuke_Related>'.chr(10));
	fclose($cache_handle);
}

// 0.8.1: SUGGEST images to be used when replacing an album or preformer image:
function lastfm_suggest_images($type,$row,&$total_found) {
	$ret=array();
	$total_found=0;
	// If it's an album, ask using last.fm's album.getinfo method:
	if ($type=='album') {
		$url=get_lastfm_api_url().'?method=album.getinfo&api_key='.get_lastfm_api_key();
		$url.='&limit=4&artist='.urlencode(get_performer_name($row['aperformer_id']));
		$url.='&album='.urlencode($row['aname']);
		$xml=retrieve_xml($url,$dummy,$dummy); 
		// Return whatever is found in 0-3:
		$x=0;
		while ($x<4) {
			$ret[$x]='';
			if (isset($xml->album[0]->image[$x])) { 
				$ret[$x]=$xml->album[0]->image[$x];
				$total_found++;
			}
			$x++;
		}
	}
	// If it's a performer, ask @ last.fm using artist.getimage method API call:
	if ($type=='performer') { 
		//$url=get_lastfm_api_url().'?method=artist.getimages&api_key='.get_lastfm_api_key();
        // 0.8.8: DAMMIT!! Last.fm replaced artist.getimages with artist.getInfo:
        $url=get_lastfm_api_url().'?method=artist.getInfo&api_key='.get_lastfm_api_key();        
		$url.='&limit=4&artist='.urlencode($row['pname']);
		$xml=retrieve_xml($url,$dummy,$dummy); 
		// Return whatever is found in 0-3:
		$x=0;
		while ($x<4) {
			$ret[$x]='';
			if (isset($xml->artist->image[$x])) { 
				$ret[$x]=$xml->artist->image[$x];
				$total_found++;
			}
			$x++;
		}	
	}
	return $ret;
}	

	

// 0.8.1: Automatically insert tracks into a favorite list using last.fm's tag.getTopArtists (based on "tag"):
function lastfm_add_artists_by_tag($fav_list,$tag) {
	echo 'Autopopulate '.$fav_list.'</b> with <b>'.$tag.'</b>...<br>';
	$lastfm_url=get_lastfm_api_url().'?method=tag.getTopArtists';
	$lastfm_url.='&tag='.urlencode($tag).'&api_key='.get_lastfm_api_key();
	$xml=retrieve_xml($lastfm_url,$n,$lastfm_max_related_artists); // Get XML
	$max_artists=sizeof($xml->topartists->artist); // Get max. items in array
	$n=0;
	// Loop through everything returned from last.fm, - check against db & insert all tracks into the favorite list 
	// from each individual performer, IF that performer exists in the database:
	while ($n<=$max_artists) {
		echo $xml->topartists->artist[$n]->name.' ';
		$pid=get_performer_id_by_name($xml->topartists->artist[$n]->name); // Get the performerid,- note 0 is returned if not found:
		if (($pid<>0) && ($xml->topartists->artist[$n]->name<>'')) { // Yes, we have the PID in the database already, - add it:
			// Get all tracks we have with this pid:
			$qry="SELECT id,performer_id,album_id,name,duration,last_played,times_played,year ";
			$qry.="FROM track WHERE performer_id='".$pid."'";
			$result=execute_sql($qry,0,1000000,$refnr);
			while ($row=mysql_fetch_array($result)) { // For each track...
				$ok=1; // ...assume it's ok to insert...
				// Do we have "Avoid duplicate entries" turned on ?
				if ($_SESSION['avoid_duplicate_entries']=="1") { // Yes, we do: Check if this track is there already:
					$q2="SELECT * FROM fav WHERE track_id='".$row['id']."'";
					$q2.=" AND user_id='".$uid."' AND fav_name='".$fav_list."'";
					$r2=execute_sql($q2,0,1,$nr);
					if ($nr!=0) { // Track is already in the favorite list: Don't add it
						$ok=0;
					}
				}			
				if ($ok==1) { // Survived a check - go ahead and insert the track: 
					$q="INSERT INTO fav (track_id, performer_id, album_id, name, duration,";
					$q.=" last_played, times_played, year, user_id, fav_name) VALUES ";
					$q.="(".$row['id'].", ".$row['performer_id'].", ".$row['album_id'].", ";
					$q.="'".$row['name']."', '".$row['duration']."', '".$row['last_played']."', ";
					$q.="'".$row['times_played']."', '".$row['year']."', ".get_user_id($_SESSION['login']).", ";
					$q.="'".$fav_list."')";				
					$res=execute_sql($q,0,-1,$dummy);
				}	
			}	
			echo $refnr.' tracks...';
		} else {
			echo 'not found...';
		}
		echo '<br>';	
		$n++;
	}
}		
?>
