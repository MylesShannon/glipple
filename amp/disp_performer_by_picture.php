<?php
// Change this in order to change the actual appearance of album covers displayed:
$col_count=5; // Number of covers in each row. Note the number of items/page is user-controlled.
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

$qry="SELECT pid, pname FROM performer WHERE pid>1";
if (isset($limit)) {
	if ($what=="search") {
		$qry.=' AND pname LIKE "%'.$limit.'%"';
	} else {	
//		$qry.=' AND pname LIKE "'.$limit.'%"';
	 	// 0.7.4: Make sure we only accept letters+numbers:
		$limit=preg_replace ("/[^0-9^a-z^A-Z^.]/","",$limit);
	 	// 0.7.4: Is limit=0 ? Yes - only show stuff starting w. numbers:
	 	if ($limit=='0..9') {
			$qry.=" AND pname REGEXP '^[0-9]'";
		} else {
			$qry.=' AND pname LIKE "'.$limit.'%"';	 		
		}		
	}	
}

if (($order_by!="performer.pid") && ($order_by!="performer.pname") && ($order_by!="rand()")) {
	$order_by="performer.pname";
}	

if (($order_by!="") && ($sorttbl=="performer")) {
	$qry.=" ORDER BY $order_by $dir ";
}	

$tmpstart=$start;
$tmpsel=$pagesel;

if ($pagesel=="performer") {
	$result=execute_sql($qry,0,100000,$num_rows);
//	$num_rows=get_num_rows('performer','pid');
	$result=execute_sql($qry,$start,$count,$n_rows);
} else {
	$result=execute_sql($qry,0,$count,$num_rows);
	$start=0;
	$pagesel="performer";
}	



$l="";
if ($limit=="") { $l.=xlate('All'); }
if ($what=="search") { $l="</i>&nbsp[".xlate("Performers")."]"; }

if ($what!="welcome") { // 0.6.3: Clever ? Smart ? Uh...
	echo headline($what,'',$limit.$l.'</i> <br>'.xlate("Matches").':<i>'.$num_rows.'</i>'); 
}

// special options: letters
print "\n\n <!-- ACTIONS TABLE START --> \n\n";

if (($what!="search" && $_SESSION['show_letters']=="1") && ($order_by<>'rand()')) { // 0.8.5: Added order_by...
	echo std_table("ampjuke_actions_table","");
	echo '<tr><td>'.show_letters("performer","performer.pname").'</td></tr></table>';
}

print "</td></tr> \n\n <!-- ACTION TABLE ENDS, NEW ROW FOR MAIN_CONTENT_TABLE: --> \n\n <tr><td>";


if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }
// 0.6.3: Changed this in order to have 'correct' zebra-table on the 'welcome' page:
if ($what!="welcome") { 
	echo std_table("ampjuke_content_table","ampjuke_content");
} else {
	echo std_table("ampjuke_content_table",$welcome_table);
	$tmpwhat=$what;
	$what="performer";
}

// 0.6.0: Show headlines for: Add to favorite, Download & Play all:
echo disp_headline_actions('performer');

$c_count=$col_count;
while ($row=mysql_fetch_array($result)) {
	if ($c_count==$col_count) {
		echo '<tr>';
	}	

	// First, the image:
	echo '<td class="content" width="'.$col_width.'%" valign="top" align="center">';
	if (file_exists('./lastfm/'.$row['pid'].'.jpg')) {
		$img='<img src="./lastfm/'.$row['pid'].'.jpg" border="0" width="126px" height="126px" class="tooltip" title="'.$row['pname'].'">'; // 0.8.7: Added tooltip
	} else {
		$img='<img src="./covers/_blank.jpg" border="0" width="126px" height="126px" class="tooltip" title="'.$row['pname'].'">'; // 0.8.7: Added tooltip
	}	
	echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'">'.add_performer_link($img,$row['pid'],'0');
	$ampjuke_animated_objects++; // 0.8.5
	// Second, link to play/enqueue:
	$s=add_play_enqueue_link($playtext,'performerid',$row['pid'],$row['pname'],'track.name','ASC','',$_SESSION['hide_icon_text']).'</td>'; // 0.8.4: hide_icon...
	$s=str_replace('<td class="content">','',$s);
	$s=str_replace('</td>','',$s);
	echo '<br>'.$s.'<br>';
	// Third, add to favorite link:
	$s=add_add2fav_link("performerid",$row['pid'],$_SESSION['hide_icon_text']); // 0.8.4
	$s=str_replace('<td class="content" align="right">','',$s);
	$s=str_replace('</td>','',$s);
	echo $s.'</p>';
	
	// Calculate row-stuff:
	echo '</td>';
	$c_count--;
	if ($c_count==0) {
		echo '</tr>';
		$c_count=$col_count;
	}	
}

print "</table>  \n\n\n";	

if ($what=="search") {
	$within="performers";
	$sorttbl="performer";
}	

if (!isset($tmpwhat)) { // 0.6.3
	require("page_numbers.php");
} else {
	$what=$tmpwhat;
}
$start=$tmpstart;
$pagesel=$tmpsel;
?>
