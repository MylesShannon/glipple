<?php
session_start();
parse_str($_SERVER["QUERY_STRING"]);

$ok=0;
if (isset($_SESSION['login'])) { $ok++; }
if (isset($_SESSION['passwd'])) { $ok++; }

if ($ok!=2) { 
	session_destroy();
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    die();
}

// 0.6.1: Remember previous settings from random setup.
// Note: Must be here.
if ((isset($what)) && (isset($act)) && ($what=="random") && ($act=="start")) {
	// The values below will be remembered for two weeks (14 days * 86400 secs/day):
	$ok=setcookie('ampjuke_notracks', $_POST['no_of_tracks'], time()+ 1209600, '/', false);
	$ok=setcookie('ampjuke_priority', $_POST['preference'], time()+1209600, '/', false);	
	$ok=setcookie('ampjuke_favlist', $_POST['name'], time()+1209600, '/', false);
	$ok=setcookie('ampjuke_max_duration',$_POST['max_duration'],time()+1209600,'/',false); // 0.8.5
	$ok=setcookie('ampjuke_min_age',$_POST['min_age'],time()+1209600,'/',false); // 0.8.5
}

include("db.php"); // 0.8.6: Moved here.

// 0.8.6: Charset:
if ((!isset($charset)) || (strlen($charset)<=3)) {
	$charset='utf-8';
}

echo '<?xml version="1.0" encoding="'.$charset.'"?>';
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="'.$_SESSION['lang'];
echo '" xml:lang="'.$_SESSION['lang'].'">';
echo '<head>';
echo '<link rel="shortcut icon" href="favicon.ico" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />';


// 0.8.6: Screensaver stuff:
if ((isset($screensaver_enabled)) && ($screensaver_enabled=='1')) {
	echo '<META HTTP-EQUIV="refresh" CONTENT="'.$screensaver_start_time.';URL='.$base_http_prog_dir.'/screensaver.php?new=true">';
	if (isset($_SESSION['screensaver_referer'])) {
		unset($_SESSION['screensaver_referer']);
	}
}

// 0.8.7: New: Tooltipster:
echo '<link rel="stylesheet" type="text/css" href="./tooltipster.css" />';

// 0.8.5: jQuery - here we go :)
echo '<script type="text/javascript" src="jquery-1.8.1.min.js"></script>';
echo '<script type="text/javascript" src="./jquery.tooltipster.min.js"></script>'; // 0.8.7
$ampjuke_animated_objects=1;

// 0.8.7: Advanced search ?
if (isset($what) && ($what=='advsearch')) {
	echo '<link rel="stylesheet" type="text/css" href="./jquery-ui-1.8.23.custom.css">';
	echo '<script type="text/javascript" src="./jquery-ui-1.8.23.custom.min.js"></script>';
	require('disp_advsearch_slider.php');
?>

<?php	
} // what=favorite_adv (long section!)

// 0.7.8: Amazing feature: Add reflection.js for albums:
if ((isset($what)) && ($what=='albumid') && (isset($add_reflections)) && ($add_reflections==1)) {
	echo '<script type="text/javascript" src="reflection.js"></script>';
}

// 0.7.3: Display some performance info. about the box ?
// Uncomment block below (*/ .. */)to turn on. 1=Display info. 2=Display info.+all SQL:
/*
$measure_performance=2; 
if (isset($measure_performance)) {
	$starttimer = time()+microtime();
	$sql_statements=0;
	$sql_txt='';
}	
*/

// 0.7.3: Introduce the 'icon_array' -> only read individual icons ONCE from disk every
// time a page is generated (i.e.: only read "track icon" from disk one time)
$icon_array=array();


// 0.4.3: We're now dynamically linking to the CSS-file:
echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'" />'; 
echo '<script type="text/javascript" src="rowcols.js"></script>';
echo '<script type="text/javascript" src="now_playing.js"></script>';

