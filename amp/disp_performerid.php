<?php
// 0.7.9: Change this in order to change the actual appearance of album covers displayed:
$col_count=5; // Number of covers in each row. Note the number of items/page is user-controlled.
$cover_param='border="0" width="126px" height="126px"'; // Reduce the size of covers to this value - 126px is the "default" returned from last.fm
$col_width=round(100/$col_count); // Calculated width of each column (simple)


// 0.7.7: (Almost completely) REWRITTEN ...

if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    die('Session timed out. <a href="login.php">Login again</a>.');
}	

require_once("sql.php");
require_once("set_td_colors.php");
require_once("disp.php");
require_once("lastfm_lib.php");

$special=only_digits($special); // 0.7.6

// get+display headline: performer's name:
$qry="SELECT * FROM performer WHERE performer.pid=".$special;
$header_result=execute_sql($qry,0,1,$nr);
$header_row=mysql_fetch_array($header_result);
if ($what!="welcome") { // 0.6.3: Clever ? Smart ? Uh...
	echo headline($what,$header_row['pname'],''); 
}


print "\n\n\n <!-- ACTIONS, TOP --> \n\n\n";
echo std_table("ampjuke_actions_table","");
echo '<tr><td>';

if (!isset($order_by) || ($order_by=="")) { // 0.5.4: Added ||-condition
	$order_by='track.name';
	$dir='ASC';
}	

if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) || 
($_SESSION['ask4favoritelist']=="1")) {
	if ($_SESSION['ask4favoritelist']=="1") {
//		echo ' '.get_icon($_SESSION['icon_dir'],'favorite_add','');
		echo add2fav_picker('','?what=performerid&id='.$special,$_SESSION['hide_icon_text']); // 0.8.4: hide_icon.. introduced
	} else {			
		echo add2fav_link(xlate('Add all tracks to favorite list').' <b>'.$_SESSION['favoritelistname'].'</b>',
		'?what=performerid&id='.$special,$_SESSION['hide_icon_text']); // 0.8.4
	}		
}	

// 0.3.6: download ?
if ($_SESSION['can_download']) {
	echo disp_download("performer",$header_row['pname'],$special,'1',$_SESSION['hide_icon_text']); // 0.8.4
}

// 0.8.4: Moved here + hide_icon... introduced
echo add_play_enqueue_link($playtext,'performerid',$special,$header_row['pname'],$order_by,$dir,'1',$_SESSION['hide_icon_text']); 


if ($_SESSION['hide_icon_text']<>'1') { // 0.8.4
	echo xlate('Expand all').':';
}
?>
	<img src="./ampjukeicons/expandall.gif" id="exp" onclick="cfg_expand_collapse_all('1')" class="tooltip" title="<?php echo xlate('Expand all');?>">
	<?php 
	if ($_SESSION['hide_icon_text']<>'1') { // 0.8.4
		echo xlate('Collapse all').':';
	}
	?>
	<img src="./ampjukeicons/collapseall.gif" id="exp" onclick="cfg_expand_collapse_all('0')" class="tooltip" title="<?php echo xlate('Collapse all');?>">
<?php	


echo '</td></tr></table>';	
print "\n\n\n <!-- ACTIONS, TOP, ENDS --> \n\n\n";

