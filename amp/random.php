<?php
// 0.5.3: Most of this script rewritten + introduction of 'weighted random selecion'.
if (!isset($_SESSION['login'])) { 
	session_start(); 
	if (!isset($_SESSION['login'])) {
		include_once("disp.php");
		redir("login.php");
		exit;
	}		
}	

require_once("disp.php");
require_once("db.php");
require_once("sql.php");
require_once("set_td_colors.php"); 
require_once("translate.php"); 

// 0.8.6: Additional check in relation to radio station:
if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled=='1')) {
    die('Sorry. Radio station mode enabled. Disable radio station mode in the configuration.');
} 

// 0.7.3: Get special extensions, if defined:
if ((isset($special_extensions_enabled)) && ($special_extensions_enabled=="1")) {
	$special_extensions=explode(',',$special_extensions);
} else {
 	$special_extensions=array();
}

function get_autoplay_parameters($user,$listname) {
 	$md5pw=get_md5_passwd($user);
	$ret="/stream.php?id=0&uid=".$md5pw."&what=";
	if ($listname!="") {
		$ret.=rawurlencode($listname).'&user='.$user;
	} else {
		$ret="tracks";
	}
	return $ret;
}		



function get_random_preference($pref,$what) {
	$ret="";
	if ($pref=="nothing") { $ret="ORDER BY rand()"; }
	if ($pref=="most_played") { $ret="ORDER BY rand()*(times_played+1) DESC"; }
	if ($pref=="least_played") { $ret="ORDER BY rand()*(times_played+1) ASC"; }
	if ($pref=="oldest") { 
	 	$now=date("U");
		$ret="ORDER BY rand()*(".$now."-last_played) DESC"; 
	}
	if ($pref=="newest") { 
	 	$now=date("U");
		$ret="ORDER BY rand()*(".$now."-last_played) ASC"; 
	}
	return $ret;
}	


parse_str($_SERVER["QUERY_STRING"]);
if (isset($autoplay)) {
	$act='start';
	$_POST['name']=$list;
	$_POST['no_of_tracks']=$num_tracks;
	if (!is_numeric($num_tracks)) {
        $_POST['no_of_tracks']=1;
    }
	$_POST['year']="";
	$_POST['preference']="nothing";
}	

if (isset($_POST['no_of_tracks'])) {
	if (!is_numeric($_POST['no_of_tracks'])) {
		$_POST['no_of_tracks']=10;
	}	
} else {
	$_POST['no_of_tracks']=10;
}	


/*

						SET UP
						
						
*/						

function check_selected($def,$set) {
	if ($def==$set) {
		echo ' selected ';
	}
}		

