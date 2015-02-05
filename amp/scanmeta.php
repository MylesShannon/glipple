<?php
// scanmeta.php: Scan+update "meta" information (ie. pictures) using M$ Bing!

require('logincheck.php');
if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
	header("Location: logout.php");
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="EN">';
echo '<head>';
require_once('db.php');

echo '<title>Scan+import metadata [AmpJuke...and YOUR hits keep on coming!]</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset="'.$charset.'" />';
//echo '<meta http-equiv="Refresh" content="1" />';
echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'" />'; 
echo '<script type="text/javascript" src="rowcols.js"></script>';
require_once('translate.php');
require_once('disp.php');
require_once('sql.php');
require_once('sql.php');
require_once('configuration.php'); 
require_once('set_td_colors.php');
require_once('tbl_header.php');
//require_once('bing_lib.php'); 0.8.6: M$ Bing! not used anymore: Low free meter usage + too complex.
require_once('google_lib.php');

// ***** CRON-SETTINGS:
// Want to run this using cron ?
// If yes, read on and uncomment+modify entries below:
/*
$_POST['get_album_images']='1'; // Uncomment if covers should be fetched.
$_POST['delete_album_images_wo_reference']='1'; // Uncomment to delete album-images wo. any reference in the database.
$_POST['album_preferred_dimension']=200; // Uncomment+change to whatever preferred size you want to look for.

$_POST['get_performer_images']='1'; // Uncomment if artist/performer images should be fetched.
$_POST['delete_performer_images_wo_reference']='1'; // Uncomment to delete performer images wo. any reference in the database.
$_POST['performer_preferred_dimension']=200; // Uncomment+change to whatever preferred size you want to look for.

$details='2'; // Uncomment+set a value of 1-4. 1=minimal output, 2=normal, 4=most.
$_POST['simulate_import']='1'; // Uncomment if you're a sissy :-) Well: doesnt make sense to simulate something in a CRON-script, right ?

$path_to_covers='/absolute/path/to/covers/WITH/slash/'; // Uncomment+set the absolute path to covers. Remember last slash...
$path_to_performers='/absolute/path/to/performers/WITH/slash/';  // Uncomment+set absolute path to performers. Remember last slash
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
echo '<body>';
/*
******************************
						SCAN (support functions below)
******************************
*/
function get_bing_id() {
	return get_configuration('bing_appid');
}

function update_status($level,$details,$msg) {
 	if ($details>=$level) {
		echo '<tr><td>'.$msg.'</td>';
		print "</tr> \n";
		@flush(); 
		@ob_flush();
	}	
}	


function store_it($url,$dir,$id,$details) {
	$data=file_get_contents($url);
	update_status('4',$details,'Storing data... From:'.$url.' To:'.$dir.$id.'.jpg');
	$handle=fopen($dir.$id.'.jpg', 'w');
	fwrite($handle,$data);
	fclose($handle);
	update_status('4',$details,'Stored!');
}


/*


			ACTUAL PROCESSING:
*/


// "Generic" function to remove "what" (album or performer) images from "where" (=foldername):
function process_stuff_wo_reference($simulate_import,$details,$what,$where) {
	// Read all images from "where", store in array:
	update_status('2',$details,'Read all existing <b>'.$what.'</b> images from <b>'.$where.'</b>...');
	$cover=array();
	$i=0;
	$d = opendir($where);
	while(($f = readdir($d)) !== false) {
		if(ereg('.jpg$', $f)) {
			$cover[$i]=$f;
			$i++;
		}
	}
	closedir($d);
	update_status('2',$details,'Number of existing <b>'.$what.'</b> images found: '.$i.' in '.$where);

	// Process array, compare with id's in database, if id is not in database then delete the corresponding .jpg from the filesystem.
	// Optional: If what=performer then try to delete XML file as well:
	$victims=0;
	$n=0;
	while ($n<$i) {
		$c=explode('.',$cover[$n]);
		if (is_numeric($c[0])) {
			if ($what=='album') {
				$qry="SELECT aid FROM album WHERE aid=".$c[0];
			}
			if ($what=='performer') {
				$qry="SELECT pid FROM performer WHERE pid=".$c[0];
			}
			$found=0;
			$result=execute_sql($qry,0,1,$found);
			update_status('4',$details,'QRY='.$qry.' FOUND='.$found);
			if ($found==0) {
				$victims++;
				if (!is_writable($where.$c[0].'.jpg')) {
					update_status('3',$details,'<b>Cannot remove '.$what.' image: '.$where.$c[0].'.</b> Not enough permission');
				} else {
					if ($simulate_import==0) {
						unlink($where.$c[0].'.jpg');
					}
					update_status('3',$details,'Removed '.$what.' image: '.$c[0]);
				}
				// Optional: Remove XML-file associated with this performer:
				if ($what=='performer') {
					if (!is_writable($where.$c[0].'.xml')) {
						update_status('3',$details,'<b>Cannot remove XML-file: '.$where.$c[0].'.xml.</b> Not enough permission');
					} else {
						if ($simulate_import==0) {
							unlink($where.$c[0].'.xml');
						}
						update_status('3',$details.'Removed XML-file: '.$where.$c[0].'.xml');
					}
				}
			}
		}
		$n++;
	}
	update_status('2',$details,'Number of <b>'.$what.'</b> images without reference: '.$victims);
}



