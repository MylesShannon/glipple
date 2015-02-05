<?php
/***********************************************************
 *			config.php
 *		    ------------------
 * Created		: Wed, Nov 19, 2003
 * Copyright		: (C) LookStrike Team
 * WebSite		: http://www.lookstrike.com
 *
 * $Id: config.php,v 1.11 2005/02/07 18:28:39 jsgoupil Exp $
 *
 ***********************************************************/
if(!defined('IN_ID'))die('You are not allowed to access to this page.');
/*
[path]	=>	Paramètre de gestion de Fichier
	[documentroot]	=>	DOCUMENT_ROOT (à modifier dans header.php)
	[documentplus]	=>	Dossier en Plus (à modifier dans header.php)
	[real]		=>	Adresse d'inclusion
	[relative_path]	=>	Adresse Relative du Fichier (auto)
	[filename]	=>	Nom du fichier sans extension (auto)
	[extension]	=>	Extension du Fichier (auto)
*/

$sys_conf = array(
	'path' => array(
		'documentroot'	=> $temp_root,
		'documentplus'	=> $temp_plus,
		'real'		=> $temp_root.$temp_plus,
		'relative_path'	=> '',
		'filename'	=> '',
		'extension'	=> '',
		'music'		=> './music'
	)
);
?>