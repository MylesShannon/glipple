<div class="content">
    <h1>Your profile</h1>

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <div>
        Your username: <?php echo Session::get('user_name'); ?>
    </div>
    <div>
        Your email: <?php echo Session::get('user_email'); ?>
    </div>
    <div>
        Your avatar image:
        <?php // if usage of gravatar is activated show gravatar image, else show local avatar ?>
        <?php if (USE_GRAVATAR) { ?>
            Your gravatar pic (on gravatar.com): <img src='<?php echo Session::get('user_gravatar_image_url'); ?>' />
        <?php } else { ?>
            Your avatar pic (saved on local server): <img src='<?php echo Session::get('user_avatar_file'); ?>' />
        <?php } ?>
    </div>
	<br>
	<div>
	<?php 
	error_reporting(E_ALL ^ E_DEPRECATED);
	
	$userid = Session::get('user_id');
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	
	
	mysql_connect($server, $user, $pass);
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT * FROM profiles WHERE user_id LIKE ".$userid) or die(mysql_error());  

	$row=mysql_fetch_array($result);

		$bio = $row['band_bio'];
		if ($bio == NULL){
			$bio = "placeholder='Insert your profile bio here...'>";
		}else{
			$bio = ">".$row['band_bio'];
		}
	mysql_close(); 
	?>

		<form id='bandBio' method="post">
		<textarea name='bandBio' id='bandBioText' rows='4' cols='50' <?php echo $bio; ?></textarea>
		<input type="submit" value="Update Bio" name="submit">
		</form>
	</div>
	<br>
	<div>
		<form id="bandImage" action="<?php echo URL ?>login/upload_image" method="post" enctype="multipart/form-data">
		Select an image to upload as your band image:
		<input type="file" name="uploadImage" id="upload_image">
		<input type="submit" value="Upload Image" name="submit">
		</form>
	</div>
	<br>
	<div>
		<form id="bandLinks" method="post">
		Profile links:
		<input type="text" placeholder="Provider 1" name="link1p" id="link1p">
		<input type="text" placeholder="Link 1" name="link1" id="link1">
		<input type="text" placeholder="Provider 2" name="link2p" id="link2p">
		<input type="text" placeholder="Link 2" name="link2" id="link2">
		<input type="text" placeholder="Provider 3" name="link3p" id="link3p">
		<input type="text" placeholder="Link 3" name="link3" id="link3">
		<input type="text" placeholder="Provider 4" name="link4p" id="link4p">
		<input type="text" placeholder="Link 4" name="link4" id="link4">
		<input type="text" placeholder="Provider 5" name="link5p" id="link5p">
		<input type="text" placeholder="Link 5" name="link5" id="link5">
		<input type="submit" value="Update Links" name="submit">
		</form>
	</div>
	
	<!--
    <div>
        Your account type is: <?php echo Session::get('user_account_type'); ?>
    </div>
	-->
</div>

<script>
$(document).ready(function(){
    $("#bandBio").submit(function(){
        $.post("upload_bio",
        {
          $('#bandBio').serialize())
        }
		);
		alert("Bio submitted!");
    });
	/*
	$("#bandImage").submit(function(){
        $.post("upload_image",
        {
          bandImage: "/dir"
        });
    });
	*/
	$("#bandLinks").submit(function(){
        $.post("upload_links",
        {
			link1 : $('input#link1').val(),
			link2 : $('input#link2').val(),
			link3 : $('input#link3').val(),
			link4 : $('input#link4').val(),
			link5 : $('input#link5').val(),
			
			link1p : $('input#link1p').val(),
			link2p : $('input#link2p').val(),
			link3p : $('input#link3p').val(),
			link4p : $('input#link4p').val(),
			link5p : $('input#link5p').val()
        });
		alert("Links submitted!");
    });
});
</script>
