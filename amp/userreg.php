<?php
// 0.8.4: userreg.php: Handles (most) aspects in relation to self-registration of users + forgotten passwords
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';
echo '<head>';
echo '<link rel="shortcut icon" href="favicon.ico" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';
echo "<link href='http://fonts.googleapis.com/css?family=Josefin+Sans+Std+Light&subset=latin' rel='stylesheet' type='text/css'>"; // uh huh.
echo '<link rel="stylesheet" type="text/css" href="./css/AmpJukeStandard.css">';


parse_str($_SERVER["QUERY_STRING"]);
if (((!isset($uuid)) || (!file_exists('./tmp/'.$uuid))) || (!isset($act))) { // wow...
	echo 'Sorry. The request is not valid. Please <a href="login.php">click here to try again</a>.';
	die();
}	
//@unlink('./tmp/'.$uuid); Noooo...we MIGHT need that!

$forbidden=array('"',"'",'<','>','=',';'); // more..? We're filtering 'stuff' using this..

echo '</head><body>';

session_start();
	
// The usual suspects - erm - inclusions:
require('db.php');
require('disp.php');
require('sql.php');
// plus one:
require('configuration.php');


// This is stolen from login.php: Ban furhter actions if we have IP-banning enabled
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

echo '<table class="ampjuke_content_table">'; // Here we go...
//
//
//				NEW USER REG: ENTRY FORM
//
//
if (($act=='new') && (isset($user_reg_enabled)) && ($user_reg_enabled==1)) {
	echo '<tr><td align="center" colspan="2"><b>AmpJuke: New user registration</b></td></tr>';
	echo '<tr><td colspan="2" clign="center"><i>Fill out the form below. All fields are required.</i></td></tr>';	
	echo '<FORM NAME="userregform" METHOD="POST" action="userreg.php?act=store_new_user&uuid='.$uuid.'">';
	// Username:
	echo '<tr><td>Username:</td><td>'.add_textinput('username','',40);
	echo ' <i>Must be '.$user_reg_username_min_length.'-'.$user_reg_username_max_length.' characters.</i>';
	echo '</td></tr>';
	echo '<tr><td colspan="2">';
	echo '<hr width="100%" color="#abcdef" align="center"></td></tr>';
	// Email address + confirmation:
	echo '<tr><td>Email address:</td><td>'.add_textinput('email1','',40).'</td></tr>';
	echo '<tr><td>Confirm email address:</td><td>'.add_textinput('email2','',40).'</td></tr>';	
	echo '<tr><td colspan="2">';
	echo '<hr width="100%" color="#abcdef" align="center"></td></tr>';
	// Password + confirmation:
	echo '<tr><td>Password:</td><td>'.add_textinput_password('passwd1','',40);
	echo ' <i>Must be '.$user_reg_password_min_length.'-'.$user_reg_password_max_length.' characters.</i>';	
	echo '</td></tr>';
	echo '<tr><td>Confirm password:</td><td>'.add_textinput_password('passwd2','',40).'</td></tr>';
	echo '<tr><td colspan="2">';
	echo '<hr width="100%" color="#abcdef" align="center"></td></tr>';
	echo '</table>';
	
	// Verification that we're dealing with a human being:
	// It's QUIZ-time:
	$qry="SELECT track.id, track.name, track.performer_id, ";
	$qry.="track.duration, track.year, track.last_played, ";
	$qry.="track.times_played, track.path, ";
	$qry.="track.album_id, performer.pid, performer.pname";
	$qry.=" FROM track, performer ";
	$qry.="WHERE track.performer_id=performer.pid ORDER BY rand() LIMIT 0,20";
	$result=execute_sql($qry,0,-1,$dummy);
	$victim=rand(0,20); // What track will be quizzed ?
	$quizz_type=rand(0,3); // 0=name, 1=performer, 2=duration, 3=times_played
	$x=0;
	echo '<table class="ampjuke_content_table" rules="rows" width="50%">'; 
	echo '<tr><td colspan="6">Verification:</td></tr>';
	echo '<tr><td>Track#</td><td>Name</td><td>Performer</td><td>Year</td><td>Duration</td><td>Times played</td></tr>';
	while ($row=mysql_fetch_array($result)) {
		echo '<tr>';
		
		echo '<td class="quiz">'.$x.'</td>';
		
		echo '<td class="quiz">'.$row['name'].'</td>';
		
		$perf=get_performer_name($row['performer_id']);
		echo '<td class="quiz">'.$perf.'</td>';
				
		echo '<td class="quiz">'.$row['year'].'</td>';
		
		echo '<td class="quiz">'.mydate($row['duration']).'</td>';
		
		echo '<td class="quiz">'.$row['times_played'].'</td>';
	
		print "</tr> \n";
		
		if ($x==$victim) {
			$rt=$x;
			switch ($quizz_type) {
				case 0: $right_answer=$row['name']; break;
				case 1: $right_answer=$perf; break;
				case 2: $right_answer=mydate($row['duration']); break;
				case 3: $right_answer=$row['times_played']; break;
			}
		}
		$x++;
	}
	echo '</table>';
	echo '<table class="ampjuke_content_table">'; 
	echo '<tr><td>';
	echo 'Please answer this: ';
	switch ($quizz_type) {
		case 0: $q='Enter the name of track #'.$rt; break;
		case 1: $q='Enter the name of the performer on track #'.$rt; break;
		case 2: $q='Enter the duration of track #'.$rt; break;
		case 3: $q='Enter how many times track #'.$rt.' has been played'; break;
	}
	echo '</td><td>'.$q.' '.add_textinput('user_answer','',40).'</td></tr>';
// ...
	$_SESSION['right_answer']=$right_answer;
	//echo ' Right='.$right_answer.'</td></tr>';
	//
	echo '<tr><td colspan="2" align="center"><input type="submit" NAME="Submit" value="Submit"></td></tr>';
	echo '</form>';
}

