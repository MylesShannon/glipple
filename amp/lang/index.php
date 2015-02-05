<?php

// 0.4.3: Check if we're installing/upgrading:
if (file_exists('db_new.sql')) {
	echo '<table border="1" rules="rows" align="center">';
	echo '<tr><td><font color="red"><b>Note:</b><font color="black"><br>';
	if (!file_exists('db.php')) { // not installed, yet:
		echo 'It looks like you have not installed AmpJuke, yet.<br>';
		echo 'If this is <b>not correct</b>, please remove the file <b>"db_new.sql"</b>.<br><br>';
		echo 'To <b>install</b> AmpJuke, <a href="install.php?act=install">click here</a>.';
	}

	if (file_exists('db.php')) { // installed, but (maybe) not upgraded:
		echo 'It looks like you want to <b>upgrade</b> AmpJuke.<br>';
		echo 'If this is <b>not correct</b>, please remove the file <b>"db_new.sql"</b>.<br><br>';
		echo 'To <b>upgrade</b> AmpJuke, <a href="install.php?act=upgrade">click here</a>.';
	}	

	echo '</td></tr></table>';
	exit;
}	
// checks ends



require("make_header.php");

// 0.3.6: Play something automatically ?
if (isset($_SESSION['autoplay'])) { // autoplay: jump to different URL using js.:
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("random.php?autoplay=1&num_tracks='.$num_tracks.'&list='.$list.'");';
	echo '</script>';
	session_unregister('autoplay'); // we don't want to do autoplay again in this session.
}
//



// 0.4.3: Insted of a bunch of if/then's, we're switching the $what parameter:
switch ($what) {
	case "track": 				require("disp_track.php"); break;
	case "performerid": 		require("disp_performerid.php"); break;
	case "performer": 			require("disp_performer.php"); break;
	case "albumid": 			require("disp_albumid.php"); break;
	case "album":				require("disp_album.php"); break;
	case "favoriteid":			require("disp_favoriteid.php"); break;
	case "favorite":			require("disp_favorite.php"); break;
	case "yearid":				require("disp_yearid.php"); break;
	case "year":				require("disp_year.php"); break;
	case "queue":				require("disp_queue.php"); break;
	case "random":				require("random.php"); break;
	case "search":				require("search.php"); break;
	case "settings":			require("disp_settings.php"); break;		
	case "users":				require("disp_users.php"); break;
	case "edit":				require("edit.php"); break;
	case "download":			require("download.php"); break;	
	default: 					require("welcome.php"); break;
}	
//

print "</td></tr></table> \n\n\n <!-- MAIN_CONTENT_TABLE_ENDS --> \n\n\n";
print "\n\n\n <!-- ROW ENDS FOR THE OUTLINE TABLE: --> \n </td></tr><tr></table>";
?>
<!-- 0.4.3: apply rollover-effects using a bit of JS -->
<script type="text/javascript">
addTableRolloverEffect('ampjuke_content','tableRollOverEffect','');
<?php
if (isset($table2)) {
?>
addTableRolloverEffect('ampjuke_content2','tableRollOverEffect','');
<?php
}
?> 
<?php
if (isset($table3)) {
?>
addTableRolloverEffect('ampjuke_content3','tableRollOverEffect','');
<?php
}
?> 
</script>


<table>
<tr><td>
<!-- 
Do not remove the link to the AmpJuke website.
It's a fair "price" to pay.
-->
<a href="http://www.ampjuke.org" target="_blank"><font face="Verdana"><font size="1"><color="#a9a9a9">
AmpJuke Version <?php echo $version ?></a>
</td></tr></table>
</body>
</html>

