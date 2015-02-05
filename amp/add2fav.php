<?php
session_start();
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}
?>	
<SCRIPT LANGUAGE="JavaScript">
<!--// Hide script from non-javascript browsers.
// Load Page Into Parent Window
// Version 1.0
// Last Updated: May 18, 2000
// Code maintained at: http://www.moock.org/webdesign/javascript/
// Copy permission granted any use provided this notice is unaltered.
// Written by Colin Moock.
function loadinparent(url){
	self.opener.location = url;	
}
//-->
</SCRIPT>	
<?php
	
require_once("db.php");
require_once("sql.php");
require_once("disp.php");
require_once('echonest_lib.php'); // 0.8.7		
parse_str($_SERVER["QUERY_STRING"]);
$favlist=$_SESSION['favoritelistname'];

// 0.8.7:
if (!isset($keep_open)) {
	$keep_open='';
}

// 0.8.2: If 'suggestion' is set it means we're adding something last.fm suggested in the popup:
if (isset($suggestion)) {
	// Setup:
	$favlist=my_filter_var($suggestion);
	$uid=get_user_id($_SESSION['login']);
	$u=get_user_details($uid);

	// Check if we have the favlist created already:
	$qry="SELECT user_id,fav_name FROM fav WHERE user_id=".$uid." AND fav_name='".$favlist."'";
	$result=execute_sql($qry,0,1,$nr);
	if ($nr==0) { // We do not have a list with this name, - create it:
		$qry="INSERT INTO fav (user_id, fav_name) VALUES";
		$qry.="('".$uid."', '".$favlist."')";
		$result=execute_sql($qry,0,-1,$nr);
	}
	// Add it as usual (see below)
}
	

// 0.8.2: Crap out, if:$favlist is empty AND $picker isn't set
if (($favlist=='') && (!isset($picker))) {
	echo 'Cannot add to an empty favorite list. Create one, select it and then you can start add something.<br>';
	echo 'More help here: <a href="http://www.ampjuke.org/faq.php?q_id=76" target="_blank">http://www.ampjuke.org/faq.php?q_id=76</a>.<br>';
	echo '<br><br>An alternative solution: Turn on the personal setting "Ask for name of favorite list".<br>';
	echo 'More help about that here: <a href="http://www.ampjuke.org/faq.php?q_id=31" target="_blank">http://www.ampjuke.org/faq.php?q_id=31</a>';
	die();
}

