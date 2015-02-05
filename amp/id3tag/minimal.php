<?php
define('IN_ID',true);

// TAG 1.1
include('modules/class/mp3_id3v11.php');

$mp3_id3v11 = new mp3_id3v11();
$mp3_id3v11->load_file('music/music.mp3');

print_r($mp3_id3v11->get_tag());

echo '<br />';
echo '<br />';

// TAG 2.4
include('modules/class/mp3_id3v2.php');
include('modules/class/TagValue.php');
$mp3_id3v2 = new mp3_id3v2();
$mp3_id3v2->load_file('music/music.mp3');

print_r($mp3_id3v2->get_tag());
?>