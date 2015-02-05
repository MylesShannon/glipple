<?php
// 0.8.5: Avoid "not-installed-stuff":
if (!file_exists('db.php')) {
	echo 'It seems like you have not <b>installed</b> AmpJuke, yet.<br>';
	echo 'If that is true, you might try <a href="./">this link</a>.<br>';
	echo 'Please note there is a complete step-by-step installation guide <a href="http://www.ampjuke.org/?id=installation">right here</a> (opens in a new window/tab.';
	die();
}
// 0.7.2: Generate the uuid:
function make_uuid($prefix) {
    $chars = md5(uniqid(rand()));
    $uuid  = substr($chars,0,8) . '-';
    $uuid .= substr($chars,8,4) . '-';
    $uuid .= substr($chars,12,4) . '-';
    $uuid .= substr($chars,16,4) . '-';
    $uuid .= substr($chars,20,12);
    return $prefix . $uuid;
}
require("db.php"); // 0.7.7: Moved here -> banned_ip's are now configurable:

// 0.7.5: Ban IP if number of failed login attempts is higher than X.
// 0.7.7: Only ban if enabled
if ((isset($max_failed_login_enabled)) && ($max_failed_login_enabled=='1')) {
	$count=0;
	if (file_exists('./tmp/banned_ips.txt')) {
		$handle=fopen('./tmp/banned_ips.txt', 'r');
		while (!feof($handle)) {
			$line=fgets($handle);
			if (trim($line)==trim($_SERVER["REMOTE_ADDR"])) {
				$count++;
			}
		}
		fclose($handle);
		if ($count>$max_failed_login_attempts) { 
			die('Sorry, too many wrong login attempts. IP-address is banned.'); 
		}
	}	
} 			


// 0.6.3: Remember login ?? Don't display anything - simply redirect right away:
// 0.8.1: ...but ONLY if "login_hide_keep_me_signed_in" isn't set:
if ((isset($_COOKIE['ampjuke_remember_all'])) && ((!isset($login_hide_keep_me_signed_in)) || ($login_hide_keep_me_signed_in<>'1'))) {
    $def_navn=$_COOKIE['ampjuke_username'];
	$def_pass=$_COOKIE['ampjuke_password'];
	echo '<form name="login" method="POST" action="loginvalidate.php">';
	echo '<input type="text" name="login" value="'.$def_navn.'">';
	echo '<input type="password" name="password" value="'.$def_pass.'">';
	echo '<input type="hidden" name="remember_login" value="1">';
	echo '<input type="hidden" name="saved_url_params" value="'.$_SERVER["QUERY_STRING"].'">';

	// 0.7.2: Create temporary file, so loginvalidate.php knows we were here:
    $uuid  = './tmp/'.make_uuid('').'.tmp';
    touch($uuid);
    echo '<input type="hidden" name="uuid" value="'.$uuid.'">';
    
	print '<script language="JavaScript"> document.login.submit(); </script>';
}
// 0.6.1: Login.php now uses css-definitions to ease customization of the loginpage.
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head><title>AmpJuke - Login</title>
<link rel="stylesheet" type="text/css" href="./ampstyles.css">
</head>
<body>
<noscript>
<h3 align="center"><font color="red">Please turn on JavaScript - AmpJuke relies on it</h3>
</noscript>
<table class="ampjuke_login_table" align="center">
<form name="login" method="POST" action="loginvalidate.php">

<tr class="ampjuke_login_tr">
<td colspan="2">
<img src="./ampjukeicons/ampjuke_login.jpg" border="0">
</td>
</tr>
<?php
// Create temporary file, so loginvalidate.php knows we were here (-> attempt to avoid POSTing from remote site):
$uuid  = './tmp/'.make_uuid('').'.tmp';
touch($uuid);
echo '<input type="hidden" name="uuid" value="'.$uuid.'">';
?>
<tr class="ampjuke_login_tr">
<td class="ampjuke_login_left_td">Login:</td>
<td class="ampjuke_login_right_td">
<input class="ampjuke_login_field" type="text" name="login" tabindex=1>
<?php 
if (isset($demo)) { echo ' <i>Demo, - use: <b>ampjuke</b></i>'; } 
// 0.8.4: User-registration enabled ?
if ((isset($user_reg_enabled)) && ($user_reg_enabled==1)) {
	echo '<a href="./userreg.php?act=new&uuid='.str_replace('./tmp/','',$uuid);
	echo '">'.$user_reg_display_text.'</a>';
}
?>
</td></tr>

<tr class="ampjuke_login_tr">
<td class="ampjuke_login_left_td">Password:</td>
<td class="ampjuke_login_right_td">
<input class="ampjuke_login_field" type="password" name="password" tabindex=2>
<?php 
if (isset($demo)) { echo ' <i>Demo, - use: <b>ampjuke</b></i>'; } 
// 0.8.4: Enable retrieval of password ?
if ((isset($enable_email_with_lost_password)) && ($enable_email_with_lost_password==1)) {
	echo '<a href="./userreg.php?act=forgot_passwd&uuid='.str_replace('./tmp/','',$uuid).'">';
	echo $enable_email_with_lost_password_text.'</a>';
}
?>
</td></tr>

<!-- 0.6.3: New, AmpJuke offers "Keep me logged in" -->
<?php
// 0.8.1: This is always displayed, UNLESS 'Hide "Keep me signed in"' is configured in miscellaneous sttings:
if ((!isset($login_hide_keep_me_signed_in)) || ($login_hide_keep_me_signed_in<>'1')) {
	echo '<tr class="ampjuke_login_tr">';
	echo '<td class="ampjuke_login_left_td">Keep me signed in:</td>';
	echo '<td class="ampjuke_login_right_td"><input type="checkbox" name="remember_login" tabindex=3>';
	echo 'for 2 weeks, unless I log out</td></tr>';
}

?>
<tr class="ampjuke_login_tr">
<td colspan="2" align=center><input type="submit" name="Submit" value="Submit" class="ampjuke_login_field" tabindex=4>


<!-- 
NOTE: 

Do _not_ remove the link to the AmpJuke site below.

Michael H. Iversen.

-->
<tr class="ampjuke_login_tr">
<td colspan="2" align="right"><a href="http://www.ampjuke.org" target="_blank">
<font face="Verdana"><font size="1"><color="#a9a9a9">AmpJuke Version 0.8.8</a></td>
</tr>

<tr class="ampjuke_login_tr">
<td colspan="2" align="right">
</td></tr>
</form>
<br>
<?php
if (isset($demo)) {
	echo '<h4><a href="http://www.ampjuke.org"><-- Return to homepage</a></h4></td></tr>';
}	

print '<form name="login_anonymous" method="POST" action="loginvalidate.php">';
if (isset($allow_anonymous) && ($allow_anonymous==1)) {
	echo '<tr class="ampjuke_login_tr"><td colspan="2" align="center"><br><hr><br>';
	echo '<input type="hidden" name="login" value="anonymous">';
	echo '<input type="hidden" name="password" value="anonymous">';
    echo '<input type="hidden" name="uuid" value="'.$uuid.'">'; // 0.7.3	
	echo '<input type="submit" name="Submit" value="Login as anonymous (guest)" ';
	echo 'class="ampjuke_login_field"></td></tr>';
}

echo '</table>';
print '<script language="JavaScript"> document.forms[0].login.focus(); </script>';
?>
</body></html>
