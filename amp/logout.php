<?php
session_start();
$u=$_SESSION['login']; // 0.8.4 - later - see below
session_unset(); // 0.7.4
session_destroy();
require_once("disp.php");

// 0.6.3: Remove cookies:
$ok=setcookie('ampjuke_username', '', time()+1, '/', false);
$ok=setcookie('ampjuke_password', '', time()+1, '/', false);
$ok=setcookie('ampjuke_remember_all','',time()+1, '/', false);


redir("login.php");
?>