// 0.5.5: PICK from a list	
if (isset($picker)) {
	if ($picker==1) {
	 	$loc=$base_http_prog_dir.'/add2fav.php?picker=2&what='.$what.'&id='.$id;
		echo '<script type="text/javascript" language="javascript">';	 		 	
		echo "history.go(-1);";
//		echo 'var rw = window.open("'.$loc.'","AmpJuke_Picker_'.$id.'","width=650,height=300,resizable=yes,scrollbar=yes");';
		echo 'var rw = window.open("'.$loc.'","AmpJuke_Picker","width=650,height=300,resizable=yes,scrollbar=yes");';
		echo '</script>';  			
		die();
	} 
	if ($picker==2) {
		disp_fav_list_picker($_SESSION['login'],$_SERVER["QUERY_STRING"],$keep_open); // 0.8.7: Added $keep_open
		// 0.8.2: So! We're adding a TRACK - display what favorite lists this track is in:
		if ($what=='track') { 
			$uid=get_user_id($_SESSION['login']);
			$u=get_user_details($uid);
			$qry="SELECT id,track_id,user_id,fav_name FROM fav WHERE track_id='".$id."' AND user_id='".$uid."'";
			$result=execute_sql($qry,0,1000,$nr);
			$current_lists=array(); // Array of favritelistnames where the track is in already
			$i=0;
			if ($nr>0) {
				echo '<table class="ampjuke_content_table"><tr><td><strong>';
				echo xlate('This track is currently in').':</strong></td></tr>';
				while ($row=mysql_fetch_array($result)) {
					echo '<tr><td>'.$row['fav_name'].add_delete_link('favoriteid',$row['id'],$row['fav_name'],1);
					echo '</td></tr>';
					$current_lists[$i]=$row['fav_name'];
					$i++;
				}
				echo '<table>';
			}
			// If we're allowed to get suggestions based on tags AND we have the option enabled in the personal settings...
			if (($lastfm_allow_favorite_suggestion=='1') && ($u['ask4favoritelist_disp_suggestion']=='1')) {
				$track=get_track_extras($id);
				$perf=get_performer_name($track['performer_id']);
				require('lastfm_lib.php');
				//$toptags=lastfm_track_get_toptags($id,$perf,$track['name']);
				$toptags=lastfm_get_toptags('track',$id,$perf,$track['name']);
				$x=0;
				if ((is_array($toptags)) && (count($toptags)>0)) {
					$disp_buf=0;
					$buf='<table class="ampjuke_content_table"><tr><td><strong>'.xlate('Suggestions').':</strong></td></tr>';			
					while ($x<count($toptags)) {
						// Do we have this track added to a fav.list w. same name as the suggested toptag from last.fm ?					
						$found=in_array($toptags[$x],$current_lists); 
						if ($found==0) { // ...no, we dont - offer option to add it:
							$disp_buf=1;
							$buf.='<tr><td>'.$toptags[$x].'<a href="./add2fav.php?what=track&id='.$id.'&suggestion='.$toptags[$x];
							$buf.='">'.get_icon($_SESSION['icon_dir'],'favorite_add','').'</a></td></tr>';
						}
						$x++;
					}
					$buf.='</table>';
					if ($disp_buf==1) { // We're faced with some suggestions (names of favorites) we haven't used, yet:
						echo $buf;
					}
				}
				// 0.8.7: New: Do we want to show/display related tracks (based on Echonest values) ?
				if (($echonest_enabled=='1') && (echonest_get_track_status($id)>0)) { // well...we do! 
					require_once('set_td_colors.php');
					$tdnorm='';
					$tdalt='';
					$tdhighlight='';			
					$r=get_track_extras($id);
					$related_tracks=echonest_get_related_tracks($r,5,date('U'));
					if (strlen($related_tracks)>2) { // Yes - something was found:
						$related_track=explode(',',$related_tracks);
						$i=0;
						echo '&nbsp<br><table class="ampjuke_content_table"><tr><td colspan="5" align="center">';
						echo xlate('You might also like').'...</td></tr>';
						echo '<tr><td><b>'.xlate('Title').'</td><td><b>'.xlate('Performer').'</td><td align="right"><b>'.xlate('Duration').'</td>';
						echo '<td align="right"><b>'.xlate('Year').'</td>';
						echo '<td> </td>';
						while ($i<sizeof($related_track)-1) {
							$t=get_track_extras(trim($related_track[$i]));
							if (is_array($t)) {
								$p=get_performer_name_track($t['performer_id'],$t['album_id'],$perf_name,$dummy);
								fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
								echo '<td>'.add_play_link('play',$related_track[$i],$t['name']).'</td>';
								// Performer link - clicking on it will display the performer & close the popup:
								echo '<td><a href="javascript:loadinparent(';
								$url='./index.php?what=performerid&start=0&count=15&special='.$t['performer_id'];
								echo "'".$url."'";
								echo ', true);" onClick="javascipt:window.close();">';
								echo $perf_name.'</a></td>';
								// The default code for performer:
								//	echo '<td>'.$perf_name.'</td>';
								display_duration($t['duration']);
								display_last_played($t['year']);
								// Same method (loadinparent) used when clicking on add2favorite:
								echo '<td class="content" align="right">'; 
								$url='./add2fav.php?what=track&id='.$t['id'].'&picker=1';
								echo '<a href="javascript:loadinparent(';
								echo "'".$url."'";
								echo ', true);" onClick="javascipt:window.close();">';
								echo get_icon($_SESSION['icon_dir'],'favorite_add','');
								echo '</a></td>';
								echo '</tr>';
							}
							$i++;
						}
						echo '</table>';
					}
				} // 0.8.7: ...ends				
			} // ...lastfm_allow_favorite_suggestions
		} // 0.8.2: Add TRACK suggestion ends...on to...
		
		// ...artist/performer suggestion:
		if (($lastfm_allow_favorite_suggestion=='1') && ($_SESSION['ask4favoritelist_disp_suggestion']=='1')) {
			if ($what=='performerid') {
				$perf=get_performer_name($id);
				require('lastfm_lib.php');
				//$toptags=lastfm_performer_get_toptags($id,$perf);
				$toptags=lastfm_get_toptags('performer',$id,$perf,'');
				$x=0;
				if ((is_array($toptags)) && (count($toptags)>0)) {
					echo '<table class="ampjuke_content_table">';
					echo '<tr><td>'.xlate('Suggestions').':</td></tr>';			
					while ($x<count($toptags)) {
						echo '<tr><td>'.$toptags[$x].'<a href="./add2fav.php?what=performerid&id='.$id.'&suggestion='.$toptags[$x];
						echo '">'.get_icon($_SESSION['icon_dir'],'favorite_add','').'</a></td></tr>';
						$x++;
					}
					echo '</table>';
				}
				// 0.8.7: Display related performers, if any:
				$total_related_performers=lastfm_get_number_of_related_performers($id,urlencode($perf),
				$lastfm_min_related_match,$lastfm_max_related_artists);
				if ($total_related_performers>$lastfm_max_related_artists) {
					$total_related_performers=$lastfm_max_related_artists;
				}					
				if ($total_related_performers>0) {
					require_once('set_td_colors.php');
					$tdnorm='';
					$tdalt='';
					$tdhighlight='';			
					$n=0;
					$xml=retrieve_xml('./lastfm/'.$id.'.xml',$n,$lastfm_max_related_artists);
					echo '&nbsp<br><table class="ampjuke_content_table"><tr><td align="center">';
					echo xlate('You might also like').'...</td></tr>';
					while ($n<$lastfm_max_related_artists) {
						if (isset($xml->similarartists->artist[$n]->name[0])) {
							$pid=get_performer_id_by_name(mysql_escape_string($xml->similarartists->artist[$n]->name[0]));
							if ($pid<>1) {
								fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
								// Performer link - clicking on it will display the performer & close the popup:
								echo '<td><a href="javascript:loadinparent(';
								$url='./index.php?what=performerid&start=0&count=15&special='.$pid;
								echo "'".$url."'";
								echo ', true);" onClick="javascipt:window.close();">';
								echo $xml->similarartists->artist[$n]->name[0].'</a></td>';
							}
						}	
						$n++;
					}
					echo '</table>';
				} // 0.8.7: ends...		
			} // what==performerid
		} // if lastfm_allow_favorite_suggestion...
		// 0.8.2: ..we might (also) want one or more suggestions from last.fm when adding an album to a favorite list:
		if (($lastfm_allow_favorite_suggestion=='1') && ($_SESSION['ask4favoritelist_disp_suggestion']=='1')) {
			if ($what=='albumid') {
				$aname=str_replace('[','',get_album_name($id));
				$aname=str_replace(']','',$aname);
				$perf=get_performer_name_album($id);
				require('lastfm_lib.php');
				//$toptags=lastfm_album_get_toptags($id,$perf,$aname);
				$toptags=lastfm_get_toptags('album',$id,$perf,$aname);
				$x=0;
				if ((is_array($toptags)) && (count($toptags)>0)) {
					echo '<table class="ampjuke_content_table">';
					echo '<tr><td>'.xlate('Suggestions').':</td></tr>';			
					while ($x<count($toptags)) {
						echo '<tr><td>'.$toptags[$x].'<a href="./add2fav.php?what=albumid&id='.$id.'&suggestion='.$toptags[$x];
						echo '">'.get_icon($_SESSION['icon_dir'],'favorite_add','').'</a></td></tr>';
						$x++;
					}
					echo '</table>';
				}			
			}
		}
		// 0.8.7: New stuff: Adding an advanced search result to a favorite liste:
		if ($what=='advsearch') {
			echo '<table class="ampjuke_content_table">';
			echo '<tr><td> ';
			$qry=get_advanced_search_query('track.last_played','ASC');
			$result=execute_sql($qry,0,500,$nr);
			echo xlate('Advanced search').': <b>'.$nr.'</b> '.strtolower(xlate('Tracks'));
			echo '</td></tr></table>';
		} // advsearch
		die();
	} // if picker==2
	if ($picker==3) {
		// 0.8.7: Do we want to keep the pop-up open ?
		if (!isset($_POST['keep_open'])) {
			// Nope... Just do as usual: close the pop-up: 		
			echo '<script type="text/javascript" language="javascript">';
			echo 'self.close();';
			echo '</script>';
		} // 0.8.7
		if (isset($_POST['favoritelistname'])) {
			$favlist=$_POST['favoritelistname'];
		}
	}				
}	

