<?php
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    die('<a href="./login.php">Timeout. Login again.</a>');
}	
require_once("sql.php");
require_once("disp.php");
require_once("set_td_colors.php");

// 0.8.6: Makes notes (in red) about various settings:
function settings_note($setting_enabled,$msg) {
	$ret='';
	if ($setting_enabled=='1') {
		$ret='<font color="red">'.$msg.'</font>';
	}
	return $ret;
}

echo headline($what,'','');
print "</td></tr> \n\n\n <!-- Actual CONTENT comes here: --> \n\n\n <tr><td>";

echo '<table class="ampjuke_headline_table"><tr><td>';
echo xlate('Expand all').':';
?>
	<img src="./ampjukeicons/expandall.gif" id="exp" onclick="cfg_expand_collapse_all('1')">
	<br><?php echo xlate('Collapse all').':' ?>
	<img src="./ampjukeicons/collapseall.gif" id="exp" onclick="cfg_expand_collapse_all('0')">
	
	

<?php	

echo '<FORM NAME="setup" method="POST" action="write_settings.php">';

//
//
//			1. PLAY & DISPLAY OPTIONS
//
//

?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif1" onclick="handleClick('to_col1','gif1')">
<?php echo xlate('Play & display options'); ?>
<div id="to_col1" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content");
// play/enqueue:
// Will NOT be shown to anonymous users:

if ($_SESSION['login']!="anonymous") {
    if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) { // 0.8.7: Added jukebox_mode...
	    fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	    echo '<td valign="top" width="50%">';
	    //echo settings_note($jukebox_mode_enabled,'* '); // 0.8.6
	    echo xlate("When a track is selected").':'.add_faq(59).'</td>';
	    echo '<td align="left"><input type="radio" name="playmethod" value="1"';
	    if ($_SESSION['enqueue']=="1") { echo ' checked'; }
	    echo '> '.xlate("Put it in the queue").'<br>';
	    echo '<input type="radio" name="playmethod" value="0"';
	    if ($_SESSION['enqueue']=="0") { echo ' checked'; }
	    echo '> '.xlate("Play it immediately");
	    echo '</td></tr>';
    } else { // 0.8.7: We're in jukebox mode - just remember the settings:
        echo '<input type="hidden" name="playmethod" value="'.$_SESSION['enqueue'].'">';
    }
}	

// Use flash player ?
if (($_SESSION['login']!='anonymous') && (isset($xspf_enabled)) && ($xspf_enabled=='1')) {
	if ((isset($xspf_only_player)) && ($xspf_only_player=='0')) { // We're allowed to change it...
	    if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled<>'1')) {
		    fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		    //echo '<td>'.settings_note($jukebox_mode_enabled,'* '); // 0.8.6
		    echo '<td>'.xlate("Use flash player to listen to music").':'.add_faq(71).'</td>';
		    echo '<td align="left">'.add_checkbox('xspf_active',$_SESSION['xspf_active']);
		    echo ' </td></tr>';
		}
	} 	
} else { // We're NOT allowed to change it, but remember the setting:
	echo '<input type="hidden" name="xspf_active" value="'.$_SESSION['xspf_active'].'">';
}
	
// how many items/page:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td>'.xlate("Number of items per page").':'.add_faq(60).'</td>';
echo '<td align="left">'.add_textinput('count',$_SESSION['count'],5).'</td></tr>';

// disply when track was played last:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td>'.xlate("Display when a track was played last time").':'.add_faq(61).'</td>';
echo '<td align="left">'.add_checkbox('disp_last_played',$_SESSION['disp_last_played']).'</td></tr>';

// display how many times a track has been played:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td>'.xlate("Display how many times a track has been played").':'.add_faq(62).'</td>';
echo '<td align="left">'.add_checkbox('disp_times_played',$_SESSION['disp_times_played']).'</td></tr>';

