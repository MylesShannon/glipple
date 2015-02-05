<?php
// 0.3.3: This code was rewritten in order to avoid the "famous" POST-warning in the browser

if ($limit=="") {
  	if (isset($_POST['search'])) {
  	 	// 0.7.4: Expand as needed...
	 	$limit=preg_replace ("/[^0-9^a-z^A-Z^_^ ^.^(^)^+^#]/","",$_POST['search']);
		$_SESSION['last_search']=$limit;
	} else {	
		$limit=$_SESSION['last_search'];
	}	
	require("configuration.php");
	require("db.php");

    $loc=$base_http_prog_dir.'/index.php?what=search&start=0&dir=ASC&sorttbl=track&order_by=track.name&limit='.$limit;
    
    // 0.7.1:
    if ($limit=="") {
    	$loc=$base_http_prog_dir.'/index.php?what=welcome';
    }	
    
	echo '<script type="text/javascript" language="javascript">'; 
	echo 'window.location.replace("'.$loc.'");';
	echo '</script>';   
} else {
	// 0.7.1: Filter out unwanted stuff:
	$limit = strip_tags($limit);
//	$limit=ereg_replace('[^a-zA-Z0-9 ()]', "", $limit); // 0.7.2	
// 0.7.4: Above replaced with:
	$limit=preg_replace ("/[^0-9^a-z^A-Z^_^.^ ^#]/", "", $limit);
	
    if ($limit=="") {
    	$loc=$base_http_prog_dir.'/index.php?what=welcome';   
		echo '<script type="text/javascript" language="javascript">'; 
		echo 'window.location.replace("'.$loc.'");';
		echo '</script>';   
	}	
 
    require("disp_track.php");
	print "</td></tr> \n\n <!-- PERFORMER SEARCH: --> \n\n <tr><td>";
    require("disp_performer.php");
    $table2=1;
//	print "</table></td></tr> \n\n <!-- ALBUM SEARCH: --> \n\n <tr><td>"; 
// 0.8.5: Replaced with:
	print "</td></tr> \n\n <!-- ALBUM SEARCH: --> \n\n <tr><td>";
    require("disp_album.php");
    $table3=1;
	print "</table>";
}

?>