function process_album_images($simulate_import,$details,$album_preferred_dimension,$path_to_covers,$use_blank_album_cover) {
	$qry="SELECT * FROM album ORDER BY aname";
	$result=execute_sql($qry,0,1000000,$dummy);
	update_status('1',$details,'Number of albums to be scanned: '.$dummy);
	while ($row=mysql_fetch_array($result)) {
		update_status('4',$details,'Check: '.$row['aid'].' - '.$row['aname']);
		if (!file_exists($path_to_covers.$row['aid'].'.jpg')) {
			$s='"'.get_performer_name($row['aperformer_id']).' - '.$row['aname'].'"';
			update_status('3',$details,'No cover found for: '.$s.'. Ask Google...'); // 0.8.6
			//$cover_url=bing_search(get_bing_id(),$s,3,$album_preferred_dimension,$album_preferred_dimension);
			$cover_url=google_get_image_url($s);
			update_status('3',$details,'Google returned: '.$cover_url);
			if ($simulate_import==0) { // We're not kiddin': Do it:
				if ($cover_url<>'') {
					store_it($cover_url,$path_to_covers,$row['aid'],$details);
					update_status('2',$details,'Found image for:'.$row['aname'].' at:'.$cover_url);
				} else {
					update_status('3',$details,'Nothing found - use blank image...');
					store_it($path_to_covers.'_blank.jpg',$path_to_covers,$row['aid'],$details);
				}
			}
		}
	}
	update_status('2',$details,'Done processing album images');
}