// display ID-numbers:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Display ID numbers").':'.add_faq(63);
echo '</td><td align="left">'.add_checkbox('show_ids',$_SESSION['show_ids']).'</td></tr>';

// show letters ('jump to'):
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Show letters (the 'Jump to' option)").':'.add_faq(66).'</td>';
echo '<td align="left">'.add_checkbox('show_letters',$_SESSION['show_letters']).'</td></tr>';

// display duration:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Display duration on tracks").':'.add_faq(64).'</td>';
echo '<td align="left">'.add_checkbox('disp_duration',$_SESSION['disp_duration']).'</td></tr>';

// display total duration:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Display totals").':'.add_faq(65).'</td>';
echo '<td align="left">'.add_checkbox('disp_totals',$_SESSION['disp_totals']).'</td></tr>';

// Only display option for "related performers" if it's enabled in the server configuration...
if ($lastfm_allow_related=='1') {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Display related performers").":".add_faq(41)."</td>";
	echo '<td align="left">'.add_checkbox('disp_related_performers',$_SESSION['disp_related_performers']).'</td></tr>';
} else { // ...but hide/remember previous user-setting if related performers are turned off:
	if ($_SESSION['disp_related_performers']=="1") {
		echo '<input type="hidden" name="disp_related_performers" value="1">';
	}
}	

// Display a small image in association w. performers and albums:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Display small images (albums/performers)").":".add_faq(53)."</td>";
echo '<td align="left">'.add_checkbox('disp_small_images',$_SESSION['disp_small_images']).'</td></tr>';
// Browse albums by cover:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Browse albums by covers").":".add_faq(67)."</td>";
echo '<td align="left">'.add_checkbox('browse_albums_by_covers',$_SESSION['browse_albums_by_covers']).'</td></tr>';
// Browse performers by pictures:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Browse performers by pictures").":".add_faq(68)."</td>";
echo '<td align="left">'.add_checkbox('browse_performer_by_picture',$_SESSION['browse_performer_by_picture']).'</td></tr>';
// Confirm deletion:
if ($_SESSION['login']!="anonymous") {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Confirm deletion").':'.add_faq(69).'</td>';
	echo '<td align="left">'.add_checkbox('confirm_delete',$_SESSION['confirm_delete']).'</td></tr>';
}	

// IF we are allowed to download, then display the option to show/hide that:
if ($_SESSION['can_download']=="1") {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Show download option").':'.add_faq(70).'</td>';
	echo '<td align="left">'.add_checkbox('disp_download',$_SESSION['disp_download']).'</td></tr>';
}

// IF we are allowed to upload, then display option to show/hide that:
if ($_SESSION['can_upload']=="1") {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Show upload option").':'.add_faq(70).'</td>';
	echo '<td align="left">'.add_checkbox('disp_upload',$_SESSION['disp_upload']).'</td></tr>';
}

// Is lyrics enabled ? If yes: Allow changing this:
if ($lyrics_enabled==1) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Lyrics").":".add_faq(26)."</td>";
	echo '<td align="left">'.add_checkbox('disp_lyrics',$_SESSION['disp_lyrics']).'</td></tr>';
} 	

// 0.8.4: Hide text after icons ?
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td>'.xlate('Hide text next to icons').':'.add_faq(84).'</td>';
echo '<td align="left">'.add_checkbox('hide_icon_text',$_SESSION['hide_icon_text']).'</td></tr>';

