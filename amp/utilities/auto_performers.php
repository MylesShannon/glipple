<?php
// auto_performers.php: Automatically fetch album images
//
// By: Jesper S.

die('Sorry...');

require_once("sql.php");
require_once("set_td_colors.php");
require_once("disp.php");
require_once("lastfm_lib.php");

$special=10;
$antal=300;
echo "processing: " . $antal;

$special=only_digits($special); // 0.7.6

while($special < $antal){
	// get+display headline: performer's name:
	$qry="SELECT * FROM performer WHERE performer.pid=".$special;
	$header_result=execute_sql($qry,0,1,$nr);
	$header_row=mysql_fetch_array($header_result);


	//
	// ************************************* 
	//				*** PART 1 : BIO ***
	// ************************************* 
	//
	print($header_row['pname']);
	//echo $special;
	
	$bio=lastfm_get_bio($header_row['pid'],urlencode($header_row['pname']),0,1);

	$total_related_performers=lastfm_get_number_of_related_performers($header_row['pid'],urlencode($header_row['pname']),
	$lastfm_min_related_match,$lastfm_max_related_artists);
	//print $total_related_performers;
	$total_related_performers=0;
	
	if ($total_related_performers==0) { // ask last.fm:
		$total_related_performers=lastfm_update_related_performers($header_row['pid'],urlencode($header_row['pname']),
		$lastfm_min_related_match,$lastfm_max_related_artists);
		$total_related_performers=lastfm_get_number_of_related_performers($header_row['pid'],urlencode($header_row['pname']),
		$lastfm_min_related_match,$lastfm_max_related_artists);
	}
	
	$lastfm_img_url='http://ws.audioscrobbler.com/2.0/?method=artist.getimages';
	$lastfm_img_url.='&artist='.str_replace(' ','+',urlencode($header_row['pname']));
	$lastfm_img_url.='&api_key=b25b959554ed76058ac220b7b2e0a026';
	$img_xml=retrieve_xml($lastfm_img_url,$n,$n);
	
	$img_url='';
	if (isset($img_xml->images->image[0]->sizes->size[2])) { // Found a better one (126x126 "largesquare")
		$img_url=$img_xml->images->image[0]->sizes->size[2];
	}  
		
	if ($img_url<>'') { // If we have an image of a related performer...
		// ...store the image in local cache, if we the performername in the database:
		$rel_pid=$header_row['pid'];

		if ($rel_pid>0) { // 0.7.7: Found it! Write everything to the local cache:
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
		}
	}
	
	$special++;
}
?>