//
// ************************************* 
//				*** PART 1 : BIO ***
// ************************************* 
//
// 0.7.7: If set, get+display bio-summary
if ($perf_info==1) { // yes:
	// Expand/collapse: WARNING: ugly code ahead....
	echo '<p class="note" align="left"><b>';
	if (!isset($full_bio)) { ?>
		<img src="./ampjukeicons/expand.gif" id="gif1" onclick="handleClick('to_col1','gif1')">
	<?php } else { ?>
		<img src="./ampjukeicons/collapse.gif" id="gif1" onclick="handleClick('to_col1','gif1')">	
	<?php } 
	echo xlate('More information about').' <b>'.$header_row['pname'].'</b>';
	if (!isset($full_bio)) { ?>
		<div id="to_col1" style="display:none;">
	<?php } else { ?>
		<div id="to_col1" style="display:block;">
<?php
	}
	echo std_table("ampjuke_content_table","ampjuke_content");
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
	$bio=lastfm_get_bio($header_row['pid'],urlencode($header_row['pname']),$full_bio,$refresh_bio);
	
	echo '<tr><td valign="top">';
	if (file_exists('./lastfm/'.$header_row['pid'].'.jpg')) {
		echo '<img src="./lastfm/'.$header_row['pid'].'.jpg" border="0" class="tooltip" title="'.$header_row['pname'].'">';
	}	
	// 0.7.9: Offer to upload a new picture, if we are entitled to do so:
	if (($_SESSION['can_upload']=="1") && ($allow_upload=="1") && ($_SESSION['disp_upload']=="1")) {
		echo '<br><a href="./index.php?what=upload&type=performerid&fn='.$header_row['pid'].'">';
		echo '<img src="./ampjukeicons/mnu_arr.gif" border="0" class="tooltip" title="Upload new...">Upload new picture...</a>';
	}	

    // 0.8.1: New: Offer option to LOOKUP an image:
    // 0.8.6: changed so it's only possible to lookup a new performer-image if we're admin: (Thanks, Peter S.)
    if ($_SESSION['admin']=='1') {
		echo '<br><a href="index.php?what=images&type=performer&special='.$header_row['pid'].'">';
		echo '<img src="./ampjukeicons/mnu_arr.gif" border="0" class="tooltip" title="Lookup new...">Lookup a new image...</a>';
    }
	
	
	echo '<td valign="top">';
	if ($full_bio==0) { 
		echo str_replace('quot;','"',$bio);
		if ($bio<>'n/a') {
			echo '<br><a href="'.$_SERVER["REQUEST_URI"].'&full_bio=1'.'">';
			echo get_icon($_SESSION['icon_dir'],'more_information',xlate('More information about'));
			echo ' <b>'.$header_row['pname'].'</b></a>';
		}
		// Offer to refresh:
		if ($_SESSION['admin']=='1') {
			echo ' <a href="'.$_SERVER["REQUEST_URI"].'&refresh_bio=1">';
			echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">Refresh bio</a>';
		}
	} else {
		echo str_replace('quot;','"',$bio);
	}	
	// 0.7.9: Offer to edit:
	// 0.8.0: ...but only if we're an admin.:
	if ($_SESSION['admin']=='1') {
		echo ' <a href="./index.php?what=edit&edit=performerbio&id='.$header_row['pid'].'&full_bio='.$full_bio.'">';
		echo '<img src="./ampjukeicons/mnu_arr.gif" border="0">Edit bio</a>';			
	}
	echo '</td></tr></table></div>';
}

//
// ************************************* 
//				*** PART 2 : ALBUM LIST ***
// ************************************* 
//
// 1. Get # of albums:
$qry="SELECT * FROM album WHERE aperformer_id=".$special;
$qry.=" ORDER BY aname";
$album_result=execute_sql($qry,0,1000000,$album_rows);
// ...add # of albums this performer appears on - which is a bit tricky, BECAUSE...
// ...get total # of TRACKS...
$qry="SELECT album_id,performer_id FROM track WHERE performer_id=".$special;
$result=execute_sql($qry,0,1000000,$nr);
// ...get+count+build array of the albums from this result where aperformer_id=1 ("various"):
$max_count=0;
while ($row=mysql_fetch_array($result)) {
	$q2="SELECT * FROM album WHERE aid='".$row['album_id']."' AND aperformer_id='1'";
	$r2=execute_sql($q2,0,1,$nr);
	if ($nr>0) { // found one, add it:
		$appear_on_album[$max_count]=$row['album_id'];
		$max_count++;
	}	
}
// ...we'll get back to this later on, when we need these results + this array.

