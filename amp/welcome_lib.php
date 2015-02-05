<?php

// welcome_lib.php: "library" used w. welcome page in AmpJuke.
// 20131010/Michael

require_once('echonest_lib.php');

if (isset($_SESSION['welcome_func'])) {
    $ret='';
    $uid=get_user_id($_SESSION['login']);
    $total=5;
    if (isset($_SESSION['welcome_num_items'])) {
        $total=only_digits($_SESSION['welcome_num_items']);
    }
    
    if ($_SESSION['welcome_func']=='related_tracks') {
        $ret=std_table("ampjuke_content_table","ampjuke_content_table2");
        if (file_exists('./tmp/last_track_'.$uid.'.txt')) {
            $id=only_digits(file_get_contents('./tmp/last_track_'.$uid.'.txt'));
            
            $r=get_track_extras($id);
            $related_tracks=echonest_get_related_tracks($r,$total,date('U')); // get $total related tracks
            $ret.='<tr><td><b>'.xlate('Title').'</td><td><b>'.xlate('Performer').'</td>';
  		    $ret.='<td align="left"><b>'.xlate('Year').'</td>';
            $ret.='<td align="right"><b>'.xlate('Duration').'</td>';
            $ret.='<td align="right"><b>'.xlate('Last played').'</td>';
            $ret.='<td align="right"><b>'.xlate('Played').'</td>';
		    $ret.='<td> </td>';
            //$ret.='<table class="ampjuke_content">';
            $i=0;
            $related_track=explode(',',$related_tracks);
      		while (($i<sizeof($related_track)-1) && ($i<$total)) {
			    $t=get_track_extras(trim($related_track[$i]));
                $ret.=fancy_tr_buf($tmpcount,'','','');
                
			    if (is_array($t)) {
				    $p=get_performer_name_track($t['performer_id'],$t['album_id'],$perf_name,$pid);
				    // Title:
				    $ret.='<td>'.add_play_link('play',$related_track[$i],$t['name']).'</td>';
				    // Performer/artist:
                    $ret.='<td>'.add_performer_link($perf_name,$pid,$_SESSION['disp_small_images']).'</td>';
                    // Year:
                    $ret.=add_year_link($t['year'],$t['year'],'right');
                    // Duration:
                    if ($_SESSION['disp_duration']=="1") {
                        $ret.='<td align="right">'.$t['duration'].'</td>';
                    } else {
                        $ret.='<td> </td>';
                    }
                    // Last played:
                    $ret.='<td align="right">'.mydate($t['last_played']).'</td>';
                    // Playcount:
                    $ret.='<td align="right">'.$t['times_played'].'</td>';
                    // Add2fav.:           
                    $ret.='<td class="content" align="right">';
                    $ret.=add_add2fav_link("track",$t['id'],$_SESSION['hide_icon_text']).'</td>';
			    }
                $ret.='</tr>';
			    $i++;
		    }
		    $ret.='</table>';      
        }
    }      
    
    if ($_SESSION['welcome_func']=='related_performers') {
        require_once('lastfm_lib.php');
        include('db.php');
        $ret=std_table("ampjuke_content_table","ampjuke_content_table2");
        $ret.=fancy_tr_buf($tmpcount,'','','');

        if (file_exists('./tmp/last_perf_'.$uid.'.txt')) {
            $pid=only_digits(file_get_contents('./tmp/last_perf_'.$uid.'.txt'));
            $pname=get_performer_name($pid);
            
        	$total_related_performers=lastfm_get_number_of_related_performers($pid,urlencode($pname),
        	$lastfm_min_related_match,$lastfm_max_related_artists);
        
        	if ((isset($refresh_related)) && ($refresh_related==1)) { // Ask last.fm (req. by user)
        		$total_related_performers=0;
        	}	
	
        	if ($total_related_performers==0) { // ask last.fm:
        		$total_related_performers=lastfm_update_related_performers($pid,urlencode($pname),
        		$lastfm_min_related_match,$lastfm_max_related_artists);
        		$total_related_performers=lastfm_get_number_of_related_performers($pid,urlencode($pname),
        		$lastfm_min_related_match,$lastfm_max_related_artists);
        	}	

        	$n=0;
        	$ampjuke_animated_objects=1;
        	$cover_param='border="0" width="126px" height="126px"'; // fake it to make it :-)
        	if ($total_related_performers>0) {
	        	$xml=retrieve_xml('./lastfm/'.$pid.'.xml',$n,$lastfm_max_related_artists);
        		while (($n<$lastfm_max_related_artists) && ($n<$total)) { 
        			if (!isset($xml->similarartists->artist[$n]->image[0])) {
        				$n=$lastfm_max_related_artists+1;
        			} else {
                        $rel_filename=$xml->similarartists->artist[$n]->image[0];
                        $pidx=1;
                        $pidx=get_performer_id_by_name(mysql_escape_string($xml->similarartists->artist[$n]->name[0]));
                        if ($pidx<>1) {	
                            $ret.='<td class="content" align="center">';
                            $ret.=add_performer_link('<p class="ampjuke_animation_'.$ampjuke_animated_objects.'"><img src="'.$rel_filename.'" '.$cover_param.' title="'.get_performer_name($pidx).'" class="tooltip">',$pidx);
                            $ampjuke_animated_objects++; // 0.8.5
                            $ret.='<br>'.add_performer_link(get_performer_name($pidx),$pidx);
                        }
                    }
                    $n++;
                }
            }
        } else {
            $ret.='<td>'.xlate('None').'</td>';
        }
        $ret.='</tr></table>';
    }
    echo $ret;
}

?>

    
