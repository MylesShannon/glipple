<!-- // 0.7.9:  TinyMCE configuration (used w. album+performer bios): -->
<script type="text/javascript" src="./tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	mode : "textareas",
    theme : "advanced",
    theme_advanced_toolbar_location : "top",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,"
    + "justifyleft,justifycenter,justifyright,justifyfull,formatselect,"
    + "bullist,numlist,outdent,indent",
    theme_advanced_buttons2 : "link,unlink,anchor,image,separator,"
    +"undo,redo,cleanup,code,separator,sub,sup,charmap",
    theme_advanced_buttons3 : ""
});
</script>

<?php
if (!isset($_SESSION['login']) && ($_SESSION['admin']!="1")) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    die('Sorry - edit could not redirect to loginpage...');
}

require_once("sql.php");
require_once("disp.php");

//
// WRITING SUBMITTED FORM VALUES:
//
if (isset($act) && ($act=="write")) { // we're storing data...find out what should be written:
	if ($_POST['writewhat']=="track") { // ...it's a track:
		$qry="UPDATE track SET name='".addslashes(htmlspecialchars_decode($_POST['name']))."', ";
		$qry.="performer_id='".$_POST['performer_id']."', ";
		$qry.="album_id='".$_POST['album_id']."', ";
		$qry.="year='".$_POST['year']."', ";
		$qry.="path='".addslashes(htmlspecialchars_decode($_POST['path']))."', ";
		// 0.7.4: Added track_no, times_played:
		$qry.="track_no='".$_POST['track_no']."', ";
		$qry.="times_played='".$_POST['times_played']."', ";
		// 0.8.8: Added ALL Echonest-parameters:
		$qry.="echonest_tempo='".$_POST['echonest_tempo']."', ";
		$qry.="echonest_loudness='".$_POST['echonest_loudness']."', ";
		$qry.="echonest_danceability='".$_POST['echonest_danceability']."', ";
		$qry.="echonest_energy='".$_POST['echonest_energy']."', ";
		$qry.="echonest_mode='".$_POST['echonest_mode']."', ";
		$qry.="echonest_key='".$_POST['echonest_key']."', ";
		$qry.="echonest_time_signature='".$_POST['echonest_time_signature']."', ";
		$qry.="echonest_status='".$_POST['echonest_status']."', ";
		$qry.="echonest_liveness='".$_POST['echonest_liveness']."', ";
		$qry.="echonest_speechiness='".$_POST['echonest_speechiness']."', ";
		$qry.="echonest_acousticness='".$_POST['echonest_acousticness']."', ";
		$qry.="echonest_valence='".$_POST['echonest_valence']."'";
		// 0.8.8: ...ends
		$qry.=" WHERE id='".$id."' LIMIT 1";
	}
	if ($_POST['writewhat']=="performer") { // ...it's a performer:
		if ($_POST['performer_id']!="0") { // we want to transfer the tracks from this performer to somebody else:
			$qry="UPDATE track SET performer_id='".$_POST['performer_id']."' ";
			$qry.="WHERE performer_id='".$id."'";
		} else { // we're just renaming the fucker:
			$qry="UPDATE performer SET pname='".addslashes($_POST['pname'])."' WHERE pid='".$id."' LIMIT 1";
		}
	}
	if ($_POST['writewhat']=="album") { // ...it's an album:
		if ($_POST['album_id']!="0") { // we want to transfer all tracks on this 
		// album to a different album:
			$qry="UPDATE track SET album_id='".$_POST['album_id']."' ";
			$qry.="WHERE album_id='".$id."'";
		} else { // we're just renaming the fucker:
			$qry="UPDATE album SET aname='".addslashes($_POST['aname'])."' ";
			$qry.="WHERE aid='".$id."' LIMIT 1";
		}
	}
	if ($_POST['writewhat']=="favorite") { // ...it's a favorite list:
		$qry="UPDATE fav SET fav_name='".addslashes($_POST['favoritelistname'])."' ";
		$qry.=" WHERE fav_name='".addslashes($id);
		$qry.="' AND user_id='".get_user_id($_SESSION['login'])."'";
		// 0.5.0: well...sometimes you bump into 'hidden' things you forgot:
		if ($id==$_SESSION['favoritelistname']) {
			$_SESSION['favoritelistname']=$_POST['favoritelistname'];
		}
		// 0.5.2: Also change the name in any shared favorites:
		$result=execute_sql($qry,0,-1,$nr); // first process previous qry	
		$qry="UPDATE fav_shares SET fav_name='".addslashes($_POST['favoritelistname'])."'";
		$qry.=" WHERE fav_name='".addslashes($id)."'";
		$qry.="	AND owner_id='".get_user_id($_SESSION['login'])."'";
	}
	// 0.7.9: Album bios:
	if ($_POST['writewhat']=='album_bio') {
		$qry="UPDATE album set ".$_POST['bio_type']."='".addslashes($_POST['bio_txt'])."'";
		$qry.=" WHERE aid=".$id." LIMIT 1";
	}	
	// 0.7.9: Performer bio:
	if ($_POST['writewhat']=='performer_bio') {
		$qry="UPDATE performer set ".$_POST['bio_type']."='".addslashes($_POST['bio_txt'])."'";
		$qry.=" WHERE pid=".$id." LIMIT 1";
	}		
	$result=execute_sql($qry,0,-1,$nr);
	//die('QRY='.$qry);
	echo '<script type="text/javascript" language="javascript">'; echo "history.go(-2);";
	echo '</script>';  
}		

