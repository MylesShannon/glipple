<?php
/*
Date: 2012-07-24.
Version: 1.0
Author(s): Michael H. Iversen (michael@ampjuke.org).

Description:
google_lib.php: Automatically fetch album covers for AmpJuke using Google. Very, very simple. 
Quick hack, since M$ Bing! is abandoning true free meter usage (typical).

Requirements:
None.

*/

// "Constructs" and returns URL based on q:
function google_construct_query($q,$rsz=4) {
	return 'https://ajax.googleapis.com/ajax/services/search/images?v=1.0&rsz='.$rsz.'&as_filetype=jpg&as_epq=1&safe=1&q='.urlencode($q);
}

// Search Google (quick hack) & return results:
function google_image_search($gurl,$decode = true) {
	$json = file_get_contents($gurl);
	if ($decode==true) {
		$json = json_decode($json,true);
	}
	return $json;
}


// Return ONE URL (1st found) from a google search:
function google_get_image_url($q) {
	$ret='';
	$qry=google_construct_query($q,1);
	$g=google_image_search($qry);
	if (isset($g['responseData']['results'][0]['url'])) {
		if ($g['responseData']['results'][0]['width']<800) { // Avoid very large images
			$ret=$g['responseData']['results'][0]['url'];
		}
	}
	return $ret;
}

// Display search results (used to replace an image of a performer or an album):
function google_suggest_images($g,$type,$special) {
	echo std_table("ampjuke_content_table","ampjuke_content2"); 
	echo '<tr>';
	echo '<td colspan="8" align="center"><i>Search results powered by: <a href="http://www.google.com" target="_blank">';
	echo 'Google Search</td></tr><tr>';

	$i=0;
	while ($i<4) {
		if (isset($g['responseData']['results'][$i]['url'])) {
			if ($g['responseData']['results'][$i]['width']<800) { // Avoid very large images
				echo '<td>';
				echo '<a href="index.php?what=images&type='.$type.'&special='.$special.'&act=replace';
				echo '&new_img='.$g['responseData']['results'][$i]['url'].'">';
				echo '<img src="'.$g['responseData']['results'][$i]['url'].'" border="0" title="Use this image">';
				echo '</a></td>';
			}
		}
		$i++;
	}
	echo '</tr></table>';
}

?>
