<?php
/*
DISP.PHP: "Library" of supporting functions for the various DISP_....PHP scripts.
*/

function add2fav_link($t,$l,$hide_txt='0') { // Return link to add something to a specific favorite list. 0.8.4: hide_txt introduced
	$ret='<a href="add2fav.php'.$l.'" class="tooltip"'; // title="'.$_SESSION['favoritelistname'].'"';
	$ret.=' title="'.xlate('Add to favorite').'">'; // 0.8.4: title...
	$ret.=get_icon($_SESSION['icon_dir'],'favorite_add',''); // 0.8.4: Moved here - the icon mu be within the link	
	if ($t=="") {
		if ($hide_txt<>'1') { // 0.8.4
			$ret.=xlate("Add to favorite");
		}
	} else {
		$ret.=get_icon($_SESSION['icon_dir'],'favorite_add',$t);
	}	
	$ret.='</a>';
	return $ret; 
}	


function add2fav_picker($t,$l,$hide_txt='0') { // Returns link to "add to favorite..." (using pop-up flag). 0.8.4: hide_txt introduced
	$ret='<a href="add2fav.php'.$l.'&picker=1"';
	$ret.=' class="tooltip" title="'.xlate('Add to favorite').'...">';	
	$ret.=get_icon($_SESSION['icon_dir'],'favorite_add',''); // 0.8.4: Moved here - the icon mu be within the link	
	if ($t=="") {
		if ($hide_txt<>'1') {
			$ret.=xlate("Add to favorite").'...';
		}
	} else {
		$ret.=get_icon($_SESSION['icon_dir'],'favorite_add',$t);
	}	
	$ret.='</a>';
	return $ret; 
}	


function add_add2fav_link($what,$id,$hide_txt='0') {  // Return a link to add something to a favorite list (uses add2fav_picker & add2fav_link). 0.8.4: hide_txt introduced (see above)
 	$ret="";
	if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) || 
			($_SESSION['ask4favoritelist']=="1")) {
//		$ret='<td class="content" align="right">';	 
		if ($_SESSION['ask4favoritelist']=="1") {
			$ret.=add2fav_picker('','?what='.$what.'&id='.$id,$hide_txt);		
		} else {			
			$ret.=add2fav_link('','?what='.$what.'&id='.$id,$hide_txt);
		}
//		$ret.='</td>';	
	}	
	return $ret;
}


function add_album_link($n,$id,$disp_small_images = '0') { // Returns link to a specific album (id):
	if (!isset($_SESSION['count'])) { $_SESSION['count']=25; } // 0.6.6: "Tweaked"
	$ret='<td class="content">';
	$ret.='<a href="index.php?what=albumid&start=0&count='.$_SESSION['count'];
    $ret.='&special='.$id.'&order_by=track.track_no"';
	// 0.7.3: Add a "tooltip" w. album-cover ?
	if (($disp_small_images=="1") && (file_exists('./covers/'.$id.'.jpg'))) {
		include('db.php'); // 0.8.4: b/c of popout_width + ...height
	 	//$ret.=' class="tip"><span><img src="./covers/'.$id.'.jpg" width="'.$popout_width.'" height="'.$popout_height.'"></span>';
		// 0.8.7:
		$ret.=' class="tooltip" title="';
		$ret.='<img src=./covers/'.$id.'.jpg width='.$popout_width.' height='.$popout_height.'><br>'.get_album_tracklist($id).'">';	 	
	} else { $ret.='>'; }	
	$ret.=$n.'</a></td>';
	return $ret;
}	


function add_checkbox($name,$checked = '') { // Return <input type="checkbox-thingy w. name and optional marked as selected. FORMS:
	$ret='<input type="checkbox" class="tfield" name="'.$name.'"';
	if (($checked!='') && ($checked<>'0')) {
		$ret.=' checked';
	}
	$ret.='>';
	return $ret;
}		


function add_delete_link($what,$id,$special,$return_link_only=0,$hide_txt='0') { // 0.8.2: Added the optional "return_link_only" parameter. 0.8.4: hide_txt introduced
	$ret='';
	if ($return_link_only==0) {
		$ret.='<td align="right" class="content">';
	}
	$ret.='<a href="delete.php?what='.$what.'&id='.$id.'&fav_name='.$special.'" title="'.xlate('Delete').'">'; // 0.8.4: added title="...
	// 0.7.0: Find 'appropriate' icon for the delete-operation:
	$wi='delete';
	if ($what=='queue') { $wi='queue_remove'; }
	if ($what=='favoriteid') { $wi='favorite_remove'; }
	$ret.=get_icon($_SESSION['icon_dir'],$wi,'');
		if ($hide_txt<>'1') { // 0.8.4
		$ret.=xlate('Delete');
	}
	$ret.='</a>';
	if ($return_link_only==0) {
		$ret.='</td>';
		return $ret;
	} else {
		return $ret;
	}
}


function add_download_link($what_to_download,$l,$id,$hide_txt='0') { // Display a link to download something. 0.8.4: hide_txt introduced
	if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
// 0.8.4: "removed":		echo '<td class=content" align="right">';
		echo disp_download($what_to_download,$l,$id,'',$hide_txt);
// 0.8.4		echo '</td>';
	}		
}

function add_edit_link($what_to_edit,$id,$hide_txt='0') { // Return link to edit something incl, the exact id-number of that.
	$ret="";
	if (($_SESSION['show_ids']=="1") && ($what_to_edit<>"favorite")) {
	/*
		if ($no_tds<>'0') { // 0.8.4
			$ret='<td class="content">';
		}
	*/
		if ($_SESSION['admin']=="1") { // offer option to edit by displaying ID-nr. as ref.:
			$ret.='<a href="index.php?what=edit&edit='.$what_to_edit.'&id='.$id.'" title="'.xlate('Edit').'">'; // 0.8.4: added: title="...
			$ret.=$id.'</a>';
		} else {
			$ret.=$id;
		}
	}	
	if ($what_to_edit=="favorite") {
	/*
		if ($no_tds<>'0') {
			$ret='<td class="content">';
		}
	*/	
		$ret.='<a href="index.php?what=edit&edit='.$what_to_edit.'&id='.$id.'" title="'.xlate('Edit').' '.$id.'">'; // 0.8.4: added: title="...
		$ret.=get_icon($_SESSION['icon_dir'],'edit','');
		if ($hide_txt<>'1') { // 0.8.4
			$ret.=xlate('Edit');
		}
		$ret.='</a>';
	/*
		if ($no_tds<>'0') { // 0.8.4
			$ret.='</td>';
		}
	*/
	}	
	return $ret;
}

 
function add_edit_link_tags($id,$same_tab=0) { // 0.8.5: Introduced: Returns a link to edit the tags of a specific track/file:
	$ret='';
	if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1') && ($_SESSION['admin']=='1')) {
		$qry="SELECT id,path FROM track WHERE id=".$id;
		$result=execute_sql($qry,0,1,$nr);
		if ($nr==1) {
			$row=mysql_fetch_array($result);
			if (is_writable($row['path'])) {
				if (!isset($base_http_prog_dir)) {
					include('db.php');
				}
				$ret.='<a href="'.$base_http_prog_dir.'/id3tag/?filename=';		
				$ret.=urlencode($row['path']).'" ';
				if ($same_tab<>0) { 
					$ret.='target="_blank"';
				}
				$ret.='>'.xlate('Edit tags').'</a>';
			} else {
				$ret.=''; 
			}
		}
	}
	return $ret;
}


function add_faq($id, $opt_txt='', $force_display=0) { // Return link to Amjuke FAQ. 0.8.3: Added $opt_txt+$force_disp.
	$ret='';
	if (((isset($_SESSION['disp_help'])) && ($_SESSION['disp_help']=="1")) || ($force_display==1)) {
//		$ret='<a href="http://www.ampjuke.org/faq.php?q_id='.$id.'" target="_blank"'; 0.8.3: Changed to:
		$ret='<a href="http://www.ampjuke.org/?id=faq'.$id.'" target="_blank"';
		$ret.=' title="Help will open in a new window">';
		$ret.='<img src="./ampjukeicons/icon_question.gif" border="0">'.$opt_txt.'</a>';
	}
	return $ret;
}


