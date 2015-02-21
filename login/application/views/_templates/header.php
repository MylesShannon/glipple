<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
	<?php 
		//Formulate page title based on current url
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
	?>
	</title>

    <?php // include "../../../../header.php"; ?>

    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Favicon -->
	<link rel="shortcut icon" href="<?php echo URL; ?>public/img/fav.ico" type="image/x-icon" />
	<link rel="icon" href="<?php echo URL; ?>public/img/fav.ico" type="image/x-icon" />
    <!-- CSS -->
    <!-- <link rel="stylesheet" href="<?php echo URL; ?>public/css/reset.css" /> -->
    <link rel="stylesheet" href="<?php echo URL; ?>public/css/style.css" />
	<link rel="stylesheet" href="<?php echo URL; ?>public/css/dropzone.css" type="text/css" />
	<link rel="stylesheet" href="//cdn.datatables.net/1.10.4/css/jquery.dataTables.css" />
    <!-- <script type="text/javascript" src="//code.jquery.com/jquery-2.1.1.min.js"></script> -->
	<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>public/js/application.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>public/js/dropzone.js"></script>
	<script type="text/javascript" src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>

    <script src="<?php echo URL; ?>public/js/prototype.js" type="text/javascript"></script>
    <script src="<?php echo URL; ?>public/js/scriptaculous.js" type="text/javascript"></script>
	<script src="<?php echo URL; ?>public/js/controls.js" type="text/javascript"></script>

		
	<!-- load jplayer and skin -->	
	<script type="text/javascript" src="<?php echo URL; ?>public/js/jquery.jplayer.min.js"></script>
	<link type="text/css" href="<?php echo URL; ?>public/js/jPlayer/skins/blue.monday/jplayer.blue.monday.css" rel="stylesheet" />
	<script type="text/javascript" src="<?php echo URL; ?>public/js/initjplayer.js"></script>
	
	<!-- google anal
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-56705111-1', 'auto');
	ga('send', 'pageview');
	</script>
	-->
	
	
</head>
<body>
	<!--
    <div class="debug-helper-box">
        DEBUG HELPER: you are in the view: <?php echo $filename; ?>
    </div>
	-->

    <div class='title-box'>
        <a href="<?php echo URLlog; ?>"><img class="logo" src="<?php echo URL; ?>public/img/logo.png" WIDTH="238px" HEIGHT="100px" alt="Glipple"></a>
		
	</div>
	
	<!-- load Now Playing js -->
	<script src="<?php echo URL; ?>public/js/playing.js"></script> 
	
	
	<!-- <iframe src="http://glipple.com:8000/radio" ></iframe> -->
	
	<!--
	<div style="display:inline;">
		<audio controls preload="none">
			<source src="http://glipple.com:8000/radio" type="audio/mpeg">
		Your browser does not support the audio element.
		</audio>
    </div>
	-->
	
	<!--
	<button type="button" onclick="loadXMLDoc()">Request data</button>
	<div id="myDiv"></div>
	-->

    <div class="header">
        <div class="header_left_box">
        <ul id="menu">
            <!-- <li <?php if ($this->checkForActiveController($filename, "index")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>index/index">Home</a>
            </li> -->
			<!--
			<li <?php if ($this->checkForActiveController($filename, "test")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>test/index">Test</a>
            </li>
			-->
			<!-- <li <?php if ($this->checkForActiveController($filename, "radio")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>radio/index">Radio</a>
            </li> -->
            <li <?php if ($this->checkForActiveController($filename, "help")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>help/index">Help</a>
            </li>
            
			
			<?php if (Session::get('user_logged_in') == true):?>
            <li <?php if ($this->checkForActiveController($filename, "upload")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>upload/index">Upload</a>
				<!--
				<ul class="sub-menu">
					<li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                        <a href="<?php echo URL; ?>upload/edit">Edit existing</a>
					</li>
				</ul>
				-->
            </li>
            <?php endif; ?>
			<!--
            <?php if (Session::get('user_logged_in') == true):?>
            <li <?php if ($this->checkForActiveController($filename, "dashboard")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>dashboard/index">Dashboard</a>
            </li> 
			<?php endif; ?>
			-->
			<?php if (Session::get('user_logged_in') == true):?>
            <li <?php if ($this->checkForActiveController($filename, "music")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>music/index">Music</a>
            </li>
            <?php endif; ?>
            <!-- <?php if (Session::get('user_logged_in') == true):?>
            <li <?php if ($this->checkForActiveController($filename, "note")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>note/index">My Notes</a>
            </li>
            <?php endif; ?>
			-->
			<!--
			<?php if (Session::get('user_logged_in') == true):?>
            <li <?php if ($this->checkForActiveController($filename, "overview")) { echo ' class="active" '; } ?> >
                <a href="<?php echo URL; ?>overview/index">Overview</a>
            </li>
            <?php endif; ?>
			-->
            <?php if (Session::get('user_logged_in') == true):?>
                <li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo URL; ?>login/showprofile">My Account</a>
                    <ul class="sub-menu">
                        <!--<li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo URL; ?>login/changeaccounttype">Change account type</a>
                        </li>-->
                        <li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo URL; ?>login/uploadavatar">Upload an avatar</a>
                        </li>
                        <li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo URL; ?>login/editusername">Edit my username</a>
                        </li>
                        <li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo URL; ?>login/edituseremail">Edit my email</a>
                        </li>
                        <li <?php if ($this->checkForActiveController($filename, "login")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo URL; ?>login/logout">Logout</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- for not logged in users -->
            <?php if (Session::get('user_logged_in') == false):?>
                <li <?php if ($this->checkForActiveControllerAndAction($filename, "login/index")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo URL; ?>login/index">Login</a>
                </li>
                <li <?php if ($this->checkForActiveControllerAndAction($filename, "login/register")) { echo ' class="active" '; } ?> >
                    <a href="<?php echo URL; ?>login/register">Register</a>
                </li>
            <?php endif; ?>
        </ul>
        </div>

        <?php if (Session::get('user_logged_in') == true): ?>
            <div class="header_right_box">
                <div class="namebox">
                    Hello <?php echo Session::get('user_name'); ?> !
                </div>
                <div class="avatar">
                    <?php if (USE_GRAVATAR) { ?>
                        <img src='<?php echo Session::get('user_gravatar_image_url'); ?>'
                             style='width:<?php echo AVATAR_SIZE; ?>px; height:<?php echo AVATAR_SIZE; ?>px;' />
                    <?php } else { ?>
                        <img src='<?php echo Session::get('user_avatar_file'); ?>'
                             style='width:<?php echo AVATAR_SIZE; ?>px; height:<?php echo AVATAR_SIZE; ?>px;' />
                    <?php } ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="clear-both"></div>
    </div>