echo '<p class="note" align="left"><b>';
// Expand/collapse:
if (($album_rows>0) || ($max_count>0)) {
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/collapse.gif" id="gif2" onclick="handleClick('to_col2','gif2')">
<?php echo xlate('Albums').':'.$album_rows.' '.xlate('Appears on').':'.$max_count.'</p>'; ?>
<div id="to_col2" style="display:block;">
<?php
$no_albums=0;
} else {
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif2" onclick="handleClick('to_col2','gif2')">
<?php echo xlate('Albums').':'.$album_rows.' '.xlate('Appears on').':'.$max_count.'</p>'; ?>
<div id="to_col2" style="display:none;">
<?php
$no_albums=1;
}
echo std_table("ampjuke_content_table","ampjuke_content2");
$table2=1;

if ($_SESSION['browse_albums_by_covers']=='0') { // 0.7.9: Do as usual:
	echo '<tr><td><b>'.xlate('Album').'</b></td><td align="right"><b>'.xlate('Tracks').'</b></td>';
	echo '<td align="right"><b>'.xlate('Duration').'</b></td>';
	echo '<td> </td></tr>';
}	
$c_count=1; // 0.7.9: Used later
// Always show "own" albums first:
while ($row=mysql_fetch_array($album_result)) {
	if ($_SESSION['browse_albums_by_covers']=='0') { // 0.7.9: Do as usual:
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);

		echo add_album_link($row['aname'],$row['aid'],$_SESSION['disp_small_images']); 

		if ($_SESSION['disp_totals']=="1") {
        	$total_playtime=0;
	        $total_tracks=0;
    	    $qry2="SELECT id,album_id,duration FROM track WHERE album_id=".$row['aid'];
	        $result2=execute_sql($qry2,0,-1,$n);
    	    while ($row2=mysql_fetch_array($result2)) {
        	    $total_tracks++;
				// 0.8.4: split() replaced by explode():
          		$item=explode(":",$row2['duration']);
	          	$s=$item[1] + ($item[0]*60);
    	       	$total_playtime=$total_playtime+$s;
        	}
	        echo '<td class="content" align="right">'.$total_tracks.'</td>'; 
			echo '<td class="content" align="right">'.my_duration($total_playtime).'</td>'; 
	   	}
		echo '<td class="content" align="right">'; // 0.8.4
		echo add_add2fav_link("albumid",$row['aid'],$_SESSION['hide_icon_text']);  //0.8.4
		add_download_link("album",'',$row['aid'],$_SESSION['hide_icon_text']); // 0.8.4
	   	echo add_play_enqueue_link($playtext,'albumid',$row['aid'],'...','track.track_no','ASC','',$_SESSION['hide_icon_text']);	// 0.8.4
		
	   	echo '</td></tr>';
	} else { // 0.7.9: Display album covers:
		// Find out if it's time to switch to a new row:
		$c_count--;
		if ($c_count==0) {
			print "</tr><tr> \n\n";
			$c_count=$col_count;
		}			
		echo '<td width="'.$col_width.'%" valign="top" align="center">';
		// First, the album image:
		echo '<a href="index.php?what=albumid&start=0&count='.$_SESSION['count'];
	    echo '&special='.$row['aid'].'&order_by=track.track_no"';
		echo ' title="'.get_album_tracklist($row['aid']).'" class="tooltip">'; // 0.8.7: added tooltip + get_album_tracklist
		if (file_exists('./covers/'.$row['aid'].'.jpg')) { // Show the actual image:
			echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'"><img src="./covers/'.$row['aid'].'.jpg" '.$cover_param.'>'; // 0.8.5
			$ampjuke_animated_objects++; // 0.8.5
		} else { // Show the default image:
			echo '<p class="ampjuke_album'.$ampjuke_animated_objects.'"><img src="./covers/_blank.jpg" '.$cover_param.'>'; // 0.8.5	
			$ampjuke_animated_objects++; // 0.8.5			
		}
		echo '</a><br>';
		// Second, a link to play/queue:
		echo add_play_enqueue_link($playtext,'albumid',$row['aid'],$row['aname'],'track.track_no','ASC','1',$_SESSION['hide_icon_text']); // 0.8.4
		echo '<br>';
		// Finally, an option to add to favorite:
		$s=add_add2fav_link("albumid",$row['aid'],$_SESSION['hide_icon_text']);
		$s=str_replace('<td class="content" align="right">','',$s); // Get rid the <td>-formatted stuff
		$s=str_replace('</td>','',$s);
		echo $s.'</p>';	
	}
}	
// Second show albums w. "appears on" (back again, from above):
$c=0;
if ($max_count>0) { // We found something earlier:
	while ($c<$max_count) {
		$qry="SELECT * FROM album WHERE aid=".$appear_on_album[$c]." AND aperformer_id!=".$special;
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		if ($nr==1) {
			if ($_SESSION['browse_albums_by_covers']=='0') { // 0.7.9: Do as usual:
				fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);

				echo add_album_link('<i>('.xlate("Appears on").')</i> '.$row['aname'],$row['aid'],$_SESSION['disp_small_images']);
        	    // Display totals ?
            	if ($_SESSION['disp_totals']=="1") {
                	$total_tracks=0;
	                $total_playtime=0;
    	            $qry2="SELECT id,album_id,duration FROM track WHERE album_id=".$row['aid'];
        	        $result2=execute_sql($qry2,0,-1,$n);
            	    while ($row2=mysql_fetch_array($result2)) {
                	    $total_tracks++;
						// 0.8.4: split() replaced by explode():
                 		$item=explode(":",$row2['duration']);
	                   	$s=$item[1] + ($item[0]*60);
    	               	$total_playtime=$total_playtime+$s;
        	        }
            	    echo '<td class="content" align="right">'.$total_tracks.'</td>';
                	echo '<td class="content" align="right">'.my_duration($total_playtime).'</td>';
	            }
				echo '<td class="content" align="right">'; // 0.8.4
				echo add_add2fav_link("albumid",$row['aid'],$_SESSION['hide_icon_text']); // 0.8.4
				add_download_link("album",'',$row['aid'],$_SESSION['hide_icon_text']); // 0.8.4				
        	   	echo add_play_enqueue_link($playtext,'albumid',$row['aid'],'...','track.track_no','ASC','',$_SESSION['hide_icon_text']); // 0.8.4
	            echo '</td></tr>';
			} else { // 0.7.9: Display album covers:
				// Find out if it's time to switch to a new row:
				$c_count--;
				if ($c_count==0) {
					print "</tr><tr> \n\n";
					$c_count=$col_count;
				}			
				echo '<td class="content" width="'.$col_width.'%" valign="top" align="center">';
				// First, the album image:
				echo '<a href="index.php?what=albumid&start=0&count='.$_SESSION['count'];
			    echo '&special='.$row['aid'].'&order_by=track.track_no"';
				echo ' title="'.get_album_tracklist($row['aid']).'" class="tooltip">'; // 0.8.7: Added tooltip + get_album_tracklist
				if (file_exists('./covers/'.$row['aid'].'.jpg')) { // Show the actual image:
					echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'"><img src="./covers/'.$row['aid'].'.jpg" '.$cover_param.'>'; //0.8.5
					$ampjuke_animated_objects++;
				} else { // Show the default image:
					echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'"><img src="./covers/_blank.jpg" '.$cover_param.'>'; // 0.8.5
					$ampjuke_animated_objects++;
				}
				echo '</a><br>';
				echo '<i>('.xlate('Appears on').')</i><br>';				
				// Second, a link to play/queue:
				echo add_play_enqueue_link($playtext,'albumid',$row['aid'],$row['aname'],'track.track_no','ASC','1',$_SESSION['hide_icon_text']); // 0.8.4
				echo '<br>';
				// Finally, an option to add to favorite:
				$s=add_add2fav_link("albumid",$row['aid'],$_SESSION['hide_icon_text']); // 0.8.4
				$s=str_replace('<td class="content" align="right">','',$s); // Get rid the <td>-formatted stuff
				$s=str_replace('</td>','',$s);
				echo $s.'</p>';	
	}
				 
		}
		$c++;
	}
}	
// 0.7.9: Final touch, fill out last row with empty <td>'s if we're browsing albums by covers:
if ($_SESSION['browse_albums_by_covers']=='1') { 
	while ($c_count>1) {
		echo '<td width="'.$col_width.'%"> </td>';
		$c_count--;
	}
	echo '</tr>';
}
// 0.8.0: If album_rows=0 and max_count=0 then the performer doesn't appear on any albums -> show an empty <td> (for the "mouseover" effect to work:
if (($album_rows==0) && ($max_count==0)) {
	echo '<!-- EMPTY ROW - JUST FOR "MOUSEOVER" EFFECT TO WORK -->';
	echo '<tr><td> </td></tr>';
}	
echo '</table></div>';