function add_lyrics_link($id,$hide_txt='0') { // Display link to link to a specific track (id) w. lyrics (0.8.2: Is this still used??). 0.8.4: hide_txt introduced
	if (($_SESSION['disp_lyrics']=="1")) {
//		echo '<td class="content" align="right">';
		echo disp_lyrics($id,$hide_txt); // 0.8.4
	} 
}


function add_performer_link($n,$id,$disp_small_images = '0') { // Return a link to a specific performer (id):
	if (!isset($_SESSION['count'])) { $_SESSION['count']=25; } // 0.6.6: "Tweaked"	
	$ret='<a href="index.php?what=performerid&start=0&count=';
	$ret.=$_SESSION['count'].'&special='.$id.'"';
	if (($disp_small_images=="1") && (file_exists('./lastfm/'.$id.'.jpg'))) {
		//$ret.=' class="tip"><span><img src="./lastfm/'.$id.'.jpg"></span>';
		// 0.8.7:
		include('db.php');
		$ret.=' class="tooltip" title="';
		$ret.='<img src=./lastfm/'.$id.'.jpg width='.$popout_width.' height='.$popout_height.'>">';
        touch('./lastfm/'.$id.'.jpg'); // 0.8.6: Extend lifetime
	} else { $ret.='>'; }
	$ret.=$n.'</a>';
	return $ret;
}	


function add_play_enqueue_link($playtext,$what,$id,$name,$order_by,$dir,$graphic_included,$hide_txt='1') { // Returns link to play/queue something. 0.8.4: hide_txt introduced
// 0.8.6: If we're in radio station / jukebox mode, then don't offer to do anything (ie. return empty string/link):
    $ret='';
    include('db.php');
    if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {
    	$text=xlate($playtext.' all tracks with').' <b>'.$name.'</b>';
	    if ($what=="albumid") {
	    	$text=xlate($playtext.' all tracks from').' <b>'.$name.'</b>';
    	}	
	    if (($what=="yearid") || ($what=="favorite_list")) {
	    	$text=xlate($playtext.' all tracks from').' <b>'.$name.'</b>';
	    }	
    	$ret='<a href="play_action.php?act=playall';
	    $ret.='&what='.$what.'&id='.$id;
    	$ret.='&order_by='.$order_by.'&dir='.$dir.'" class="tooltip" title="'.my_filter_var($text).'">'; // 0.8.7: tooltip

	    // 0.8.4: Hide the text no matter what ?
	    if ($hide_txt=='1') {
	    	$text='';
    	}
	
	    if ($graphic_included=="1") {
	    	$ret.=get_icon($_SESSION['icon_dir'],$playtext,$text).'</a>';
	    } else {
	    	$ret.=get_icon($_SESSION['icon_dir'],$playtext,$text).'</a>';
    	}
    } else { // 0.8.6
        $ret=$name;
    }
	return $ret;
}	


function add_play_link($action,$id,$link_name) { // Return link to play|queue something (id). Use link_name as label:
// 0.8.6: If we're in radio station mode, then don't return a link to play_action.
// 0.8.6: Added call to stripslashes()
    $ret='';
    include('db.php');
    if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {
        $ret='<a href="play_action.php?act='.$action.'&id='.$id.'">'.stripslashes($link_name).'</a>';
    } else {  // 0.8.6: ... return a _request_ link
        if ($jukebox_mode_msg_popup_enabled<>'1') {
            $ret='<a href="request.php?id='.$id.'">'.stripslashes($link_name).'</a>';
        } else {
            $ret='<a href="request.php?id='.$id.'&picker=1">'.stripslashes($link_name).'</a>';
        }
    }
    return $ret;
}	


function add_radio($name,$value,$checked = '') { // Return <input type="radio-thingy w. name, value and optional mark option as checked. FORMS:
 	$ret='<input type="radio" name="'.$name.'" value="'.$value.'"';
 	$ret.=' class="tfield"';
 	if ($checked<>'') {
 		$ret.=' checked';
 	}
	$ret.='>';
	return $ret;
}		


function add_select_option($value,$disp_name,$selected = '') { // Return '<OPTION>-option value w. 'disp_name' as label and optional mark as selected. FORMS:
	$ret='<OPTION VALUE="'.$value.'"';
	if ($selected<>'') {
		$ret.=' selected';
	}
	$ret.='>'.$disp_name.'</OPTION>';
	$ret.="\n";
	return $ret;
}


function add_textinput($name,$value = '', $size = '') { // Return a input type="text"-thingy w. optional default value and optional size. FORMS:
 	$ret='<input type="text" class="tfield" name="'.$name.'" value="'.$value.'" size="'.$size.'"';
 	$ret.='>';
 	return $ret;
}	

function add_textinput_password($name,$value = '', $size = '') { // 0.8.3: Return a input type="password"-thingy w. optional default value and optional size. FORMS:
 	$ret='<input type="password" class="tfield" name="'.$name.'" value="'.$value.'" size="'.$size.'"';
 	$ret.=' class="tfield">';
 	return $ret;
}	
	
function add_tr($id,$uid,$favlist) { // 0.8.2: Moved to disp.php. Adds a TRACK (id) to a favorite (favlist) belonging to user-id (uid):
 	$ok=1;
	// 0.8.2: Modified: Get 'avoid_duplicate_entries' directly from user-table rather than from $_SESSION:
	$u=get_user_details($uid);
	// Avoid duplicate entries ?
	if ($u['avoid_duplicate_entries']=="1") { 
		$qry="SELECT * FROM fav WHERE track_id='".$id."'";
		$qry.=" AND user_id='".$uid."' AND fav_name='".$favlist."'";
		$r=execute_sql($qry,0,10,$nr);
//		mydebug('add_tr',$qry);
		if ($nr!=0) { // it's already there: Don't add it
			$ok=0;
//			mydebug('add_tr','OK=0');
		}
	}		
	// 0.8.3: If we DO NOT allow automatic creation of *new* favorite lists, then check to see if the favorite list exists already:
	if (($ok==1) && ($u['auto_add2favorite_create_new']=='0')) {
		//mydebug('add_tr',$id.' auto_add2favorite_create_new=0');
		$qry="SELECT user_id,fav_name FROM fav WHERE user_id='".$uid."' AND fav_name='".$favlist."'";
		$r=execute_sql($qry,0,1,$nr);
		if ($nr==0) { // The favorite list doesn't exist, and we will not create one automatically:
			//mydebug('add_tr',$favlist.' not found + will not be created for track_id='.$id);
			$ok=0;
		}
	}
	
	if ($ok==1) {	
//		mydebug('add_tr','OK=1');
		$pid=get_performer_id($id);
		$aid=get_album_id($id);
		$r=get_track_extras($id);
		$qry="INSERT INTO fav (track_id, performer_id, album_id, name, duration,";
		$qry.=" last_played, times_played, year, user_id, fav_name) VALUES";
		$qry.=" ('".$id."', '".$pid."', '".$aid."', ";
		$qry.='"'.$r['name'].'"';
		$qry.=", '".$r['duration']."', ";
		$qry.="'".$r['last_played']."', '".$r['times_played']."', ";
		$qry.="'".$r['year']."', '".$uid."', '".$favlist."')";
		$r=execute_sql($qry,0,-1,$nr);
	}	
}	

function add_year_link($n,$id,$align='') { // Return a link to a specific year (id):
	if (!isset($_SESSION['count'])) { $_SESSION['count']=25; } // 0.6.6: "Tweaked"
	$ret='<td class="content"';
    if ($align<>'') {
        $ret.=' align="'.$align.'"';
    }
	$ret.='><a href="index.php?what=yearid&start=0&count='.$_SESSION['count'];
    $ret.='&special='.$id.'&dir=DESC&order_by=track.id">';
	$ret.=$n.'</a></td>';
	return $ret;
}	


