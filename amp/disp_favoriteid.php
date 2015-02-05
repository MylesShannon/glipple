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

$qry="SELECT fav.id, fav.track_id, fav.name, fav.performer_id, ";
$qry.="fav.duration, fav.year, fav.last_played, fav.times_played, fav.user_id, fav.fav_name, ";
$qry.="performer.pid, performer.pname";
$qry.=" FROM fav, performer ";
$qry.="WHERE fav.performer_id=performer.pid AND (fav_name='".$special."'";

// 0.5.2: Check against own vs. shared lists -> permit access to both:
$therest=""; // needed below in headlines+page numbers
$uid=get_user_id($_SESSION['login']);
if (!isset($shared)) {
	$qry.=" AND user_id='".$uid."')";
} else {
 	$qry2="SELECT * FROM fav_shares WHERE fav_name='".$special."'";
 	$qry2.=" AND share_id='".$uid."'";
 	$result2=execute_sql($qry2,0,1,$x);
 	if ($x==1) {
 	 	$row2=mysql_fetch_array($result2);
 		$u=$row2['owner_id'];
 		$qry.=" AND user_id='".$u."')";
 		$therest="&shared=yes";
 	}
}	 	

	
if ($order_by=="fav.track_id" && $_SESSION['show_ids']=="0") {
	$order_by="fav.name";
	$dir="ASC";
}
if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }

$qry.=" ORDER BY $order_by $dir ";
$result=execute_sql($qry,0,10000000,$num_rows); // 0.7.3
$result=execute_sql($qry,$start,$count,$n_rows);

echo headline($what,$special,$special);
echo std_table("ampjuke_actions_table","");

echo '<tr><td>';
/* 0.8.4: Hey! Let's use what we have rather than re-inveting the wheel
echo '<a href="play_action.php?act=playall&what=favoriteid&id='.$special;
// 0.5.0: include order_by:
echo '&order_by='.$order_by.'&dir='.$dir.$therest;
echo '">';
echo get_icon($_SESSION['icon_dir'],$playtext,xlate($playtext.' all tracks from'));
echo ' <b>'.$special.'</b></a>&nbsp&nbsp';
*/
// 0.8.4:
echo add_play_enqueue_link($playtext,'favorite_list',$special,$special,$order_by,$dir,'',$_SESSION['hide_icon_text']);

echo '<a href="delete.php?what=duplicates_favorite&id='.$special.$therest; // 0.5.2: Addded therest
echo '">';
echo get_icon($_SESSION['icon_dir'],'delete','');
if ($_SESSION['hide_icon_text']<>'1') { // 0.8.4:
	echo xlate('Remove duplicate entries');
	echo ':<b>'.$special.'</b></a> '.add_faq(46); // 0.6.6: ..faq	
}

if ($_SESSION['can_download']=="1") {
	echo ' '.disp_download('favorite_list',$special,$special,'1',$_SESSION['hide_icon_text']);
}  

	
print "</table></td></tr> \n\n\n <!-- CONTENT BELOW: --> \n\n\n <tr><td>";

require("tbl_header.php");
echo std_table("ampjuke_content_table","ampjuke_content");

if ($_SESSION['show_ids']=="1") {
	tbl_header($what,xlate("ID"),"left","fav.track_id",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&special='.$special.$therest);
}
tbl_header($what,$d_performer,"left","performer.pname",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&special='.$special.$therest);

tbl_header($what,xlate("Title"),"left","fav.name",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&special='.$special.$therest);

tbl_header($what,$d_year,"left","fav.year",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&special='.$special.$therest);

if ($_SESSION['disp_duration']=="1") {
	tbl_header($what,xlate("Duration"),"right","fav.duration",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&special='.$special.$therest);
}

if ($_SESSION['disp_last_played']=="1") {
	tbl_header($what,xlate("Last played"),"right","fav.last_played",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&special='.$special.$therest);
}

if ($_SESSION['disp_times_played']=="1") {
	tbl_header($what,xlate("Played"),"right","fav.times_played",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&special='.$special.$therest);
}

/* 0.8.4:
if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
	echo '<th class="tbl_header"> </th>';
}

if ($_SESSION['disp_lyrics']=="1") {
	echo '<th class="tbl_header"> </th>';
}

echo '<th> </th>'; // 0.7.8: The delete option
...replaced with: */
echo '<th> </th>';

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	
	if ($_SESSION['show_ids']=='1') {
		echo '<td class="content">';
		echo add_edit_link_tags($row['track_id']).' ';
		echo add_edit_link("track",$row['track_id']);
		echo '</td>';
	}

	$perf=get_performer_name($row['performer_id']);
	
	// 0.7.3: disp_small_images introduced:
	echo '<td class="content">'.add_performer_link($row['pname'],$row['pid'],$_SESSION['disp_small_images']).'</td>';

	echo '<td class="content">'.add_play_link("play",$row['track_id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php

	echo add_year_link($row['year'],$row['year']);	

	display_duration($row['duration']);

	display_last_played($row['last_played']);

	display_times_played($row['times_played']);

	echo '<td class="content" align="right">';

	echo add_delete_link('favoriteid',$row['id'],$special,1,$_SESSION['hide_icon_text']); // 0.8.4

	add_download_link("track",'',$row['track_id'],$_SESSION['hide_icon_text']); // 0.8.4

	print "</td></tr> \n\n";
}

$special.=$therest;
require("page_numbers.php");
	
?>