//
//			FUNCTIONS USED FOR EDITING:
//
function disp_performer_selection($default) {
	$pqry="SELECT * FROM performer ORDER BY pname";
	$presult=execute_sql($pqry,0,10000000,$nr);
	echo '<SELECT NAME="performer_id" class="tfield">';
	echo '<OPTION VALUE="0"';
	if ($default=="0") {
		echo ' selected';
	}
	echo '>[Do not transfer]</OPTION>';	
	while ($prow=mysql_fetch_array($presult)) {
		echo '<OPTION VALUE="'.$prow['pid'].'"';
		if ($prow['pid']==$default) {
			echo ' selected';
		}
		echo '>'.$prow['pname'].'</OPTION>';
	}
	echo '</SELECT>';
}	

function disp_album_selection($default) {
	$pqry="SELECT * FROM album";
	$presult=execute_sql($pqry,0,10000000,$nr);
	echo '<SELECT NAME="album_id" class="tfield">';
	echo '<OPTION VALUE="0"';
	if ($default=="0") {
		echo ' selected';
	}
	echo '>[NO album]</OPTION>';	
	while ($prow=mysql_fetch_array($presult)) {
		echo '<OPTION VALUE="'.$prow['aid'].'"';
		if ($prow['aid']==$default) {
			echo ' selected';
		}
		echo '>'.$prow['aname'].'</OPTION>';
	}
	echo '</SELECT>';
}	
		

// Editing:
if ($edit=="track") {
	$qry="SELECT * FROM track WHERE id='".$id."' LIMIT 1";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
}
if ($edit=="performer") {
	$qry="SELECT * FROM performer WHERE pid='".$id."' LIMIT 1";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
}		
if ($edit=="album") {
	$qry="SELECT * FROM album WHERE aid='".$id."' LIMIT 1";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
}	
if ($edit=="favorite") {
	$qry="SELECT * FROM fav WHERE fav_name='".$id."' AND user_id='".get_user_id($_SESSION['login']);
	$qry.="' LIMIT 1";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
}
// 0.7.9: Whohoo...album bio:
if ($edit=='albumbio') {
	$bio_type='bio_short';
	if ((isset($full_bio)) && ($full_bio=='1')) {
		$bio_type='bio_long';
	}	
	$qry="SELECT aid,$bio_type FROM album WHERE aid='".$id."'";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
}
// 0.7.9: Performer bio:
if ($edit=='performerbio') {
	$bio_type='bio_short';
	if ((isset($full_bio)) && ($full_bio=='1')) {
		$bio_type='bio_long';
	}	
	$qry="SELECT pid, $bio_type FROM performer WHERE pid='".$id."'";
	$result=execute_sql($qry,0,1,$nr);
	//echo $qry.' -> '.$nr;  // 0.8.0: Damn...
	$row=mysql_fetch_array($result);
}

	
// Form setup:
echo headline("","Edit data","");