$display_it=0; // Dont display the option
if (($_SESSION['login']!='anonymous') && ($allow_now_playing==1)) {
	$display_it=1;
}
if (($xspf_enabled=='1') && ($_SESSION['xspf_active'])) {
	$display_it=0;
}	
if ($display_it==1) {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);	
	echo '<td>'.xlate("Display what is being played").'</b>:'.add_faq(42).'</td>';	
	echo '<td align="left">'.add_checkbox('disp_now_playing',$_SESSION['disp_now_playing']);
	// 0.8.2: Also offer this option in "Now playing" ?
	if ($lastfm_allow_favorite_suggestion=='1') {
		echo add_checkbox('disp_now_playing_add2favorite',$_SESSION['disp_now_playing_add2favorite']);
		echo ' '.xlate('Show option to add to favorite').add_faq(81);
	} else {
		echo '<input type="hidden" name="disp_now_playing_add2favorite" value="'.$_SESSION['disp_now_playing_add2favorite'].'">';
	}
	// Note about flash ?
	if (($xspf_enabled=='1') && ($_SESSION['xspf_active'])) {
		echo '<b><font color="red"> ';
		echo xlate('Note: Flash player is active. This function is disabled').'</b></font> '.add_faq(72);
	}	
	echo '</td></tr>';
} 
// 0.8.5: Removed:
//else { // Hide it, but make a "dummy" entry:
//	echo '<input type="hidden" name="disp_now_playing" value="'.$_SESSION['disp_now_playing'].'">';
//	echo '<input type="hidden" name="disp_now_playing_add2favorite" value="'.$_SESSION['disp_now_playing_add2favorite'].'">'; // 0.8.2
//}	

echo '</table></div>';

//
//
//			2. FAVORITES
//
//

if ($_SESSION['login']!="anonymous") {
	// Expand/collapse:
	?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif10" onclick="handleClick('to_col10','gif10')">
	<?php echo xlate('Favorites'); ?>
	</p>
	<div id="to_col10" style="display:none;">
	<?php
	echo std_table("ampjuke_content_table","ampjuke_content10");
	$table10=1;
	// Show/hide shared favorites:
	// 0.8.3: we might hide this altogether, if the almighty configuration says: No, you cannot display shared favorite lists:
	if ((isset($shared_favorites_allow)) && ($shared_favorites_allow=='1')) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo "<td>".xlate("Display shared favorites").":".add_faq(29)."</td>";
		echo '<td align="left">'.add_checkbox('disp_fav_shares',$_SESSION['disp_fav_shares']).'</td></tr>';
	} else { // save it (although we're not using it in the SESSION, it might be turned on later by some admin):
		echo '<input type="hidden" name="disp_fav_shares" value="'.$_SESSION['disp_fav_shares'].'">';
	}
	// Ask for favoritelist name when adding:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Ask for name of favoritelist every time").":".add_faq(31)."</td>";
	echo '<td align="left">'.add_checkbox('ask4favoritelist',$_SESSION['ask4favoritelist']);
	// 0.8.2: Are we allowed to & do we also want suggestions ?
	if ($lastfm_allow_favorite_suggestion=='1') {
		echo ' '.add_checkbox('ask4favoritelist_disp_suggestion',$_SESSION['ask4favoritelist_disp_suggestion']);
		echo ' '.xlate('Suggest favorites based on tags');
	} else {
		echo '<input type="hidden" name="ask4favoritelist_disp_suggestion" value="'.$_SESSION['ask4favoritelist_disp_suggestion'].'">';
	}
	echo '</td></tr>';
	// Avoid duplicate entries:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo "<td>".xlate("Avoid duplicate entries").":".add_faq(46)."</td>";
	echo '<td align="left">'.add_checkbox('avoid_duplicate_entries',$_SESSION['avoid_duplicate_entries']).'</td></tr>';
	// 0.8.2: Add what's being played automatically to favorite lists ?
	if ((isset($lastfm_allow_auto_add2favorite)) && ($lastfm_allow_auto_add2favorite=='1')) { 
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td valign="top">'.xlate('Add tracks to favorite lists automatically').':'.add_faq(82).'</td>';
		echo '<td align="left">'.add_checkbox('auto_add2favorite',$_SESSION['auto_add2favorite']);
		// 0.8.3: Only add to EXISTING favorite lists ?
		echo add_checkbox('auto_add2favorite_create_new',$_SESSION['auto_add2favorite_create_new']);
		echo ' '.xlate('Create new favorite lists automatically').'<br>';
		
		echo xlate('Prefix').':'.add_textinput('auto_add2favorite_prefix',$_SESSION['auto_add2favorite_prefix'],40);
		echo '</td></tr>';		
	} else {
		echo '<input type="hidden" name="auto_add2favorite" value="'.$_SESSION['auto_add2favorite'].'">';
		echo '<input type="hidden" name="auto_add2favorite_prefix" value="'.$_SESSION['auto_add2favorite_prefix'].'">';
	}

	echo '</table></div>';
}