function check_recently_played($pid,$num,$debug='0') { // 0.8.7: Checks recently $num played tracks for $pid:
    $debug=1;
    $ret=0; // nothing found
    $qry="SELECT * FROM track ORDER by last_played DESC";
    $result=execute_sql($qry,0,$num,$nr);
    if ((isset($debug)) && ($debug=='1')) {
        mydebug('disp.php','Check recently played performers using: '.$qry.' -> nr='.$nr);
    }
    while (($ret==0) && ($row=mysql_fetch_array($result))) {
       if ((isset($debug)) && ($debug=='1')) {
            mydebug('disp.php','Comparing: '.$pid.' with '.$row['performer_id']);
       }        
  
       if ($row['performer_id']==$pid) { // Found one
            $ret=1;
        }
    }
    
      if ((isset($debug)) && ($debug=='1')) {
            mydebug('disp.php','Finsihed comparing. Returnvalue is: '.$ret);
        }        
      
    return $ret;
}

function check_recently_played_performer($pid,$jukebox_mode_min_age_performer,$debug='0') { // 0.8.8: Checks recently played tracks for performer $pid:
    //$debug=1;
    $ret=0; // nothing found -> which is OK
    // Convert "...min_age_..." to seconds:
    $jukebox_mode_min_age_performer=$jukebox_mode_min_age_performer * 3600;
    // Calculate timestamp in the past:
    $before=date('U') - $jukebox_mode_min_age_performer;
    
    $count=0;
    $qry="SELECT * FROM track WHERE last_played>'".$before."' AND performer_id='".$pid."'"; // AND performer_id<>0";
    $result=execute_sql($qry,0,10000,$nr);
//    mydebug('disp.php','check_recently_played_performer:'.$qry.' Found:'.$nr);
    if ($nr<>0) { 
        $ret=1;
    }
    
    if ((isset($debug)) && ($debug=='1')) {
        mydebug('disp.php','Was artist/performer '.get_performer_name($pid).' played within last '.($jukebox_mode_min_age_performer / 3600).' hours ? Answer:'.$ret.' (0=No; 1=Yes)');
    }        
      
    return $ret;
}

function cpy_file_to_tmp($src,$dest,$tmp_dir,$kext) { // Copy a file to from $src to $dest in ./tmp
	if (!file_exists($tmp_dir.$dest)) {
		copy($src,$tmp_dir.$dest);
	} else { // It does actually exist already - extend the lifetime:
		touch($tmp_dir.$dest);
	}	
}	



function display_duration($duration) { // Displays $duration: (0.8.2: Is it used anymore?)
	if ($_SESSION['disp_duration']=="1") {
		echo '<td class="content" align="right">'.$duration.'</td>';
	}
}


function display_last_played($last_played) { // Display when something was played last time:
	if ($_SESSION['disp_last_played']=="1") {
		echo '<td class="content" align="right">'.mydate($last_played).'</td>';
	}
}


function display_times_played($times_played) { // Display how many times a track has been played:
	if ($_SESSION['disp_times_played']=="1") {
		echo '<td class="content" align="right">'.$times_played.'</td>';
	}	
}


function disp_download($what_to_download,$name,$download_id,$graphic_included,$hide_txt='0') { // Returns complete link to download something. 0.8.4: hide_txt introduced
// 0.3.6: disp_download: offer the option to download something:
	if ($_SESSION['disp_download']=="0") { 
		$ret=""; 
	} else {
		$ret='<a href="download.php?type=';
		if ($what_to_download=="album") {
			$ret.='album';
		}
		if ($what_to_download=="favorite_list") {
			$ret.="favorite_list";
		}	
		if ($what_to_download=="year") {
			$ret.="year";
		}
		if ($what_to_download=="track") {
			$ret.="track";
		}	
		if ($what_to_download=="performer") {
			$ret.="performer";
		}		
		if ($what_to_download=="queue") {
			$ret.="queue";
		}	
		$ret.='&download_id='.$download_id.'" title="'.xlate('Download').' '.$name.'">'; // 0.8.4: title introduced
		// 0.6.0: Rewritten these 3-4 lines of rather shitty code...
		// 0.7.0: WTF is graphic_included ??
		if ($graphic_included=="1") {
			$ret.=get_icon($_SESSION['icon_dir'],'download','');
		} else {
			$ret.=get_icon($_SESSION['icon_dir'],'download','');
		}
		if ($hide_txt<>'1') { // 0.8.4
			$ret.=xlate('Download').' <b>'.$name.'</b>';
		}
		$ret.='</a>';
	} // if disp_download=1
	return $ret;
}			


function disp_favorite_lists($user,$opt) { // display favorite lists (used in adm./selection of fav. lists):
	$qry="SELECT DISTINCT fav_name FROM fav WHERE user_id='".get_user_id($_SESSION['login']);
	$qry.="' ORDER BY fav_name";
	$result=execute_sql($qry,0,1000000,$nr);
	
	echo std_table("ampjuke_content_table","ampjuke_content");
	echo '<tr><td valign="top" class="content">';

	if ($nr>0) { // do we have one or more favorite lists:
		echo '<FORM name="fav_list" METHOD="POST" action="create_favoritelist.php">';
		if ($opt=="1") {
			echo xlate('Copy the queue to the favorite list').':';
		} else {
			echo xlate("Select a favorite list").':';
		}		
		if ($opt=="1") { // we're offering to COPY:
			echo '<input type="hidden" name="copy" value="1">'; 
		}	
		echo '<SELECT NAME="favoritelistname" class="tfield" ONCHANGE="Javascript:submit()">';
		echo '<OPTION VALUE="" selected>---</OPTION>';
		while ($row=mysql_fetch_array($result)) {
			echo '<OPTION VALUE="'.$row['fav_name'].'">'.$row['fav_name'].'</OPTION>';
		}
		// 0.5.2: Added the option to select shared fav.lists...
		// 0.8.3: ...but ONLY show this, if shared_favorites_allow is turned on:
		if (($_SESSION['disp_fav_shares']=="1") && (isset($shared_favorites_allow)) && ($shared_favorites_allow=='1')) {
			$qry="SELECT * FROM fav_shares WHERE share_id='".get_user_id($_SESSION['login'])."'";
			$qry.=" ORDER BY fav_name";
			$result=execute_sql($qry,0,1000000,$x);
			while ($row=mysql_fetch_array($result)) {
				echo '<OPTION value="'.$row['fav_name'].'">('.xlate("Shared").') '.$row['fav_name'];
				echo '</OPTION>';
			}
		}	
		echo '</SELECT></form>';	
	} 

	echo ' </td>';

	if ($opt!="1") { // we're offering to create a new favorite list:
		echo '</tr><tr><form name="c_form" method="POST" action="create_favoritelist.php">';
		echo '<td valign="top" class="content">'.add_faq(76).' '.xlate("Create new");
        echo ' :'.add_textinput('new_favlist','',20);
        // 0.8.1: Uuuweee - offer to insert tracks automatically using last.fm's API (tag.getTopArtists method):
        echo ' '.xlate('Use tags').': '.add_textinput('tags','',40);
        echo '<input type="submit" class="tfield" value="'.xlate('Save & continue').'">';
 		echo '</form>';
		echo '</td></tr>';
	}	
	echo '</table>';
}	

