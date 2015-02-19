<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
    <title><?php 	
		if ($_GET) 
		{
			$page = $_GET['url'];
			$parts = explode("/",$page); 
			$page = $parts['0']; 
			
			if($page == 'index')
			{
				$page = 'home';
			}
		} else {
			$page = "home";
		}
		
		$page = ucfirst($page);
		echo 'Glipple - '.$page;
	?></title>
	
	<?php include "./header.php"; ?>
	
	<!-- favicon -->
	<link rel="shortcut icon" href="<?php echo URL; ?>public/img/fav.ico" type="image/x-icon" />
	<link rel="icon" href="<?php echo URL; ?>public/img/fav.ico" type="image/x-icon" />
	

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo URL; ?>public/css/reset.css" />
    <link rel="stylesheet" href="<?php echo URL; ?>public/css/style.css" />
	<link rel="stylesheet" href="<?php echo URL; ?>public/css/stickyfooter.css" />
	
	<!-- google analytics 
	<script type="text/javascript" src="<?php echo URL; ?>public/js/google.js"></script>
	-->
	
	
</head>
<body>
	<!--
    <div class="debug-helper-box">
        DEBUG HELPER: you are in the view: <?php echo $filename; ?>
    </div>
	-->

    <div class='title-box'>
        <a href="<?php echo URL; ?>"><img class="logo" src="<?php echo URL; ?>public/img/logo.png" WIDTH="238px" HEIGHT="100px" alt="Glipple"></a>
	</div>
	 
	<div class='radio'>
			<div id="jquery_jplayer_1" class="jp-jplayer"></div>
			<div id="jp_container_1" class="jp-audio">
				<div class="jp-type-single">
					<div class="jp-gui jp-interface">
						<ul class="jp-controls">
						<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
						<!-- <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li> -->
						<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
						<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
						  <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
						  <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
						</ul>
						<div class="jp-progress">
							<div class="jp-seek-bar">
								<div class="jp-play-bar"></div>
							</div>
						</div>
						<div class="jp-volume-bar">
							<div class="jp-volume-bar-value"></div>
						</div>
						<div class="jp-time-holder">
							<div class="jp-current-time"></div>
							<div class="jp-duration"></div>
							<ul class="jp-toggles">
								<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
								<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
							</ul>
						</div>
					</div>
					<div class="jp-details">
						<ul>
						<li><span class="jp-title"><div id="song_title" ></div></span></li>
						</ul>
					</div>
					<div class="jp-no-solution">
						<span>Update Required</span>
						To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
					</div>
				</div>
			</div>
	</div>
	
	<!-- load Now Playing js -->
	<script type="text/javascript" src="<?php echo URL; ?>public/js/playing.js"></script> 
	
	<div class="search-bar">
		<form id="searchbar">
			<input type="text" placeholder="Search for Title, Artist, Album, or Genre" id="s" onkeyup="loadsearchresults(this.value)"><br>
		</form>
	</div>
	
	<div class="clear-both"></div>
	
	<div class="content">
		<div id="filler"></div>
	</div>
	
	<!-- content filler AJAX js -->
	<script type="text/javascript" src="<?php echo URL; ?>public/js/content.js"></script> 
	
    <div class="footer">
		
		<div class="footer_left_box">
			<ul id="menu">
				<li>
					<a href="#home" onclick="loadXMLDoc('home')">Home</a>
				</li>
				<!--
				<li>
					<a href="#test" onclick="loadXMLDoc('test')">Test</a>
				</li>
				-->
				<li>
					<a href="#radio" onclick="loadXMLDoc('radio')">Radio</a>
				</li>
				<li>
					<a href="#help" onclick="loadXMLDoc('help')">Help</a>
				</li>
				<li>
					<a href="#blog" onclick="loadXMLDoc('blog')">Blog</a>
				</li>
				<li>
					<a href="#profile" onclick="loadXMLDoc('userid-12')">Myles's Profile</a>
				</li>
			</ul>
        </div>
		
		<div class="footer_right_box">
			<div class="login-access-box">
				<a href="<?php echo URL; ?>login" >artist login</a>
			</div>
		</div>
		
		<div class="clear-both"></div>
    </div>
</body>
</html>