// 0.8.4: Add support for Windows 7 with Internet Explorer 9 Jump List:
// echo '<script type="text/javascript" src="ie9_jumplist.js"></script>'; // 0.8.7: Depreciated. Who uses IE anyway ?? ;-)

// 0.7.3: Include the CSS tooltip:
// 0.8.7: Not anymore 
// echo '<link rel="stylesheet" type="text/css" href="./tooltip.css">'; 

include("sql.php");
include("disp.php");

if (!isset($what)) {
	$what="welcome";
}		
$what=strip_tags($what);
$what=my_filter_var($what);


// Add the expand/collapse stuff:
if (($what=='settings') || ($what=='sitecfg') || ($what=='performerid')) {
	echo '<script type="text/javascript" src="expand_collapse.js"></script>';
	print "\n";
}

$user_id=get_user_id($_SESSION['login']);

// 0.6.4:
if (($_SESSION['login']!="anonymous") && ($allow_now_playing==1) 
&& (isset($_SESSION['disp_now_playing'])) && ($_SESSION['disp_now_playing']=="1")) {
?>
<script type="text/javascript">
var c=0
var t
function timedCount() {
document.getElementById('ampjuke_now_playing_count').value=c
c=c+1
sndReq('ampjuke_now_playing',<?php echo $user_id; ?>)
t=setTimeout("timedCount()",<?php echo $now_playing_update_rate; ?>)
}
</script>

<?php
}

// VALIDATE/SET URL-parameters. 
if (!isset($start)) {
	$start=0;
}
$start=only_digits($start); // 0.7.6

if (!isset($count)) {
	$count=$_SESSION['count'];
}
$count=only_digits($count); // 0.7.6

if (!isset($order_by)) {
	$order_by="";
}	
$order_by=strip_tags($order_by); // 0.7.2
$order_by=my_filter_var($order_by); // 0.8.5

if (!isset($dir) || (($dir!="ASC") && ($dir!="DESC"))) {
	$dir="ASC";
}
$dir=my_filter_var($dir); // 0.8.5

if (!isset($special)) {
	$special="";
}
$special=strip_tags($special); // 0.7.2
$speciel=my_filter_var($special); // 0.8.5


if (!isset($limit)) {
	$limit='';		
}
// 0.7.2: Refine limit further to prevent XSS:
$limit = strip_tags($limit);
$forbidden = array ("=", "<", ">");
$limit = str_replace($forbidden, "", $limit);
$limit=my_filter_var($limit); // 0.8.4

// 0.8.5: max_duration:
if (isset($_POST['max_duration'])) {
	$max_duration=only_digits($_POST['max_duration']) * 60;
} else {
	$max_duration=0;
}

if (!isset($sorttbl)) {
	$sorttbl='';
}
if (($sorttbl<>'') && ($sorttbl<>'performer') && 
($sorttbl<>'track') && ($sorttbl<>'album')) {
 	$sorttbl='';
}	
$sorttbl=my_filter_var($sorttbl); // 0.8.5

if (!isset($pagesel)) {
	$pagesel='';
}	
if (($pagesel<>'') && ($pagesel<>'performer') && 
($pagesel<>'track') && ($pagesel<>'album')) {
 	$pagesel='';
}	
$pagesel=my_filter_var($pagesel); // 0.8.5

if (isset($filter_tracks)) { // we want to FILTER the tracks displayed
    $_SESSION['filter_tracks']=$filter_tracks;
}

if (!isset($del_btn)) {
	$del_btn=0;
}
$del_btn=only_digits($del_btn);

$filter_tracks=$_SESSION['filter_tracks'];
if (isset($_SESSION['new_start'])) { // start somewhere else wo. regard to what the URL-parameter is:
    $start=$_SESSION['new_start']; // set 'start'...
    unset($_SESSION['new_start']); // ...just once, so we don't get stuck !
}

// used as "global" within scripts:
$playtext='Play';
if ($_SESSION['enqueue']=='1') { $playtext='Queue'; }
$tdnorm='';
$tdalt='';
$tdhighlight='';

$version="0.8.8";