// 0.5.5: Basically a copy of disp_favorite_lists...with a twist...
function disp_fav_list_picker($user,$therest,$keep_open = '') { // 0.8.7: Added $keep_open
 	parse_str($_SERVER["QUERY_STRING"]); 
 	require("translate.php");
 	echo '<html><head><title>'.xlate("Add to favorite").'</title>'; 
	echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'">'; 
	echo '</head><body>';
	echo '<table class="ampjuke_content_table"><tr><td>';
	// get some text to display:
	if ($what=="track") {
		$row=get_track_extras($id);
		echo xlate("Track").': <b>'.get_performer_name($row['performer_id']).' - '.$row['name'];
	}
	if ($what=="albumid") {
		$s=get_album_name($id);
		$s=str_replace("]","",$s);
		$s=str_replace("[","",$s);
		echo xlate("Album").': <b>'.$s;
	}	
	if ($what=="performerid") {
	 	echo xlate("Performer").': <b>'.get_performer_name($id);
	}	
	if ($what=="yearid") {
		echo xlate("Year").': <b>'.$id;
	}	
	if ($what=='advsearch') {
		echo xlate('Advanced search');
	}
	print "</b></td></tr><tr><td> \n\n\n";	
 	include_once("db.php");
 	include_once("sql.php");
 	include_once("translate.php");
	$qry="SELECT DISTINCT fav_name FROM fav WHERE user_id='".get_user_id($_SESSION['login']);
	$qry.="' ORDER BY fav_name";
	$result=execute_sql($qry,0,1000000,$nr);
	echo '<tr><td valign="top" class="content">';
	if (isset($picker)) {
		$picker++;
	}	
	if (($nr>0) || ($what=='advsearch')) { // do we have one or more favorite lists. 0.8.7: Added advsearch
		echo '<FORM name="fav_list" METHOD="POST" action="add2fav.php?what=';
		echo $what.'&id='.$id.'&picker='.$picker.'">';	
		echo xlate("Select a favorite list").':';
		echo '<SELECT NAME="favoritelistname" class="tfield" ONCHANGE="Javascript:submit()">';
		echo '<OPTION VALUE="" selected>---</OPTION>';
		while ($row=mysql_fetch_array($result)) {
			echo '<OPTION VALUE="'.$row['fav_name'].'">'.$row['fav_name'].'</OPTION>';
		}
		// Shared fav.lists:
		if ($_SESSION['disp_fav_shares']=="1") {
			$qry="SELECT * FROM fav_shares WHERE share_id='".get_user_id($_SESSION['login'])."'";
			$qry.=" ORDER BY fav_name";
			$result=execute_sql($qry,0,1000000,$x);
			while ($row=mysql_fetch_array($result)) {
				echo '<OPTION value="'.$row['fav_name'].'">('.xlate("Shared").') ';
				echo $row['fav_name'].'</OPTION>';
			}
		}	
		echo '</SELECT>';
		// 0.8.7: Offer to keep the pop-up open:
		echo ' '.add_checkbox('keep_open',$keep_open).xlate('Keep selection open');
		echo '</form>';	
	} 
	echo '</td></tr></table>'; // close content table 
}


function disp_headline_actions($what) { // 0.6.0: Display 'empty' headers for disp_...: performer, album
// 0.8.4: Just get rid of this: Have each disp... deal with <th>'s themselves:
	$ret="";
/*
	if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
		|| ($_SESSION['ask4favoritelist']=="1") ) {
		$ret.='<th class="tbl_header"> </th>';
	}

	if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
		$ret.='<th class="tbl_header"> </th>';
	}	

	if ($what!='track') {
		$ret.='<th class="tbl_header"> </th>';
	}
	// 0.8.4: "removed":
	if (($what=='album') || ($what=='performer')) {
		$ret.='<th> </th><th> </th><th> </th>';
	}
*/
	return $ret;
}			


function disp_icon_picker($default) { // Displays select-list w. options for selecting specific icon-set:
	echo '<SELECT NAME="icon_dir" class="tfield">';
	echo '<OPTION VALUE="">'.xlate('No icons').'</OPTION>';
	$handle=fopen("./ampjukeicons/icon_ref.txt", "r");
    while (!feof($handle)) {
        $line=fgets($handle);
        $item=explode(";", $line);
        if (count($item)==2) {
            echo '<OPTION VALUE="'.$item[0].'"';
            if ($default==$item[0]) {
                echo ' selected';
            }
            echo '>'.$item[1].'</OPTION>';
        }
    }
    echo '</SELECT>';
    fclose($handle);
}


function disp_language_options($default) { // Displays a select-list w. options for selecting a language:
    echo '<SELECT NAME="lang" class="tfield">';
    $handle=fopen("./lang/languages.txt", "r");
    while (!feof($handle)) {
        $line=fgets($handle);
        $item=explode(";", $line);
        if (count($item)==2) {
            echo '<OPTION VALUE="'.$item[0].'"';
            if ($default==$item[0]) {
                echo ' selected';
            }
            echo '>'.$item[1].'</OPTION>';
        }
    }
    echo '</SELECT>';
    fclose($handle);
}


function disp_lyrics($id,$hide_txt='0') { // Returns link to get lyrics for a specific track (id). 0.8.4: hide_txt introduced
	if ($_SESSION['disp_lyrics']=="0") {
		$ret="";
	} else {		
		$ret='<a href="get_lyrics.php?id='.$id.'" target="blank" title="'.xlate('Lyrics').'">'; // 0.8.4
		$ret.=get_icon($_SESSION['icon_dir'],'lyrics','');
		if ($hide_txt<>'1') { // 0.8.4
			$ret.=xlate('Lyrics');
		}		
		$ret.='</a>';
	}		
	return $ret;
}		


function disp_theme_picker($default) { // Display select-option to pick a specific theme:
	echo '<SELECT NAME="cssfile" class="tfield">';
    $handle=fopen("./css/themes.txt", "r");
    while (!feof($handle)) {
        $line=fgets($handle);
        $item=explode(";", $line);
        if (count($item)==2) {
            echo '<OPTION VALUE="'.$item[0].'"';
            if ($default==$item[0]) {
                echo ' selected';
            }
            echo '>'.$item[1].'</OPTION>';
        }
    }
    echo '</SELECT>';
    fclose($handle);
}

function dskspace($dir,$max_hours) { // 0.8.2: Moved here. Remove stuff older than 'max_hours' from 'dir':
	$space=0;
	$tcount=0;
	$now=date("U");
	if (is_dir($dir)) {
		$dh=opendir($dir);
		while (($file=readdir($dh)) != false) {
			if ($file!="." && $file!=".." && $file!="index.php") {
				$space += filesize($dir.$file);
				if (filemtime($dir.$file)) {
                    $diff=$now-filemtime($dir.$file);
                    $diff=$diff/(60*60); // convert to hours
                    //echo $dir.$file.' '.$diff.' ';                  
                    if ($diff>$max_hours) {
                        //echo 'is old! ';
                        @unlink($dir.$file); // 0.8.8
                        /* 0.8.8: Removed
						if (is_writeable($dir.$file)) {
							@unlink($dir.$file);
  						} else {
							$tcount++;
                            echo ' ...and NOT writable';
						}
                        */
                	} 
				}
			}
		}
	}
	closedir($dh);
	return $tcount;
}				

 

function filter_link($fo,$val,$txt,$icon_dir) { // Used when displaying options in relation to filtering.
	$ret="";
    if ($fo!=$val) {
        $ret=' <img src="./ampjukeicons/mnu_arr.gif" border="0">';
        if ($val==0) {
         	$ret=get_icon($icon_dir,'filter_remove','');
            $ret.='<a href="change_disp_options.php?what=filter_tracks&set=0">';
			$ret.=xlate("Filter").':';
            $ret.=xlate($txt).'</a>&nbsp';
        }
        if ($val==1) {
         	$ret=get_icon($icon_dir,'filter_add','');
            $ret.='<a href="change_disp_options.php?what=filter_tracks&set=1">';
			$ret.=xlate("Filter").':';
            $ret.=xlate($txt).'</a>&nbsp';
        }
        if ($val==2) {
         	$ret=get_icon($icon_dir,'filter_add','');
            $ret.='<a href="change_disp_options.php?what=filter_tracks&set=2">';
			$ret.=xlate("Filter").':';
            $ret.=xlate($txt).'<a>';
        }
      }
	return $ret;
}


function generate_password_salt () { // 0.7.4: Generate password-salt:
	$ret='';
	$salt_length=rand(20,40);
	for ($i = 0; $i < $salt_length; $i++) {
		$ret.=chr(rand(65, 90));
	}
	return $ret;
}

