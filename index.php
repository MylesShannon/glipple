<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
    <title> 	
		Glipple
	</title>
	
	<?php include "./header.php"; ?>
</head>
<body>
	<!--
    <div class="debug-helper-box">
        DEBUG HELPER: you are in the view: <?php echo $filename; ?>
    </div>
	-->
	<table width="100%">
	<tr>
	<td>
    <div class='title-box'>
        <img id="logo" class="link" src="<?php echo URL; ?>public/img/logo.png" WIDTH="238px" HEIGHT="100px" alt="Glipple" />
	</div>
	 </td>
	 <td id="slogan">
		Music: Free. Legal. Easy.
	 </td>
	 <td>
		<?php include "views/radio.php"; ?>

	</td></tr></table>
	<!--
	<div class="search-bar">
		<form id="searchbar">
			<input type="text" placeholder="Search for Title, Artist, Album, or Genre" id="s" onkeyup="loadsearchresults(this.value)"><br>
		</form>
	</div>
	-->
	
	<div class="clear-both"></div>
    <div class="header">
		<div class="header_left_box">
			<ul class="menu">
				<li>
					<a href="#" id="blog" class="link">Blog</a>
				</li>
				<li>
					<a href="#" id="faqs" class="link">FAQ's</a>
				</li>
			</ul>
        </div>
		
		<div class="header_right_box">
			<div class="login-access-box">
				<a href="<?php echo URL; ?>login" >Login/Register</a>
			</div>
		</div>
		
		<div class="clear-both"></div>
    </div>
	
	<div class="content">
		<div id="filler"></div>
	</div>
	
	<!-- content filler AJAX js -->
	<script type="text/javascript" src="<?php echo URL; ?>public/js/content.js"></script> 
	
    <div class="footer">
		
		<div class="footer_left_box">
			<ul class="menu">
				<li>
					<a href="#" id="legal" class="link">Legal</a>
				</li>
				<li>
					<a href="#" id="contact" class="link">Contact</a>
				</li>
			</ul>
        </div>
		
		<div class="footer_right_box">
			<div class="copyright-box">
					<span class="tos-box">Use of glipple.com is in accordance with our <a href="#" id="tospp">Terms of Service</a></span> &copy; Glipple Inc 2015
			</div>
		</div>
		
		<div class="clear-both"></div>
    </div>
</body>
</html>
