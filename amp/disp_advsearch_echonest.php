<?php
// 0.8.8: Added the four new parameters: liveness,speechiness,acoutsticness and valence.
// ...."THANKS" for (not) telling me about 'em for 6 months, Echonest...
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}	

if (!isset($act)) {
	$act='setup';
}
$act=my_filter_var($act);

// Set session stuff:
if (!isset($_SESSION['use_tempo'])) {
	$_SESSION['use_tempo']='';
}
if (!isset($_SESSION['use_danceability'])) {
	$_SESSION['use_danceability']=''; 
}
if (!isset($_SESSION['use_energy'])) {
	$_SESSION['use_energy']='';
}
/*
if (!isset($_SESSION['use_loudness'])) {
	$_SESSION['use_loudness']='';
}
*/
if (!isset($_SESSION['use_key'])) {
	$_SESSION['use_key']='';
}
if (!isset($_SESSION['use_time_signature'])) {
	$_SESSION['use_time_signature']='';
}
if (!isset($_SESSION['use_year_range'])) {
	$_SESSION['use_year_range']='';
}
if (!isset($_SESSION['use_duration'])) {
	$_SESSION['use_duration']='';
}
// 0.8.8: New: liveness,speechiness,acousticness,valence
if (!isset($_SESSION['use_liveness'])) {
    $_SESSION['use_liveness']='';
}
if (!isset($_SESSION['use_speechiness'])) {
    $_SESSION['use_speechiness']='';
}
if (!isset($_SESSION['use_acousticness'])) {
    $_SESSION['use_acousticness']='';
}
if (!isset($_SESSION['use_valence'])) {
    $_SESSION['use_valence']='';
}

require_once('sql.php');
require_once('disp.php');
require_once('set_td_colors.php');


/* 
****************************************************************************
				THE FORM
****************************************************************************
*/
if ($act=='setup') {
	echo headline($what,'',xlate('Tracks'));
	print "\n\n\n <!-- Now on to the adv.search FORM --> \n\n\n </td></tr><tr><td>";

	echo '<FORM NAME="amp_juke_adv_search_form" METHOD="POST" action="./?what=advsearch&act=check">';
	echo std_table("ampjuke_content_table","ampjuke_content");
	//require('tbl_header.php');

	// Tempo aka. BPM:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_tempo',$_SESSION['use_tempo']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Tempo/BPM range:</label>
		<input type="text" class="tfield" id="tempo" name="tempo" size="9" />
		<div id="slider-range-tempo"></div>	
	</td>

	<?php
	echo '</tr>';

	// Danceability:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_danceability',$_SESSION['use_danceability']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Danceability:</label>
		<input type="text" class="tfield" id="danceability" name="danceability" size="10" />
		<div id="slider-range-danceability"></div>	
	</td>
	
	<?php
	echo '</tr>';

	// Energy:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_energy',$_SESSION['use_energy']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Energy:</label>
		<input type="text" class="tfield" id="energy" name="energy" size="12" />
		<div id="slider-range-energy"></div>	
	</td>
	
	<?php
	echo '</tr>';
	
	// Key:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_key',$_SESSION['use_key']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Key:</label>
		<input type="text" class="tfield" id="key" name="key" size="9" />
		<div id="slider-range-key"></div>	
	</td>
	
	<?php
	echo '</tr>';
	
	// Time_signature:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_time_signature',$_SESSION['use_time_signature']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Time signature:</label>
		<input type="text" class="tfield" id="time_signature" name="time_signature" size="9" />
		<div id="slider-range-time_signature"></div>	
	</td>

	<?php
	echo '</tr>';	

	// Year_range:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_year_range',$_SESSION['use_year_range']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Years:</label>
		<input type="text" class="tfield" id="year_range" name="year_range" size="12" />
		<div id="slider-range-year_range"></div>	
	</td>

	<?php
	echo '</tr>';	

	// 0.8.8: Liveness:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_liveness',$_SESSION['use_liveness']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Liveness:</label>
		<input type="text" class="tfield" id="liveness" name="liveness" size="12" />
		<div id="slider-range-liveness"></div>	
	</td>

	<?php
	echo '</tr>';	

	// 0.8.8: Speechiness:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_speechiness',$_SESSION['use_speechiness']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Speechiness:</label>
		<input type="text" class="tfield" id="speechiness" name="speechiness" size="12" />
		<div id="slider-range-speechiness"></div>	
	</td>

	<?php
	echo '</tr>';	
	
	// 0.8.8: Acousticness:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_acousticness',$_SESSION['use_acousticness']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Acousticness:</label>
		<input type="text" class="tfield" id="acousticness" name="acousticness" size="12" />
		<div id="slider-range-acousticness"></div>	
	</td>

	<?php
	echo '</tr>';	
	
	// 0.8.8: Valence:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_checkbox('use_valence',$_SESSION['use_valence']).'</td>';
	echo '<td>'; ?>
	<td>
		<label>Valence:</label>
		<input type="text" class="tfield" id="valence" name="valence" size="12" />
		<div id="slider-range-valence"></div>	
	</td>

	<?php
	echo '</tr>';	

	
	echo '<input type="hidden" name="sid" value="'.session_id().'">';
	
	// Submit:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="3" align="center"><input type="submit" value="'.xlate('Save & continue').'">';
	echo '</td></tr>';

	print "</table></form> \n\n";
} // act=setup