function get_advanced_search_query($order_by='',$dir='') { // 0.8.8: Added liveness,speechiness,acousticness and valence
	$qry="SELECT track.id, track.name, track.performer_id, ";
	$qry.="track.duration, track.year, track.last_played, ";
	$qry.="track.times_played, track.path, ";
	$qry.="track.album_id, performer.pid, performer.pname";
	$qry.=" FROM track, performer ";
	$qry.="WHERE track.performer_id=performer.pid";
//	$t='Parameters: ';
	// Tempo/BPM:
	if ($_SESSION['use_tempo']=='1') {
		$qry.=" AND (echonest_tempo>=".$_SESSION['tempo_min']." AND echonest_tempo<=".$_SESSION['tempo_max'].")";
//		$t.='Tempo/BPM:'.$_SESSION['tempo_min'].'-'.$_SESSION['tempo_max'].'  ';
	}
	// Danceability:
	if ($_SESSION['use_danceability']=='1') {
		$qry.=" AND (echonest_danceability>=".$_SESSION['danceability_min']." AND echonest_danceability<=".$_SESSION['danceability_max'].")";
//		$t.='Danceability:'.$_SESSION['danceability_min'].'-'.$_SESSION['danceability_max'].'  ';
	}
	// Energy:
	if ($_SESSION['use_energy']=='1') {
	    $qry.=" AND (echonest_energy>=".$_SESSION['energy_min']." AND echonest_energy<=".$_SESSION['energy_max'].")";
//		$t.='Energy:'.$_SESSION['energy_min'].'-'.$_SESSION['energy_max'].'  ';
	}
	// Key:
	if ($_SESSION['use_key']=='1') {
	    $qry.=" AND (echonest_key>=".$_SESSION['key_min']." AND echonest_key<=".$_SESSION['key_max'].")";
//		$t.='Key:'.$_SESSION['key_min'].'-'.$_SESSION['key_max'].'  ';
	}
	// Time signature:
	if ($_SESSION['use_time_signature']=='1') {
	    $qry.=" AND (echonest_time_signature>=".$_SESSION['time_signature_min']." AND echonest_time_signature<=".$_SESSION['time_signature_max'].")";
//		$t.='Time signature:'.$_SESSION['time_signature_min'].'-'.$_SESSION['time_signature_max'].'  ';
	}
	// Year range:
	if ($_SESSION['use_year_range']=='1') {
	    $qry.=" AND (year>=".$_SESSION['year_range_min']." AND year<=".$_SESSION['year_range_max'].")";
//		$t.='Year:'.$_SESSION['year_range_min'].'-'.$_SESSION['year_range_max'].'  ';
	}	
	// Liveness range - new in 0.8.8:
	if ($_SESSION['use_liveness']=='1') {
	    $qry.=" AND (echonest_liveness>=".$_SESSION['liveness_min']." AND echonest_liveness<=".$_SESSION['liveness_max'].")";
//		$t.='Liveness:'.$_SESSION['liveness_min'].'-'.$_SESSION['liveness_max'].'  ';
	}	
	// Speechiness range - new in 0.8.8:
	if ($_SESSION['use_speechiness']=='1') {
	    $qry.=" AND (echonest_speechiness>=".$_SESSION['speechiness_min']." AND echonest_speechiness<=".$_SESSION['speechiness_max'].")";
//		$t.='Speechiness:'.$_SESSION['speechiness_min'].'-'.$_SESSION['speechiness_max'].'  ';
	}	
	// Acousticness range - new in 0.8.8:
	if ($_SESSION['use_acousticness']=='1') {
	    $qry.=" AND (echonest_acousticness>=".$_SESSION['acousticness_min']." AND echonest_acousticness<=".$_SESSION['acousticness_max'].")";
//		$t.='Acousticness:'.$_SESSION['speechiness_min'].'-'.$_SESSION['speechiness_max'].'  ';
	}	
	// Valence range - new in 0.8.8:
	if ($_SESSION['use_valence']=='1') {
	    $qry.=" AND (echonest_valence>=".$_SESSION['valence_min']." AND echonest_valence<=".$_SESSION['valence_max'].")";
//		$t.='Valence:'.$_SESSION['speechiness_min'].'-'.$_SESSION['speechiness_max'].'  ';
	}	
	
    
	if ($order_by<>'') {
		$qry.=" ORDER BY $order_by $dir";
	}
	return $qry;
}

	
function get_album_id($id) { // 0.5.0: get albumid based on trackid:
	$ret="";
	$q1="SELECT id,album_id FROM track WHERE id='".$id."' LIMIT 1";
	$r1=execute_sql($q1,0,-1,$nr);
	$row1=mysql_fetch_array($r1);
	$tmpid=$row1['album_id'];
	$q2="SELECT aid FROM album WHERE aid='".$tmpid."' LIMIT 1";
	$r2=execute_sql($q2,0,-1,$nr);
	$row2=mysql_fetch_array($r2);
	$ret=$row2['aid'];
	return $ret;
}


function get_album_name($x) {
	$qry="SELECT aname FROM album WHERE aid=".$x;
	$result=execute_sql($qry,0,1,$nr);
	$row=mysql_fetch_array($result);
	if ($row['aname']!="") {
		return ' ['.$row['aname'].']';
	} else {
		return "";
	}
}

function get_album_tracklist($id) { // 0.8.7: Get+return a tracklist for an album. Used w. "tooltip" (hover) for an album
    // Get the performer-id for the album (Note: used with "Various artists" albums):
    $qry="SELECT * FROM album WHERE aid=".$id;
    $result=execute_sql($qry,0,1,$nr);
    if ($nr==1) {
        $row=mysql_fetch_array($result);
        $perfid=$row['aperformer_id'];
    }
    // Format the title:
    $a=array('[',']','"');
    $ret='<strong><font size=3em>';
    if ($perfid>1) {
        $ret.=get_performer_name($perfid).' - ';
    }
    $ret.=str_replace($a,'',get_album_name($id)).'</strong><font size=2em><br>';
    // Get the tracks:
    $qry="SELECT * FROM track WHERE album_id='".$id."' ORDER BY track_no";
    $result=execute_sql($qry,0,100,$nr);
    while ($row=mysql_fetch_array($result)) {
		$ret.=$row['track_no'].' ';
		if ($perfid<=1) { // this is a "various artists" album:
    		$perfname=get_performer_name($row['performer_id']);
	    	$ret.=$perfname.' - ';
	    }	
		$ret.=str_replace($a,'',$row['name']).'<br>'; // Add this if ya want more returned: .' '.$row['year'].' '.$row['duration'].' <br>';
    }
    // Avoid "overload" in the tooltip:
    if (strlen($ret)>1024) { // We dont want to return "too much":
        $ret=substr($ret,0,1024).' ...';
    }
    // Return results:
    return $ret;
}
    


function get_file_extension($file) { // 0.7.3: Introduced. Is used in several places
	$ret="";
	$ext=explode(".", $file);
	if (is_array($ext)) {
		$ret=strtolower(($ext[count($ext)-1]));
	}	
	return $ret;
}	


function get_icon($dir,$wanted_icon,$add_txt) { // 0.7.0: Get the corresponding icon to something
	global $icon_array;
 	// DEFAULT: return what we had until version 0.7.0: The small "mnu_arr.gif":
	$ret='<img src="./ampjukeicons/mnu_arr.gif" border="0">';
	$icon='';
	$is_already_read=0;
	
	// 0.7.3: Did we read this icon from disk previously ?
	// 0.8.2: ...and have we even defined the array ?
	if ((is_array($icon_array)) && (array_key_exists($wanted_icon,$icon_array))) {
		$ret=$icon_array[$wanted_icon];
		$is_already_read=1;
	}	

	if ($is_already_read==0) {
		if ((file_exists('./ampjukeicons/'.$dir.'/icon_index.php')) &&
			(is_readable('./ampjukeicons/'.$dir.'/icon_index.php'))) {
			include('./ampjukeicons/'.$dir.'/icon_index.php');
			if (($icon!='') && (file_exists('./ampjukeicons/'.$dir.'/'.$icon)) 
			&& (is_readable('./ampjukeicons/'.$dir.'/'.$icon))) {
				$ret='<img src="./ampjukeicons/'.$dir.'/'.$icon.'" border="0">';
				$icon_array[$wanted_icon]=$ret;		
			}	
		}
	}
	// If any additional text was supplied then add it:
	if ($add_txt<>'') {
		$ret.=' '.$add_txt;
	}	
	
	return $ret;
}		


function get_local_lame($uid,&$params) { // 0.7.0: Return 1 if users downsample is enabled, otherwise 0. Also returns whatever the 'local' lame-parameters have been set to...
	$ret=0;
	$params="";
	$qry="SELECT * FROM user WHERE id=".$uid;
	$result=execute_sql($qry,0,1,$nr);
	if ($nr==1) {
		$row=mysql_fetch_array($result);
		$params=$row['lame_local_parameters'];
		if ($row['lame_local_enabled']=="1") {
			$ret=1;
		}	
	}
	return $ret;
}	


