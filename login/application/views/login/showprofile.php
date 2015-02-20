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
		<form id="bandBio" method="post">
		<textarea name="bandBio" rows="4" cols="50" placeholder="Insert your profile bio here:"></textarea>
		<input type="submit" value="Update Bio" name="submit">
		</form>		
	</div>
	<br>
	<div>
		<form id="bandImage" method="post" enctype="multipart/form-data">
		Select an image to upload as your band image:
		<input type="file" name="uploadImage" id="fileToUpload">
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
        $.post("upload_bio.php",
        {
          bandBio: "This is the jQ test bio"
        },
		alert("Submitted");
		);
    });
	/*
	$("#bandImage").submit(function(){
        $.post("upload_image.php",
        {
          name: "Donald Duck",
          city: "Duckburg"
        });
    });
	
	$("#bandLinks").submit(function(){
        $.post("upload_links.php",
        {
          name: "Donald Duck",
          city: "Duckburg"
        });
    });
	*/
});
</script>
