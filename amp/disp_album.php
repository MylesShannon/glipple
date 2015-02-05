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

// 0.6.1: The SQL-statement has been modified (Stuart Hardy):
$qry="SELECT aid, aname, IFNULL(pid, '0') AS pid, IFNULL(pname, 'Various Artists') ";
$qry.="AS pname FROM album LEFT OUTER JOIN performer ON album.aperformer_id=performer.pid";

if (isset($limit)) {
	if ($what=="search") {
		$qry.=' WHERE aname LIKE "%'.$limit.'%"';
	} else {	
	 	$qry.=' WHERE 1=1';
	 	// 0.7.4: Make sure we only accept letters+numbers:
		$limit=preg_replace ("/[^0-9^a-z^A-Z^.]/","",$limit);
	 	// 0.7.4: Is limit=0 ? Yes - only show stuff starting w. numbers:
	 	if ($limit=='0..9') {
			$qry.=" AND aname REGEXP '^[0-9]'";
		} else {
			$qry.=' AND aname LIKE "'.$limit.'%"';	 		
		}
	}		
}

// 0.6.1 (revised): Sort by album-ID if we show ID's. If not, sort by name:
if ($order_by=="album.aid" && $_SESSION['show_ids']=="1") {
    $order_by="album.aid";
    $dir="DESC";
} elseif ($order_by == "album.aid") {
        $order_by="album.aname";
        $dir="ASC";
}

// 0.7.4: Filter $order_by:
if (($order_by!="album.aid") && ($order_by!="album.aname") && ($order_by!="rand()") && ($order_by!="aid")) {
	$order_by="album.aname";
}	

if (($order_by!="") && ($sorttbl=="album")) {
	$qry.=" ORDER BY $order_by $dir ";
}

$result=execute_sql($qry,0,1000000,$num_rows);

$tmpstart=$start;
$tmpsel=$pagesel;
if ($pagesel=="album") {
	$result=execute_sql($qry,$start,$count,$n_rows);
} else {
	$result=execute_sql($qry,0,$count,$n_rows);
	$start=0;
	$pagesel="album";
}

$l="";
if ($limit=="") { $l.=xlate('All'); }
if ($what=="search") { $l="</i>&nbsp[".xlate("Albums")."]"; }
if ($what!="welcome") {
	echo headline($what,'',$limit.$l.'</i> <br>'.xlate("Matches").':<i>'.$num_rows.'</i>'); 
}

print "\n\n\n <!-- ACTIONS TABLE START --> \n\n\n";
echo '<table class="ampjuke_actions_table">';

if (($what!="search" && $_SESSION['show_letters']=="1") && ($order_by<>'rand()')) { // 0.8.5: Added order_by...
	echo std_table("ampjuke_actions_table","");	
	echo '<tr><td>'.show_letters("album","album.aname").'</td></tr></table>';
}

print "\n\n <!-- ACTIONS TABLE ENDS, ROW FOR MAIN_CONTENT_TABLE: --> \n\n </td></tr><tr><td>";

if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }

// 0.6.3: Changed this in order to have 'correct' zebra-table on the 'welcome' page:
if ($what!="welcome") {
	echo std_table("ampjuke_content_table","ampjuke_content");
} else {
	echo std_table("ampjuke_content_table",$welcome_table);
	$tmpwhat=$what;
	$tmpcount=$count;
	$what="album";
	$count=$_SESSION['count'];
}

require_once("tbl_header.php");

if ($_SESSION['show_ids']=="1") {
	tbl_header($what,xlate("ID"),"left","album.aid",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=album&pagesel='.$pagesel);
}	

tbl_header($what,xlate("Album"),"left","album.aname",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&sorttbl=album&pagesel='.$pagesel);

tbl_header($what,xlate("Performer"),"left","performer.pname",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&sorttbl=album&pagesel='.$pagesel);

// 0.3.5: We might want to display the totals:
if (($_SESSION['disp_totals'])=="1") {
    echo '<th align="right" valign="bottom">'.xlate("Tracks").'</th>';
    echo '<th align="right" valign="bottom">'.xlate("Duration").'</th>';
}

// 0.8.4: "removed": echo disp_headline_actions('album');
echo '<th class="tbl_header"> </th>'; // 0.8.4

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);

	if ($_SESSION['show_ids']=="1") { // 0.6.3
		echo '<td class="content">'.add_edit_link("album",$row['aid'],'').'</td>';
	}
	
	// 0.7.3: ..images..:
	echo add_album_link($row['aname'],$row['aid'],$_SESSION['disp_small_images']);
	// 0.7.3: ..images..:
	echo '<td class="content">'.add_performer_link($row['pname'],$row['pid'],$_SESSION['disp_small_images']).'</td>';

	// 0.3.5: We might want to display the totals:
	if ($_SESSION['disp_totals']=="1") {
        $total_playcount=0;
        $total_playtime=0;
        $qry2="SELECT id, performer_id, album_id, duration, times_played";
        $qry2.=" FROM track WHERE album_id=".$row['aid'];
        $res2=execute_sql($qry2,0,-1,$nr);
        while ($row2=mysql_fetch_array($res2)) {
            $total_playcount++;
			// 0.8.4: split() replaced by explode():
        	$item=explode(":",$row2['duration']);
        	$s=$item[1] + ($item[0]*60);
        	$total_playtime=$total_playtime+$s;
        }
        $dur=my_duration($total_playtime);	
		display_times_played($total_playcount);
        echo '<td class="content" align="right">'.$dur.'</td>';
    }
    
    // 0.6.0: Options to add to favorite, download & play/queue:
	echo '<td class="content" align="right">'; // 0.8.4
	echo add_add2fav_link("albumid",$row['aid'],$_SESSION['hide_icon_text']); // 0.8.4
    if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
		echo disp_download('album','',$row['aid'],'',$_SESSION['hide_icon_text']); // 0.8.4
	}  
	echo add_play_enqueue_link($playtext,'albumid',$row['aid'],'...','track.track_no','ASC','',$_SESSION['hide_icon_text']); // 0.8.4
	print "</td></tr> \n";
}

echo '</table>';	
print "\n\n\n <!-- CONTENT ENDS, NEW ROW FOR MAIN_CONTENT_TABLE: --> \n\n\n </td></tr><tr><td>";

if ($what=="search") {
	$within="albums";
	$sorttbl="album"; 
}	

if (!isset($tmpwhat)) { // 0.6.3
	require("page_numbers.php");
} else {
	$what=$tmpwhat;
}
$start=$tmpstart;
$pagesel=$tmpsel;
?>