// 0.5.2: Add stuff to a shared list ?
$uid=get_user_id($_SESSION['login']); // this is the default
$qry="SELECT * FROM fav_shares WHERE share_id='".$uid."'";
$qry.=" AND fav_name='".$favlist."'";
$result=execute_sql($qry,0,10,$x);
if ($x<>0) { // yes: we're adding to a shared list -> change uid:
	$row=mysql_fetch_array($result);
	$uid=$row['owner_id'];
}	
	
if ($what=='track') {
	add_tr($id,$uid,$favlist);
}
	
if ($what=='albumid') {
	$qry="SELECT id FROM track WHERE album_id=".$id;
	$result=execute_sql($qry,0,1000000,$nr);
	while ($row=mysql_fetch_array($result)) {
		add_tr($row['id'],$uid,$favlist);
	}
}	
	
if ($what=='performerid') {
	$qry="SELECT id FROM track WHERE performer_id=".$id;
	$result=execute_sql($qry,0,100000,$nr);
	while ($row=mysql_fetch_array($result)) {
		add_tr($row['id'],$uid,$favlist);
	}
}		
	
if ($what=='yearid') {
	$qry="SELECT id FROM track WHERE year=".$id;
	$result=execute_sql($qry,0,10000000,$nr);
	while ($row=mysql_fetch_array($result)) {
		add_tr($row['id'],$uid,$favlist);
	}		
}	

