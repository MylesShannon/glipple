<?php
session_start();
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

if(version_compare(phpversion(),'5.0.0','>=')!==true)
	exit('Sorry, but you have to run this script with PHP5... You currently have the version <b>'.phpversion().'</b>.');

$temp_root = getcwd();
$temp_plus = '';

// Inclusion des fichiers
require($temp_root.$temp_plus.'/modules/config.php');
include($sys_conf['path']['real'].'/modules/system.php');
include($sys_conf['path']['real'].'/modules/function.php');
if(!defined('NO_HTML') || constant('NO_HTML')===false)
	include($sys_conf['path']['real'].'/modules/menu1.php');
?>