require("translate.php");
$d_track=xlate("Track"); 
$d_performer=xlate("Performer");
$d_album=xlate("Album");
$d_year=xlate("Year");
$d_favorites=xlate("Favorites");
$d_queue=xlate("Queue");
$d_random_play=xlate("Random play");
$d_settings=xlate("Settings");
$d_search=xlate("Search");
$d_logout=xlate("Logout");
$d_admins_options=xlate("Admin's options");
$d_scan_music=xlate("Scan music...");
$d_user_adm=xlate("User adm...");
$d_configuration=xlate("Configuration...");
$d_upload=xlate("Upload");

// 0.6.0: Get/set a more "intelligent" title:
$title="";
if ($what=="track") {
	$title.=xlate("Tracks");
	if (isset($limit) && ($limit<>"")) {
		$title.=':'.$limit;
	}
}	
if ($what=="performer") {
	$title.=xlate("Performers");
	if (isset($limit) && ($limit<>"")) {
		$title.=':'.$limit;
	}
}
if ($what=="performerid") {
	require_once("sql.php");
	require_once("disp.php");
	$special=my_filter_var($special); // 0.8.3
	$title.=get_performer_name($special);
}
if ($what=="album") {
	$title.=xlate("Albums");
	if (isset($limit) && ($limit<>"")) {
		$title.=':'.$limit;
	}
}	
if ($what=="albumid") {
	require_once("sql.php");
	require_once("disp.php");
	$special=my_filter_var($special); // 0.8.3
	$t=get_performer_name_album($special);
	if ($t<>"") { 
		$title.=$t.' - ';
	} else {
		$title.=$t;
	}		
	$forbidden=array("[", "]");
	$title.=str_replace($forbidden,"",get_album_name($special));
}	
if (($what=="year") || ($what=="yearid")) {
	$title.=$d_year;
	if (isset($special) && ($special<>"")) {
		$title.=':'.$special;
	}
}
if ($what=="favorite") {
	$title.=$d_favorites;
}
if ($what=="favoriteid") {
	$title.=xlate("Favorite list").':'.$special;
}	
if ($what=="queue") {
	$title.=xlate("The queue");
}	
if ($what=="random") {
	$title.=$d_random_play;
}	
if ($what=="settings") {
	$title.=$d_settings;
}
if ($what=="search") {
	$title.=$d_search.':'.$limit;
}		
// 0.6.1: Upload
if ($what=="upload") {
 	$title=$d_upload;
}

// 0.7.2: "Folded" menu-items (used with personal settings & configuration):
// 0.8.5: WTF??
//if ($what=='settings') {
//}  
?>
<title><?php echo $title.'  [AmpJuke...and YOUR hits keep on coming !]'; ?></title>

<!-- 0.8.7: Tooltipster start (change values as you see fit / michael@ampjuke.org) -->
<script>
$(document).ready(function() {
$('.tooltip').tooltipster({
animation: 'fade',
arrow: true,
delay: 200,
speed: 400,
timer: 0,
trigger: 'hover',
position: 'bottom-left',
interactiveTolerance: 0,
updateAnimation: true }
);
});
</script>
<!-- 0.8.7: Tooltipster ends -->

</head>
<?php
if (($_SESSION['login']!="anonymous") && ($allow_now_playing==1) && (isset($_SESSION['disp_now_playing'])) && ($_SESSION['disp_now_playing']=="1")) { // 0.8.4
	echo '<body onload="javascript:timedCount();">';
} else {
 	echo '<body>';
}
?>
<div class="all_content">
<!-- OUTLINE TABLE START -->
<table class="ampjuke_outline_table">

<tr>
<td valign="top">
<!-- MENUTABLE START -->
<table class="ampjuke_menu_table">
<tr><td>
<a href="index.php?what=welcome">
<img src="./ampjukeicons/ampjuke_top.gif" border="0" alt="AmpJuke welcome page"/></a>
</td></tr>

