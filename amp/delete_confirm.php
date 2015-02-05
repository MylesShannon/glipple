<?php
require("make_header.php");

function get_name($id,$what,$l) {
	require_once("disp.php");
	require_once("sql.php");
	$ret="";
	if ($what=="track") {
		$qry="SELECT * FROM track WHERE id=$id LIMIT 1";
		$row=get1row($qry);
		$r=get_performer_name($row['performer_id']);
		$ret=$r.' - '.$row['name'];
	}
	if ($what=="albumid") {
		$qry="SELECT * FROM album WHERE aid=$id LIMIT 1";
		$row=get1row($qry);		
		if ($row['aperformer_id']!='1') { // this is not an album made by "various":
			$r=get_performer_name($row['aperformer_id']);
			$ret=$r.' - '.$row['aname'];
		} else { // this IS an album amde by "various", - just return album name:
			$ret=$row['aname'];
		}				
	}
	if ($what=="performerid") {
		$qry="SELECT * FROM performer WHERE pid='".$id."' LIMIT 1";
		$row=get1row($qry);
		$ret=$row['pname'];
	}
	if ($what=="yearid") {
		$ret=$id; // !! not used, yet !!
	}					
	if ($what=="favoriteid") {
		$qry="SELECT * FROM fav WHERE id=$id LIMIT 1"; // 0.5.6: Changed from 'favorites'
		$row=get1row($qry);		
		$qry2="SELECT * FROM track WHERE id=".$row['track_id']." LIMIT 1"; // 0.5.6: Changed from 'fid'
		$row2=get1row($qry2);		
		$r=get_performer_name($row2['performer_id']);
		$ret='<i>('.xlate("Favorite list").' '.$row['fav_name'].')</i> '.$r.' - '.$row2['name'];
	}	
	if ($what=="favorite") {
		$ret=xlate("Favorite list").' : '.$id;
	}
	if ($what=="queue") {
		$ret=xlate("The queue");
	}
	if ($what=="queueid") {
		$qry="SELECT * FROM queue WHERE qid=$id LIMIT 1";
		$row=get1row($qry);		
		$qry2="SELECT * FROM track WHERE id=".$row['track_id']." LIMIT 1";
		$row2=get1row($qry2);		
		$r=get_performer_name($row2['performer_id']);
		$ret='<i>('.xlate("The queue").')</i> '.$r.' - '.$row2['name'];
	}	
	if ($what=="cache") {
		$ret=xlate("The cache");
	}
	if ($what=="cover") {
		$ret='<br><img src="./covers/'.$id.'.jpg" border="0">';
	}		
	return $ret;		
}	

// delete_confirm.php: confirm actually DELETION of something:
echo '<table class="ampjuke_content_table">';
echo '<tr><td>';
echo '<b><font color="red">'.strtoupper(xlate("Delete")).': </b><font color="black">';
// display the exact NAME(S) of what it is we're about to get rid of:
$n=get_name($id,$what,'');
echo '<b class="note">'.$n.'</b> ?</td></tr><tr><td>';
//	
echo xlate("Are you sure").' ?</td></tr><tr><td>';
$therest="";
if (isset($fav_name)) {
	$therest.="&fav_name=".$fav_name;
}	
if (isset($replace)) {
	$therest.="&replace=".$replace;
}	
echo '<a href="delete.php?confirmed=yes&what='.$what.'&id='.$id.$therest.'&jsb=2">';
echo xlate("Yes").'</a>&nbsp&nbsp&nbsp';
echo '<a href="delete.php?confirmed=no&what='.$what.'&id='.$id.'"&jsb=2>';
echo xlate("No").'</a> ';
echo '</td></tr></table>';
?>
