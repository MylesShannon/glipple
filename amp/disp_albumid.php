<?php
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}

require_once("sql.php");
require_once("set_td_colors.php");
require_once("disp.php");

$special=only_digits($special);

$qry='SELECT track.id, track.performer_id, track.album_id, track.track_no, track.name, ';
$qry.='track.duration, track.year, track.path, track.last_played, track.times_played, ';
$qry.='performer.pid, performer.pname, album.aid FROM album ';
$qry.='LEFT JOIN track ON track.album_id = album.aid ';
$qry.='LEFT JOIN performer ON track.performer_id = performer.pid ';
$qry.="WHERE album.aid='$special' ";


if ($order_by!='') {
	$qry.=" ORDER BY $order_by $dir ";
}		
$result=execute_sql($qry,0,1000000,$n_rows); // 0.8.4: By replacing $num_rows with $n_nows and commenting out
// next line, it appears that *all* tracks on any given album are listed on *one* screen (i.e. no page_numbers)
//$result=execute_sql($qry,$start,$count,$n_rows);

$qry="SELECT * FROM album WHERE album.aid=".$special;
$header_result=execute_sql($qry,0,1,$nr);
$header_row=mysql_fetch_array($header_result);
echo headline($what,$title,'');

print "\n\n\n <!-- ACTIONS TABLE START --> \n\n\n";
echo std_table("ampjuke_actions_table","");
echo '<tr><td>';
echo add_play_enqueue_link($playtext,'albumid',$special,$title,$order_by,$dir,'1',$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced

if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) || 
($_SESSION['ask4favoritelist']=="1")) {
	if ($_SESSION['ask4favoritelist']=="1") {
		//echo ' '.get_icon($_SESSION['icon_dir'],'favorite_add',''); // 0.8.4: "removed"
		echo add2fav_picker('','?what=albumid&id='.$special,$_SESSION['hide_icon_text']); // 0.8.4
	} else {			
	echo add2fav_link(xlate('Add album to favorite list').' <b>'.$_SESSION['favoritelistname'].'</b>',
	'?what=albumid&id='.$special);
	}		
}	

if ($_SESSION['can_download']=="1") {
	echo disp_download('album',$title,$special,'1',$_SESSION['hide_icon_text']); // 0.8.4
}	

echo '</td></tr></table><tr><td>';
print "\n\n\n <!-- ACTIONS TABLE END --> \n\n\n";

if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }

print "\n\n\n <!-- CONTENT TABLE START --> \n\n\n";
echo std_table("ampjuke_content_table","ampjuke_content");
require("tbl_header.php");
tbl_header("albumid","#","left","track.track_no",$order_by,$dir,$newdir,$count,'&special='.$special);
tbl_header("albumid",$d_performer,"left","performer.pname",$order_by,$dir,$newdir,$count,'&special='.$special);
tbl_header("albumid",xlate("Title"),"left","track.name",$order_by,$dir,$newdir,$count,'&special='.$special);

tbl_header("albumid",xlate("Year"),"left","track.year",$order_by,$dir,$newdir,$count,'&special='.$special);

if ($_SESSION['disp_duration']=="1") {
	tbl_header("albumid",xlate("Duration"),"right","track.duration",$order_by,$dir,$newdir,$count,'&special='.$special);
}

if ($_SESSION['disp_last_played']=="1") {
	tbl_header("albumid",xlate("Last played"),"right","track.last_played",$order_by,$dir,$newdir,$count,'&special='.$special);
}

if ($_SESSION['disp_times_played']=="1") {
	tbl_header("albumid",xlate("Played"),"right","track.times_played",$order_by,$dir,$newdir,$count,'&special='.$special);
}

/* 0.8.4: "removed":
if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
|| ($_SESSION['ask4favoritelist']=="1") ) {
	echo '<th class="tbl_header"> </th>';
}

if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) { 
	echo '<th> </th>';
}

if ($_SESSION['disp_lyrics']=="1") {
	echo '<th> </th>';
}
...replaced by: */
echo '<th class="tbl_header"> </th>';	

$last_performer="";
$last_performer_ok=1;
$total_playtime=0;
$total_tracks=0;
$total_playcount=0;


