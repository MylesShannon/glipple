<?php
function js_back($x) {
	echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
	echo '</script>';  
}

function get_autoplay_parameters($user,$listname) {
// 	$md5pw=get_md5_passwd($user);
	$ret="/stream.php?id=0&what="; 
	if ($listname!="") {
		$ret.=rawurlencode($listname).'&user='.$user;
	} else {
		$ret.="Tracks&user=".rawurlencode($user);
	}
	$ret.='&language='.$_SESSION['lang']; // 0.7.2
	return $ret;
}				


function check_anonymous_permissions($allow_anonymous,$login,$allow_anonymous_streaming) {
	// 0.6.4: Are we 'anonymous' AND are we allowed to stream ?
	if (($allow_anonymous=="1") && ($login=="anonymous")) {
		if ($allow_anonymous_streaming=="0") {
			redir("demo_anonymous.php");
			die();
		}
	}
}

session_start();

parse_str($_SERVER["QUERY_STRING"]);
require("disp.php");
require("db.php");
require("sql.php");

$user=$_SESSION['login']; 

// 0.7.3: Get special extensions, if defined:
if ((isset($special_extensions_enabled)) && ($special_extensions_enabled=="1")) {
	$special_extensions=explode(',',$special_extensions);
} else {
 	$special_extensions=array();
}

// 0.8.0: Create the xspf-playlist ?
if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
	xspf_create($user);
}	

/*
									*** PLAY ONE TRACK ***
									
*/


if ($act=="play") { // we just want to play/enqueue ONE track RIGHT now:
	$qry="SELECT * FROM track WHERE id=".$id;
	$result=execute_sql($qry,0,1,$nr);
	$row=mysql_fetch_array($result);
	if ($_SESSION['enqueue']=="0") { // play the stuff right away:
		$file=$row['path'];
		$name=set_name($row['performer_id'],$row['name'],$row['album_id'],$keep_extension,$file); 
		$handle=fopen("./tmp/".$user.".m3u", "w");
		// 0.8.4: split() replaced by explode():
		$item=explode(":",$row['duration']);
		$s=$item[1] + ($item[0]*60);

		// 0.7.3: Special handling of files w. "special" extension (f.ex. .m4a):
		$ext='.'.get_file_extension($file);
		$found=0;
		foreach ($special_extensions as $value) {
			if ($value==$ext) { $found=1; }
		}	
		if ($found==1) {
			handle_m4a($handle,1,$name,$keep_extension,$row,$s,$id,$user);
		} else { // Do as usual:
		write_m3u($handle,1,$s,"$name",$base_http_prog_dir.'/stream.php?/'.$name.'&id='.$id.'&user_id='.get_user_id($_SESSION['login']).'&language='.$_SESSION['lang'].get_now_playing_preferences($user)); // 0.6.5: get_now...
			// 0.8.0: Write to xspf-playlist ?
			if ((isset($xspf_enabled)) && ($xspf_enabled=='1')) {
				if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
					//cpy_file_to_tmp($row['path'],$row['id'].$ext,'./tmp/','1');
					xspf_write_track($user,$row,$name,$ext);
				}					
			}
		}
	
		// 0.5.0: Continue play after last track ?
		if (($_SESSION['autoplay_last']=="1") && ($_SESSION['enqueue']==0)) {
			$param=get_autoplay_parameters($_SESSION['login'],$_SESSION['autoplay_last_list']);
			// 0.5.1: added user_id and no translation of "Automatic play":
			$param.="&user_id=".get_user_id($_SESSION['login']);
			$param.=get_now_playing_preferences($user); // 0.6.5: get_now...
			write_m3u($handle,2,"-1","Automatic play",$base_http_prog_dir.$param);
		}	
		fclose($handle);
		$loc=$base_http_prog_dir."/tmp/".$user.".m3u?id=".date("U"); 

		check_anonymous_permissions($allow_anonymous,$_SESSION['login'],$allow_anonymous_streaming);

		// 0.8.0: Close the xspf-playlist:
		if ((isset($xspf_enabled)) && ($xspf_enabled=='1') && (isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
			xspf_close($user);
		}	
	
		if (!isset($demo)) { // 0.5.2: To ease HP setup...
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
			header("Location: $loc");
		} else {
			update_stats($id); // 0.6.0: We want visitors on the HP to see some action...
			redir("demo.php?id=$id");
		}					
	} else { // enqueue is NOT=0 -> place them in the queue:
		$qry="INSERT INTO queue VALUES ('','".$_SESSION['login']."',".$row['id'].")";
		$result=execute_sql($qry,0,-1,$nr);
		js_back(1);
	}			
}	


//
//
//
//						*** PLAY ALL OF SOMETHING ***
//
//
//
function wrt_ices($file,$new='0') { // EXPERIMENTAL:
	if ($new<>'0') {
		@unlink('/usr/share/ices/pl.m3u');
	} else {
		$h=fopen('/usr/share/ices/pl.m3u', 'a');
		fwrite($h,$file);
		fwrite($h,chr(10));
		fclose($h);
	}
}