//
// ************************************* 
//				*** PART 3: TRACKS  ***
// ************************************* 
//
$qry="SELECT track.id, track.performer_id, track.album_id, track.track_no, ";
$qry.="track.name, track.duration, track.year, ";
$qry.="track.last_played, track.times_played, track.path, ";
$qry.="performer.pid, performer.pname ";
$qry.="FROM track, performer";
$qry.=" WHERE ((track.performer_id='".$special."'";
$qry.=" AND performer.pid='".$special."'))";
if (!isset($order_by)) {
	$order_by="track.name";
	$dir="ASC";
}
if ($order_by!="") {
	$qry.=" ORDER BY $order_by $dir ";
}
$result=execute_sql($qry,0,100000,$num_rows); // 0.7.3
$result=execute_sql($qry,$start,$count,$n_rows);	
require("tbl_header.php");
echo '<p class="note" align="left"><b>';
// Expand/collapse:
?>
<p class="note" align="left"><b>
<?php
if ($no_albums==1) {
?>
	<img src="./ampjukeicons/collapse.gif" id="gif3" onclick="handleClick('to_col3','gif3')">
<?php 
} else {	
	if (!isset($clicksort)) { 
?>
		<img src="./ampjukeicons/expand.gif" id="gif3" onclick="handleClick('to_col3','gif3')">
<?php 
	} else {
?>
		<img src="./ampjukeicons/collapse.gif" id="gif3" onclick="handleClick('to_col3','gif3')">
<?php 
	} 
} // else..
echo xlate('Tracks').':'.$num_rows.'</p>'; 