//
//
//			3. AUTOMATIC PLAY
//
//
// Further, ask for autoplay w. associated parameters after login...
// This option will obviously NOT be shown for "anonymous" users:
if ($_SESSION['login']!="anonymous") {
	if (($_SESSION['xspf_active']!='1') && ($jukebox_mode_enabled=='0')) { // 0.8.0: Only display settings if flah-player is *NOT* active:
	// 0.8.7: ...AND we're not in jukebox-mode
		// Expand/collapse:
		?>
		<p class="note" align="left"><b>
		<img src="./ampjukeicons/expand.gif" id="gif2" onclick="handleClick('to_col2','gif2')">
		<?php echo xlate('Automatic play'); ?>
		</p>
		<div id="to_col2" style="display:none;">
		<?php
 
		echo std_table("ampjuke_content_table","ampjuke_content5");
		$table5=1;
	} // ...if flash player is active
	
	// First, get the defaults:
	$qry="SELECT * FROM user WHERE name='".$_SESSION['login']."'";
	$result=execute_sql($qry,0,10,$nr);
	$row=mysql_fetch_array($result);
	$autoplay=$row['autoplay'];
	$autoplay_num_tracks=$row['autoplay_num_tracks'];
	$autoplay_list=$row['autoplay_list'];
	// Automatic play after last track
	$autoplay_last=$row['autoplay_last'];
	$autoplay_last_list=$row['autoplay_last_list'];

	// Second, show the options in relation to this:
	// Only show if flash-player isn't active:
	// ...0.8.7: ...AND we're not in jukebox-mode:
	if (($_SESSION['xspf_active']!='1') && ($jukebox_mode_enabled=='0')) {
		echo '<tr><td colspan="2" align="center">';
		echo xlate("Automatic play").' : ';
		echo xlate('After login').'</b> '.add_faq(45).'</b></td></tr>'; // add_faq
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>'.xlate("Automatic play").':</td>';
		echo '<td align="left">'.add_checkbox('autoplay',$autoplay).'</td></tr>';
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>'.xlate("Number of tracks").':</td>';
		echo '<td align="left">'.add_textinput('autoplay_num_tracks',$autoplay_num_tracks,3).'</td></tr>';
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		$qry="SELECT DISTINCT fav_name FROM fav WHERE ";
		$qry.="user_id='".get_user_id($_SESSION['login'])."' ORDER BY fav_name";
		$result=execute_sql($qry,0,1000000,$nr);
		echo '<td>'.xlate("Favorite list").':</td>';
		echo '<td><SELECT NAME="autoplay_list" class="tfield">';
		echo '<OPTION VALUE="Tracks"';
		if (($autoplay_list=="Tracks") || ($autoplay_list=="")) { echo ' selected'; }
		echo '>['.xlate("All").' '.xlate("Tracks").']</OPTION>';
		while ($row=mysql_fetch_array($result)) {
			echo '<OPTION VALUE="'.$row['fav_name'].'"';
			if ($autoplay_list==$row['fav_name']) { echo ' selected'; }
			echo '>'.$row['fav_name'].'</OPTION>';
		}
		echo '</SELECT>';	
		// Automatic play after last track:
		echo '<tr><td colspan="2" align="center">';
		echo xlate("Automatic play").' : ';
		echo xlate('After last track is played').'</b> '.add_faq(45).'</td></tr>'; 
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>'.xlate("Automatic play").':</td>';
		echo '<td align="left">'.add_checkbox('autoplay_last',$autoplay_last).'</td></tr>';
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		$qry="SELECT DISTINCT fav_name FROM fav";
		$qry.=" WHERE user_id='".get_user_id($_SESSION['login'])."' ORDER BY fav_name";
		$result=execute_sql($qry,0,1000000,$nr);
		echo '<td>'.xlate("Favorite list").':</td>';
		echo '<td><SELECT NAME="autoplay_last_list" class="tfield">';
		echo '<OPTION VALUE="Tracks"';
		if (($autoplay_last_list=="Tracks") || ($autoplay_last_list=="")) { echo ' selected'; }
		echo '>['.xlate("All").' '.xlate("Tracks").']</OPTION>';
		// Related performers:
		echo '<OPTION VALUE="Related"';
		if ($autoplay_last_list=="Related") { echo ' selected'; }
		echo '>['.xlate("Related performers").']</OPTION>';

		// 0.8.6: ECHONEST!
		echo '<OPTION VALUE="Echonest"';
		if ($autoplay_last_list=='Echonest') { echo ' selected'; }
		echo '>['.xlate('Similar tracks').']</OPTION>';
		
		while ($row=mysql_fetch_array($result)) {
			echo '<OPTION VALUE="'.$row['fav_name'].'"';
			if ($autoplay_last_list==$row['fav_name']) { echo ' selected'; }
			echo '>'.$row['fav_name'].'</OPTION>';
		}		
		echo '</SELECT>';	
		echo '</table></div>'; 
	} else { // Hide the settings (flash player is active), but remember 'em when we save:
		echo '<input type="hidden" name="automatic_play" value="'.$autoplay.'">';
		echo '<input type="hidden" name="autoplay_num_tracks" value="'.$autoplay_num_tracks.'">';
		echo '<input type="hidden" name="autoplay_list" value="'.$autoplay_list.'">';
		echo '<input type="hidden" name="autoplay_last" value="'.$autoplay_last.'">';
		echo '<input type="hidden" name="autoplay_last_list" value="'.$autoplay_last_list.'">';
	}	
} // if anonymous...	

