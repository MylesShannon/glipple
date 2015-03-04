<div class="content">

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <div class="register-default-box">
        <!-- register form -->
        <form method="post" action="<?php echo URL; ?>login/register_action" name="registerform">
            <!-- the user name input field uses a HTML5 pattern check -->
            <label for="login_input_username">
                Username
                <span style="display: block; font-size: 14px; color: #999;">(only letters and numbers, 2 to 64 characters)</span>
            </label>
            <input id="login_input_username" class="login_input" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required />
            <!-- the email input field uses a HTML5 email type check -->
            <label for="login_input_email">
                User's email
                <span style="display: block; font-size: 14px; color: #999;">
                    (you'll get a verification email with an activation link)
                </span>
            </label>
            <input id="login_input_email" class="login_input" type="email" name="user_email" required />
            <label for="login_input_password_new">Password (min. 6 characters)</label>
            <input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />
            <label for="login_input_password_repeat">Repeat password</label>
            <input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
            <label for="login_input_passcode">Passcode</label>
			<input id="login_input_passcode" class="passcode_input" type="password" name="passcode" required autocomplete="off" />
			
			<!-- show the captcha by calling the login/showCaptcha-method in the src attribute of the img tag -->
            <!-- to avoid weird with-slash-without-slash issues: simply always use the URL constant here -->
            <img id="captcha" src="<?php echo URL; ?>login/showCaptcha" />
            <span style="display: block; font-size: 11px; color: #999; margin-bottom: 10px">
                <!-- quick & dirty captcha reloader -->
                <a href="#" onclick="document.getElementById('captcha').src = '<?php echo URL; ?>login/showCaptcha?' + Math.random(); return false">[ Reload Captcha ]</a>
            </span>
            <label>
                Please enter these characters
            </label>
            <input type="text" name="captcha" required />
			
			<style type="text/css">
				.button
				{
					width: 150px;
					padding: 10px;
					font-weight:bold;
					text-decoration:none;
				}
				#cover{
					position:fixed;
					top:0;
					left:0;
					background:rgba(0,0,0,0.6);
					z-index:5;
					width:100%;
					height:100%;
					display:none;
				}
				#tosScreen
				{
					height:380px;
					width:340px;
					margin:0 auto;
					position:relative;
					z-index:10;
					display:none;
					background: #FFF;
					border:5px solid #cccccc;
					border-radius:10px;
					overflow:auto;
				}
				#tosScreen:target, #tosScreen:target + #cover{
					display:block;
					opacity:2;
				}
				.cancel
				{
					display:block;
					position:absolute;
					top:3px;
					right:2px;
					background:rgb(245,245,245);
					color:black;
					height:30px;
					width:35px;
					font-size:30px;
					text-decoration:none;
					text-align:center;
					font-weight:bold;
				}
			</style>
			
			<div id="tos_box">
				<label for="login_input_tos" id="login_label_tos">I agree to the Glipple <a href='#tosScreen' id="tos" class="button" >Terms of Service & Privacy Policy</a>: </label>
				<input id="login_input_tos" class="tos_input" type="checkbox" name="tos" required />
			</div>
			<div id="tosScreen">
			<a href="#" class="cancel">&times;</a>
			<div id="tos">
					<?php include "tospp.php"; ?>
			</div>
			</div>
			<div id="cover" >
			</div>
			
            <input type="submit"  name="register" value="Register" />
        </form>
    </div>

    <?php if (FACEBOOK_LOGIN == true) { ?>
        <div class="register-facebook-box">
            <h1>or</h1>
            <a href="<?php echo $this->facebook_register_url; ?>" class="facebook-login-button">Register with Facebook</a>
        </div>
    <?php } ?>

</div>

<script>
$(document).ready(function() {
	//load ToS on link click
	$("#ToS_link").click(function () {
		$.get("http://www.glipple.com/views/tospp.php", function( legal ) {
			alert(legal.text());
		}, 'html'); 
    });
} );
</script>
