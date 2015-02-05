<?php
parse_str($_SERVER["QUERY_STRING"]);
session_start();
$ok=0;
if (isset($_SESSION['login'])) { $ok++; }
if (isset($_SESSION['passwd'])) { $ok++; }
if ($ok!=2) { 
	session_destroy();
	include_once('disp.php');
	redir("login.php");
	die('Not logged in.');

}
// Construct the URL to be passed to the flash-obj.:
$url=$base.'/xspf/xspf_player.swf?d='.date('U').'&playlist_url='.$base.'/tmp/'.$u.session_id().'.xspf&autoplay=true'; // d: because of FRIGGIN IE....

header("Location: $url");
die();
?>	