if ($no_albums==1) {
?>
	<div id="to_col3" style="display:block;">
<?php
} else {	
	if (!isset($clicksort)) { 
?>
		<div id="to_col3" style="display:none;">
<?php 
	} else { 
?>
	<div id="to_col3" style="display:block;">
<?php
	}
}	
echo std_table("ampjuke_content_table","ampjuke_content3");
$table3=1; 
$the_rest='&clicksort=1';
if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }
if ($_SESSION['show_ids']=="1") {
	tbl_header($what,xlate("ID"),"left","track.id",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&special='.$special.$the_rest);
}		
tbl_header("performerid",xlate("Title"),"left","track.name",$order_by,$dir,$newdir,
$count,"&special=".$special.$the_rest);
tbl_header("performerid",xlate("Year"),"left","track.year",$order_by,$dir,$newdir,
$count,"&special=".$special.$the_rest);

// 0.3.5: show duration ?
if ($_SESSION['disp_duration']=="1") {
	tbl_header("performerid",xlate("Duration"),"right","track.duration",
   	$order_by,$dir,$newdir,$count,"&special=".$special.$the_rest); // 0.3.5: changed to "right" from "left"
}

if ($_SESSION['disp_last_played']=="1") {
	tbl_header("performerid",xlate("Last played"),"right","track.last_played",$order_by,$dir,
	$newdir,$count,'&special='.$special.$the_rest);
}
if ($_SESSION['disp_times_played']=="1") {
	tbl_header("performerid",xlate("Played"),"right","track.times_played",$order_by,$dir,
	$newdir,$count,'&special='.$special.$the_rest);
}

/* 0.8.4: Hey, old friend! You're history now...
if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
|| ($_SESSION['ask4favoritelist']=="1") ) {
	echo '<th class="tbl_header"> </th>';
}
if ((($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1"))) {
	echo '<th class="tbl_header"> </th>';
}
//0.3.8: display lyrics:
if ($_SESSION['disp_lyrics']=="1") {
	echo '<th class="tbl_header"> </th>';
}		
...replaced witth: */
echo '<th class="tbl_header"> </th>';

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1')) { // 0.8.5
		echo '<td class="content">'.add_edit_link_tags($row['id']).' '.add_edit_link('track',$row['id']).'</td>'; 
	}
	echo '<td class="content">'.add_play_link("play",$row['id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php
	echo add_year_link($row['year'],$row['year']);
	display_duration($row['duration']);
	display_last_played($row['last_played']);
	display_times_played($row['times_played']);
	echo '<td class="content" align="right">'; // 0.8.4
	echo add_add2fav_link("track",$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: $_SESSION... introduced
	add_download_link("track",'',$row['id'],$_SESSION['hide_icon_text']); // 0.8.4
	add_lyrics_link($row['id'],$_SESSION['hide_icon_text']); // 0.8.4
	print "</td></tr> \n";
}
echo '</table></div>';

//
// ************************************* 
//				*** PART 4: RELATED PERFORMERS ***
// ************************************* 
//
if (($lastfm_allow_related==1) && ($_SESSION['disp_related_performers']=="1")) {
	error_reporting(0);
	// First of all, no matter what, do some housekeeping (get rid of locally cached last.fm that's too old):
 	if ((isset($lastfm_cache_days)) && (is_numeric($lastfm_cache_days))) {
 		if ($lastfm_cache_days>0) {
		 	dskspace('./lastfm/',$lastfm_cache_days*24); 
		} else {
		 	dskspace('./lastfm/',1); 
		}
	}		

	$total_related_performers=lastfm_get_number_of_related_performers($header_row['pid'],urlencode($header_row['pname']),
	$lastfm_min_related_match,$lastfm_max_related_artists);

	if ((isset($refresh_related)) && ($refresh_related==1)) { // Ask last.fm (req. by user)
		$total_related_performers=0;
	}	
	
	if ($total_related_performers==0) { // ask last.fm:
		$total_related_performers=lastfm_update_related_performers($header_row['pid'],urlencode($header_row['pname']),
		$lastfm_min_related_match,$lastfm_max_related_artists);
		$total_related_performers=lastfm_get_number_of_related_performers($header_row['pid'],urlencode($header_row['pname']),
		$lastfm_min_related_match,$lastfm_max_related_artists);
	}	

		
	
	echo '<p class="note" align="left"><b>';
	// Expand/collapse:
	?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/collapse.gif" id="gif4" onclick="handleClick('to_col4','gif4')">
	<?php
	// 0.7.9: Whoops - display the correct max. # of rel. performers, no matter if the actual number is higher:
	if ($total_related_performers>$lastfm_max_related_artists) {
		$total_related_performers=$lastfm_max_related_artists;
	}	
	echo xlate('Related performers').':'.$total_related_performers.'</p>'; ?>
	<div id="to_col4" style="display:block;">
	<?php
	echo std_table("ampjuke_content_table","ampjuke_content4");
	$table4=1; 
	// So - lets get it on:
	$n=0;
	$c_count=$col_count; // 0.7.9: See below
	if ($total_related_performers>0) {
		$xml=retrieve_xml('./lastfm/'.$header_row['pid'].'.xml',$n,$lastfm_max_related_artists);
//		echo '<tr><td width="10%"> </td></tr>'; // 0.8.4: 
		while ($n<$lastfm_max_related_artists) { 
			if (!isset($xml->similarartists->artist[$n]->image[0])) {
				$n=$lastfm_max_related_artists+1;
			}	
			$rel_filename=$xml->similarartists->artist[$n]->image[0];
			$pid=get_performer_id_by_name(mysql_escape_string($xml->similarartists->artist[$n]->name[0]));
			if ($pid<>1) {
				// 0.7.8: Show some tracks/samples from a related performer:
				// 0.7.9: All of this is now _one_ block, since there are _two_ different ways to display related performers (with/without sample tracks):
				if ((isset($lastfm_disp_sample_tracks)) && ($lastfm_disp_sample_tracks=='1')) {
					fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
					echo '<td class="content">'.add_performer_link('<img src="'.$rel_filename.'" border="0">',$pid).'</td>';
					echo '<td valign="top" class="content">'.add_performer_link(get_performer_name($pid),$pid);
			
					echo ' (';
					$qry="SELECT track.id, track.performer_id, track.album_id, track.track_no, ";
					$qry.="track.name, track.duration, track.year, ";
					$qry.="track.last_played, track.times_played, track.path, ";
					$qry.="performer.pid, performer.pname ";
					$qry.="FROM track, performer";
					$qry.=" WHERE ((track.performer_id='".$pid."'";
					$qry.=" AND performer.pid='".$pid."'))";
					switch ($lastfm_disp_sample_priority) {
						case "nothing": $qry.=" ORDER BY rand()"; break;
						case "most_played": $qry.=" ORDER BY times_played DESC"; break;
						case "least_played": $qry.=" ORDER BY times_played ASC"; break;
						case "oldest": $qry.=" ORDER BY last_played DESC"; break;
						case "newest": $qry.=" ORDER BY last_played ASC"; break;
					}					
					// $result=execute_sql($qry,0,$lastfm_disp_sample_number+1,$num_rows);
					// 0.7.9: Changed to:
					$result=execute_sql($qry,0,1000000,$num_rows);
					echo $num_rows.' ';
					if ($num_rows>1) { echo xlate('Tracks'); } else { echo xlate('Track'); }
					echo ': ';
					$x=1;
					if ($num_rows>0) {
						while (($x<=$lastfm_disp_sample_number) && ($row=mysql_fetch_array($result))) {
							if ($x<=$lastfm_disp_sample_number) {
								echo '<i>'.add_play_link("play",$row['id'],$row['name']).'</i>';
								if (($x<>$lastfm_disp_sample_number) && ($x<>$num_rows)) { echo '; '; }
							}	
							$x++;
						}	
						if ($num_rows>$lastfm_disp_sample_number) { echo '...'; }
					}
					echo ')';
					echo '</td></tr>';					
				} else { // 0.7.9: We're NOT displaying samples from related performers: Display related performers different:
					if ($c_count==$col_count) {
						fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
					}
					echo '<td class="content" width="'.$col_width.'%" align="center">';
					// 0.8.1: Added $cover_param (images from many sources = images of many sizes...):
					// 0.8.5: Added the animation stuff
					echo add_performer_link('<p class="ampjuke_animation_'.$ampjuke_animated_objects.'"><img src="'.$rel_filename.'" '.$cover_param.' title="'.get_performer_name($pid).'" class="tooltip">',$pid);
					$ampjuke_animated_objects++; // 0.8.5
					echo '<br>'.add_performer_link(get_performer_name($pid),$pid);	
   					$c_count--;
					if ($c_count==0) {
						$c_count=$col_count;
						echo '</tr>';
					}
				}	
			} // if pid<>1	
			$n++;
		} // while...
	} // if total_related_performers>0
	
	// 0.7.9: Final touch, fill out row with empty <td>'s, if we're not displaying a number of sample tracks from related performers:
	if ((!isset($lastfm_disp_sample_tracks)) || ($lastfm_disp_sample_tracks=='0')) {	
		while ($c_count>0) {
			echo '<td width="'.$col_width.'%"></td>';
			$c_count--;
		}
		echo '</tr>';
	}
	
	
	if ($_SESSION['admin']=="1") { 
		echo '<tr><td colspan="'.$col_count.'"><a href="'.$_SERVER["REQUEST_URI"].'&refresh_related=1"><img src="./ampjukeicons/mnu_arr.gif" border="0">'; // 0.8.4: col_count used
		echo xlate('Refresh related performer(s) from last.fm').'</a></td></tr>';
	}	
	echo '</table></div>';
}

?>
