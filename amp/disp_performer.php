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

require_once("tbl_header.php");

if ($_SESSION['show_ids']=="1") {
	tbl_header($what,xlate("ID"),"left","performer.pid",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=performer&pagesel='.$pagesel);
}	

tbl_header($what,$d_performer,"left","performer.pname",$order_by,$dir,$newdir,
$count,'limit='.$limit.'&sorttbl=performer&pagesel='.$pagesel);

// 0.3.5: Display totals ?
if (($_SESSION['disp_totals'])=="1") {
    echo '<th align="right" valign="bottom" class="tbl_header">'.xlate("Albums").'</th>';
    echo '<th align="right" valign="bottom" class="tbl_header">'.xlate("Tracks").'</th>';
    echo '<th align="right" valign="bottom" class="tbl_header">'.xlate("Duration").'</th>';
}

echo '<th class="tbl_header"> </th>'; // 0.8.4: For the 'actions'

// 0.6.0: Show headlines for: Add to favorite, Download & Play all:
// 0.8.4: "removed": echo disp_headline_actions('performer');

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	
	if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1')) { // 0.8.5
		echo '<td class="content">'.add_edit_link("performer",$row['pid'],'').'</td>';
	}

	// 0.7.3: disp_small_images introduced:
	echo '<td class="content">'.add_performer_link($row['pname'],$row['pid'],$_SESSION['disp_small_images']).'</td>';

	// 0.3.5: CALCULATE the totals:
	if ($_SESSION['disp_totals']=="1") {
    	$q="SELECT aid,aperformer_id FROM album WHERE aperformer_id='".$row['pid']."'";
	    $res=execute_sql($q,0,100000,$nr);
    	echo '<td align="right" class="content">'.$nr.'</td>';
	    $q="SELECT id,performer_id,duration FROM track WHERE performer_id='".$row['pid']."'";
    	$res=execute_sql($q,0,1000000,$nr);
        echo '<td align="right" class="content">'.$nr.'</td>';
        // calculate total playtime:
        $total_playtime=0;
	    while ($r=mysql_fetch_array($res)) {
			// 0.8.4: split() replaced by explode():
        	$item=explode(":",$r['duration']);
    	    $s=$item[1] + ($item[0]*60);
    	    $total_playtime=$total_playtime+$s;
        }
        $dur=my_duration($total_playtime);
        echo '<td align="right" class="content">'.$dur.'</td>';
    }
    
    // 0.6.0: Options to add to favorite, download & play/queue:
    // add...
	echo '<td class="content" align="right">'; // 0.8.4
	echo add_add2fav_link("performerid",$row['pid'],$_SESSION['hide_icon_text']); 
	// download...
    if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
		echo disp_download('performer','',$row['pid'],'',$_SESSION['hide_icon_text']); // 0.8.4
	}  
	// play/queue...
	echo add_play_enqueue_link($playtext,'performerid',$row['pid'],'...','track.name','ASC','',$_SESSION['hide_icon_text']); // 0.8.4: hide_icon.. introduced
  
	print "</td></tr> \n";
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