function get_md5_passwd($user) { // 0.5.0: get_md5_passwd: get md5-"encrypted" password:
	$ret="";
 	$md5qry="SELECT id,name,password FROM user WHERE name='".$user."' LIMIT 1";
 	$md5res=execute_sql($md5qry,0,-1,$nr);
 	$md5row=mysql_fetch_array($md5res);
 	$ret=md5($md5row['password']);	
 	return $ret;
}


function get_now_playing_preferences($user) { // Get the preferences for "now playing":
	$ret='';
	if (!isset($_SESSION['disp_now_playing'])) {
		$_SESSION['disp_now_playing']="1"; 
	}	
	if ($_SESSION['disp_now_playing']=="1") { 
		$ret="&update_now_playing=1";
	}
	$ret.='&upw='.get_md5_passwd($user);
	return $ret;
}


function get_performer_id($id) { // 0.5.0: get performerid based on trackid:
	$ret="";
	$q1="SELECT id,performer_id FROM track WHERE id='".$id."' LIMIT 1";
	$r1=execute_sql($q1,0,-1,$nr);
	$row1=mysql_fetch_array($r1);
	$tmpid=$row1['performer_id'];
	$q2="SELECT * FROM performer WHERE pid='".$tmpid."' LIMIT 1";
	$r2=execute_sql($q2,0,-1,$nr);
	$row2=mysql_fetch_array($r2);
	$ret=$row2['pid'];
	return $ret;
}


function get_performer_id_by_name($pname) { // 0.7.7: get performerid based on performername (used w. last.fm/related performers):
	$ret=0;
	$q="SELECT pid,pname FROM performer WHERE pname='".$pname."'";
	$r=execute_sql($q,0,1,$nr);
	if ($nr==1) { // Found it:
		$row=mysql_fetch_array($r);
		$ret=$row['pid'];
	}
	return $ret;
}


function get_performer_name($x) { //Returns performername for performerid=x:
	$ret="";
	$qry="SELECT * FROM performer WHERE pid=$x";
	$result=execute_sql($qry,0,1,$n);
	$row=mysql_fetch_array($result);
	$ret=$row['pname'];
	return $ret;
}	


function get_performer_name_album($x) { // Get the performer name for a specific albumID:
	$ret="";
	$qry="SELECT * FROM album WHERE aid=".$x;
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
	$ret=get_performer_name($row['aperformer_id']);
	return $ret;
}		


function get_performer_name_track($x,$a,&$perf_name,&$perf_id) { //Input performerid:x albumid:a. Output: performername and -id
	$ret="";
	$qry="SELECT * FROM track WHERE track.performer_id=$x AND track.album_id=$a";
	$res=execute_sql($qry,0,1,$nr);
	$r=mysql_fetch_array($res);
	$qry="SELECT * FROM performer WHERE performer.pid=".$r['performer_id'];
	$res=execute_sql($qry,0,1,$nr);
	$r=mysql_fetch_array($res);
	$perf_name=$r['pname'];
	$perf_id=$r['pid'];	
}	


function get_recently_played_performers($min_age_performer,$table,$debug='0') { // 0.8.7: Returns additional SQL-statements if enabled:
// 0.8.8: switched to jukebox_mode_min_age_performer -> $min_age_performer in this func.
    $debug='0';
    $ret='';

    if ((isset($debug)) && ($debug=='1')) { 
        mydebug('disp.php','Hours to look back for plays from same artist/performer:'.$min_age_performer);
    }
   
    if ((isset($min_age_performer)) && (($min_age_performer>0))) { // Yes - *sigh* - it's enabled: on the job...
        // 0.8.8: Calculate a timestamp in the past:
        $before=date('U') - ($min_age_performer * 3600); 
        $qry="SELECT * FROM track WHERE last_played>'".$before."' ORDER BY last_played DESC";
        $result=execute_sql($qry,0,10000,$nr);
        
        if ((isset($debug)) && ($debug=='1')) { 
	        mydebug('disp.php: get_recently_played_performers','TOTAL number of performers played within last:'.$min_age_performer.' hours: '.$nr);
	        //qry : '.$qry); 
    	}
    		
        if ($nr<>0) {
            while ($row=mysql_fetch_array($result)) {
                $ret.=" AND performer_id<>'".$row['performer_id']."' ";
            }
        }
    }
    /*
    if ((isset($debug)) && ($debug=='1')) { 
	    mydebug('disp.php: get_recently_played_performers','Return : '.$ret); 
	}
	*/	
    return $ret;
}

function get_request_array($l) { // 0.8.6: Returns an array from queue.user_name / +++[0];timestamp[1];user[2];streamed[3]
	$a=array();
	trim($l);
	if (strlen($l)>3) {
		$a=explode(';',$l); // Explodes into an array: +++[0];timestamp[1];username[2];streamed[3]
	}
	return $a;
}


function get_track_extras($id) { // 0.5.0: get track-name,-duration,-last_played etc. based on id:
	$ret="";
	$q="SELECT * FROM track WHERE id='".$id."' LIMIT 1";
	$r=execute_sql($q,0,-1,$nr);
	$row=mysql_fetch_array($r);
	$row['name']=stripslashes($row['name']); // 0.8.6
	$ret=$row;
 	return $ret;
}


function get_username($id) { // 0.5.2: get username based on id:
	$ret="";
	$q="SELECT id,name FROM user WHERE id='".$id."' LIMIT 1";
	$r=execute_sql($q,0,-1,$nr);
	$row=mysql_fetch_array($r);
	$ret=$row['name'];
	return $ret;
}	


function get_user_details($id) { // 0.8.2: Get all details based on userid, returns a $row from user-table:
	$q="SELECT * FROM user WHERE id='".$id."' LIMIT 1";
	$r=execute_sql($q,0,-1,$nr);
	$row=mysql_fetch_array($r);
	return $row;
}	


function get_user_id($user) { // 0.5.0: get userid based on username:
	$ret="";
	$q="SELECT id,name FROM user WHERE name='".$user."' LIMIT 1";
	$r=execute_sql($q,0,-1,$nr);
	$row=mysql_fetch_array($r);
	$ret=$row['id'];
	// 0.8.3: Obviously, some extremely, handy things (f.ex. streaming!) will STOP unless there's a user-id set...
	// ...in case we're 'anonymous' just return a pseudo-number:
	if ($user=='anonymous') {
		$ret='99999';
	}
	return $ret;
}	


function handle_m4a($handle,$nr,$name,$keep_extension,$row,$s,$id,$user) {
 	require("db.php");
	// extension MUST be included, so do that no matter whats configured:
	if ($keep_extension==0) {
		$name.='.'.get_file_extension($row['path']);
	}
	// Streaming m4a/mp4 is only possible "directly", so copy file to ./tmp/"name":
	cpy_file_to_tmp($row['path'],$name,'./tmp/','');
	// Just to make sure: Get rid of all files related to "now playing":
	if (file_exists('./tmp/np'.get_user_id($user).'.txt')) {
		unlink('./tmp/np'.get_user_id($user).'.txt');
		@unlink('./tmp/np'.get_user_id($user).'pop.txt');
		@unlink('./tmp/npnext'.get_user_id($user).'.txt');
	}		
	// Have we enabled special_extensions AND the update of "now playing" ?
	if ((isset($special_extensions_enabled)) && ($special_extensions_enabled=="1")
	&& (isset($special_extensions_update_playing)) 
	&& ($special_extensions_update_playing=="1")) {
		write_m3u($handle,$nr,"1",'Update now playing entry for '.$name,
		$base_http_prog_dir.'/stream.php?id='.$id.'&dummy_update=2&user_id='.get_user_id($_SESSION['login']).'&language='.$_SESSION['lang'].get_now_playing_preferences($user));
	}
	// Write playlist entry w. m4a-file (or whatever it is that's "special"):
	write_m3u($handle,$nr,$s,"$name",$base_http_prog_dir.'/tmp/'.$name);
	// Have we enabled special_extensions AND the update of statistics ?
	if ((isset($special_extensions_enabled)) && ($special_extensions_enabled=="1")
	&& (isset($special_extensions_update_statistics))
	&& ($special_extensions_update_statistics=="1")) {
		write_m3u($handle,$nr,"1",'Statistic entry for '.$name,
		$base_http_prog_dir.'/stream.php?id='.$id.'&dummy_update=1&user_id='.get_user_id($_SESSION['login']).get_now_playing_preferences($user));
	}
}