//
//
//			4. THEME + ICONS
//
//
// Theme picker:
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif3" onclick="handleClick('to_col3','gif3')">
<?php echo xlate('Theme').'</p>'; ?>
<div id="to_col3" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content3");
$table3=1;

fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td colspan="2" align="center">';
disp_theme_picker($_SESSION['cssfile']);
echo '</tr>';
// Icon picker:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td colspan="2" align="center">';
disp_icon_picker($_SESSION['icon_dir']);
echo '</tr></table></div>';


//
//
//			5. LAST.FM STUFF
//
//
// Last.fm stuff:
if (($lastfm_allow_submission=="1") && ($lastfm_allow_local_users) &&
($_SESSION['login']!="anonymous")) {
// 0.8.7: Also check for jukebox-mode:
if ($jukebox_mode_enabled<>'1') {
	?>
	<p class="note" align="left"><b>
	<img src="./ampjukeicons/expand.gif" id="gif4" onclick="handleClick('to_col4','gif4')">
	Last.fm
	</p>
	<div id="to_col4" style="display:none;">
	<?php
	echo std_table("ampjuke_content_table","ampjuke_content4");
	$table4=1;
 
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Submit streamed tracks to last.fm").' '.add_faq(52).':</td><td>';
	echo add_checkbox('lastfm_active',$_SESSION['lastfm_active']).'</td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("last.fm username").':</td><td>';
	echo add_textinput('lastfm_username',$_SESSION['lastfm_username'],20);
	// Add link directly to last.fm:
	echo '  <a href="http://www.last.fm/user/'.$_SESSION['lastfm_username'].'" target="_blank">';
	echo '<img src="./ampjukeicons/popout.gif" border="0">Visit last.fm</a>';
	echo '</td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("last.fm password").':</td><td>';
	echo add_textinput_password('lastfm_password',$_SESSION['lastfm_password'],20).'</td></tr>'; // 0.8.3: Changed to password-type input
	echo '</table></div>'; 
	} else { // 0.8.7: Jukebox is enabled: remember settings:
	    echo '<input type="hidden" name="lastfm_active" value="'.$_SESSION['lastfm_active'].'">';
	    echo '<input type="hidden" name="lastfm_username" value="'.$_SESSION['lastfm_username'].'">';
	    echo '<input type="hidden" name="lastfm_password" value="'.$_SESSION['lastfm_password'].'">';
	}
}	


