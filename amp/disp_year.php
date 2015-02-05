<?php
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}	

require_once("sql.php");
require_once("disp.php");
require_once("set_td_colors.php");

$qry="SELECT DISTINCT year FROM track";
if ($order_by!="") {
	$qry.=" ORDER BY $order_by $dir ";
}	
$result=execute_sql($qry,0,1000000,$num_rows); // 0.7.3: Speed optimized
$result=execute_sql($qry,$start,$count,$n_rows);



$l="";
if ($limit=="") { $l.=xlate('All'); }
echo headline($what,'',$limit.$l.'</i> <br>'.xlate("Matches").':<i>'.$num_rows.'</i>'); 
if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }

require("tbl_header.php");
print "\n\n\n <!-- HEADLINE ENDS, JUMP STRAIGHT TO CONTENT --> \n\n\n </td></tr><tr><td>";
echo std_table("ampjuke_content_table","ampjuke_content");

tbl_header("year",xlate("Year"),"left","track.year",$order_by,$dir,$newdir,$count,'');
// 0.3.5: Display totals ?
if ($_SESSION['disp_totals']=="1") {
    echo '<th align="right"'.xlate("Tracks").'</th>';
}

// 0.6.0: Show headlines for: Add to favorite, Download & Play all:
// 0.8.4: removed: disp_headline_actions('album');
echo '<th class="tbl_header"> </th>';

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td><a href="index.php?what=yearid&start=0&order_by=track.year&special=';
    echo $row['year'].'&limit=">'.$row['year'].'</td>';

    // 0.3.5: Display totals ?
    if ($_SESSION['disp_totals']=="1") {
   	    $qry2="SELECT id FROM track WHERE year='".$row['year']."'";
	    $result2=execute_sql($qry2,0,1000000,$nrows2);
    	$row2=mysql_fetch_array($result2);
	    $nrows2=mysql_num_rows($result2);
    	echo '<td align="right">'.$nrows2.'</td>';
    }
    
    // 0.6.0: Options to add to favorite, download & play/queue:
    // add...
	echo '<td class="content" align="right">'; // 0.8.4
	echo add_add2fav_link("yearid",$row['year'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced
	// download...
    if ($_SESSION['can_download']=="1") {
		echo disp_download('year','',$row['year'],'',$_SESSION['hide_icon_text']); // 0.8.4
	}  
	// play/queue...
	echo add_play_enqueue_link($playtext,'yearid',$row['year'],$row['year'],'track.name','ASC','',$_SESSION['hide_icon_text']);    
    
	print "</td></tr> \n";
}
echo '</table>';	

require("page_numbers.php");
?>

