<?php
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}


require_once("sql.php");
require_once("set_td_colors.php");
require_once("disp.php");


$qry="SELECT track.id, track.name, track.performer_id, ";
$qry.="track.duration, track.year, track.last_played, ";
$qry.="track.times_played, track.path, ";
$qry.="track.album_id, performer.pid, performer.pname";
$qry.=" FROM track, performer ";
$qry.="WHERE track.performer_id=performer.pid";
if (isset($limit)) {
	if ($what=="search") {
		$qry.=' AND track.name LIKE "%'.$limit.'%"';
	} else {
	 	// 0.7.4: Make sure we only accept letters+numbers:
 		$limit=preg_replace ("/[^0-9^a-z^A-Z^.]/","",$limit);
	 	// 0.7.4: Is limit=0 ? Yes - only show stuff starting w. numbers:
	 	if ($limit=='0..9') {
			$qry.=" AND track.name REGEXP '^[0-9]'";
		} else {
			$qry.=' AND track.name LIKE "'.$limit.'%"';	 		
		}
	}		
}

// do we want to filter ?
if (isset($filter_tracks)) {
    if ($filter_tracks==1) {
        $qry.=" AND track.album_id<>0"; // tracks WITH albums
    }
    if ($filter_tracks==2) {
        $qry.=" AND track.album_id=0"; // tracks WITHOUT albums
    }
}

if ($order_by=="track.id" && $_SESSION['show_ids']=="0" && $what!="welcome") { // 0.6.3: ...!=welcome
	$order_by="track.name";
	$dir="ASC";
}

// 0.7.4: Filter $order_by:
if (($order_by!="track.id") && ($order_by!="track.name") && ($order_by!="performer.pname")
&& ($order_by!="track.year") && ($order_by!="track.duration") && ($order_by!="track.last_played")
&& ($order_by!="track.times_played") && ($order_by!="rand()")) {
	$order_by="track.name";
}	

if (($order_by!="") && ($sorttbl="track")) {
	$qry.=" ORDER BY $order_by $dir ";
}

$tmpstart=$start;
$tmpsel=$pagesel;

if ($pagesel=="track") {
	$result=execute_sql($qry,0,100000,$num_rows,0);
	$result=execute_sql($qry,$start,$count,$n_rows,0);	
} else {
	$result=execute_sql($qry,0,$count,$num_rows,0);	
	$start=0;
	$pagesel="track";
}	

$l="";
if ($limit=="") { $l.=xlate('All'); }
if ($what=="search") { $l="</i>&nbsp[".xlate("Tracks")."]"; }

// Make appropriate headline
switch ($filter_tracks) {
    case 0: $e="No"; break;
    case 1: $e="Tracks only on albums"; break;
    case 2: $e="Tracks not on any album"; break;
}
if ($what!="welcome") {
	if ($filter_tracks==0) {
    	echo headline($what,'',$limit.$l.'</i> <br>'.xlate("Matches").':<i>'.$num_rows.'</i>');
	} else {
    	echo headline('',$e,$limit.$l.'</i> <br>'.xlate("Matches").':<i>'.$num_rows.'</i>');
	}
}

echo std_table("ampjuke_actions_table","");

// display filter options:
if (($what!="search") && ($what!="welcome")) { // 0.6.3: Added check for "welcome"
 	echo '<tr><td>';
	echo filter_link($filter_tracks,0,"No",$_SESSION['icon_dir']);
    echo filter_link($filter_tracks,1,"Tracks only on albums",$_SESSION['icon_dir']);
    echo filter_link($filter_tracks,2,"Tracks not on any album",$_SESSION['icon_dir']);
	echo add_faq(7); // 0.6.5
	echo show_letters("track","track.name");
	echo '</td></tr></table></td></tr><tr><td>';    
}

if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }

// 0.6.3: Changed this in order to have 'correct' zebra-table on the 'welcome' page:
if ($what!="welcome") { 
	echo std_table("ampjuke_content_table","ampjuke_content");
} else {
	echo std_table("ampjuke_content_table",$welcome_table);
} 
if ($what=="welcome") {
	$tmpwhat=$what;
	$tmpcount=$count;
	$count=$_SESSION['count'];
	$what="track";
}

require_once("tbl_header.php");
if ($_SESSION['show_ids']=="1") {
	tbl_header($what,xlate("ID"),"left","track.id",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
}	
tbl_header($what,xlate("Title"),"left","track.name",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);

// 0.6.6: Moved here - was 1st before...
tbl_header($what,$d_performer,"left","performer.pname",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);

tbl_header($what,$d_year,"left","track.year",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);

if ($_SESSION['disp_duration']=="1") {
	tbl_header($what,xlate("Duration"),"right","track.duration",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
}

if ($_SESSION['disp_last_played']=="1") {
	tbl_header($what,xlate("Last played"),"right","track.last_played",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
}

if ($_SESSION['disp_times_played']=="1") {
	tbl_header($what,xlate("Played"),"right","track.times_played",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
}

/* 0.8.4: "removed"...
if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
|| ($_SESSION['ask4favoritelist']=="1") ) {
	echo '<th class="tbl_header"> </th>';
}

if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
	echo '<th class="tbl_header"> </th>';
}

if ($_SESSION['disp_lyrics']=="1") {
	echo '<th class="tbl_header"> </th>';
}
... in stead use: */
echo '<th class="tbl_header"> </th>';

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	
	if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1')) { // 0.8.5
		echo '<td class="content">'.add_edit_link('track',$row['id'],'').' '.add_edit_link_tags($row['id']).'</td>';
	}

	echo '<td class="content">'.add_play_link("play",$row['id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php

// 0.6.6: Moved here - was 1st before...
	$perf=get_performer_name($row['performer_id']);
	echo '<td class="content">'.add_performer_link($perf,$row['performer_id'],$_SESSION['disp_small_images']).'</td>';

	echo add_year_link($row['year'],$row['year']);

	display_duration($row['duration']);

	display_last_played($row['last_played']);

	display_times_played($row['times_played']);

	echo '<td class="content" align="right">'; // 0.8.4
	echo add_add2fav_link("track",$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced

	add_download_link("track",'',$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon.. introduced

	add_lyrics_link($row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide... introduced

	print "</td></tr> \n";
}

echo '</table>';	


if ($what=="search") {
	$within="tracks";
	$sorttbl="track";
}	

if (!isset($tmpwhat)) {
	include("page_numbers.php");
} else {
	$what=$tmpwhat;
}

$start=$tmpstart;
$pagesel=$tmpsel;
?>
