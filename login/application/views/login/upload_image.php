

<?php

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";
$table = "profiles";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


$userID = Session::get('user_id');

$userDir = URL."public/img/bands/".$userID;

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
} else {
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir)) {
	    echo "The file ". $filename. " has been uploaded.";
		$path = $userDir."/profile.jpg";
	rename($target_dir, $path);

<<<<<<< HEAD
if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))) {
// Row with user_id exists but band_image is NULL
		echo "<br>Row exists but band_image is NULL";
		

mysql_query("UPDATE $table SET band_image = '$path' WHERE user_id LIKE '$userID'") or die(mysql_error());  

	}else{
		// If row with user_id does not exist, insert new row and rename file to new row id
		echo "<br>Row does not exist";
		mysql_query("INSERT INTO $table (user_id, band_image) VALUES('$userID', '$path') ") or die(mysql_error());
=======
mysql_query("UPDATE $table SET band_image = '$path' WHERE user_id LIKE '$userID'") or die(mysql_error());  

	} else {
	    echo "Sorry, there was an error uploading your file.";
>>>>>>> parent of 4b8a1e2... updated upload image script
	}
}else{
echo "Sorry, your file was not uploaded.";
}
}

mysql_close();
?>
