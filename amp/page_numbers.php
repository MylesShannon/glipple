<?php
// page numbers, ONLY possible AFTER SQL has been exec.:

//global $tp, $total_pages, $current_page; 0.8.4: Gone!

print "\n\n <!-- PAGENUMBER TABLE START --> \n";
echo std_table("ampjuke_pagenumber_table","");

print '<tr><td align="center">';

$ob=$order_by;
if (($count > 0) && (isset($num_rows))) { // fixed division by zero error by rezso. 0.8.4: Added check for $num_rows
	$total_pages=round($num_rows/$count);
	$tp=$num_rows/$count;
} else { // 0.8.4:
	$num_rows=1;
	$count=1;
	$total_pages=1;
	$tp=1;
}

if ($tp-$total_pages>0) { $total_pages++; }
if($count > 0) { $current_page=round($start/$count)+1; } // fixed division by zero error by rezso
if (!isset($disp_pages)) {
	$disp_pages=14; 
}	

$first_page=round($current_page-($disp_pages/2));
$last_page=round($current_page+($disp_pages/2));

if ($current_page>=$last_page) { $first_page=$current_page-$disp_pages; }
if ($current_page>=$first_page) { $last_page=$current_page+$disp_pages; }
if ($first_page<1) { $first_page=1; $last_page=$disp_pages; }
if ($last_page>$total_pages) { $last_page=$total_pages; }

$pref='<a href="index.php?what='.$what;
$pref.="&order_by=$ob&dir=$dir&sorttbl=$sorttbl&pagesel=$pagesel&special=$special";
$tp=$start-$count;

if ($tp<0) { $tp=0; }
$tn=$start+$count;
// 0.8.4: 
if (!isset($num_rows)) { $num_rows=100000; }
if ($tn>$num_rows) { $tn=$num_rows; }
$prev_link=$pref.'&start='.$tp.'&count='.$count.'&limit='.$limit;
$next_link=$pref.'&start='.$tn.'&count='.$count.'&limit='.$limit;
// 0.6.7: 1st & last page links:
$first_link=$pref.'&start=0&count='.$count.'&limit='.$limit;
$last_link=$pref.'&start='.(round($num_rows / $count)*$count).'&limit='.$limit;

if ($current_page>1) { 
	// 0.6.7: First page link
	echo $first_link.'">'.get_icon($_SESSION['icon_dir'],'page_one','').'</a>&nbsp&nbsp';
	echo $prev_link.'">'.get_icon($_SESSION['icon_dir'],'page_prev','').'</a>&nbsp&nbsp';
}

$tmp_page=$first_page;

while ($disp_pages>=0 && $tmp_page<=$total_pages) { 

	$tmp_start=($tmp_page*$count)-$count;
	if ($tmp_page<>$current_page) {
		echo $pref.'&start='.$tmp_start.'&count='.$count.'&limit='.$limit.'"';
		// 0.7.8: Add a TITLE to the link, so we'll know in advance what we might jump to:
		$title=$tmp_page; // Just se we have it set.
		// 0.7.8: Titles for albums:
		if ($what=='album') {
			$qry="SELECT aid,aname FROM album ";
			// Do we have a limit (f.ex.: 'M') set ?
			if (isset($limit)) {
			 	if ($limit=='0..9') {
					$qry.=" WHERE aname REGEXP '^[0-9]'";
				} else {
					$qry.=' WHERE aname LIKE "'.$limit.'%"';	 		
				}
			}
			$qry.=" ORDER BY ".$order_by." ".$dir;
			$qry.=" LIMIT ".$tmp_start.",1";
			$result=execute_sql($qry,0,-1,$nr);
			$row=mysql_fetch_array($result);
			$title.=': '.$row['aname'];
		}
		// 0.7.8: Titles for tracks:
		if ($what=='track') {
			$qry="SELECT track.id, track.name, track.performer_id, track.duration, track.year, track.last_played, track.times_played, track.path, track.album_id, performer.pid, performer.pname FROM track, performer WHERE track.performer_id=performer.pid AND ";
			// Do we have a limit (f.ex.: 'M') set ?
			if (isset($limit)) {
			 	if ($limit=='0..9') {
					$qry.="name REGEXP '^[0-9]'";
				} else {
					$qry.='name LIKE "'.$limit.'%"';	 		
				}
			}
			$qry.=" ORDER BY ".$order_by." ".$dir;
			$qry.=" LIMIT ".$tmp_start.",1";
			$result=execute_sql($qry,0,-1,$nr);
			$row=mysql_fetch_array($result);
			$title.=': '.$row['name'];
		}
		// 0.7.8: Titles for performers:
		if ($what=='performer') {
			$qry="SELECT pid, pname FROM performer WHERE pid>1";
			// Do we have a limit (f.ex.: 'M') set ?
			if (isset($limit)) {
			 	if ($limit=='0..9') {
					$qry.=" AND pname REGEXP '^[0-9]'";
				} else {
					$qry.=' AND pname LIKE "'.$limit.'%"';	 		
				}
			}
			$qry.=" ORDER BY ".$order_by." ".$dir;
			$qry.=" LIMIT ".$tmp_start.",1";
			$result=execute_sql($qry,0,-1,$nr);
			$row=mysql_fetch_array($result);
			$title.=': '.$row['pname'];
		}
		echo ' title="'.$title.'"';
		echo '>'.$tmp_page.'</a> '; 
	} else { echo "<b> [$tmp_page] </b>"; }

	$disp_pages--; $tmp_page++;
	print "\n";
}



if ($current_page<$total_pages) { 
	echo $next_link.'" >'.get_icon($_SESSION['icon_dir'],'page_next','').'</a>&nbsp&nbsp'; 
	// 0.6.7: Last page link:
	echo $last_link.'">'.get_icon($_SESSION['icon_dir'],'page_last','').'</a>&nbsp&nbsp';
	if ($disp_pages==-1) { echo ' <i>('.$total_pages.' '.xlate("pages in total").')</i>';	} 
	// ...changed from $disp_pages==-2 @ 23/07/05/MHI
} 

?>

</td></tr></table>
<?php
print "\n <!-- PAGENUMBER TABLE END --> \n";
?>