<tr><td>
<a href="index.php?what=track&start=0&dir=DESC&order_by=track.id&sorttbl=track&pagesel=track">
<?php // 0.7.0: Get corresponding icon
echo get_icon($_SESSION['icon_dir'],'menu_track',$d_track); ?></a>
</td></tr>

<tr><td>
<a href="index.php?what=performer&start=0&dir=ASC&order_by=performer.pname&sorttbl=performer&pagesel=performer">
<?php echo get_icon($_SESSION['icon_dir'],'menu_performer',$d_performer); ?></a> 
</td></tr>

<tr><td>
<a href="index.php?what=album&start=0&dir=ASC&order_by=album.aid&sorttbl=album&pagesel=album">
<?php echo get_icon($_SESSION['icon_dir'],'menu_album',$d_album); ?></a> 
</td></tr>

<tr><td>
<a href="index.php?what=year&start=0&dir=DESC&order_by=track.year">
<?php echo get_icon($_SESSION['icon_dir'],'menu_year',$d_year); ?></a>
</td></tr>

<?php
// 0.3.7: Block these options for anonymous users:
if ($_SESSION['login']!="anonymous") {
?>
<tr><td>
<a href="index.php?what=favorite">
<?php echo get_icon($_SESSION['icon_dir'],'menu_favorite',$d_favorites); ?></a>
</td></tr>
<tr><td>
<a href="index.php?what=queue">
<?php echo get_icon($_SESSION['icon_dir'],'menu_queue',$d_queue); ?></a>
</td></tr>
<?php } ?>

<tr><td>
<?php // 0.8.6: If we're in jukebox mode, hide random mode - no matter what:
if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) { ?>
<a href="index.php?what=random&act=setup">
<?php echo get_icon($_SESSION['icon_dir'],'menu_random',$d_random_play); } ?></a>
</td></tr>

<?php
// 0.6.1: Upload
// 0.7.4: Added $_SESS...['disp_upload...] check:
include_once("db.php");
if (($_SESSION['can_upload']=="1") && (isset($allow_upload)) 
&& ($allow_upload==1) && ($_SESSION['disp_upload']=="1")) {
?>
<tr><td>
<a href="index.php?what=upload&act=new">
<?php echo get_icon($_SESSION['icon_dir'],'menu_upload',$d_upload); ?></a>
</td></tr>
<?php
}
?>

<tr><td>
<a href="index.php?what=settings">
<?php echo get_icon($_SESSION['icon_dir'],'menu_settings',$d_settings); ?></a>
</td></tr>

<tr><td>
<form name="search" method="POST" 
action="index.php?what=search&start=0&dir=ASC&sorttbl=track&order_by=track.name">

<?php // 0.7.8: Changed so rather than a text-string it now links to adv. search:
echo '<a href="index.php?what=advsearch&act=setup">';
echo get_icon($_SESSION['icon_dir'],'menu_search',$d_search); // Used to be just the text-link
echo '</a>';
?>:

<?php
if (($what=="search") && (isset($limit))) {
	$_SESSION['last_search']=$limit;
}	
// 0.8.4:
if (!isset($_SESSION['last_search'])) {
	$_SESSION['last_search']='';
}
?>	

<input type="text" class="tfield" size="8" name="search"
 value="<?php echo $_SESSION['last_search']?>">
</td></tr>
</form>

<tr><td>
<a href="logout.php">
<?php echo get_icon($_SESSION['icon_dir'],'menu_logout',$d_logout); ?></a>
</td></tr>

<?php
//
//
// 0.6.4: New AmpJuke feature: display what's currently playing:
//
//
if (($_SESSION['login']!="anonymous") && ($allow_now_playing==1) && (isset($_SESSION['disp_now_playing'])) && ($_SESSION['disp_now_playing']=="1")) { // 0.8.4
?>
<tr><td>
<input type="hidden" id="ampjuke_now_playing_count" size="4">
<div id="ampjuke_now_playing"><b>AmpJuke</b><br>...and YOUR hits keep on coming!
</div>
</td></tr>
<?php
print "</td></tr> \n\n";
}

