<?php
/***********************************************************
 *			system.php
 *		    ------------------
 * Created		: Wed, Nov 19, 2003
 * Copyright		: (C) LookStrike Team
 * WebSite		: http://www.lookstrike.com 
 *
 * $Id: system.php,v 1.3 2004/12/05 17:08:31 Jean-Sebastien Exp $
 *
 ***********************************************************/
if(!defined('IN_ID'))die('You are not allowed to access to this page.');
// Slash Removal
$sys_conf['path']['relative_path'] = $_SERVER['PHP_SELF'];
while(substr($sys_conf['path']['relative_path'],0,2)=='//')
	$sys_conf['path']['relative_path'] = ereg_replace('//','/',$sys_conf['path']['relative_path']);

// FileName & Extension
$system_temp_array = explode('/',$sys_conf['path']['relative_path']);
$system_temp_array2 = explode('.',$system_temp_array[count($system_temp_array)-1]);
$system_temp_string = '';
for($i=0;$i<count($system_temp_array2)-1;$i++)
	$system_temp_string .= '.'.$system_temp_array2[$i];
$system_temp_string = substr($system_temp_string,1);
$sys_conf['path']['extension'] = $system_temp_array2[count($system_temp_array2)-1];
$sys_conf['path']['filename'] = $system_temp_string;
?>