/* 
****************************************************************************
				GET VALUES + CONSTRUCT QUERY
****************************************************************************
*/
function loc_get_values($s,&$min,&$max,$default_min,$default_max) {
	$n=0;
	if (strlen($s)<3) {
		$min=$default_min;
		$max=$default_max;
	}
	
	$v=explode('-',$s);
	if ((is_array($v)) && (sizeof($v)==2)) {
		$min=trim($v[0]);
		$max=trim($v[1]);
		$min=only_digits($min);
		$max=only_digits($max);
		if ($min>$max) { 
			$min=$max;
		}
	}
}

if ($act=='check') {
// Did we leave this page from an earlier (old) session ?
	if (!isset($_POST['sid'])) {
		redir('index.php?what=welcome');
	}
// Tempo/BPM:
	if (isset($_POST['use_tempo'])) {
		$_SESSION['use_tempo']='1';
	} else {
		$_SESSION['use_tempo']='';
	}
	loc_get_values($_POST['tempo'],$tempo_min,$tempo_max,80,130);
	//echo '<br>Tempo min='.$tempo_min.' max='.$tempo_max;
	$_SESSION['tempo_min']=$tempo_min;
	$_SESSION['tempo_max']=$tempo_max;
// Danceability:
	if (isset($_POST['use_danceability'])) {
		$_SESSION['use_danceability']='1';
	} else {
		$_SESSION['use_danceability']='';
	}
	loc_get_values($_POST['danceability'],$danceability_min,$danceability_max,0.4,0.7);
	//echo '<br>Danceability min='.$danceability_min.' max='.$danceability_max;
	$_SESSION['danceability_min']=$danceability_min;
	$_SESSION['danceability_max']=$danceability_max;
// Energy:
	if (isset($_POST['use_energy'])) {
		$_SESSION['use_energy']='1';
	} else {
		$_SESSION['use_energy']='';
	}
	loc_get_values($_POST['energy'],$energy_min,$energy_max,0.4,0.7);
	//echo '<br>Energy min='.$energy_min.' max='.$energy_max;
	$_SESSION['energy_min']=$energy_min;
	$_SESSION['energy_max']=$energy_max;
// Key:
	if (isset($_POST['use_key'])) {
		$_SESSION['use_key']='1';
	} else {
		$_SESSION['use_key']='';
	}
	loc_get_values($_POST['key'],$key_min,$key_max,1,10);
	//echo '<br>Key min='.$key_min.' max='.$key_max;
	$_SESSION['key_min']=$key_min;
	$_SESSION['key_max']=$key_max;
// Time signature:
	if (isset($_POST['use_time_signature'])) {
		$_SESSION['use_time_signature']='1';
	} else {
		$_SESSION['use_time_signature']='';
	}
	loc_get_values($_POST['time_signature'],$time_signature_min,$time_signature_max,4,6);
	//echo '<br>Time_signature min='.$time_signature_min.' max='.$time_signature_max;
	$_SESSION['time_signature_min']=$time_signature_min;
	$_SESSION['time_signature_max']=$time_signature_max;
// Year range:
	if (isset($_POST['use_year_range'])) {
		$_SESSION['use_year_range']='1';
	} else {
		$_SESSION['use_year_range']='';
	}
	loc_get_values($_POST['year_range'],$year_range_min,$year_range_max,1910,date('Y'));
	//echo '<br>Year_range min='.$year_range_min.' max='.$year_range_max;
	$_SESSION['year_range_min']=$year_range_min;
	$_SESSION['year_range_max']=$year_range_max;
// Liveness - new in 0.8.8:
    if (isset($_POST['use_liveness'])) {
        $_SESSION['use_liveness']='1';
    } else {
        $_SESSION['use_liveness']='';
    }
    loc_get_values($_POST['liveness'],$liveness_min,$liveness_max,0.4,0.7);
    //echo '<br>Liveness min='.$liveness_min.' max='.$liveness_max; 
    $_SESSION['liveness_min']=$liveness_min;
    $_SESSION['liveness_max']=$liveness_max;
// Speechiness - new in 0.8.8:
    if (isset($_POST['use_speechiness'])) {
        $_SESSION['use_speechiness']='1';
    } else {
        $_SESSION['use_speechiness']='';
    }
    loc_get_values($_POST['speechiness'],$speechiness_min,$speechiness_max,0.4,0.7);
    //echo '<br>Speechiness min='.$speechiness_min.' max='.$speechiness_max; 
    $_SESSION['speechiness_min']=$speechiness_min;
    $_SESSION['speechiness_max']=$speechiness_max;
// Acousticness - new in 0.8.8:
    if (isset($_POST['use_acousticness'])) {
        $_SESSION['use_acousticness']='1';
    } else {
        $_SESSION['use_acousticness']='';
    }
    loc_get_values($_POST['acousticness'],$acousticness_min,$acousticness_max,0.4,0.7);
    //echo '<br>Acousticness min='.$acousticness_min.' max='.$acousticness_max; 
    $_SESSION['acousticness_min']=$acousticness_min;
    $_SESSION['acousticness_max']=$acousticness_max;    
// Valence - new in 0.8.8:
    if (isset($_POST['use_valence'])) {
        $_SESSION['use_valence']='1';
    } else {
        $_SESSION['use_valence']='';
    }
    loc_get_values($_POST['valence'],$valence_min,$valence_max,0.4,0.7);
    //echo '<br>Valence min='.$valence_min.' max='.$valence_max; 
    $_SESSION['valence_min']=$valence_min;
    $_SESSION['valence_max']=$valence_max;

// Wrap it up:    
    $loc=$base_http_prog_dir.'/index.php?what=advsearch&act=disp&dir=ASC&sorttbl=track&order_by=track.last_played'; 
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("'.$loc.'");';
	echo '</script>';   
	
} // act=check


