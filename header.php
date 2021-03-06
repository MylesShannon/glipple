<?php
define('URL', $_SERVER['PATH_INFO']);
?>
	
	<!-- load jQuery -->
	<script type="text/javascript" src="<?php echo URL; ?>public/js/jquery-2.1.3.js"></script>
	<!-- <script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script> -->
	
	<!-- load dropzone css and js -->
	<link rel="stylesheet" href="<?php echo URL; ?>public/css/dropzone.css" type="text/css" />
	<script src="<?php echo URL; ?>public/js/dropzone.js"></script>
	
	<!-- load jplayer and skin -->	
	<!-- <link type="text/css" href="<?php echo URL; ?>public/js/jPlayer/skins/blue.monday/jplayer.blue.monday.css" rel="stylesheet" /> -->
	<link href="<?php echo URL; ?>public/css/skin/blue.mondayorg/css/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo URL; ?>public/js/jPlayer/jquery.jplayer.min.js"></script>
	
	<!-- dataTables -->
	<script type="text/javascript" src="<?php echo URL; ?>public/js/jquery.dataTables.min.js"></script>
	<link rel="stylesheet" href="<?php echo URL; ?>public/css/jquery.dataTables.css" />

	<!-- using this to make on the fly editable fields
	<script src="<?php echo URL; ?>public/js/prototype.js" type="text/javascript"></script>
	<script src="<?php echo URL; ?>public/js/scriptaculous.js?load=controls" type="text/javascript"></script>
	-->

	<!-- load stickyfooter -->
	<link rel="stylesheet" href="<?php echo URL; ?>public/css/stickyfooter.css" />
	
	<!-- fonts -->
	<link href='http://fonts.googleapis.com/css?family=Arimo' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" href="<?php echo URL; ?>public/css/circle.skin/circle.player.css">
	<script type="text/javascript" src="<?php echo URL; ?>public/js/profile.js"></script>
	
	<script type="text/javascript" src="<?php echo URL; ?>public/js/update.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>public/js/circle.player.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>public/js/jquery.transform2d.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>public/js/jquery.grab.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>public/js/mod.csstransforms.min.js"></script>
	
		<!-- favicon -->
	<link rel="shortcut icon" href="<?php echo URL; ?>public/img/fav.ico" type="image/x-icon" />
	<link rel="icon" href="<?php echo URL; ?>public/img/fav.ico" type="image/x-icon" />

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo URL; ?>public/css/reset.css" />
    <link rel="stylesheet" href="<?php echo URL; ?>public/css/style.css" />
	
	<!-- google analytics -->
	<script type="text/javascript" src="<?php echo URL; ?>public/js/google.js"></script>	