if ($act=="playall") { // we want to play "all" of something
	$number=1;
	$handle=fopen("./tmp/".$user.".m3u", "w");

	if ($what=="albumid") {
		$qry="SELECT * FROM track WHERE album_id=".$id." ORDER BY ".$order_by." ".$dir;
	}	

	if ($what=="performerid") {
		$qry="SELECT * FROM track WHERE performer_id=".$id;
		if (isset($order_by) && ($order_by!="")) {
			$qry.=" ORDER BY ".$order_by." ".$dir;
		}	 		
	}	

	if ($what=="yearid") {
		$qry="SELECT * FROM track WHERE year=".$id;
		if (isset($order_by) && ($order_by!="")) {
			$qry.=" ORDER BY ".$order_by." ".$dir;
		}	 		
	}

	// 0.8.7: Play "all" of something based on advanced search result:
	if ($what=='advsearch') {
		$qry=get_advanced_search_query($order_by,$dir);
	}
	
	if ($what=="albumid" || $what=="performerid" || $what=="yearid" || $what=='advsearch') { // 0.8.7: advsearch added
		$result=execute_sql($qry,0,1000000,$nr);
		while ($row=mysql_fetch_array($result)) {
			if ($_SESSION['enqueue']==0) { // play tracks right away:
				$file=$row["path"];
				$name=set_name($row['performer_id'],$row['name'],$row['album_id'],$keep_extension,$file);
				// 0.8.4: split() replaced by explode():
				$item=explode(":",$row['duration']);
				$s=$item[1] + ($item[0]*60);
				// 0.7.3: Special handling of files w. "special" extension (f.ex. .m4a):
				$ext='.'.get_file_extension($file);
				$found=0;
				foreach ($special_extensions as $value) {
					if ($value==$ext) { $found=1; }
				}	
				if ($found==1) {
					handle_m4a($handle,1,$name,$keep_extension,$row,$s,$id,$user);
				} else { // Do as usual:
					write_m3u($handle,1,$s,$name,$base_http_prog_dir.'/stream.php?/'.$name.'&id='.$row['id'].'&user_id='.get_user_id($_SESSION['login']).'&language='.$_SESSION['lang'].get_now_playing_preferences($user)); // 0.6.5: get_now...
					// 0.8.0: Write to xspf-playlist ?
					if ((isset($xspf_enabled)) && ($xspf_enabled=='1')) {
						if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
							//cpy_file_to_tmp($row['path'],$row['id'].$ext,'./tmp/','1');
							xspf_write_track($user,$row,$name,$ext);
						}					
					}
				}
				// 0.6.0: Update stats if in demo-mode:
				if (isset($demo)) {
					update_stats($row['id']);
				}
									
				$number++;
			} else { // "enqueue" is NOT 0 -> put them in the queue:
				$qry2="INSERT INTO queue VALUES ('','".$_SESSION['login']."',".$row['id'].")";
				$result2=execute_sql($qry2,0,-1,$nr);
			}	
		} // while row=...
		// 0.5.0: Continue play after last track:
		if (($_SESSION['autoplay_last']=="1") && ($_SESSION['enqueue']==0)) {
			$param=get_autoplay_parameters($_SESSION['login'],$_SESSION['autoplay_last_list']);
			// 0.5.1: added user_id and no translation of "Automatic play":
			$param.="&user_id=".get_user_id($_SESSION['login']).'&language='.$_SESSION['lang'];	// 0.7.2
			$param.=get_now_playing_preferences($user); // 0.6.5
			write_m3u($handle,2,"-1","Automatic play",$base_http_prog_dir.$param);
		}
		// 0.8.0: Close the xspf-playlist:
		if ((isset($xspf_enabled)) && ($xspf_enabled=='1') && (isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
			xspf_close($user);
		}	

	} // albumid || performerid || yearid

	if ($what=="favorite_list" || $what=="queue") {
		if ($what=="favorite_list") { // 0.5.2: Changed below...
		 	$uid=get_user_id($_SESSION['login']);
		 	// 0.5.2: Find out if it's a shared list, - if it is: change uid:
		 	if (isset($shared)) {
		 		$tqry="SELECT * FROM fav_shares WHERE fav_name='".$id."'";
				$tqry.=" AND share_id='".get_user_id($_SESSION['login'])."'";
				$tres=execute_sql($tqry,0,1,$x);
				if ($x<>0) { // yes: change uid:
					$trow=mysql_fetch_array($tres);
					$uid=$trow['owner_id'];
				}
			}

			// 0.8.0: Damn...: Error correction:
			if ($order_by=='track.track_no') {
				$order_by='fav.id';
			}	

			$qry2="SELECT * FROM fav WHERE fav_name='".$id."'";
			$qry2.=" AND user_id='".$uid."'"; // 0.5.2: changed to uid
			$qry2.=" AND track_id<>0";
			$qry2.=" ORDER BY ".$order_by." ".$dir;
		}			

		if ($what=="queue") {
			$qry2="SELECT * FROM queue WHERE user_name='".$_SESSION['login']."' ORDER BY qid";
		}
		$result2=execute_sql($qry2,0,1000000,$nr);
		while ($row2=mysql_fetch_array($result2)) {
			if ($_SESSION['enqueue']=="0" || $what=="queue") { 
			// play tracks right away....
			// ...the queue can only be played, not queued (...):
				$qry="SELECT * FROM track ";
				$qry.="WHERE id=".$row2['track_id'];
				$result=execute_sql($qry,0,1,$nr);
				$row=mysql_fetch_array($result);
				$file=$row["path"];
				$name=set_name($row['performer_id'],$row['name'],$row['album_id'],$keep_extension,$file); 
				// 0.8.4: split() replaced by explode():
				$item=explode(":",$row['duration']);
				$s=$item[1] + ($item[0]*60);
				// 0.7.3: Special handling of files w. "special" extension (f.ex. .m4a):
				$ext='.'.get_file_extension($file);
				$found=0;
				foreach ($special_extensions as $value) {
					if ($value==$ext) { $found=1; }
				}	
				if ($found==1) {
					handle_m4a($handle,1,$name,$keep_extension,$row,$s,$id,$user);
				} else { // Do as usual:
					write_m3u($handle,1,$s,$name,$base_http_prog_dir.'/stream.php?/'.$name.'&id='.$row['id'].'&user_id='.get_user_id($_SESSION['login']).'&language='.$_SESSION['lang'].get_now_playing_preferences($user));
					// 0.8.0: Write to xspf-playlist ?
					if ((isset($xspf_enabled)) && ($xspf_enabled=='1')) {
						if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
							//cpy_file_to_tmp($row['path'],$row['id'].$ext,'./tmp/',$kext);
							xspf_write_track($user,$row,$name,$ext);
						}					
					}
				}				
				// 0.6.0: Update stats if in demo-mode:
				if (isset($demo)) {
					update_stats($row['id']);
				}

				$number++;
			} else { // enqueue is NOT=0 -> put 'em in the queue:	
				$qry3="INSERT INTO queue VALUES ('','".$_SESSION['login']."',".$row2['track_id'].")";
				$result3=execute_sql($qry3,0,-1,$nr);
			}
		}

		// 0.5.0: Continue play after last track ?
		if (($_SESSION['autoplay_last']=="1")) { // ...yes: 
			$param=get_autoplay_parameters($_SESSION['login'],$_SESSION['autoplay_last_list']);
			// 0.5.1: added user_id and no translation of "Automatic play":
			$param.="&user_id=".get_user_id($_SESSION['login']).'&language='.$_SESSION['lang'];	// 0.7.2
			$param.=get_now_playing_preferences($user); // 0.6.5
			write_m3u($handle,2,"-1","Automatic play",$base_http_prog_dir.$param);
		}		
		// 0.8.0: Close the xspf-playlist:
		if ((isset($xspf_enabled)) && ($xspf_enabled=='1') && (isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
			xspf_close($user);
		}	

	} // favoriteid || queue

	if ($_SESSION['enqueue']=="0" || $what=="queue") { // yes: send the .m3u file right away:	
		$number--;
		fclose($handle);
		$loc=$base_http_prog_dir."/tmp/".$user.".m3u?id=".date("U");
		check_anonymous_permissions($allow_anonymous,$_SESSION['login'],$allow_anonymous_streaming);

		if (!isset($demo)) { // If we're not in demo-mode... 
			// 0.8.0: Use xspf (flash player) ?
			if ((isset($xspf_enabled)) && ($xspf_enabled=='1')) {
				if ((isset($_SESSION['xspf_active'])) && ($_SESSION['xspf_active']=='1')) {
					$loc=xspf_make_url($base_http_prog_dir,$user);
					xspf_play_it($loc);
				}	
				// We're forced to use the flash player:
				if ((isset($xspf_only_player)) && ($xspf_only_player=='1')) {
					$loc=xspf_make_url($base_http_prog_dir,$user);
					xspf_play_it($loc);
				}		
			}	
			// Otherwise, just send the playlist. Let client determine what media-player is associated w. playlists (.m3u):
			header("Location: $loc");
		} else { // ...we ARE in demo-mode:
			$add_url='';
			if ($what=='albumid') { $add_url='?aid='.$id; }
			if ($what=='performerid') { $add_url='?pid='.$id; }
			redir("demo.php$add_url");
		}				
	} else { // No: we're not playing right away -  so step back 1 page:
		js_back(1);
	}		
} // playall	

?>