function headline($what,$hl,$limit) { // Returns complete headline:
	$ret="\n\n\n <!-- HEADLINE START --> \n\n\n";
	$ret.=std_table("ampjuke_headline_table","");
    $ret.= '<tr><td class="content" align="center">';
	switch ($what) {
		case "track":
			$hl=xlate('Tracks');
			break;
		case "album":
			$hl=xlate('Albums');
			break;
		case "albumid":
			$hl.=' '; // !
			break;
		case "performer":
			$hl=xlate('Performers');
			break;
		case "performerid":
//			$limit=$hl; // uuuuhhh....
//			$hl='';
			break;
		case "year":
			$hl=xlate('Year');
			break;
		case "yearid":
			$hl=xlate('Year');
			break;
		case "favorite":
			$hl=xlate('Favorites');
			break;	
		case "favoriteid":
			$hl=xlate('Favorite list');
			break;	
		// 0.8.7:
		case "favorite_adv":
			$hl=xlate('Advanced favorite list creation');
			break;
		case "queue":
			$hl=xlate("The queue");
			break;	
		case "search":
			$hl=xlate("Search results");
			break;	
		case "users":
			$hl=xlate("Administration");
			break;
		case "settings":
			$hl=xlate("Personal settings");
			break;	
		case "sitecfg": // 0.7.6: Site cfg.
			$hl="AmpJuke site configuration";
			break;
		case "advsearch": // 0.7.8: Advanced search:
			$hl=xlate("Advanced search");
			break;
        case 'build_link': // 0.8.6: Build link(s)
            $hl=xlate('Build a link');
            break;
	    case "";
            $hl=xlate($hl);
            break;
	}
	$ret.=$hl;
	if ($limit!="") {
		$ret.=': '.$limit;
	}	
	$ret.='</td></tr></table>';
	$ret.="\n <!-- HEADLINE ENDS --> \n\n";
	$ret.='<tr><td> <!-- NEW ROW MAIN_CONTENT_TABLE --> '; 
	return $ret;
}


function my_filter_var($s,$filter_type = FILTER_SANITIZE_STRING) { // 0.8.2: PREG_REPLACE vanishes in new versions of PHP...use filter_var instead:
	$s=filter_var($s,$filter_type,FILTER_FLAG_STRIP_LOW);
	$s=filter_var($s,$filter_type,FILTER_FLAG_STRIP_HIGH);
	return $s;
}


function mydate($d) { // Input: datestamp. Output: "dateformat" formatted date:
	require("db.php");
	if (strlen($d)==10) { 
		return date($dateformat,$d);
	} else {
		return $d;
	}
}


function mydebug($debug,$entry) { // 0.7.1: Just for debugging purposes
	$dh=fopen('./tmp/debug.htm', "a");
	$dnow=date("Y-m-d H:i:s");
	fwrite($dh,$dnow.'('.$debug.'): '.$entry.'<br>'. chr(13) . chr(10));
	fclose($dh);
}	


function my_duration($totalseconds) { // Input: # of seconds. Return: d days h m s...
  	$days=floor($totalseconds / 86400);
  	$day_tag=xlate("day");
  	if ($days>1) { $day_tag=xlate("days"); }

    $hours = floor($totalseconds / 3600) % 24;
   	if ($hours<10) { $hours='0'.$hours; }    

    $minutes = floor($totalseconds / 60) % 60;
    if ($minutes<10) { $minutes="0".$minutes; }

    $seconds = $totalseconds % 60;
    if ($seconds<10) { $seconds="0".$seconds; }

    if ($days>0) {
    	return "$days $day_tag $hours:$minutes:$seconds";
	}    	

    if ($hours>0) {
        return "$hours:$minutes:$seconds";
    } else {
        return "$minutes:$seconds";
    }
}


function my_mail($to,$subject,$msg,$from='') { // 0.8.4: Introduced
	// From not set - attempt to get it from php.ini:
	if ($from=='') {
		$from=ini_get('sendmail_from');
		if ($from=='') {
			die('The php.ini setting: sendmail_from is not configured/defined. Cannot send mail.');
		}
	} else {
		ini_set('sendmail_from',$from);
		$remember_restore=1;
	}

	$h='MIME-Version: 1.0' . "\r\n";
	$h.='Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$h.='To: ' . $to . "\r\n";
	$h.='From: ' . $from . "\r\n";
	$h.='Reply-To: '.$from . "\r\n";
	$h.='Return-Path: '.$from . "\r\n";
	mail($to,$subject,$msg,$h);

	if (isset($remember_restore)) {
		ini_restore('sendmail_from');
	}
}

function only_digits($s) { // 0.7.6: Only return digits:
	$ret="";
	$v=0;
	while ($v<strlen($s)) {
		$c=substr($s,$v,1);
		if ((($c>="0") && ($c<="9")) || ($c==".")) {
			$ret=$ret.$c;
		} else {
			redir("logout.php");
		}	
		$v++;
	}
	return $ret;
}		


function redir($to) { // Javascript redirect to: "./$to"
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("./'.$to.'");';
	echo '</script>';	
	die(); // 0.8.6
}	


function retrieve_xml($file,&$n,$lastfm_max_related_artists) { // Function to retrieve XML (being local or @lastfm).
	$xml="";
	if ($hf=@fopen($file,'r')) {
    	for ($sfile='';$buf=fread($hf,8192);) {  
        	$sfile.=$buf;
		}
	} else {
		$n=$lastfm_max_related_artists+1; 
	}	
    @fclose($hf);
	$forbidden=array('&',"'");
	if (isset($sfile)) { // 0.8.4
		$sfile=str_replace($forbidden,'',$sfile); // 0.7.7
		$xml = SimpleXML_Load_String($sfile);	
	}
	return $xml;
}	

function schedule_change($what,$debug='1') { // 0.8.8: Looks in schedule.php & possibly returns new name of favorite list to pick from:
    $ret=''; // Default: empty!
    if ((file_exists('./schedule.php')) && (is_readable('./schedule.php'))) {
        $handle=fopen('./schedule.php', 'r');
        $found=0;
        while ((!feof($handle)) && ($found==0)) {
            $linje=fgets($handle);
            if ($debug=='1') {
                mydebug('sc',$linje.'<br>');
            }
            if ((strlen($linje)>5) && (substr($linje,0,1)<>'#')) { // if it's a "valid" entry & not a comment:
                $now=date('U');
                $i=explode(';',$linje);
                if (is_array($i)) {
                    if (($now>=strtotime($i[0])) && ($now<=strtotime($i[1]))) { // We have a valid entry
                        $ret=rtrim($i[2]);
                        $found=1;
                        if ($debug=='1') {
                            mydebug('sc','Scheduler switching to: '.$i[2]);
                        }
                    }
                }
            }
        }
        fclose($handle);
    } else {
        if ($debug=='1') {
            mydebug('sc','ERROR: Cannot open schedule.php or it is now readable');
        }
    }
    return $ret;
}


function set_name($pid,$n,$aid,$keep_extension,$file) { // Input: performerid(pid),name(n),albumid(aid),keep_extension(0|1),file(filename). Returns:"name"
// pid=performer_id, n=name, aid=album_id.
// Basically determine what names in the playlist and download should look like.
	$name=get_performer_name($pid).' - '; // performer name, 1st.
	$name.=$n; // track name, 2nd.
	$name.=get_album_name($aid); // album name in "[", 3rd.


	if ($keep_extension==1) {
		$ext=strrchr($file,".");
		$name.=$ext;
	}

	// Avoid the player gets "confused" regarding 
	// spaces...comment it out if your mediaplyer gets "confused":
	// $name=str_replace(" ","_",$name); 

	// 0.6.4: Avoid "forbidden" characters:
	require_once("configuration.php");
	$forbidden=get_configuration("forbidden_characters");
	$forbidden_chars=array();
	$n=0;
	if (isset($forbidden)) {
		while ($n<=strlen($forbidden)) {
			$forbidden_chars[$n]=substr($forbidden,$n,1);
			$n++;
		}
		if ($n!=0) {
			$name=str_replace($forbidden_chars,"",$name);
		}
	}

	return $name;
}