function process_performer_images($simulate_import,$details,$performer_preferred_dimension,$path_to_performers,
$use_blank_performer_cover) {
	$qry="SELECT pid,pname FROM performer ORDER BY pname";
	$result=execute_sql($qry,0,1000000,$dummy);
	update_status('1',$details,'Number of performers to be scanned: '.$dummy);
	while ($row=mysql_fetch_array($result)) {
		if (strlen($row['pname'])>2) {
			update_status('4',$details,'Check: '.$row['pid'].' - '.$row['pname']);
			if (!file_exists($path_to_performers.$row['pid'].'.jpg')) {
				$s='"'.$row['pname'].'" "artist"';
				update_status('3',$details,'No image found for: '.$row['pname'].'. Ask Google...');
				//$cover_url=bing_search(get_bing_id(),$s,3,$performer_preferred_dimension,$performer_preferred_dimension);
				$cover_url=google_get_image_url($s);
				update_status('3',$details,'Google returned: '.$cover_url);
				if ($simulate_import==0) {
					if ($cover_url<>'') {
						store_it($cover_url,$path_to_performers,$row['pid'],$details);
						update_status('2',$details,'Found image for:'.$row['pname'].' at: '.$cover_url);
					} else {
						update_status('3',$details,'Nothing found - use blank image...');
						store_it($path_to_performers.'_blank.jpg',$path_to_performers,$row['pid'],$details);
					}
				}
			}
		}
	}
	update_status('2',$details,'Done processing performer images');
}
/*
******************************
						SCAN
******************************						
*/
if ($act=='scan') {
	set_time_limit(0); // Believe me: you want this !
	error_reporting(0); // Believe me: you also want this ! Unfortunately...
	$starttimer = time()+microtime(); // Used to calc. the total duration

	echo headline('','Scan+import meta data','');
	echo std_table("ampjuke_content_table","ampjuke_content");
	?>
	<th colspan="3">Status</th>
	<tr><td width="50%" valign="top"><p class="note">
	Note: Bery very, very patient ! This might take a long time.</p>
	<?php

	// Setup what's POST'ed, display settings:
	$details=$_POST['details'];
	update_status('1',$details,'Detail level (1-4): '.$details);

	update_status('1',$details,'Settings (1=activated, 0=not activated):');
	$get_album_images=0;
	if (isset($_POST['get_album_images'])) {
		$get_album_images=1;
	}
	update_status('1',$details,'Lookup and store album images: '.$get_album_images);
	
	$delete_album_images_wo_reference=0;
	if (isset($_POST['delete_album_images_wo_reference'])) {
		$delete_album_images_wo_reference=1;
	}
	update_status('1',$details,'Delete album images without any reference in the database: '.$delete_album_images_wo_reference);

	$use_blank_album_cover=0;
	if (isset($_POST['use_blank_album_cover'])) {
		$use_blank_album_cover=1;
	}
	update_status('1',$details,'If no album cover is found, use a blank one: '.$use_blank_album_cover);

	$get_performer_images=0;
	if (isset($_POST['get_performer_images'])) {
		$get_performer_images=1;
	}
	update_status('1',$details,'Lookup and store performer images: '.$get_performer_images);
	
	$delete_performer_images_wo_reference=0;
	if (isset($_POST['delete_performer_images_wo_reference'])) {
		$delete_performer_images_wo_reference=1;
	}
	update_status('1',$details,'Delete performer images without any reference in the database: '.$delete_performer_images_wo_reference);	

	$use_blank_performer_cover=0;
	if (isset($_POST['use_blank_performer_cover'])) {
		$use_blank_performer_cover=1;
	}
	update_status('1',$details,'If no performer cover is found, use a blank one: '.$use_blank_performer_cover);	

	$simulate_import=0;
	if (isset($_POST['simulate_import'])) {
		$simulate_import=1;
		update_status('1',$details,'<b>Note:</b> Simulation is turned on. Nothing will be changed.');
	}
	// Sets paths, unless already specified in CRON-settings above:
	if (!isset($path_to_covers)) {
		$path_to_covers='./covers/';	
	}
	update_status('1',$details,'Path (absolute or relative) to album covers: '.$path_to_covers);

	if (!isset($path_to_performers)) {
		$path_to_performers='./lastfm/';
	}
	update_status('1',$details,'Path (absolute or relative) to performer images: '.$path_to_performers);

	update_status('1',$details,'Start scan+import of metadata...');


// **************************************	
// 						GO !!!!!!
// **************************************	
	if ($get_album_images==1) {
		update_status('2',$details,'Get album images checked...');
		process_album_images($simulate_import,$details,$album_preferred_dimension,$path_to_covers);
	}
	if ($delete_album_images_wo_reference==1) {
		update_status('2',$details,'Delete album images wo. reference...');
		process_stuff_wo_reference($simulate_import,$details,'album',$path_to_covers);
	}
	if ($get_performer_images==1) {
		update_status('2',$details,'Get performer images checked...');
		process_performer_images($simulate_import,$details,$performer_preferred_dimension,$path_to_performers);
	}
	if ($delete_performer_images_wo_reference==1) {
		update_status('2',$details,'Delete performer images wo. reference...');
		process_stuff_wo_reference($simluate_import,$details,'performer',$path_to_performers);
	}

	update_status('1',$details,'Done');
	
	$stoptimer = time()+microtime();
	$timer = round($stoptimer-$starttimer,2);
    $msg='<br><p>Scan+import finished after '.$timer.' seconds<br>';
    $msg.='<a href="'.$base_http_prog_dir.'/';
	$msg.='">Click here to go back to the "Welcome" page.</a>';
	echo $msg;
	update_status('1',$details,$msg);
}
/*
******************************
						SET UP
******************************						
*/
if ($act=='setup') {

// Setup: Options
 	echo '<FORM NAME="scanform" method="POST" action="scanmeta.php?act=scan">';
	echo std_table("ampjuke_content_table","ampjuke_content");
	echo '<th colspan="2">Scan metadata</th>';
	echo '<tr><td align="left">';
	echo '<a href="http://www.ampjuke.org/?id=faq77" target="_blank">';
	echo 'Click here to see the FAQ-entry explaining this (will open in a new window)</a>';
	echo '<td align="right"><i>Note:Search is powered by the <a href="http://www.google.com/" target="_blank">';
	echo 'Google Search</a></i></td></tr>';

// General options:
	// Bing! appid:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan=2" align="center"><b>Options</b></td></tr>';
	// Write status messages to screen:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Show details during scan+import:</td><td>';
	echo '<SELECT NAME="details" class="tfield">';
	echo add_select_option('1','Minimal: Very little ','');
	echo add_select_option('2','Normal: Status on new albums,performers','1');
	echo add_select_option('3','Detailed: Like "normal" plus more info.','');
	echo add_select_option('4','Very detailed: A LOT of things will be shown. Really.','');
	echo '</SELECT></td></tr>';
	// Simulate:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>Simulate import (don't change/alter anything):</td><td>";
	echo add_checkbox('simluate_import','').'</td></tr>';
		
// Get album images ?
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan=2" align="center"><b>Album options</b></td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Get album images:</td>';
	echo '<td>'.add_checkbox('get_album_images','1');
	echo '</td></tr>';
	// If none found, use _blank.jpg:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>If no cover image is found, use a blank one:</td>';
	echo '<td>'.add_checkbox('use_blank_album_cover','').'</td></tr>';	
	// Delete album images without reference:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Delete album images without a reference:</td>';
	echo '<td>'.add_checkbox('delete_album_images_wo_reference','').'</td></tr>';

// Get performer images, preferred size:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan=2" align="center"><b>Performer options</b></td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Get performer images:</td>';
	echo '<td>'.add_checkbox('get_performer_images','1');
	echo '</td></tr>';
	// If none found, use _blank.jpg:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>If no performer image is found, use a blank one:</td>';
	echo '<td>'.add_checkbox('use_blank_performer_cover','').'</td></tr>';		
	// Delete performer images without reference:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>Delete performer images without a reference:</td>';
	echo '<td>'.add_checkbox('delete_performer_images_wo_reference','').'</td></tr>';

// 
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center">';
	echo '<input type="submit" value="Start scan+import"></td></tr>';
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
