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

		$bio    = $row['band_bio'];
		$image = $row['band_image'];
		
		$link1p = $row['link1p'];
		$link1  = $row['link1'];
		$link2p = $row['link2p'];
		$link2  = $row['link2'];
		$link3p = $row['link3p'];
		$link3  = $row['link3'];
		$link4p = $row['link4p'];
		$link4  = $row['link4'];
		$link5p = $row['link5p'];
		$link5  = $row['link5'];
		
		if ($bio == NULL){
			$bio = "placeholder='Insert your profile bio here...'>";
		}else{
			$bio = ">".$bio;
		}
		
		if ($link1p == NULL){
			$link1p = "placeholder='Provider...'";
		}else{
			$link1p = "value='".$link1p."'";
		}
		
		if ($link1 == NULL){
			$link1 = "placeholder='Link...'";
		}else{
			$link1 = "value='".$link1."'";
		}
		
		if ($link2p == NULL){
			$link2p = "placeholder='Provider...'";
		}else{
			$link2p = "value='".$link2p."'";
		}
		
		if ($link2 == NULL){
			$link2 = "placeholder='Link...'";
		}else{
			$link2 = "value='".$link2."'";
		}
		
		if ($link3p == NULL){
			$link3p = "placeholder='Provider...'";
		}else{
			$link3p = "value='".$link3p."'";
		}
		
		if ($link3 == NULL){
			$link3 = "placeholder='Link...'";
		}else{
			$link3 = "value='".$link3."'";
		}
		
		if ($link4p == NULL){
			$link4p = "placeholder='Provider...'";
		}else{
			$link4p = "value='".$link4p."'";
		}
		
		if ($link4 == NULL){
			$link4 = "placeholder='Link...'";
		}else{
			$link4 = "value='".$link4."'";
		}
		
		if ($link5p == NULL){
			$link5p = "placeholder='Provider...'";
		}else{
			$link5p = "value='".$link5p."'";
		}
		
		if ($link5 == NULL){
			$link5 = "placeholder='Link...'";
		}else{
			$link5 = "value='".$link5."'";
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
	
		<!--	<form action="<?php echo URL ?>login/upload_image" id="imageUpload" method="post" class="dropzone"> </form>
		-->
				<form id="bandImage" method="post" >
		Select an image to upload as your band image:
		<input type="file" name="uploadImage" id="file">
		<input type="submit" value="Upload Image" name="submit">
		</form>
		
		<img <?php echo "src='http://www.glipple.com/public/img/bands/".$userid."/profile.jpg'"; ?> alt="Your Profile Image" style="width:50%;height:50%">
	</div>
	<br>
	<div>
		<form id="bandLinks" method="post">
		Profile links:
		<input type="text" name="link1p" id="link1p" <?php echo $link1p; ?>>
		<input type="text" name="link1" id="link1" <?php echo $link1; ?>>
		<input type="text" name="link2p" id="link2p" <?php echo $link2p; ?>>
		<input type="text" name="link2" id="link2" <?php echo $link2; ?>>
		<input type="text" name="link3p" id="link3p" <?php echo $link3p; ?>>
		<input type="text" name="link3" id="link3" <?php echo $link3; ?>>
		<input type="text" name="link4p" id="link4p" <?php echo $link4p; ?>>
		<input type="text" name="link4" id="link4" <?php echo $link4; ?>>
		<input type="text" name="link5p" id="link5p" <?php echo $link5p; ?>>
		<input type="text" name="link5" id="link5" <?php echo $link5; ?>>
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
    $('#bandBio').submit(function(){
		var bio = $('#bandBio').serialize();
        $.post('upload_bio', bio);
		return false;
	});
	$('#bandLinks').submit(function(){
		var links = $('#bandLinks').serialize();
		$.post('upload_links', links);
		return false;
	});
	
	$("form[name='uploadImage']").submit(function(e) {
        var formData = new FormData($(this)[0]);

        $.ajax({
            url: "upload_image",
            type: "POST",
            data: formData,
            async: false,
            success: function (msg) {
                alert(msg)
            },
            cache: false,
            contentType: false,
            processData: false
        });

        e.preventDefault();
    });
});
</script>
