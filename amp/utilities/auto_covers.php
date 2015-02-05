<?php
die('Sorry...');
// auto_covers.php: Automatically fetch album covers from last.fm
//
// By: Jesper S.

/*if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}*/
//jts ->
$special = 1;
//jts <-

require_once("./sql.php");
require_once("./set_td_colors.php");
require_once("./disp.php");
require_once('./lastfm_lib.php');

$special=only_digits($special); // 0.7.6

while ($special < 5000) {
    
    $qry="SELECT * FROM album WHERE album.aid=".$special;
    $header_result=execute_sql($qry,0,1,$nr);
    $header_row=mysql_fetch_array($header_result);
   
        $cover=lastfm_get_cover($header_row);
           
        echo $special. '\n';
    $special++;
    }
   
?>

