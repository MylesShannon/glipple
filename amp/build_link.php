<?php
// 0.8.6: Introduced. Build a link.
require('logincheck.php');
if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
	header("Location: logout.php");
	die('Not logged in');
}


require_once("sql.php");
require_once("disp.php");
require_once("set_td_colors.php");

echo headline($what,'','');

print "\n\n\n <!-- Now on to content --> \n\n\n </td></tr><tr><td>";
echo std_table("ampjuke_content_table","ampjuke_content");

// Build a link for radio station mode:
if ($act=='rs_setup') {
    $fn='jukebox_'.date('U').'.m3u'; // The filename for the link
    $handle=fopen('./tmp/'.$fn,'w');
    // We're making a complete .m3u-playlist with exactly one entry: the link
    fwrite($handle,'#EXTM3U' . chr(10));
    fwrite($handle,'#EXTINF:-1,AmpJuke Jukebox' . chr(10));
    $link=$base_http_prog_dir.'/stream_radio.php?id=0'; // ...to be continued...

    // Get the details for the username we're supposed to be running the radio station as:
    $rsu_id=get_user_id($jukebox_mode_user); // jukebox_mode_user is in the configuration.
    $rsu=get_user_details($rsu_id);
    // Check we have something, and/or return error(s) if not:
    if ((!isset($rsu['name'])) || ($rsu['name']<>$jukebox_mode_user)) {
        echo '<tr><td><font color="red">Error.</font>Can not build a link. The user <b>'.$jukebox_mode_user.'</b>';
        echo ' does not exist on this server.<br>';
        echo '<a href="sitecfg.php">Check your configuration</a> and try again.';
        die('</td></tr></table>');
    }
    // We survived. Now build the rest of the link:
    $link.='&what='.$rsu['autoplay_last_list']; // corresponds to Settings -> Autmatic play (in GUI).
    $link.='&user='.$rsu['name']; // pretty much self-explaining...
    $link.='&user_id='.$rsu['id']; // ...same...
    $link.='&upw='.get_md5_passwd($rsu['name']); // and finally the encrypted password

    fwrite($handle,$link . chr(10)); // write the link to the playlist...
    fclose($handle); // ...done.
    echo '<tr><td>Right click and save the link below. It will be used as a "feed" for the media-player that streams the music:';
    echo '</td></tr><tr><td>';
    echo '<b><a href="'.$base_http_prog_dir.'/tmp/'.$fn.'" title="Radio station link">'.$fn.'</a></b>';
    echo '</td></tr>';
}

echo '</table>';
?>