echo std_table("ampjuke_content_table","ampjuke_content");
echo '<FORM NAME="edit" method="POST" action="index.php?what=edit&act=write&id='.$id.'">';
echo '<tr><td>ID:</td>';
echo '<td>'.$id;
echo '</td></tr>';
//echo '<tr><td>';


if ($edit=="track") { // we're editing a single TRACK:

	// 0.7.4: Added option to edit track_number:
	echo '<tr><td>Track number:</td>';
	echo '<td><input name="track_no" type="text" class="tfield" value="'.$row['track_no'].'">';
	echo '</td></tr>';

	echo '<tr><td>Track name:</td>';
	echo '<input type="hidden" name="writewhat" value="track">';
	echo '<td><input type="text" class="tfield" name="name" value="'.stripslashes(htmlspecialchars($row['name'])).'" size="80">'; // 0.8.6
	echo '</td></tr>';
	echo '<tr><td>Performer:</td><td>';
	disp_performer_selection($row['performer_id']);
	echo '</td></tr>';
	echo '<tr><td>Album:</td><td>';
	disp_album_selection($row['album_id']);
	echo '</td></tr>';
	echo '<tr><td>Year:</td>';
	echo '<td><input name="year" type="text" class="tfield" value="'.$row['year'].'">';
	echo '</td></tr>';
	echo '<tr><td>Path:</td>';
	echo '<td><input name="path" type="text" class="tfield" value="'.stripslashes(htmlspecialchars($row['path'])).'" size="100">'; // 0.8.6
	echo '</td></tr>';
	// 0.7.4: Added option to edit times_played:
	echo '<tr><td>Number of times played:</td>';
	echo '<td><input name="times_played" type="text" class="tfield" value="'.$row['times_played'].'">';
	echo '</td></tr>';	
	// 0.8.6: Edit Echonest values:
	if ((isset($echonest_enabled)) && ($echonest_enabled=='1')) {
		echo '<tr><td colspan="5"><hr class="hr_std"></td></tr>';
		echo '<tr><td colspan="5" align="center">Echonest values</td></tr>';
		echo '<tr><td>Tempo (aka. BPM):</td>';
		echo '<td>'.add_textinput('echonest_tempo',$row['echonest_tempo'],9).'</td></tr>';
		echo '<tr><td>Loudness:</td>';
		echo '<td>'.add_textinput('echonest_loudness',$row['echonest_loudness'],9).'</td></tr>';
		echo '<tr><td>Danceability:</td>';
		echo '<td>'.add_textinput('echonest_danceability',$row['echonest_danceability'],9).'</td></tr>';
		echo '<tr><td>Energy:</td>';
		echo '<td>'.add_textinput('echonest_energy',$row['echonest_energy'],9).'</td></tr>';
        // 0.8.8: 'Missing' values:
   		echo '<tr><td>Liveness:</td>';
		echo '<td>'.add_textinput('echonest_liveness',$row['echonest_liveness'],9).'</td></tr>';
		echo '<tr><td>Speechiness:</td>';
		echo '<td>'.add_textinput('echonest_speechiness',$row['echonest_speechiness'],9).'</td></tr>';
		echo '<tr><td>Acousticness:</td>';
		echo '<td>'.add_textinput('echonest_acousticness',$row['echonest_acousticness'],9).'</td></tr>';
		echo '<tr><td>Valence:</td>';
		echo '<td>'.add_textinput('echonest_valence',$row['echonest_valence'],9).'</td></tr>';
        // 0.8.8: ...ends
		echo '<tr><td>Mode:</td>';
		echo '<td>0:';
		$ts=$row['echonest_mode'];
		if ($ts<>'0') { $ts=''; }
		echo add_radio('echonest_mode','0',$ts);
		if ($ts<>'1') { $ts=''; }
		if (($ts<>'0') && ($ts<>'1')) { $ts='1'; } // 0.8.8 
		echo '1:'.add_radio('echonest_mode','1',$ts);
		echo ' <i>(0=minor, 1=major)</i></td></tr>';
		echo '<tr><td>Key:</td>';
		echo '<td>'.add_textinput('echonest_key',$row['echonest_key'],2).' <i>(Range:0-11)</i></td></tr>';
		echo '<tr><td>Time signature:</td>';
		echo '<td>'.add_textinput('echonest_time_signature',$row['echonest_time_signature'],2).' <i>(Range: 3-7)</i></td></tr>';
		echo '<tr><td>Status:</td>';
		echo '<td>'.add_textinput('echonest_status',$row['echonest_status'],15).' <i>(Options: "-1"=Never identified, "0"=Upload required or "timestamp")</i></td></tr>';




	}
	// used in DELETE below:
	$del_btn=$row['name']; 
	$del_warn="you will be DELETING this track";
	$what="track";
}
if ($edit=="performer") { // we're editing the name of a PERFORMER:
	echo 'Performer name:</td>';
	echo '<input type="hidden" name="writewhat" value="performer">';	
	echo '<td><input type="text" class="tfield" name="pname" value="'.stripslashes($row['pname']).'" size="100">';
	echo '</td></tr>';
	echo '<tr><td>Transfer all tracks with this performer to another performer:</td>';
	echo '<td>';
	disp_performer_selection("0");
	echo '</td></tr>';
	// used in DELETE below:
	$del_btn=$row['pname']; 
	$del_warn="you will be DELETING <b>all</b> tracks with this performer as well as <b>all albums</b>";
    echo ' where this performer is the only performer';
	$what="performerid";
}
if ($edit=="album") {
	echo 'Album name:</td>';
	echo '<input type="hidden" name="writewhat" value="album">';	
	echo '<td><input type="text" class="tfield" name="aname" value="'.stripslashes($row['aname']).'" size="100">';
	echo '</td></tr>';
	echo '<tr><td>Transfer all tracks from this album to another album:</td>';
	echo '<td>';
	disp_album_selection("0");
	echo '</td></tr>';
	// used in DELETE below:
	$del_btn=$row['aname'];	
	$del_warn="you will be DELETING this album";
	$what="albumid";
}		
if ($edit=="favorite") {
	echo 'Favorite list name:</td>';
	echo '<input type="hidden" name="writewhat" value="favorite">';	
	echo '<td><input type="text" class="tfield" name="favoritelistname" value="'.$row['fav_name'].'" size="100">';
	echo '</td></tr>';
	$del_btn=$row['fav_name'];
	$del_warn="you will be DELETING this favorite list";
	$what="favorite";
}
// 0.7.9: Album bio:
if ($edit=='albumbio') {
	echo '<input type="hidden" name="writewhat" value="album_bio">';
	echo '<input type="hidden" name="bio_type" value="'.$bio_type.'">';
	echo '<tr><td valign="top">Edit:</td><td><textarea name="bio_txt" rows="15" cols="50">'.$row[$bio_type].'</textarea>';
	echo '</td></tr>';
}	
// 0.7.9: Performer bio:
if ($edit=='performerbio') {
	echo '<input type="hidden" name="writewhat" value="performer_bio">';
	echo '<input type="hidden" name="bio_type" value="'.$bio_type.'">';
	echo '<tr><td valign="top">Edit:</td><td><textarea name="bio_txt" rows="15" cols="50">'.$row[$bio_type].'</textarea>';
	echo '</td></tr>';
}	



echo '<tr><td colspan="5"><hr class="hr_std"></td></tr>';
// 0.3.2: Translate the submit button's text:
echo '<tr><td colspan="5" align="center"><input type="submit" class="tfield" value="'.xlate("Save & continue").'"';
echo ' name="submit"></td></tr>';

echo '<tr><td colspan="5"><hr class="hr_std"></td></tr>';
$btn="Delete ".$del_btn;
echo '<tr><td colspan="5" align="center">';
echo '<p class="note">Warning: Only data in the';
echo ' <b>database</b> will be deleted. Not the physical music-file(s)';
echo '<br><a href="delete.php?what='.$what.'&id='.$id.'&jsb=3&special='.$id.'">';
echo '<img src="./ampjukeicons/mnu_arr.gif" border="0"> '.xlate("Delete").' '.$del_btn.'</a><br>';
echo '</td></tr>';
	
echo '</FORM></table>';
?>	
