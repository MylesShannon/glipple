<?php
// This script allow you to show the image from an ID3TagV2.x
session_start();
if(isset($_GET['PHPSESSID']))
	session_id($_GET['PHPSESSID']);
if(isset($_GET['name'])){
	if(isset($_SESSION[$_GET['name'].'[data]']) && isset($_SESSION[$_GET['name'].'[type]'])){
		$mime = $_SESSION[$_GET['name'].'[type]'];
		$data = $_SESSION[$_GET['name'].'[data]'];
		header('Content-Type: '.$_SESSION[$_GET['name'].'[type]']);
		echo $data;
	}
}
?>