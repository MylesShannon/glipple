

<?php

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";
$table = "profiles";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


$userID = Session::get('user_id');

$userDir = "/var/www/html/public/img/bands/".$userID;

// Does the user have a music directory, if not create one
if (!is_dir($userDir)) 
{
	mkdir($userDir, 0775);
}

$filename=basename( $_FILES["file"]["name"]);
echo "File basename:".$filename;




 $uploadok = 1;
 if (!isset($_FILES["file"]))

 {
 	// we need to make dropzone error
	$uploadok =0;
	}	
$target_dir = $userDir ."/". $filename;

$imageFileType = pathinfo($target_dir,PATHINFO_EXTENSION);

if ($imageFileType!='jpg'){
	$uploadok =0;
}

//$song_file = mime_content_type($_FILES["file"]["tmp_name"]);
//echo $song_file;
//echo $target_dir."<br>";
//print_r($_FILES);
//echo move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir);
if ($uploadok == 0){

	    echo "Sorry, your file was not uploaded.";
}
	else{
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir)) {
	    echo "The file ". $filename. " has been uploaded.";
		$path = $userDir."/profile.jpg";
	rename($target_dir, $path);

define('profilepic', URL."public/img/profile.jpg');

mysql_query("INSERT INTO $table (band_image) VALUES ($path)") or die(mysql_error());  

	} else {
	    echo "Sorry, there was an error uploading your file.";
	}
}

mysql_close();
?>
</div>