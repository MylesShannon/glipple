

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
$target_dir = "/var/www/html/public/img/bands/".$userID."/profile.jpg";
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

$imageFileType = pathinfo($filename,PATHINFO_EXTENSION);

if ($imageFileType!='jpg'){

	echo "Sorry, only JPG/JPEG are allowed.";
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

		if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))){
			mysql_query("UPDATE $table SET band_image = '$target_dir' WHERE user_id LIKE '$userID'") or die(mysql_error());  
			echo "The database has been updated.";
		} else{
			mysql_query("INSERT INTO $table (user_id, band_image) VALUES('$userID', '$target_dir') ") or die(mysql_error());
			echo "The database has been updated with a new row.";

		}
	} else {
	    echo "Sorry, there was an error uploading your file.";
	}
}

mysql_close();
?>
</div>