// 0.8.7: Adding adv search results:
if ($what=='advsearch') {
	$qry=get_advanced_search_query('track.last_played','ASC');
	$result=execute_sql($qry,0,250,$nr);
	while ($row=mysql_fetch_array($result)) {
		add_tr($row['id'],$uid,$favlist);
	}
}

// 0.8.7:
if (isset($_POST['keep_open'])) {
	/*
	$loc=$base_http_prog_dir.'/add2fav.php?picker=2&what='.$what.'&keep_open=1&id='.$id;
	echo '<script type="text/javascript" language="javascript">';	 		 	
	echo "history.go(-1);";
	echo 'var rw = window.open("'.$loc.'","AmpJuke_Picker","width=600,height=250,resizable=yes,scrollbar=yes");';
	echo '</script>';
	*/
	$loc=$base_http_prog_dir.'/add2fav.php?picker=2&what='.$what.'&keep_open=1&id='.$id;
	echo '<script type="text/javascript" language="javascript">';	 		 	
	echo 'window.location.replace("';
	echo $loc;
	echo '");';
	echo '</script>';
	die();
}

// 0.7.8: We have picked a fav.list already: just step back one page:
echo '<script type="text/javascript" language="javascript">'; 
echo "history.go(-1);";
echo '</script>';  
?>
