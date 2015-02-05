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

$qry="SELECT track.id, track.name, track.performer_id, track.duration, track.year, ";
$qry.="track.last_played, track.times_played, ";
$qry.="track.path, performer.pid, performer.pname ";
$qry.="FROM track, performer ";
$qry.="WHERE track.performer_id=performer.pid ";
$qry.="AND track.year='".$special."'";

if ($order_by!='') {
	$qry.=" ORDER BY $order_by $dir ";
}		

$result=execute_sql($qry,0,1000000,$num_rows); // 0.7.3
$result=execute_sql($qry,$start,$count,$n_rows);
$limit=$special;
echo headline($what,$special,$limit.'</i> <br>'.xlate("Matches").':<i>'.$num_rows.'</i>'); 

// make special options: play/enqueue all from current year.
$text=xlate($playtext.' all tracks from').' <b>'.$special.'</b>';
print "\n\n\n <!-- HEADLINE ENDS, ACTIONS: --> \n\n\n";
echo std_table("ampjuke_actions_table","");
echo '<tr><td>';
echo '<a href="play_action.php?act=playall&what=yearid&id='.$special;
// 0.5.0: included this:
echo '&order_by='.$order_by.'&dir='.$dir.'">';

// 0.7.0: Show "correct" icon:
if ($_SESSION['enqueue']=="0") {
	echo get_icon($_SESSION['icon_dir'],'play','');
	if ($_SESSION['hide_icon_text']<>'1') { // 0.8.4
		echo ' '.$text.'</a> ';
	} else {
		echo '</a>';
	}
} else {
	echo get_icon($_SESSION['icon_dir'],'queue_add','').' '.$text.'</a> ';
}	
	

// 0.5.5:
if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) || 
($_SESSION['ask4favoritelist']=="1")) {
	if ($_SESSION['ask4favoritelist']=="1") {
		// echo get_icon($_SESSION['icon_dir'],'favorite_add',''); // 0.8.4: "removed"
		echo add2fav_picker('','?what=yearid&id='.$special,$_SESSION['hide_icon_text']); // 0.8.4
	} else {
		echo add2fav_link(xlate('Add all tracks to favorite list').' <b>'.$_SESSION['favoritelistname'].'</b>','?what=yearid&id='.$special,$_SESSION['hide_icon_text']); // 0.8.4
	}		
}	

// 0.3.6: download ?
if ($_SESSION['can_download']=="1") {
	echo disp_download("year",$special,$special,'1',$_SESSION['hide_icon_text']); // 0.8.4
}

print "</td></tr></table> \n\n\n <!-- ACTIONS ENDS, CONTENT: --> \n\n\n <tr><td>";


if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }
echo std_table("ampjuke_content_table","ampjuke_content");

require("tbl_header.php");
if ($_SESSION['show_ids']=="1") {
	tbl_header($what,xlate("ID"),"left","track.id",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel.'&special='.$special);
}	

tbl_header("yearid",xlate("Performer"),"left","performer.pname",$order_by,$dir,$newdir,
$count,$special.'&special='.$special);
tbl_header("yearid",xlate("Title"),"left","track.name",$order_by,$dir,$newdir,$count,'&special='.$special);
tbl_header("yearid",xlate("Duration"),"left","track.duration",$order_by,$dir,$newdir,$count,'&special='.$special);

if ($_SESSION['disp_last_played']=="1") {
	tbl_header("yearid",xlate("Last played"),"right","track.last_played",$order_by,$dir,
	$newdir,$count,'&special='.$special);
}


if ($_SESSION['disp_times_played']=="1") {
	tbl_header("yearid",xlate("Played"),"right","track.times_played",$order_by,$dir,
	$newdir,$count,'&special='.$special);
}
/*
if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
|| ($_SESSION['ask4favoritelist']=="1") ) {
	echo '<th class="tbl_header"> </th>';
}

// 0.3.6: Can we download ?
if ($_SESSION['can_download']=="1") {
	echo '<th> </th>';
}

//0.3.8: Disp. lyrics ?
if ($_SESSION['disp_lyrics']=="1") {
	echo '<th> </th>';
}	
...and replaced by: */
echo '<th class="tbl_header"> </th>'; // <th class="tbl_header"> </th>';


while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	
	if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1')) { // 0.8.5
		echo '<td class="content">'.add_edit_link_tags($row['id']).' '.add_edit_link("track",$row['id'],'').'</td>'; 
	}

	$perf=get_performer_name($row['performer_id']);
	echo '<td class="content">'.add_performer_link($perf,$row['performer_id'],$_SESSION['disp_small_images']).'</td>';

	echo '<td class="content">'.add_play_link("play",$row['id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php

	display_duration($row['duration']);

	display_last_played($row['last_played']);

	display_times_played($row['times_played']);

	echo '<td class="content" align="right">'; // 0.8.4
	
	echo add_add2fav_link("track",$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced

	add_download_link("track",'',$row['id'],$_SESSION['hide_icon_text']); // 0.8.4

	add_lyrics_link($row['id'],$_SESSION['hide_icon_text']); // 0.8.4
	
	print "</td></tr> \n";
}

echo '</table>';	
include("page_numbers.php");
?>
