<?php
// 0.8.4: A more smooth approach: CHECK if we have a running session already:
if (session_id()=='') {
	session_start();
}
$ok=0;
if (isset($_SESSION['login'])) { $ok++; }
if (isset($_SESSION['passwd'])) { $ok++; }
if (isset($_SESSION['lang'])) { $ok++; }
if ($ok!=3) { 
	session_destroy();
	//header("Location: $base_http_prog_dir/login.php"); exit; }
	include_once("disp.php");
	redir("login.php");
	exit;
}
?>