function show_alphabet($what,$order_by,$c) { // Used w. show_letters function below. Returns "links to alphabet":
	$ret="";
	$sorttbl="";
	switch ($what) {
		case "track": $sorttbl="track&pagesel=track"; break; 
		case "album": $sorttbl="album&pagesel=album"; break; // 0.7.3: &pagesel=...
		case "performer": $sorttbl="performer&pagesel=performer"; break; // 0.7.3: &pagesel=...
	}
//	$ret.='<img src="./ampjukeicons/mnu_arr.gif" border="0">';
	$ret.=get_icon($_SESSION['icon_dir'],'jump2letter','');
	$ret.=xlate('Jump to').': ';
	$l='<a href="index.php?what='.$what.'&start=0&count='.$c;
	$l.='&sorttbl='.$sorttbl.'&order_by='.$order_by.'&limit=';
	$n=65;
	while ($n<91) {
		$ret.=$l.chr($n).'">'.chr($n).'</a>  ';
		$n++;
	}	
	// 0.7.4: Added 0..9 as an option as well:
	$ret.=$l.'0..9">0..9</a>  ';
	return $ret;
}


function show_letters($what,$field) { // Returns "links to alphabet"..:
 	$ret="";
	if ($_SESSION['show_letters']=="1") {
// 0.8.5	 	$ret.='<tr><td>'; 
		$ret.=show_alphabet($what,$field,$_SESSION['count']);
// 0.8.5		$ret.='</td></tr></table>';
	}
	return $ret;
}


function std_table($cls,$tid) {
 	$ret='<table';
 	if ($cls!="") { // "cls"= class of table
		$ret.= ' class="'.$cls.'"';
	}	
	if ($tid!="") { // "tid"= ID of table
		$ret.= ' id="'.$tid.'"';
	}
	$ret.= '>';	
	return $ret;
}		


function update_stats($idx) { // Update listening statistics for a specific track (idx):
	// setup some stuff (ie. values for the two columns we're about to update):
	$now=date("U");
	$stat_qry="SELECT id, last_played, times_played FROM track WHERE id='".$idx."' LIMIT 1";
	$stat_res=execute_sql($stat_qry,0,-1,$nr);
	$stat_row=mysql_fetch_array($stat_res);
	$c=$stat_row['times_played'];
	$c++;
	// Here comes the actual update of the track table:
	$stat_qry="UPDATE track SET times_played='".$c."', last_played='";
	$stat_qry.=$now."' WHERE id='".$idx."' LIMIT 1";
	$stat_res=execute_sql($stat_qry,0,-1,$nr);
	// 0.5.0: Also update favorite lists w. new date of play for this particular track:
	$stat_qry="UPDATE fav SET times_played='".$c."', last_played='";
	$stat_qry.=$now."' WHERE track_id='".$idx."'";
	$stat_res=execute_sql($stat_qry,0,-1,$nr);
}	


function write_m3u($handle,$no,$duration,$name,$path) { // Write entry to playlistfile:
	if ($no==1) { 
		fwrite($handle, "#EXTM3U".chr(13).chr(10));
	}	
	fwrite($handle, "#EXTINF:$duration,$name".chr(13).chr(10));
	fwrite($handle, $path.chr(13).chr(10));
}	


function xspf_create($user) { // Create new playlist for "user" in XSPF format:
	$handle=fopen('./tmp/'.$user.session_id().'.xspf', 'w');
	fwrite($handle,'<?xml version="1.0" encoding="utf-8"?>'.chr(13).chr(10).
	'<playlist version="1" xmlns="http://xspf.org/ns/0/">'.chr(13).chr(10).
	'<title>AmpJuke...and YOUR hits keep on coming!</title>'.chr(13).chr(10).
    '<creator>Michael H. Iversen</creator>'.chr(13).chr(10).
    '<info>http://www.ampjuke.org</info>'.chr(13).chr(10).
    '<trackList>'.chr(13).chr(10));
	fclose($handle);
}	


function xspf_write_track($user,$row,$name,$ext) { // Write a single track-entry to a specific XSPF playlist:
	$handle=fopen('./tmp/'.$user.session_id().'.xspf', 'a');
	fwrite($handle,'<track>'.chr(13).chr(10));
	include('db.php');

// Good,old reliable method. Not desirable though, since this expects a copy to exist in ./tmp:
//	fwrite($handle,'<location>'.$base_http_prog_dir.'/tmp/'.$row['id'].$ext.'</location>'.chr(13).chr(10));

// Use AmpJuke's built-in streaming engine, even though we know http-locations are expected within .xspf:

	// LOCATION:
	$l=$base_http_prog_dir.'/stream.php?id='.$row['id'];
	$l.='&user_id='.get_user_id($user);
	$l.='&upw='.get_md5_passwd($user);
	fwrite($handle,'<location>'.$l.'</location>'.chr(13).chr(10));

	// Handle 'local' characters:
	$name=htmlentities($name); // Convert to 'entities' -> then make a search&replace for each char.:
	$name=str_replace('&aelig;','AE',$name); // æ -> ae
	$name=str_replace('&oslash;','OE',$name); // ø -> oe
	$name=str_replace('&aring;','AA',$name); // ø -> oe	

	// IMAGE: Do we have an image of the performer ?
	if (file_exists('./lastfm/'.$row['performer_id'].'.jpg')) {
		fwrite($handle,'<image>'.$base_http_prog_dir.'/lastfm/'.$row['performer_id'].'.jpg</image>'.chr(13).chr(10));
	} else { // Just add the usual stuff:
		fwrite($handle,'<image>'.$base_http_prog_dir.'/covers/_blank.jpg</image>'.chr(13).chr(10));
	}		
	
	// TITLE: Title/Name to display:
	$s=array('.mp3','_');
	$name=str_replace($s,' ',$name);
	fwrite($handle,'<title>'.$name.'</title>'.chr(13).chr(10));
//	fwrite($handle,'<annotation>'.$name.'</annotoation>').chr(13).chr(10);
	// Link directly to the performer's page:
	$l=$base_http_prog_dir.'/index.php?what=performerid&start=0&special='.$row['performer_id'];
	fwrite($handle,'<info>'.$l.'</info>'.chr(13).chr(10));


	fwrite($handle,'</track>'.chr(13).chr(10));
	fclose($handle);
	/* Noooo...really..? 
	if ((isset($xspf_update_stats)) && ($xspf_update_stats=='1')) {
		update_stats($row['id']);
	}
	*/	
}	


function xspf_close($user) { // Close playlist in XSPF-format. Also clear user's ".txt", ".pop.txt" and ."m3u" entries in ./tmp:
	$handle=fopen('./tmp/'.$user.session_id().'.xspf', 'a');
	fwrite($handle,'</trackList>'.chr(13).chr(10).
	'</playlist>');
	fclose($handle);
	@unlink('./tmp/np'.get_user_id($user).'.txt');
	@unlink('./tmp/np'.get_user_id($user).'pop.txt');
	@unlink('./tmp/'.$user.'.m3u');
}


function xspf_make_url($base_http_prog_dir,$user) { // Return url to be used in XSPF-formatted playlist:
	$loc=$base_http_prog_dir.'/stream_flash.php?d='.date('U').'&u='.$user;
	$loc.='&playlist_url='.$base_http_prog_dir.'/tmp/'.$user.session_id().'.xspf&autoplay=1';
	$loc.='&base='.$base_http_prog_dir;
	return $loc;
}	


function xspf_play_it($loc) { // Throw something (preferrably an XSPF-playlist) out in a new window (start flash-player):
	// Play (send) the flash- & xspf-playlist to the user:
	echo '<script type="text/javascript" language="javascript">';
	echo 'var rw = window.open("'.$loc.'","AmpJuke_FlashPlayer","width=450,height=250");';
	echo "history.go(-1);";
	echo 'window.focus();';
	echo '</script>';
	die();
}	


?>
