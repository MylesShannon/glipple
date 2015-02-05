<?php
define('IN_ID',true);
include('modules/header.php');

if(isset($_GET['filename'])){
	echo '<br />';
	$table = new LSTable(2,1,'778',$null);
	$table->setTitle($_GET['filename']);
	include('tag1.php');
	include('tag2.php');
	$table->draw();
}
echo '<p align="center"><font class="littletext">Version Beta v0.01</font></p>';

include('modules/footer.php');
?>