<?php

// get_lyrics.php: Get lyrics for a particular song-id using a web service from Yahoo!
// January 2009: AmpJuke 0.7.7: Rewritten - exclusively use a specific site:
session_start();
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	redir("login.php");
	exit;
}

parse_str($_SERVER["QUERY_STRING"]);
require("db.php");
require("sql.php");
require("disp.php");

$qry="SELECT id,name,performer_id FROM track WHERE id=".$id;
$result=execute_sql($qry,0,100000,$nr);
$row=mysql_fetch_array($result);

$perf=get_performer_name($row['performer_id']);

$lyrics_path=str_replace('%PERFORMER%',$perf,$lyrics_path);
$lyrics_path=str_replace('%TRACK%',$row['name'],$lyrics_path);

echo '<script type="text/javascript" language="javascript">'; 
echo 'window.location.replace("'.$lyrics_path.'");';
echo '</script>';	
exit;

?>