if ($act=="setup") { 
 	// 0.6.1: Get previous settings (cookie-based) or use default:
 	if (isset($_COOKIE['ampjuke_notracks'])) {
		$def_notracks=$_COOKIE['ampjuke_notracks'];
	} else {
	  	$def_notracks=10;
	}
	if (isset($_COOKIE['ampjuke_priority'])) {
		$def_priority=$_COOKIE['ampjuke_priority'];
	} else {
		$def_priority='nothing';
	}
	if (isset($_COOKIE['ampjuke_favlist'])) {
		$def_favlist=$_COOKIE['ampjuke_favlist'];
	} else {
		$def_favlist='Tracks';
	}		
// 0.8.5: Max. duration introduced:
	if (isset($_COOKIE['ampjuke_max_duration'])) {
		$def_max_duration=$_COOKIE['ampjuke_max_duration'];
	} else {
		$def_max_duration='0';
	}
 // 0.8.5: Min age ("Avoid selection of tracks played within the last") introduced:
	if (isset($_COOKIE['ampjuke_min_age'])) {
		$def_min_age=$_COOKIE['ampjuke_min_age'];
	} else {
		$def_min_age=1;
	}
 
	echo headline($what,xlate('Random play'),'');
	print "\n\n\n <!-- ACTIONS TABLE START --> \n\n\n";

// 0.8.4: WTF ??
//	echo '<table class="ampjuke_actions_table">';	
//	echo '</table>';

	print "\n\n\n <!-- ACTIONS TABLE ENDS, NEW ROW FOR MAIN_CONTENT_TABLE: --> \n\n\n </td></tr><tr><td>";
	echo std_table("ampjuke_content_table","ampjuke_content");
	echo '<FORM NAME="setup" method="POST" action="index.php?what=random&act=start">';	
	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td class="content">'.xlate("Play tracks from").':</td>';
	echo '<td class="content">';
	echo '<SELECT NAME="name" class="tfield">';
	echo '<OPTION VALUE="Tracks"';
	check_selected($def_favlist,'Tracks'); // 0.6.1
	echo '>['.xlate("All").' '.xlate("Tracks").']</OPTION>';
	if ($_SESSION['login']!="anonymous") {
		$qry="SELECT DISTINCT fav_name FROM fav";
		$qry.=" WHERE user_id='".get_user_id($_SESSION['login'])."' ORDER BY fav_name";
		$result=execute_sql($qry,0,1000000,$nr);	
		while ($row=mysql_fetch_array($result)) {
			echo '<OPTION VALUE="'.$row['fav_name'].'"';
			check_selected($def_favlist,$row['fav_name']); // 0.6.1
			echo '>'.$row['fav_name'].'</OPTION>';
		}
		// 0.8.3: Only show if we have it enablde, shared_favorites_allow is set and equals 1 (it's set):
		if (($_SESSION['disp_fav_shares']=="1") && (isset($shared_favorites_allow)) && ($shared_favorites_allow=='1')) {
			$qry="SELECT * FROM fav_shares WHERE share_id='".get_user_id($_SESSION['login'])."'";
			$qry.=" ORDER BY fav_name";
			$result=execute_sql($qry,0,1000000,$x);
			while ($row=mysql_fetch_array($result)) {
				echo '<OPTION value="******'.$row['fav_name'].'"';
				check_selected($def_favlist,'******'.$row['fav_name']); // 0.6.1
				echo '>(';
				echo xlate("Shared").') '.$row['fav_name'];
				echo '</OPTION>';
			}		
		}
	} 			
	echo '</SELECT>';
	echo '</tr>';	

	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);	
	echo '<td>'.xlate("Number of tracks to select").':</td>';
	echo '<td><input type="text" name="no_of_tracks" size=8 class="tfield" ';
	// 0.6.1: Get cookie, if it exists:
	echo 'value="'.$def_notracks.'"> ';

	// 0.8.5: Max. duration:
	echo xlate("or a maximum duration of").':';
	echo ' <input type="text" name="max_duration" size=8 class="tfield" ';
	echo 'value="'.$def_max_duration.'"> '.xlate('minutes').'</td></tr>';
		
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);	
	echo '<td valign="top">'.xlate("Give priority to");
	echo ': '.add_faq(30).'</td><td>';
	echo '<SELECT NAME="preference" class="tfield">';	
	echo '<OPTION VALUE="nothing"';
	check_selected($def_priority,'nothing'); // 0.6.1
	echo '>---</OPTION>';
	echo '<OPTION VALUE="least_played"';
	check_selected($def_priority,'least_played'); // 0.6.1
	echo '>'.xlate("Least played tracks").'</OPTION>';		
	echo '<OPTION VALUE="most_played"';
	check_selected($def_priority,'most_played'); // 0.6.1
	echo '>'.xlate("Most played tracks").'</OPTION>';
	echo '<OPTION VALUE="oldest"';
	check_selected($def_priority,'oldest');	 // 0.6.1
	echo '>'.xlate("Tracks not played recently").'</OPTION>';		
	echo '<OPTION VALUE="newest">'.xlate("Tracks played recently").'</OPTION>';			
	echo '</SELECT>';
	
	// 0.8.0: Hide the "Also use this setting..." option if flash player is active:
	if ($xspf_enabled=='0') {
		$xspf_only_player='0';
	}	
	if (($xspf_only_player=='0') && ($_SESSION['xspf_active']=='0') && ($_SESSION['autoplay_last']=="1")) {
		echo '<br><input type="checkbox" name="auto_preference_on" checked>';
		echo xlate("Also use this setting later on").' "'.xlate("Automatic play").'"';
	}		
	echo '</td></tr>';

	// 0.8.5: YEEHAAA - NEW STUFF: Avoid selection of tracks streamed in the last X day(s):
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);	
	echo '<td class="content">'.xlate('Avoid selection of tracks played within the last').':</td>';
	echo '<td class="content">'.add_textinput('min_age',$def_min_age,3).' '.xlate('day').'/'.xlate('days').'</td></tr>';

	
	echo '<tr><td colspan="2" align="center">';
	echo '<input type="submit" class="tfield" name="submit"';
	echo ' value="'.xlate("Save & continue").'"></td></tr>';
	echo '</table></FORM>';
}

