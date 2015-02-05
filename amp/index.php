<?php
// 0.7.6: Check if we have setup.php - if we do: switch to setup of AmpJuke:
if (file_exists('setup.php')) {
	header("Location: setup.php");
	echo 'Tried to enter setup.php, but did not succeed...<br>';
	echo '<a href="./setup.php">Click here to go to setup.php now</a>';
	die();
}	

require("make_header.php");

// 0.3.6: Play something automatically ?
if (isset($_SESSION['autoplay'])) { // autoplay: jump to different URL using js.:
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("random.php?autoplay=1&num_tracks='.$num_tracks.'&list='.$list.'&max_duration='.$max_duration.'");'; // 0.8.5: max duration
	echo '</script>';
	session_unregister('autoplay'); // we don't want to do autoplay again in this session.
}

if ($what=="welcome") {
	require("welcome.php");
}	

if ($what=="track") {
	require("disp_track.php");
}	

if ($what=="performerid") {
	require("disp_performerid.php");
}	

if ($what=="performer") {
	// 0.7.9: Find out how we want performers displayed:
	if ($_SESSION['browse_performer_by_picture']=='0') {
		require("disp_performer.php");
	} else {
		require("disp_performer_by_picture.php");
	}	
}	

if ($what=="albumid") {
	require("disp_albumid.php");
}	

if ($what=="album") {
	// 0.7.9: Find out how we want albums to be displayed:
	if ($_SESSION['browse_albums_by_covers']=='0') {
		require("disp_album.php");
	} else {	
		require("disp_album_by_cover.php");
	}	
}	

if ($what=="year") {
	require("disp_year.php");
}

if ($what=="favorite") {
	require("disp_favorite.php");
}	

if ($what=="yearid") {
	require("disp_yearid.php");
}		

if ($what=="favoriteid") {
	require("disp_favoriteid.php");
}	

if ($what=="queue") {
	require("disp_queue.php");
}

if ($what=="random") {
	require("random.php");
}

if ($what=="search") {
	require("search.php");
}		

if ($what=="settings") {
	require("disp_settings.php");
}	

if ($what=="users") {
	require("disp_users.php");
}

if ($what=="edit") {
	require("edit.php");
}	

if ($what=="download") {
	require("download.php");
}

// 0.5.2: shared favorites:
if ($what=="fav_share") {
	require("disp_fav_shares.php");
}	

// 0.6.1: Upload !
if ($what=="upload") {
 	require("upload.php");
}	

// 0.7.1: Update last_scan_date, then redirect to the "welcome" page:
if ($what=="last_scan_date") {
	require_once("configuration.php");
	write_configuration('$last_scan_date',$unix_timestamp);
	redir("index.php");
}

// 0.7.5: Scan:
if ($what=='scan') {
 	require('scan2.php');
}	

// 0.7.6: Configure:
if ($what=='sitecfg') {
	require('sitecfg.php');
}	

// 0.7.8: Adv. search:
if ($what=='advsearch') {
	// 0.8.7: Determine HOW to perform an advanced search:
	if ((isset($echonest_advanced_search)) && ($echonest_advanced_search=='1')) { // Use echonest:
		require('disp_advsearch_echonest.php');
	} else { // ...the usual stuff:
		require('disp_advsearch_normal.php');
	}
}

// 0.8.1: Lookup images:
if ($what=='images') {
	require('disp_images.php');
}	

// 0.8.6: Build a link:
if ($what=='build_link') {
    require('build_link.php');
}

// 0.8.8: Scheduler:
if ($what=='scheduler') {
    require('disp_scheduler.php');
}

print "</td></tr></table> \n\n\n <!-- MAIN_CONTENT_TABLE_ENDS --> \n\n\n";
print "\n\n\n <!-- ROW ENDS FOR THE OUTLINE TABLE: --> \n </td></tr><tr></table>";
?>
<!-- 0.4.3: apply rollover-effects using a bit of JS -->
<script type="text/javascript">
addTableRolloverEffect('ampjuke_content','tableRollOverEffect','');
<?php
if (isset($table2)) {
?>
addTableRolloverEffect('ampjuke_content2','tableRollOverEffect','');
<?php
}
?> 
<?php
if (isset($table3)) {
?>
addTableRolloverEffect('ampjuke_content3','tableRollOverEffect','');
<?php
} 
if (isset($table4)) {
?>
addTableRolloverEffect('ampjuke_content4','tableRollOverEffect','');
<?php
}
if (isset($table5)) {
?>
addTableRolloverEffect('ampjuke_content5','tableRollOverEffect','');
<?php
}
if (isset($table6)) {
?>
addTableRolloverEffect('ampjuke_content6','tableRollOverEffect','');
<?php
}
if (isset($table7)) {
?>
addTableRolloverEffect('ampjuke_content7','tableRollOverEffect','');
<?php
}
?> 
<?php
if (isset($table8)) {
?>
addTableRolloverEffect('ampjuke_content8','tableRollOverEffect','');
<?php
}
?> 
<?php
if (isset($table9)) {
?>
addTableRolloverEffect('ampjuke_content9','tableRollOverEffect','');
<?php
}
?> 
<?php
if (isset($table10)) {
?>
addTableRolloverEffect('ampjuke_content10','tableRollOverEffect','');
<?php
}
?> 
</script>

<table>
<tr><td>
<!-- Do *NOT* under any circumstance remove the link to the AmpJuke site...

...and that's supposed to be taken seriously. Really.

Thanks !

Michael H. Iversen (michael@ampjuke.org)
-->
<a href="http://www.ampjuke.org" target="_blank">
<font face="Verdana"><font size="1"><color="#a9a9a9">
AmpJuke Version <?php echo $version ?></a>
</td>
</tr></table>

</div>
<?php
// 0.7.3: Display/log some performance-info.? Uncomment block below (and see "make_header.php", "index.php" and "sql.php"):
/*
if (isset($measure_performance)) {
	$stoptimer = time()+microtime();
	$timer = round($stoptimer-$starttimer,4);
	echo 'Page generation time:'.$timer.' secs. SQL-statements:'.$sql_statements;	
	// Uncomment next line if you want to log results to ./tmp/debug.txt
	mydebug('measure_performance',$what.';'.$timer);
	if ($measure_performance>1) {
		echo '<br>SQL-staments:<br>'.$sql_txt;
	}
}	
*/

// 0.8.5: Animate all objects, - if set:
if ((isset($animation_enabled)) && ($animation_enabled=='1')) {
?>
<script type="text/javascript">

$(document).ready(function(){
	var i=0;
	for (i=0;i<=<?php echo $ampjuke_animated_objects?>;i++)	{	
		$(".ampjuke_animation_"+i).animate({opacity: 0},1).delay(<?php echo $animation_delay_timing;?>).animate({opacity: 1.0},<?php echo $animation_opacity_timing;?>);	
	}
});
</script>
<?php } ?>
</body>
</html>
