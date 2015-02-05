<?php
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}	

require_once("sql.php");
require_once("disp.php");
require_once("set_td_colors.php");

function check_recompute($fav_name,$uid) {
	$qry2="SELECT id,track_id,duration,user_id FROM fav WHERE fav_name='".$fav_name."'";
	$qry2.=" AND track_id>'0' AND user_id='".$uid."'";
	
    $result2=execute_sql($qry2,0,1000000,$nrows2);
   	$nrows2=mysql_num_rows($result2);
    echo '<td align="right">'.$nrows2.'</td>';
	$total_playtime=0;

	// Check against "record=0"'s :
	// last_played=total_playtime
	// times_played=number of tracks in the fav. list
	$recompute=0;
	$ref_qry="SELECT * FROM fav WHERE fav_name='".$fav_name."'";
	$ref_qry.=" AND track_id='0' AND user_id='".$uid."'";
	$ref_result=execute_sql($ref_qry,0,1,$ref_num);

	// 0.8.2: Hell...Do we even HAVE the f*cker in the table ???  ("magic" record w. track_id=0  for this fav_name)
	if ($ref_num<>1) { // No - either we don't have it, or we have it more than once:
		// delete all previous 'magic' records (even though there might not be any):
		$q="DELETE FROM fav WHERE fav_name='".$fav_name."' AND track_id='0' AND user_id='".$uid."'";
		$res=execute_sql($q,0,-1,$dummy);
		// insert a new 'magic' record for this specific fav_name for this specific user:
		$q="INSERT INTO fav (fav_name, track_id, user_id) VALUES ('".$fav_name."', '0', '".$uid."');";
		$res=execute_sql($q,0,-1,$dummy);
		// repeat previous attempt (which should now succeed):
		$ref_result=execute_sql($ref_qry,0,1,$dummy);
	}
	
	$ref_row=mysql_fetch_array($ref_result);
	if ($ref_row['times_played']<>$nrows2) {
		$recompute=1;
	} else {
	 	$total_playtime=$ref_row['last_played'];
	} 	

	if ($recompute==1) {
        while ($row2=mysql_fetch_array($result2)) {
	        $qry3="SELECT id,duration FROM track WHERE id=".$row2['track_id']." LIMIT 1";
    	    $result3=execute_sql($qry3,0,-1,$n);
       		$row3=mysql_fetch_array($result3);
			// 0.8.4: split() replaced by explode():
	       	$item=explode(":",$row3['duration']);
	       	// 0.8.7: Check size of array before proceeding:
	       	if (isset($item[1])) {
       	    	$s=$item[1] + ($item[0]*60);
        		$total_playtime=$total_playtime+$s;
        	}
		}
	} 
    
	echo '<td class="content" align="right">'.my_duration($total_playtime).'</td>';
    // If recompute=1 then insert the value into "record=0":
    if ($recompute==1) {
      	$updqry="UPDATE fav SET last_played='".$total_playtime."',";
		$updqry.=" times_played='".$nrows2."'";
		$updqry.=" WHERE fav_name='".$fav_name."' AND user_id='".$uid."'";
		$updqry.=" AND track_id='0'"; 
		$updres=execute_sql($updqry,0,-1,$x);
	}	 
} 

$uid=get_user_id($_SESSION['login']);
$qry="SELECT DISTINCT fav_name FROM fav WHERE user_id='".$uid."'"; 
$qry.=" ORDER BY fav_name";


if ($order_by!="") {
	$qry.=" ORDER BY $order_by $dir ";
}	
$result=execute_sql($qry,0,10000000,$num_rows); // 0.7.3: Speed opmitized
$result=execute_sql($qry,$start,$count,$n_rows);

echo headline($what,'','');

print "\n\n\n <!-- Now on to content --> \n\n\n </td></tr><tr><td>";

if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }
echo std_table("ampjuke_content_table","ampjuke_content"); // 0.8.4
require("tbl_header.php");
echo '<th align="left">'.xlate("Favorite list").'</th>';
if ($_SESSION['disp_totals']=="1") {
    echo '<th align="right">'.xlate("Tracks").'</th>';
    echo '<th align="right">'.xlate("Duration").'</th>';
}

// 0.8.3: Only show this headline if we're allowed to display shared favoritelists:
if ((isset($shared_favorites_allow)) && ($shared_favorites_allow=='1')) {
	echo '<th align="right">'.xlate("Shared").'? '.add_faq(29).'</th>'; 
}