/*


				PLAY


*/				
if ($act=="start") { 
	$max_x=0;
	$uid=get_user_id($_SESSION['login']);
	$user=get_username($uid);
	$duration=0; // 0.8.5: used to compare against max_duration
	
	// 0.8.5: Get+set the timestamp w. the highest value we will accept (compare w. "last_played" when selcting):
	$_POST['min_age']=only_digits($_POST['min_age']);
	if ($_POST['min_age']==0) {
		$max_last_played=date('U')+(31536000*10); // This is approx. 10 years ahead in the *future*. Hard to imagine we have anything greater than that in "lasy_played"...
	} else {
		$max_last_played=date('U')-($_POST['min_age']*86400); // The max. value of "last_played" of any track selected for random play
	}
	
	// 0.8.5:
	if (isset($_POST['no_of_tracks'])) {
		$_POST['no_of_tracks']=only_digits($_POST['no_of_tracks']);
	}
	
	if (($_POST['name']!="Tracks") && ($_POST['name']!="---")) { 
		$listname=$_POST['name'];
		if (strlen($_POST['name'])>6) {	
			if (substr($_POST['name'],0,6)=="******") {
				$listname=substr($_POST['name'],6,100);
				$qry="SELECT * FROM fav_shares WHERE fav_name='".$listname."'";
				$qry.=" AND share_id='".$uid."'";
				$result=execute_sql($qry,0,1,$x);
				$row=mysql_fetch_array($result);
				$uid=$row['owner_id'];
			}	
		}	
		$qry="SELECT * FROM fav WHERE user_id='".$uid."'";
		$qry.=" AND fav_name='".$listname."' AND track_id>0 ";
		$qry.=" AND last_played<".$max_last_played." "; // 0.8.5
		$qry.=get_random_preference($_POST['preference'],$listname);
	}

	if ($_POST['name']=="Tracks") { 
		$qry="SELECT * FROM track ";
		$qry.=" WHERE last_played<".$max_last_played." "; // 0.8.5
		$qry.=get_random_preference($_POST['preference'],"Tracks");
	}
	
	// 0.8.0: Create the xspf-playlist ?
	if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
		xspf_create($user);
	}	

	$handle=fopen("./tmp/".$_SESSION['login'].".m3u", "w");
	$result=execute_sql($qry,0,$_POST['no_of_tracks'],$x);

	while ($row=mysql_fetch_array($result)) {
	 	if ($_POST['name']=="Tracks") {
			$qry2="SELECT * FROM track WHERE id=".$row['id'];
		} else {
		 	$qry2="SELECT * FROM track WHERE id=".$row['track_id'];
		}	 			

		$result2=execute_sql($qry2,0,1,$x);
		$row2=mysql_fetch_array($result2);
		$file=$row2['path'];
		$name=set_name($row2['performer_id'],$row2['name'],$row2['album_id'],$keep_extension,$file); 
		// 0.8.4: split() replaced by explode():
		$item=explode(":",$row2['duration']);
		$s=$item[1] + ($item[0]*60);

		// 0.8.5: Do we hit the ceiling? (max_duration)
		$max_duration_reached=0;
		if ((($duration+$s)>$max_duration)  && ($max_duration>0)) {
			$max_duration_reached=1;
		} else {
			$duration=$duration + $s;
			//echo 'max_duration='.$max_duration.' duration='.$duration.'<br>';
		}
		
		if ($max_duration_reached==0) {
			// 0.7.3: Special handling of files w. "special" extension (f.ex. .m4a):
			$ext='.'.get_file_extension($file);
			$found=0;
			foreach ($special_extensions as $value) {
				if ($value==$ext) { $found=1; }
			}	
			if ($found==1) {
				handle_m4a($handle,1,$name,$keep_extension,$row2,$s,$row2['id'],$user);
			} else { // Do as usual:
				write_m3u($handle,1,$s,$name,$base_http_prog_dir.'/stream.php?/'.$name.'&id='.$row2['id'].'&user_id='.$uid.'&language='.$_SESSION['lang'].get_now_playing_preferences(get_username($uid))); // 0.7.3: get_user...
				// 0.8.0: Write to xspf-playlist ?
				if ((isset($xspf_enabled)) && ($xspf_enabled=='1')) {
					if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
						//cpy_file_to_tmp($row2['path'],$row2['id'].$ext,'./tmp/',$kext);
						xspf_write_track($user,$row2,$name,$ext);
					} // xspf_active					
				} // xspf_enabled
			} // found
		} // max_duration_reached
	} // while row...

	if (($_SESSION['autoplay_last']=="1") && ($_SESSION['enqueue']==0)) {
		$param=get_autoplay_parameters($_SESSION['login'],$_SESSION['autoplay_last_list']);
		$param.="&preference=".$_POST['preference'].'&user_id='.$uid.'&language='.$_SESSION['lang']; // 0.7.2
		$param.='&max_last_played='.$max_last_played; // 0.8.5
		$param.=get_now_playing_preferences(get_username($uid)); // 0.7.3: get_user...
		write_m3u($handle,2,"-1",xlate("Automatic play"),$base_http_prog_dir.$param);
	}	

	fclose($handle);
	$loc=$base_http_prog_dir."/tmp/".$_SESSION['login'].".m3u?id=".date("U"); 

	// 0.8.0: Close the xspf-playlist:
	if ((isset($xspf_enabled)) && ($xspf_enabled=='1') && (isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
		xspf_close($user);
	}	
	
	if (!isset($autoplay)) {
		// 0.8.0: Use xspf (flash player) ?
		if ((isset($xspf_enabled)) && ($xspf_enabled=='1')) {
			if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
				$loc=xspf_make_url($base_http_prog_dir,$_SESSION['login']);
				xspf_play_it($loc);
			}	
			// We're forced to use the flash player:
			if ((isset($xspf_only_player)) && ($xspf_only_player=='1')) {
				$loc=xspf_make_url($base_http_prog_dir,$_SESSION['login']);
				xspf_play_it($loc);
			}		
		}
		// Otherwise, just send the playlist. Let client determine what media-player is associated w. playlists (.m3u):
		if (!isset($demo)) { 
			echo '<script type="text/javascript" language="javascript">';
			echo 'var rw = window.open("'.$loc.'","AmpJuke_Autoplay","width=100,height=100");';
			echo 'window.location.replace("index.php?what=welcome&autoplay=done");';
			echo 'window.focus();';
			echo '</script>';
		} else { 
			redir("demo.php");
		}		
	}	

	if (isset($autoplay)) {
		if (!isset($demo)) { 	 
			echo '<script type="text/javascript" language="javascript">';
			echo 'var rw = window.open("'.$loc.'","AmpJuke_Autoplay","width=100,height=100");';
			echo 'window.location.replace("index.php?what=welcome&autoplay=done");';
			echo 'window.focus();';
			echo '</script>';
		} else { 
			redir("demo.php");
		}
	}	
} 
?>	
