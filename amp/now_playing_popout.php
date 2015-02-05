<?php

// now_playing_popout: Show what's playing in a small window

session_start();
parse_str($_SERVER["QUERY_STRING"]);

if (isset($not_done)) {
 	require_once("db.php");
 	$loc=$base_http_prog_dir.'/now_playing_popout.php';
	echo '<script type="text/javascript" language="javascript">';	 		 	
	echo "history.go(-1);";
	echo 'var rw = window.open("'.$loc.'","AmpJuke_Now_Playing","width='.$popout_width.',height='.$popout_height.',resizable=yes");';
	echo '</script>';  			
	die();
} 

$ok=0;
if (isset($_SESSION['login'])) { $ok++; }
if (isset($_SESSION['passwd'])) { $ok++; }
if ($ok!=2) { 
	session_destroy();
	echo '<b>AmpJuke</b>.<br>Not logged in.';
    exit;
}


echo '<html><head>';
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//'.$_SESSION['lang'].'">';
echo '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';

// 0.4.3: We're now dynamically linking to the CSS-file:
echo '<link rel="stylesheet" type="text/css" href="./css/'.$_SESSION['cssfile'].'">'; 
echo '<script language="JavaScript" src="rowcols.js"></script>';
echo '<script language="JavaScript" src="now_playing.js"></script>';

include("db.php");
include("sql.php");
include("disp.php");
$user_id=get_user_id($_SESSION['login']);
?>
<script type="text/javascript">
var c=0
var t

function timedCount() {
document.getElementById('ampjuke_now_playing_count').value=c
c=c+1
sndReq('ampjuke_now_playing_popout',<?php echo $user_id; ?>)
t=setTimeout("timedCount()",<?php echo $now_playing_update_rate; ?>)
}
</script>
<title><?php echo '[AmpJuke...and YOUR hits keep on coming !]'; ?></title>
</head>
<body onLoad="javascript:timedCount();"> 

<?php
if (($_SESSION['login']!="anonymous") && ($allow_now_playing==1) && ($_SESSION['disp_now_playing']=="1")) {
print "\n\n <!-- 0.6.4: New AmpJuke feature: display what's currently playing --> \n\n";
?>
<input type="hidden" id="ampjuke_now_playing_count">
<div id="ampjuke_now_playing"><b>AmpJuke</b><br>...and YOUR hits keep on coming!
</div>
<?php } ?>
</body></html>