//
//
//				NEW USER REG: SANITAZION + VALIDATION + CREATION + SEND EMAIL VERIFICATION
//
//
if (($act=='store_new_user') && (isset($user_reg_enabled)) && ($user_reg_enabled==1)) {
// SANITAZION + PRELIMINARY VALIDATION:
	$_POST['username']=my_filter_var($_POST['username']);
	$_POST['username']=str_replace($forbidden,'',$_POST['username']);
	if(!filter_var($_POST['email1'], FILTER_VALIDATE_EMAIL)) {
		die("E-mail address is not valid.");
	}
	if(!filter_var($_POST['email2'], FILTER_VALIDATE_EMAIL)) {
		die("E-mail address is not valid.");
	}
	$_POST['passwd1']=my_filter_var($_POST['passwd1']);
	$_POST['passwd2']=my_filter_var($_POST['passwd2']);
// VALIDATION:
	$ok=1;
	$errmsg='';
	// Check email1 & email2 are identical:
	if ($_POST['email1']<>$_POST['email2']) {
		$ok=0;
		$errmsg.='You must type same email address in both fields.<br>';
	}
	// Check passwd1 & 2 are idential:
	if ($_POST['passwd1']<>$_POST['passwd2']) {
		$ok=0;
		$errmsg.='Passwords are not identical. You must type same password in both fields.<br>';
	}
	// Check username+email adr. against db:
	$qry="SELECT name,email FROM user WHERE name='".$_POST['username']."' OR email='".$_POST['email1']."'";
	$result=execute_sql($qry,0,10,$nr);
	if ($nr>0) {
		$ok=0;
		$errmsg.='Sorry: Username <b>'.$_POST['username'].'</b> or email-address <b>'.$_POST['email1'].'</b> already used by other user(s).<br>';
	}
	// Check length of username is within limits (min/max length):
	if ((strlen($_POST['username'])<$user_reg_username_min_length) || (strlen($_POST['username'])>$user_reg_username_max_length)) {
		$ok=0;
		$errmsg.='Username <b>'.$_POST['username'].'</b> is too long (or short). Must be between ';
		$errmsg.=$user_reg_username_min_length.' and '.$user_reg_username_max_length.' characters.<br>';
	}
	// Check length of password is within limits (min/max length):
	if ((strlen($_POST['passwd1'])<$user_reg_password_min_length) || (strlen($_POST['passwd1'])>$user_reg_password_max_length)) {
		$ok=0;
		$errmsg.='Password is too long (or short). Must be between ';
		$errmsg.=$user_reg_password_min_length.' and '.$user_reg_password_max_length.' characters.<br>';
	}
	// Check we answered the quiz:

	if ($_POST['user_answer']<>$_SESSION['right_answer']) {
		$ok=0;
		$errmsg.='You failed to answer the verification question, or the answer was wrong.<br>';
	}
	// Something went wrong - give up:
	if ($ok<>1) {
		echo '<tr><td>Error:<br>'.$errmsg.'<br><a href="login.php">Click here to try again.</a></td></tr>';
		die();
	}
// CREATION:
	$now=date('U');
	$qry="INSERT INTO user (name,email,password,password_salt,last_login,cssfile,admin) VALUES (";
	$qry.="'".$_POST['username']."','".$_POST['email1']."','".$_POST['passwd1']."','-1','".$now."','AmpJukeStandard.css','0');";
	$result=execute_sql($qry,0,-1,$dummy);
// SEND EMAIL VERIFICATION + ACTIVATION LINK:
	$link=$base_http_prog_dir.'/userreg.php?act=verify&user='.$_POST['username'].'&ll='.$now.'&uuid='.$uuid;
	$msg='Great! Your AmpJuke user-account has been created.<br>
	Please click on the link below to activate your account:<br><br>
	<a href="'.$link.'">'.$link.'</a><br><br>
	After activating your account using the link, you can login using the username <b>'.$_POST['username'].'</b> 
	and the password <b>'.$_POST['passwd1'].'</b><br><br>Thanks for using AmpJuke!';
	my_mail($_POST['email1'],'Activate your AmpJuke account',$msg,get_configuration('email_sender'));	
	echo '<tr><td>Your AmpJuke account has been created! Please check your email (<b>'.$_POST['email1'].'</b>) soon for details';
	echo '<br>about how to activate your account.<br></td></tr>';
}

