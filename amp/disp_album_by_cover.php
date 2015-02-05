<?php
// Change this in order to change the actual appearance of album covers displayed:
$col_count=5; // Number of covers in each row. Note the number of items/page is user-controlled.
$cover_dim='width="120px" height="120px" border="0"'; // Append this to covers displayed
$col_width=round(100/$col_count); // Calculated width of each column (simple)

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
//echo '<table class="ampjuke_actions_table">';

if (($what!="search" && $_SESSION['show_letters']=="1") && ($order_by<>'rand()')) { // 0.8.5: Added order_by...
	echo '<table class="ampjuke_actions_table"><tr><td>'.show_letters("album","album.aname").'</td></tr></table>';
}
// Special for this view: Add options to sort by:

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

// 0.8.4: "remonved":
//echo disp_headline_actions('album');
//echo '<tr>';
$c_count=$col_count;
while ($row=mysql_fetch_array($result)) {
	if ($c_count==$col_count) {
		echo '<tr>';
	}
	
	echo '<td class="content" width="'.$col_width.'%" valign="top" ALIGN="CENTER">';
	print "<!-- c_count=".$c_count." col_count=".$col_count." --> \n\n\n\n";
	// First, the album image:
	echo '<a href="index.php?what=albumid&start=0&count='.$_SESSION['count'];
    echo '&special='.$row['aid'].'&order_by=track.track_no"';
	echo ' title="'.get_album_tracklist($row['aid']).'" class="tooltip">'; // 0.8.7: Added tooltip+get_album..
	echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'">'; // 0.8.5
	if (file_exists('./covers/'.$row['aid'].'.jpg')) { // Show the actual image:
		echo '<img src="./covers/'.$row['aid'].'.jpg" '.$cover_dim.'">'; 
	} else { // Show the default image:
		echo '<img src="./covers/_blank.jpg" '.$cover_dim.'">';
	}
	echo '</a><br>';
	$ampjuke_animated_objects++; // 0.8.5
	// Second, a link to play/queue:
	echo add_play_enqueue_link($playtext,'albumid',$row['aid'],$row['pname'].'-'.$row['aname'],'track.track_no','ASC','1',$_SESSION['hide_icon_text']);

	// Finally, an option to add to favorite:
	$s=add_add2fav_link("albumid",$row['aid'],$_SESSION['hide_icon_text']); // 0.8.4
	$s=str_replace('<td class="content" align="right">','',$s); // Get rid the <td>-formatted stuff
	$s=str_replace('</td>','',$s);
	echo '<br>'.$s.'</p>';
	// Find out if it's time to switch to a new row:
	$c_count--;
	if ($c_count==0) {
		print "</tr> \n\n";
		$c_count=$col_count;
	}
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
