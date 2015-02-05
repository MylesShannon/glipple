<?php
// New in 0.8.1: Suggest images for an album or performer/artist based on API's from last.fm and Microsoft Bing.
require('logincheck.php');
if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
	header("Location: logout.php");
}

require_once("sql.php");
require_once("set_td_colors.php");
require_once("disp.php");
require_once('lastfm_lib.php');
// require_once('bing_lib.php'); 0.8.6: Abandoned M$ Bing (low free meter usage, too complex => simple not good enough!) :(
require_once('google_lib.php'); // 0.8.6: Introduced
require_once('configuration.php');

$special=only_digits($special); 
if (($type<>'album') && ($type<>'performer')) {
	die();
}	
if (!isset($act)) {
	$act='suggest';
}	


function store_it($url,$dir,$id) {
	$data=file_get_contents($url);
	$handle=fopen($dir.$id.'.jpg', 'w');
	fwrite($handle,$data);
	fclose($handle);
}


if ($act=='replace') {
	$dir='./covers/';
	if ($type<>'album') {
		$dir='./lastfm/';
	}	
	store_it($new_img,$dir,$special);
	$ref_url=$_SESSION['referer'];
	unset($_SESSION['referer']);
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("'.$ref_url.'");';
	echo '</script>';	
	die('<a href="'.$ref_url.'">'.$ref_url.'</a>');	
}

if ($act=='suggest') {
	// We need to remember where we came from
	$_SESSION['referer']=$_SERVER["HTTP_REFERER"];

	// Build+execute query:
	$qry="SELECT * FROM ".$type." WHERE ";
	$tid='aid';
	if ($type=='performer') {
		$tid='pid';
	}
	$qry.=$tid."=".$special; // complete query: select * from (album|performer) where (aid|pid)='special'
	$result=execute_sql($qry,0,1,$dummy); 
	$row=mysql_fetch_array($result); // There's always just one row in this resultset.

	print "\n\n\n <!-- CONTENT TABLE START --> \n\n\n";
	echo '<table class="ampjuke_actions_table"><tr><td>';
	// 0.8.4: Add a LINK BACK to the album we're trying to get a new image for:
	// 0.8.5: Adjusted so this also works for PERFORMERS/ARTISTS:
	$alink=$base_http_prog_dir.'/?what=albumid&start=0&count=15&special=';
	if ($type=='performer') {
		$alink=$base_http_prog_dir.'/?what=performerid&start=0&count=15&special='; // 0.8.5
	}
	
	if ($type=='performer') {
		$alink.=$row['pid'];
	} else {
		$alink.=$row['aid'];
	}
	// ...0.8.4 (cont): and use the link ($alink) next:
	if ($type=='performer') {
		echo 'Search results for the <b class="note"><a href="'.$alink.'">'.$type.': '.$row['pname'].'</a></b>. ';
	} else {
		echo 'Search results for the <b class="note"><a href="'.$alink.'">'.$type.': '.$row['aname'].'</a></b>. ';
	}	
	echo 'Click on the image below you want to use</b></td></tr></table>';

	// Grab some images from last.fm:
	$a=lastfm_suggest_images($type,$row,$total_found);
	if ($total_found>0) { // Found some piccies @ last.fm, - print 'em:
		$x=0;
		echo std_table("ampjuke_content_table","ampjuke_content");
		echo '<tr>';
		echo '<td colspan="8" align="center"><i>Search results powered by: <a href="http://www.last.fm/api/intro" target="_blank">';
		echo 'Last.fm Web Services</td></tr><tr>';
		while ($x<count($a)) {
			echo '<td align="center">';
			echo '<a href="index.php?what=images&type='.$type.'&special='.$special.'&act=replace&new_img='.$a[$x].'">';
			echo '<img src="'.$a[$x].'" border="0" title="Click on this image to use it"></a><br>';
			echo '</td>';
			$x++;
		}	
		echo '</tr></table>';
	}

	



if ($type=='performer') {
	$q=urlencode($row['pname']);
} else {
	$q=urlencode($row['aname'].' album');
}

$g=google_construct_query($q);

$result=google_image_search($g);

$table2=1;
google_suggest_images($result,$type,$special);

	
	// 0.8.6: M$ Bing: Not used anymore  - see above :(
	// If we have a bing appid, then also suggest something from Bing:
	/*
	if (get_configuration('bing_appid')<>'') {
		if ($type=='album') {
			$bing_search=get_performer_name($row['aperformer_id']).' - '.$row['aname'];
		}	
		if ($type=='performer') {
			$bing_search='"'.$row['pname'].'" "artist"';
		}	
		$a=bing_suggest_images($type,$row,get_configuration('bing_appid'),get_configuration('bing_preferred_size'),$bing_search,$total_found);
		if ($total_found>0) { // Found pictures @ BING!. Print 'em:			
			$x=0;
			echo std_table("ampjuke_content_table","ampjuke_content2"); 
			$table2=1;
			echo '<tr>';
			echo '<td colspan="8" align="center"><i>Search results powered by: <a href="http://www.bing.com/developers" target="_blank">';
			echo 'Microsoft Bing API 2.0</td></tr><tr>';
			while ($x<count($a)) {
				echo '<td align="center">';
				echo '<a href="index.php?what=images&type='.$type.'&special='.$special.'&act=replace&new_img='.$a[$x].'">';
				echo '<img src="'.$a[$x].'" border="0" title="Click on this image to use it"></a><br>';
				echo '</td>';
				$x++;
			}	
			echo '</tr></table>';
		}
	}	
	*/
	
	// Finally, always suggest the _blank images:
	echo std_table("ampjuke_content_table","ampjuke_content3"); // Print'em:
	$table3=1;
	echo '<tr><td colspan="8" align="center">';
	echo '<a href="index.php?what=images&type='.$type.'&special='.$special.'&act=replace&new_img=';
	echo $base_http_prog_dir.'/covers/_blank.jpg">';
	echo '<img src="'.$base_http_prog_dir.'/covers/_blank.jpg" border="0"></a><br>';
	echo '</td></table>';
	
	// Type an url - Jesper S 31.03.2010
	echo std_table("ampjuke_content_table","ampjuke_content4");
	$table4=1;
	echo '<FORM name="replce_img" method="get" action="index.php?what=images&type='.$type.'&special='.$special.'&act=replace">';   
	echo '<input type="hidden" name="what" value="images">';
	echo '<input type="hidden" name="type" value="'.$type.'">';
	echo '<input type="hidden" name="special" value="'.$special.'">';
	echo '<input type="hidden" name="act" value="replace">';
	echo '<tr><td>Url:</td><td colspan="3"><input type="text" class="tfield" name="new_img" size="60"></td></tr>';
	echo '<tr><td align="center" colspan="4"><input type="submit" value="Use Url"></td></tr>';
	echo '</FORM></table>';	
	
} // act==suggest

?>