// 0.7.2: Embed stuff here from last.fm, if ya' wannit, podna !
?>
<tr><td>

</td></tr>
<?php

include("db.php");

// *************************************************************************************
//
// 						Options for ADMINISTRATORS:
//
// *************************************************************************************

if (($_SESSION['admin']=="1") && ($what=="welcome")) {
	echo '<tr><td><div class="note">';
	echo $d_admins_options.":<br><br>";
	
	// 0.7.5: Link to brand new scan method:
	echo '<a href="./scan2.php?act=setup">';
	echo get_icon($_SESSION['icon_dir'],'menu_scan_music',$d_scan_music).'</a><br><br>';

	// 0.8.1: Scan+update meta info.:
	echo '<a href="./scanmeta.php?act=setup">';
	echo get_icon($_SESSION['icon_dir'],'menu_scan_music',xlate('Scan metadata')).'...</a><br><br>';
	
	// 0.8.6: Echonest API:
	if ((isset($echonest_enabled)) && ($echonest_enabled=='1')) {
		echo '<a href="./scan_echonest.php?act=setup">';
		echo get_icon($_SESSION['icon_dir'],'menu_scan_music','Echonest').'...</a><br><br>';
	}

	// 0.8.6: Alternative folder navigation + scan management (by Marc Apgar):
	/* 0.8.8: Error(s) when launching - uncomment to see+use nevertheless (at your own risk)
	echo '<a href="./browser3.php">';
	echo get_icon($_SESSION['icon_dir'],'menu_scan_music','Alternative scan').'...</a><br><br>';
	*/
	
    // 0.8.6: Jukebox mode: Build a link
    if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled=='1')) {
		echo '<a href="./?what=build_link&act=rs_setup">';
		echo get_icon($_SESSION['icon_dir'],'menu_scan_music','Jukebox: Create link').'...</a><br><br>';
        // 0.8.8: Scheduler:
		echo '<a href="./?what=scheduler&act=disp">';
		echo get_icon($_SESSION['icon_dir'],'menu_scan_music','Jukebox: Scheduler').'...</a><br><br>';        
	}

	echo '<a href="index.php?what=users&act=disp">';
   	echo get_icon($_SESSION['icon_dir'],'menu_useradm',$d_user_adm).'</a><br><br>';

	echo '<a href="sitecfg.php">';
	echo get_icon($_SESSION['icon_dir'],'menu_configuration',$d_configuration).'</a><br><br>';
	// 0.6.7: Issue warning about # of files that could't be removed in ./tmp:
	/* 0.8.4: Removed
	if ($tcount!=0) {
		echo '<i>Warning: '.$tcount.' file(s) NOT<br>';
		echo 'removed from ./tmp<br>';
		echo 'Most likely because <br>permission(s) dont allow to do so.</i>';
	}
	*/	
	echo '</tr>';
}	
// 0.8.6: Donate: Displayed on ca. 5% of admin's pages (and *only* admin's):
if (($_SESSION['admin']=="1") && ($what=="settings")) { 
?>	
<tr><td>
<font size="1">AmpJuke is <b><font color="RED">FREE</font> and available</b>.<br>Let it stay that way!<br>Please consider a donation<br>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="michael@ampjuke.org">
<input type="hidden" name="item_number" value="AmpJuke Donation (version <?php echo $version; ?>)">
<input type="hidden" name="no_shipping" value="0">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="hidden" name="lc" value="DK">
<input type="hidden" name="bn" value="PP-DonationsBF">
<input type="image" src="./ampjukeicons/paypal.gif" border="0" name="submit" 
alt="PayPal" title="Donate using Paypal">
</form>
<br><i>Note:</i> The message above is only displayed to administrators
</td></tr>
<?php	
} // if user=admin

print "</table> \n\n <!-- MENU TABLE ENDS, CONTENT FOLLOWS: --> \n\n";
echo '</td><td valign="top">';
echo '<table class="ampjuke_main_content_table"><tr><td align="left">';
?>
