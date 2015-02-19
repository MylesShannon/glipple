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
	<div id="bandBio">
		<form action="<?php echo URL ?>login/upload_bio" method="post">
		<textarea name="bandBio" rows="4" cols="50" placeholder="Insert your profile bio here:"></textarea>
		<input type="submit" value="Update Bio" name="submit">
		</form>		
	</div>
	<br>
	<div id="bandImage">
		<form action="<?php echo URL ?>login/upload_image" method="post" enctype="multipart/form-data">
		Select an image to upload as your band image:
		<input type="file" name="fileToUpload" id="fileToUpload">
		<input type="submit" value="Upload Image" name="submit">
		</form>
	</div>
	<br>
	<div id="bandLinks">
		<form action="<?php echo URL ?>login/upload_links" method="post">
		Profile link:
		<input type="text" name="link1">
		<input type="text" name="link2">
		<input type="text" name="link3">
		<input type="text" name="link4">
		<input type="text" name="link5">
		<input type="submit" value="Update Links" name="submit">
		</form>
	</div>
	
	<!--
    <div>
        Your account type is: <?php echo Session::get('user_account_type'); ?>
    </div>
	-->
</div>