/* 
****************************************************************************
				DISPLAY RESULTS (follows act=check - see above)
****************************************************************************
*/
// Before we even begin: check we have something running in the session (ie.: we didn't leave this in a tab from previous session):
/*
if (!isset($_SESSION['tempo_min'])) {
    $loc=$base_http_prog_dir.'/index.php?what=advsearch&act=setup&r='.date('U'); 
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("'.$loc.'");';
	echo '</script>';   
}
*/
if ($act=='disp') {
	if ($dir=='ASC') {
		$newdir='DESC';
	} else {
		$newdir='ASC';
	}
	$qry=get_advanced_search_query($order_by,$dir); // disp.php
    $result=execute_sql($qry,0,100000,$nr);
    //echo 'QRY='.$qry.' -> nr='.$nr.'<br>';
    //var_dump($_SESSION);
	
// ************** DISPLAY searchresults:	

// Headline:
	echo headline($what,'',xlate('Tracks').'. <b>'.xlate('Matches').': '.$nr.'</b>');

// Actions:
	echo std_table("ampjuke_actions_table","");
	echo '<tr><td>';
	// View all/some results:
	if (!isset($showall)) {
		if ($nr>$_SESSION['count']) {
			echo '<a href="./?what=advsearch&act=disp&order_by='.$order_by.'&dir='.$dir.'&showall='.$nr.'">';
			echo '<img src="./ampjukeicons/expand.gif" border="0" title="View all (max 250)">';
			echo '</a>&nbsp&nbsp';
		}	
	} else {
		if ($showall>$_SESSION['count']) {
			echo '<a href="./?what=advsearch&act=disp&order_by='.$order_by.'&dir='.$dir.'">';
			echo '<img src="./ampjukeicons/collapse.gif" border="0" title="View some">';
			echo '</a>&nbsp&nbsp';
		}	
	}	
	// Adjust settings:
	echo '<a href="./?what=advsearch&act=setup&r='.date('U').'">';
	echo get_icon($_SESSION['icon_dir'],'menu_search',xlate('Advanced search'));
	echo '</a>&nbsp&nbsp';
	// Play all or queue all:
	if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {
    	if ($_SESSION['enqueue']=='0') {
   	    	echo '<a href="./play_action.php?act=playall&what=advsearch&order_by='.$order_by.'&dir='.$dir.'">';
	    	echo get_icon($_SESSION['icon_dir'],'play',xlate('Play all tracks from').': '.xlate('Advanced search'));
	    	echo '</a>';
   	    	echo '<a href="./play_action.php?act=playall&what=advsearch&order_by=rand()&dir='.$dir.'">';   	
	    	echo get_icon($_SESSION['icon_dir'],'menu_random',xlate('Random play').': '.xlate('Advanced search'));
    	} else {
	    	echo get_icon($_SESSION['icon_dir'],'queue_add',xlate('Queue all tracks from').': '.xlate('Advanced search'));
    	}
    	echo '</a>&nbsp&nbsp';
    }
	// Add to favorite:
	echo add_add2fav_link('advsearch&order_by='.$order_by.'&dir='.$dir,0,$_SESSION['hide_icon_text']);
	echo '</td></tr></table>';

// Results:	
	echo std_table("ampjuke_content_table","ampjuke_content");	
	// 0.8.7: The following is more or less a copy of disp_track.php. However, few adjustments exists.
	require_once("tbl_header.php");
	if ($_SESSION['show_ids']=="1") {
		tbl_header($what.'&act=disp',xlate("ID"),"left","track.id",$order_by,$dir,$newdir,
		$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
	}	
	tbl_header($what.'&act=disp',xlate("Title"),"left","track.name",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);

	// 0.6.6: Moved here - was 1st before...
	tbl_header($what.'&act=disp',$d_performer,"left","performer.pname",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);

	tbl_header($what.'&act=disp',$d_year,"left","track.year",$order_by,$dir,$newdir,
	$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);

	if ($_SESSION['disp_duration']=="1") {
		tbl_header($what.'&act=disp',xlate("Duration"),"right","track.duration",$order_by,$dir,$newdir,
		$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
	}

	if ($_SESSION['disp_last_played']=="1") {
		tbl_header($what.'&act=disp',xlate("Last played"),"right","track.last_played",$order_by,$dir,$newdir,
		$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
	}

	if ($_SESSION['disp_times_played']=="1") {
		tbl_header($what.'&act=disp',xlate("Played"),"right","track.times_played",$order_by,$dir,$newdir,
		$count,'limit='.$limit.'&sorttbl=track&pagesel='.$pagesel);
	}

	echo '<th class="tbl_header"> </th>';

	// Limit number of search results displayed ?
	$count=$_SESSION['count'];
	if (isset($showall)) {
		if (strval(intval($showall))==strval($showall)) { 
			$count=$showall;
			if ($count>250) { // Hardcoded limit to avoid showing 1000's of search results
				$count=250;
			}
			if ($count<1) {
				$count=$_SESSION['count'];
			}
		}
	}
	while (($row=mysql_fetch_array($result)) && ($count>0)) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		
		if ((isset($_SESSION['show_ids'])) && ($_SESSION['show_ids']=='1')) { // 0.8.5
			echo '<td class="content">'.add_edit_link('track',$row['id'],'').' '.add_edit_link_tags($row['id']).'</td>';
		}

		echo '<td class="content">'.add_play_link("play",$row['id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php

		$perf=get_performer_name($row['performer_id']);
		echo '<td class="content">'.add_performer_link($perf,$row['performer_id'],$_SESSION['disp_small_images']).'</td>';

		echo add_year_link($row['year'],$row['year']);

		display_duration($row['duration']);

		display_last_played($row['last_played']);

		display_times_played($row['times_played']);

		echo '<td class="content" align="right">'; // 0.8.4
		echo add_add2fav_link("track",$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon... introduced

		add_download_link("track",'',$row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide_icon.. introduced

		add_lyrics_link($row['id'],$_SESSION['hide_icon_text']); // 0.8.4: hide... introduced

		print "</td></tr> \n";
		$count--;
	}

	echo '</table>';	
		
	//require('page_numbers.php');
}
?>
