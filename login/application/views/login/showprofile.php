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

	$userid = Session::get('user_id');
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());

	$result = mysql_query("SELECT * FROM profiles WHERE user_id LIKE ".$userid) or die(mysql_error());  

	$row=mysql_fetch_array($result);
		

		$bio = $row['band_bio'];
		if ($bio == NULL){
			$bio= "Insert your profile bio here:";
		}else{

		}
	mysql_close(); 
	?>

		<form id="bandBio" method="post">
		<textarea name="bandBio" rows="4" cols="50" value="<?php echo "test"; ?>"></textarea>
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
		<input type="text" placeholder="Provider 1" name="link1p">
		<input type="text" placeholder="Link 1" name="link1">
		<input type="text" placeholder="Provider 2" name="link2p">
		<input type="text" placeholder="Link 2" name="link2">
		<input type="text" placeholder="Provider 3" name="link3p">
		<input type="text" placeholder="Link 3" name="link3">
		<input type="text" placeholder="Provider 4" name="link4p">
		<input type="text" placeholder="Link 4" name="link4">
		<input type="text" placeholder="Provider 5" name="link5p">
		<input type="text" placeholder="Link 5" name="link5">
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
          bandBio : "jQ test bio"
        }
		);
		alert("Bio submitted!");
    });
	/*
	$("#bandImage").submit(function(){
        $.post("upload_image",
        {
          bandImage: "Donald Duck"
        });
    });
	*/
	$("#bandLinks").submit(function(){
        $.post("upload_links",
        {
			link1 : "test",
			link2 : "test",
			link3 : "test",
			link4 : "test",
			link5 : "test",
			
			link1p : "test",
			link2p : "test",
			link3p : "test",
			link4p : "test",
			link5p : "test"
        });
		alert("Links submitted!");
    });
});
</script>