/* 0.8.4: Ouch - again - let's just forget about this:	
echo '<th> </th><th> </th>'; // Edit/delete
if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
	echo '<th> </th>';
}
echo '<th> </th>'; // Play/queue
...and replace it with: */
echo '<th class="tbl_header"> </th>';

while ($row=mysql_fetch_array($result)) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>';

	if ($_SESSION['favoritelistname']==$row['fav_name']) {
		echo '<b>--> ';
	}

	echo '<a href="index.php?what=favoriteid&start=0&order_by=fav.track_id&special=';
    echo $row['fav_name'].'">'.$row['fav_name'].'</a>';
	if ($_SESSION['favoritelistname']==$row['fav_name']) {
		echo ' <---</i></b> '; 
	}	

	echo '</td>';
	if ($_SESSION['disp_totals']=="1") {
		check_recompute($row['fav_name'],$uid);
    } 

	// 0.5.2: Offer option to share + display how many user(s) have access to this list...
	// 0.8.3: ...BUT only if shared_favorites_allow is turned on:
	if ((isset($shared_favorites_allow)) && ($shared_favorites_allow=='1')) {
		$sqry="SELECT * FROM fav_shares WHERE fav_name='".$row['fav_name']."'";
		$sqry.=" AND owner_id='".$uid."'";
		$sresult=execute_sql($sqry,0,1000000,$total);
		echo '<td align="right">';
		$txt=xlate("Yes");
		if ($total==0) { $txt=xlate("No"); }
		echo '<a href="./?what=fav_share&act=disp&id='.urlencode($row['fav_name']).'">';
		echo $txt.' ('.$total.')</a>';
		echo '</td>';		 
	}
	
	echo '<td class="content" align="right">';
	
	echo add_delete_link("favorite",$row['fav_name'],'',1,$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced

	echo add_edit_link("favorite",$row['fav_name'],$_SESSION['hide_icon_text']); // 0.8.4: '1' addded (=no td's)
	
	// download...
	if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
		echo disp_download('favorite_list','',$row['fav_name'],'',$_SESSION['hide_icon_text']); // 0.8.4: icon_txt...
	}  
	// play/queue...
	echo add_play_enqueue_link($playtext,'favorite_list',$row['fav_name'],'...','track.track_no','ASC','',$_SESSION['hide_icon_text']); // 0.8.4: icon_text...
		
	print "</td></tr> \n";
}

// 0.8.3: Wtf..? This single-entry-table-stuf scr*wed up the layout *completely*. At least until now :-)
//echo '</table>'; 

// Display shared favorite lists:
// 0.8.3: ...however, only if shared_favorites_allow is turned on:
if (($_SESSION['disp_fav_shares']=="1") && (isset($shared_favorites_allow)) && ($shared_favorites_allow=='1')) {
	$qry="SELECT * FROM fav_shares WHERE share_id='".$uid."'";
	$qry.=" ORDER BY fav_name";
	$result=execute_sql($qry,0,100000,$n);
	while ($row=mysql_fetch_array($result)) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>';
		echo '<i>('.xlate("Shared").')</i> ';
		if ($_SESSION['favoritelistname']==$row['fav_name']) {
			echo '<b>--> ';
		}
		echo '<a href="index.php?what=favoriteid&start=0&order_by=fav.track_id&special=';
	    echo $row['fav_name'].'&shared=yes">'.$row['fav_name'].'</a>';
		if ($_SESSION['favoritelistname']==$row['fav_name']) {
			echo ' <---</i></b> '; 
		}	
		echo '</td>';

		if ($_SESSION['disp_totals']=="1") {
			check_recompute($row['fav_name'],$row['owner_id']);
	    } 
	    // 0.8.4: WTF??
	    /*
		echo '<td> </td>'; // empty column -> cannot share a shared list...
		echo '<td> </td>'; // empty column -> cannot delete a shared list...
		echo '<td> </td>'; // empty column -> cannot edit a shared list...	
		*/
		if ($_SESSION['can_download']=="1") {
			add_download_link("favorite_list",'',$row['fav_name']);
		}
		print "</tr> \n";
	}	
}	
 
echo '</table>'; 
disp_favorite_lists($_SESSION['login'],'0');

require("page_numbers.php");
echo '</table>';

?>