//
//
//				VERIFY+ACITVATE NEW USER ACCOUNT (LINK IN EMAIL CLICKED)
//
//
if ($act=='verify') {
// SANITIZE:
	$user=my_filter_var($user);
	$user=str_replace($forbidden,'',$user);
	$ll=my_filter_var($ll);
// ACTIVATE:
	$qry="UPDATE user SET password_salt='0' WHERE name='".$user."' AND last_login='".$ll."'";
	$result=execute_sql($qry,0,-1,$dummy);
	
	echo '<tr><td>The account has been activated. <a href="login.php">Please click here to login</a><br>';
	echo 'Thanks for using AmpJuke!</td></tr>';
}
	

//
//
//				FORGOT PASSWORD
//
//
if (($act=='forgot_passwd') && (isset($enable_email_with_lost_password)) && ($enable_email_with_lost_password==1)) {
	echo '<tr><td align="center"><b>AmpJuke: Forgot password</b></td></tr><tr><td>';
	echo '<i>In order to reset your password, please enter your email address.</i></td></tr>';
	echo '<FORM NAME="userregform" METHOD="POST" action="userreg.php?act=mail_passwd&uuid='.$uuid.'">';
//	echo '<tr><td>Enter your <b>username</b>:'.add_textinput('username','Username',40).'</td></tr>';
	echo '<tr><td>Enter your <b>email-address</b>:'.add_textinput('email','Email',40).'</td></tr>';
	echo '<tr><td align="center"><input type="submit" NAME="Submit" value="Submit">';
	echo '</form></td></tr>';
}	

//
//
//				EMAIL PASSWORD: SEND AN EMAIL WITH LINK THAT ALLOWS RESETTING PASSWORD
//
//
if (($act=='mail_passwd') && (isset($enable_email_with_lost_password)) && ($enable_email_with_lost_password==1)) {
	$_POST['username']=my_filter_var($_POST['username']);
//	$_POST['username']=str_replace($forbidden,'',$_POST['username']);
	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		die("E-mail address is not valid.");
 	} else {
 		// We now have a - presumably - safe username+email address: check out against db:
 		$ok=0;
// 		$qry="SELECT * FROM user WHERE name='".$_POST['username']."' AND email='".$_POST['email']."'";
		$qry="SELECT * FROM user WHERE email='".$_POST['email']."'";
 		$result=execute_sql($qry,0,1,$ok);
	}
	if ($ok==1) { // We found exactly one record - use that:
		$row=mysql_fetch_array($result);
		$link=$base_http_prog_dir.'/userreg.php?act=reset_password&user='.$row['email'].'&salt='.$row['password_salt'].'&ll='.$row['last_login'];
		$link.='&uuid='.$uuid;
		$msg='A request was made recently to reset your password.<br />
		Please click here to reset your password: <a href="'.$link.'">'.$link.'</a><br />
		<b>Note</b>: The link is only valid for 24 hours.<br /><br />
		If this request was not generated by you, please delete/ignore this email.<br />';
		my_mail($row['email'],'Reset your AmpJuke password',$msg,get_configuration('email_sender'));
		echo '<tr><td>OK. Mail sent to: <b>'.$row['email'].'</b></td></tr>';
	} else {
		echo '<tr><td>Could not find the email address <b>'.$_POST['email'].'</b>.<br>';
		echo 'Please try again. <a href="login.php">Click here</a>.';
	}
}

//
//
//				EMAIL PASSWORD: LINK IN EMAIL CLICKED THAT RESETS PASSWORD
//
//
if (($act=='reset_password') && (isset($enable_email_with_lost_password)) && ($enable_email_with_lost_password==1)) {
	if(!filter_var($user, FILTER_VALIDATE_EMAIL)) {
		die("E-mail address is not valid.");
	}
	$salt=my_filter_var($salt);
	$ll=my_filter_var($ll);
	
	$qry="SELECT id,name,email,password_salt,last_login FROM user WHERE email='".$user."' AND password_salt='".$salt."' AND last_login='".$ll."'";
	$result=execute_sql($qry,0,1,$nr);
	if ($nr<>1) {
		die('Sorry, the link seems to be invalid or no such user exists. <a href="login.php">Click here</a>');
	}
	$row=mysql_fetch_array($result);
	$new_passwd=substr(strtolower(generate_password_salt()),0,rand(1,5)+5).rand(1,10000); // 5-10 chars + # between 1-10000
	$qry="UPDATE user SET password='".$new_passwd."', password_salt='0' WHERE id='".$row['id']."'";
	$result=execute_sql($qry,0,-1,$dummy);
	my_mail($row['email'],'Your new AmpJuke password','Your new AmpJuke password is: '.$new_passwd.'<br>Your username is: '.$row['name'],get_configuration('email_sender'));
	die('OK. Please check your mail then <a href="login.php">click here to login</a> using your new password.<br><b>Note:</b> The link in the email is only valid for 24 hours.');
}

echo '</table>';

echo '</body></html>';
?>
