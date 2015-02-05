<?php
define('IN_ID',true);
include('modules/header.php');

if(isset($_GET['filename'])){
	if ((!is_writable($_GET['filename'])) || (!file_exists($_GET['filename']))) {
		die('Sorry. You cannot <b>write</b> to the file '.$_GET['filename'].'. Not enough permissions, or the file does not exist.');
	}
	echo '<br />';
	$table = new LSTable(2,1,'778',$null);
	$table->setTitle($_GET['filename']);
	include('tag1.php');
	include('tag2.php');
	$table->draw();
}
echo '<p align="center"><font class="littletext"><a href="http://other.lookstrike.com/barcode/" target="_blank">Version Beta v0.01</a></font></p>';

include('modules/footer.php');
?>