<?php
function xlate($key) {
	$ret = $key; // Return English keyword/phrase by default
	if (isset($_SESSION['lang'])) { // 0.8.0: Check if lang is defined
		$lanfile = strtolower($_SESSION['lang']); // get name of language file
		if (file_exists("./lang/".$lanfile.".php")) { // does it exist... ?
			require("./lang/".$lanfile.".php");
		}		
	}	
	return $ret;
/* The above replaces the code below...
	$ret=$key; // by default, return the English word if we cannot find a match
	if (strtoupper($_SESSION['lang']=="DA")) {
        require("./lang/da.php");
	} // Danish
	if (strtoupper($_SESSION['lang']=="DE")) {
		require("./lang/de.php");
	} // Deutch	
	if (strtoupper($_SESSION['lang']=="ES")) {
        require("./lang/es.php");
	} // Espanol
	if (strtoupper($_SESSION['lang']=="FR")) {
        require("./lang/fr.php");
	} // Français (0.3.6)
	if (strtoupper($_SESSION['lang']=="HU")) {
		require("./lang/hu.php");
	} // Magyar (Hungarian) - 0.4.0		
	if (strtoupper($_SESSION['lang']=="IT")) {
        require("./lang/it.php");
	} // Italian (0.3.4)
	if (strtoupper($_SESSION['lang']=="NL")) {
        require("./lang/nl.php");
	} // Dutch (0.3.5)
	if (strtoupper($_SESSION['lang']=="PT")) {
        require("./lang/pt.php");
	} // Português
	if (strtoupper($_SESSION['lang']=="SV")) {
        require("./lang/sv.php");
	} // Svenska
	if (strtoupper($_SESSION['lang']=="TR")) {
        require("./lang/tr.php");
	} // Turkish
	return $ret;
*/	
}
?>			
		