//
//
//			6. OTHER OPTIONS
//
//
// "Other" options - as follows below...
?>
<p class="note" align="left"><b>
<img src="./ampjukeicons/expand.gif" id="gif5" onclick="handleClick('to_col5','gif5')">
<?php echo xlate('Other options').'</p>'; ?>
<div id="to_col5" style="display:none;">
<?php
echo std_table("ampjuke_content_table","ampjuke_content6");
$table6=1;

// language selection:
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo '<td>'.xlate("Language").':'.add_faq(8).'</td><td>';
disp_language_options($_SESSION['lang']);
echo '</td></tr>';

// optionally change password:
// This option will obviously NOT be shown for anonymous users:
if ($_SESSION['login']!="anonymous") {
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Change password").' ('.xlate('Leave blank to keep current password').'):'.add_faq(4).'</td>';
	echo '<td><input type="password" name="change_password_1"';
	echo ' class="tfield" size="15"></td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Confirm new password").':</td>';
	echo '<td><input type="password" name="change_password_2" ';
	echo 'class="tfield" size="15"></td></tr>';
}	

// Display help
fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
echo "<td>".xlate("Display help (links to the AmpJuke FAQ)").":</td>";
echo '<td align="left">'.add_checkbox('disp_help',$_SESSION['disp_help']);
echo '</td></tr></table></div>';


// Display options for box-content on the 'welcome' page:
function disp_box_options($box,$default) {
	echo '<SELECT NAME="box'.$box.'" class="tfield">';
    $handle=fopen("./welcome_options.txt", "r");
    while (!feof($handle)) {
        $line=fgets($handle);
        $item=explode(";", $line);
        if (count($item)>=2) {
            echo '<OPTION VALUE="'.$item[1].'"';
            if ($default==$item[1]) {
                echo ' selected';
            }
            echo '>'.xlate($item[0]).'</OPTION>';
        }
    }
    print "</SELECT> \n\n";
    fclose($handle);
}

//
//
//			7. WELCOME PAGE OPTIONS
//
//
// Pick contents of welcome page (NOT shown for anonymous users):
if ($_SESSION['login']!="anonymous") {
	?>
	<p class="note" align="left">
	<img src="./ampjukeicons/expand.gif" id="gif6" onclick="handleClick('to_col6','gif6')">
	<?php echo xlate('Welcome page contents').'</p>'; ?>
	<div id="to_col6" style="display:none;">
	<?php
	echo std_table("ampjuke_content_table","ampjuke_content7");
	$table7=1;
 
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.add_faq(47).' '.xlate("Number of items").':</td>';
	echo '<td>'.add_textinput('welcome_num_items',$_SESSION['welcome_num_items'],3).'</td></tr>';
	$box=1;
	while ($box<=3) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>#'.$box.':</td>';
		echo '<td>';
		disp_box_options($box,$_SESSION['welcome_content_'.$box]);
		echo '</td></tr>';
		$box++;
	}	
	echo '</table></div>';
}

// 0.8.6:
/*
echo '<tr><td>'.settings_note($jukebox_mode_enabled,xlate('Note: Radio station enabled. Settings marked with * will not be affected')); 
echo '</td></tr>';
*/

// submit/save:
echo '<tr><td align="center">';
echo '<input type="submit" class="tfield" value="'.xlate("Save & continue").'" name="submit">';
echo '</td></tr></FORM></table>';
?>
