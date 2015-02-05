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

// 0.8.6: Yeehaaa - really simple, but re-using the code to display the queue 100%:
if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled=='1')) {
	// First+most important: Clean up (remove) old entries:
	$before=date('U') - ($jukebox_mode_selection_limit_minutes * 60);
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%'";
	$result=execute_sql($qry,0,10000,$nr);

	while ($row=mysql_fetch_array($result)) {
		$a=get_request_array($row['user_name']);
		if ((sizeof($a)>2) && ($a[3]=='1') && ($a[1]<$before)) {
			$qry2="DELETE FROM queue WHERE qid='".$row['qid']."'";
			$result2=execute_sql($qry2,0,-1,$dummy);
		}
	}
	
	// Next, get the records from the queue:
	$qry="SELECT * FROM queue WHERE user_name LIKE '+++%' AND track_id>'0'";
	$qry.=" ORDER BY qid ASC";
} else { // do as usual:	
	$qry="SELECT * FROM queue WHERE user_name='".$_SESSION['login']."' AND track_id>'0'";
	$qry.=" ORDER BY qid";
}

$result=execute_sql($qry,0,10000000,$num_rows);
$result=execute_sql($qry,$start,$count,$n_rows);

echo headline($what,'The Queue','');
echo std_table("ampjuke_actions_table","");

echo '<tr><td>';


if ($num_rows>0) { // we have something in the queue, - display the options:
	// 0.8.6: If we're in radio station mode, omit the "actions":
	if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {
		echo '<a href="play_action.php?act=playall&what=queue">';
		echo get_icon($_SESSION['icon_dir'],'play',xlate("Play all tracks from")).' ';
		echo xlate("The queue").'</a>&nbsp';
		echo '<a href="delete.php?what=queue&id=all">';
		echo get_icon($_SESSION['icon_dir'],'delete',xlate("Delete")).' ';
		echo xlate("The queue").'</a>&nbsp';
		echo '<a href="delete.php?what=duplicates_queue&id='.session_id().'">';
		echo get_icon($_SESSION['icon_dir'],'delete',xlate("Remove duplicate entries")).' ';	
		echo '</a>';
		// 0.3.6: download ?
		if ($_SESSION['can_download']=="1") {
			add_download_link("queue",xlate("The queue"),"queue");
		}
	}
} else { // we do not have anything in the queue...
	echo xlate('There are no tracks in the queue').'.';
}

print "</table></td></tr> \n\n\n <!-- ACTIONS FINISHED, CONTENT BELOW: --> \n\n\n <tr><td>";

require("tbl_header.php");
echo std_table("ampjuke_content_table","ampjuke_content");
echo '<th align="left">'.xlate("Performer").'</th>';
echo '<th align="left">'.xlate("Title").'</th>'; // 0.3.2: Previously: "Name"
echo '<th align="left">'.xlate("Album").'</th>';
echo '<th align="left">'.xlate("Year").'</th>';

if ($_SESSION['disp_duration']=="1") {
	echo '<th align="right">'.xlate("Duration").'</th>';
}


if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {  // 0.8.6


	if ($_SESSION['disp_last_played']=="1") {
		echo '<th align="right">'.xlate("Last played").'</th>';
	}

	if ($_SESSION['disp_times_played']=="1") {
		echo '<th align="right">'.xlate("Played").'</th>';
	}

	echo '<th> </th>'; // rest of the "actions" for individual entries
} else {
	echo '<th align="right">'.xlate('Requested by').'</th>';
}


	
while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);

	// 0.8.6: Add a 'strikethrough' ?
	$st='';
	if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled=='1')) { 
		$a=get_request_array($row['user_name']);
		if ($a[3]=='1') { // Yes, it was streamed already:
			$st='<s>';
		}
	}
	
	$qry2="SELECT id,performer_id,album_id,name, year, duration, last_played,";
    $qry2.=" times_played FROM track WHERE id=".$row['track_id'];
	$result2=execute_sql($qry2,0,1,$nr);
	$row2=mysql_fetch_array($result2);

    // Performer:
	$perf=get_performer_name($row2['performer_id']);
	echo '<td class="content">'.add_performer_link($st.$perf,$row2['performer_id'],$_SESSION['disp_small_images']).'</td>';
    
    // Track name:
	echo '<td>'.$st.$row2['name'].'</td>';	
	
	// get the name of the album:
	$qry3="SELECT * FROM album WHERE aid=".$row2['album_id']." LIMIT 1";
	$result3=execute_sql($qry3,0,-1,$nr);
	$row3=mysql_fetch_array($result3);
	echo add_album_link($st.$row3['aname'],$row3['aid'],$_SESSION['disp_small_images']);	

    // Year:
	echo add_year_link($st.$row2['year'],$row2['year']);

    // Duration:
	display_duration($st.$row2['duration']);

    // Jukebox specials - yeah!
	if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {  // 0.8.6

		display_last_played($row2['last_played']);

		display_times_played($row2['times_played']);

		echo '<td class="content">'; // 0.8.4
	
		add_delete_link("queue",$row['qid'],'');

		echo add_add2fav_link("track",$row2['id'],$_SESSION['hide_icon_text']); 

		add_download_link("track",'',$row2['id']);
	
		add_lyrics_link($row2['id']);
	} else {
		echo '<td class="content" align="right">'.$st.$a[2]; // "requested by"
	}
		
	print "</td></tr> \n"; // 0.8.4: Added td
}		



// Move/copy option:
if ($num_rows>0) {
	if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {
		disp_favorite_lists($_SESSION['login'],'1');
	}
}	

require("page_numbers.php");
?>