while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);

	if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1')) { // 0.8.5
		echo '<td class="content">'.add_edit_link_tags($row['id']).' '.$row['track_no'].'</td>';
	} else {
		echo '<td class="content">'.$row['track_no'].'</td>';
	}

	get_performer_name_track($row['performer_id'],$special,$perf_name,$perf_id);
	if (($perf_name!=$last_performer) && ($last_performer!="")) {
		$last_performer_ok=0;
	} else {
		if ($last_performer=="") {
			$last_performer=$perf_name;
		}
	}						

	// 0.7.3: disp_small_images introduced:
	echo '<td class="content">'.add_performer_link($perf_name,$perf_id,$_SESSION['disp_small_images']).'</td>';

	echo '<td class="content">'.add_play_link("play",$row['id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php

	echo add_year_link($row['year'],$row['year']);

	display_duration($row['duration']);

	display_last_played($row['last_played']);

	display_times_played($row['times_played']);

	echo '<td class="content" align="right">';
	echo add_add2fav_link("track",$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced

	add_download_link("track",'',$row['id'],$_SESSION['hide_icon_text']); // 0.8.4

	add_lyrics_link($row['id'],$_SESSION['hide_icon_text']); // 0.8.4

	print "</td></tr> \n";

	if ($_SESSION['disp_totals']=="1") {
		$total_tracks++;
		$total_playcount=$total_playcount+$row['times_played'];
		// 0.8.4: split() replaced by explode():
		$item=explode(":",$row['duration']);
		$s=$item[1] + ($item[0]*60);
		$total_playtime=$total_playtime+$s;	
	}
}

// Display totals ?
if ($_SESSION['disp_totals']=="1") {
    echo '<tr><td class="totals">';
    echo $total_tracks.' '.strtolower(xlate("Tracks"));
    echo '</td>';
    echo '<td colspan="3" class="totals">&nbsp</td>'; 
    $duration=my_duration($total_playtime);
    echo '<td align="right" class="totals">'.$duration.'</td>';

	if ($_SESSION['disp_last_played']=="1") {
	 	echo '<td class="totals"> </td>';
	 }	

    if ($_SESSION['disp_times_played']=="1") {
        echo '<td align="right" class="totals">'.$total_playcount.'</td>';
    } 
}

/* 0.8.4: Removed:
if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
|| ($_SESSION['ask4favoritelist']=="1") ) {
	echo '<td class="totals"> </td>';
}

if ((($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1"))) {
	echo '<td class="totals"> </td>';
}

if ($_SESSION['disp_lyrics']=="1") {
	echo '<td class="totals"> </td>';
}	
...replaced by: */
echo '<td class="totals"> </td>';

echo '</table>';	
include("page_numbers.php");

// Album art:
// 0.8.0: Do *NOT* use Amazon anymore:
	require_once('./lastfm_lib.php'); // 0.8.0: Yeah.
//	Use last.fm, if its enabled:
if ((isset($lastfm_download_covers)) && ($lastfm_download_covers=='1')) {
	echo '<table class="cover_table"><tr><td align="left">';
	$cover=lastfm_get_cover($header_row);
	echo '<tr><td align="center" valign="top">'; // 0.8.4
	echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'"><img src="'.$cover.'" border="0" class="tooltip" title="'; // 0.8.5
	echo get_performer_name($header_row['aperformer_id']).' - '.$header_row['aname'];
	echo '"></p>';
	$ampjuke_animated_objects++; // 0.8.5
	?>
	<script type="text/javascript">addReflections();</script>
	<?php
	echo '</td>';
	
// 0.7.7: Get album-info.:
	if (!isset($full_bio)) {
		$full_bio=0;
	} else {
		$full_bio=1;
	}	
	if (!isset($refresh_bio)) {
		$refresh_bio=0;
	} else {
		$refresh_bio=1;
	}
	$bio=lastfm_get_album_bio($header_row['aid'],get_performer_name($header_row['aperformer_id']),$header_row['aname'],
	$full_bio,$refresh_bio);

	if ($full_bio==0) { 
		if ($bio<>'n/a') {
			echo '<td align="left" valign="top" class="content">';		
			echo str_replace('quot;','"',$bio);
			echo '<br><a href="'.$_SERVER["REQUEST_URI"].'&full_bio=1'.'">';
			echo get_icon($_SESSION['icon_dir'],'more_information',xlate('More information about'));
			echo ' <b>'.$header_row['aname'].'</b></a></td>';
		}
	} else {
		if ($bio<>'n/a') {
			echo '<td align="left" valign="top" class="content">';		
			echo str_replace('quot;','"',$bio).'</td>';	
		}	
	}	
	echo '</tr>';
	
	// Offer to refresh bio + edit bio.:
	if ($_SESSION['admin']=='1') {
		echo '<tr><td colspan="5"><a href="'.$_SERVER["REQUEST_URI"].'&refresh_bio=1">';
		echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">Refresh album bio</a>';
		// 0.7.9: This is new:
		echo ' <a href="./index.php?what=edit&edit=albumbio&id='.$header_row['aid'].'&full_bio='.$full_bio.'">';
		echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">Edit album bio</a>';
		// for admin's: offer options to clear/"blank" the cover displayed:
		// 0.8.1: GONE - see below - a new type of functionality is offered:
		/*
		echo ' <a href="delete.php?what=cover&id='.$header_row['aid'].'">'; // 0.8.0: header_row[] used.
        echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">';
		echo 'Delete this cover and look up a new cover</a>';
		*/
		// 0.8.0: Hell...offer to replace:
		// 0.8.1: GONE - see below - a new type of functionality is offered:
		//	echo ' <a href="delete.php?what=cover&id='.$header_row['aid'].'&replace=true">'; // 0.8.0:header_row
		//	echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">Delete this cover and ';
		//	echo 'use a blank cover instead</a>';

		// 0.8.1: *New* (replacing options above): Offer option to LOOKUP an image:
		echo ' <a href="index.php?what=images&type=album&special='.$header_row['aid'].'">';
		echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">Lookup a new cover</a>';

		// Only display, if we are allowed to upload and we want to display the upload option:
		if (($_SESSION['can_upload']=="1") && ($allow_upload=="1") && ($_SESSION['disp_upload']=="1")) {
			echo ' <a href="index.php?what=upload&type=cover&fn='.$header_row['aid'].'.jpg">'; 
			echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">';
			echo 'Upload a new cover</a>';
		}
		echo '</td></tr>';
    }
    echo '</table>';
}
?>
