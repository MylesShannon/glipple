<?php
// -----------------------------------
// ------- Band Image Upload ---------
// -----------------------------------


// Global vars
$userID = Session::get('user_id');
$bandImageDir = "/var/www/html/public/img/bands/".$userID."/";
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";
$table = "profiles";

if(isset($_POST["submit"])) {
	$type = upload_image();
	mysql_image($type);
} else {
	echo "No submission!";
}

function upload_image() {
	$userID = Session::get('user_id');
	$bandImageDir = "/var/www/html/public/img/bands/".$userID."/";
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	$table = "profiles";
	
	// Does the user have a band image directory, if not create one
	if (!is_dir($bandImageDir)) 
	{
		mkdir($bandImageDir, 0775);
	}

	$target_file = $bandImageDir . basename($_FILES["uploadImage"]["name"]);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
		$check = getimagesize($_FILES["uploadImage"]["tmp_name"]);
		if($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		} else {
			echo "File is not an image.";
			$uploadOk = 0;
		}
	}

	// Check if file already exists
	/*
	if (file_exists($target_file)) {
		echo "Sorry, file already exists.";
		$uploadOk = 0;
	}
	*/

	// Check file size
	if ($_FILES["uploadImage"]["size"] > 500000) {
		echo "Sorry, your file is too large.";
		$uploadOk = 0;
	}

	// Change file extension to .jpg if image is .jpeg
	if ( $imageFileType == 'jpeg' || $imageFileType == 'JPEG' || $imageFileType == 'JPG') {
			$imageFileType = 'jpg';
	} else {
		// Allow certain file formats
		echo "Sorry, only JPG/JPEG are allowed.";
		$uploadOk = 0;
	}

	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
	// if everything is ok, try to upload file
	} else {
		if (move_uploaded_file($_FILES["uploadImage"]["tmp_name"], $target_file)) {
			echo "The file ". basename( $_FILES["uploadImage"]["name"]). " has been uploaded.";
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	}
	
	return $imageFileType;
}

// -----------------------------------
// ----------- DATABASE --------------
// -----------------------------------
function mysql_image($imageFileType){
	
	$userID = Session::get('user_id');
	$bandImageDir = "/var/www/html/public/img/bands/".$userID."/";
	$server = "localhost";
	$user = "root";
	$pass = "4DaL0v3AM0n3y";
	$db = "login";
	$table = "profiles";
	
	mysql_connect($server, $user, $pass) or die(mysql_error());
	mysql_select_db($db) or die(mysql_error());
	
	$result = mysql_query("SELECT * FROM $table WHERE user_id LIKE $userID") or die(mysql_error());
	$row = mysql_fetch_array($result);
	$existingRow = $row['id'];

	if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'")) && $row['band_image'] == NULL){
		// Row with user_id exists but band_image is NULL
		echo "<br>Row exists but band_image is NULL";
		
		$newpath = "public/img/bands/".$userID."/".$existingRow.".".$imageFileType;
		mysql_query("UPDATE $table SET band_image = '$newpath' WHERE id = $existingRow") or die(mysql_error());
		// Rename uploaded file to last row id
		rename($bandImageDir.basename($_FILES["uploadImage"]["name"]), $bandImageDir.$existingRow.".".$imageFileType);
	} elseif(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'")) && $row['band_image'] != NULL){
		// Row with user_id exists but band_image is NOT NULL (should add check that 'band_image' does in fact equal id.jpg)
		// rename file to existing id after upload
		// set $existingRow to 'id' of that user's existing row id
		echo "<br>Row exists but band_image is NOT NULL";
		
		rename($bandImageDir.basename($_FILES["uploadImage"]["name"]), $bandImageDir.$existingRow.".".$imageFileType);
	} elseif(!mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))){
		// If row with user_id does not exist, insert new row and rename file to new row id
		echo "<br>Row does not exist";
		mysql_query("INSERT INTO $table (user_id) VALUES('$userID') ") or die(mysql_error());
		$lastRow = mysql_insert_id() or die(mysql_error());;
		$newpath = "public/img/bands/".$userID."/".$lastRow.".".$imageFileType;
		mysql_query("UPDATE $table SET band_image = '$newpath' WHERE id = $lastRow") or die(mysql_error());
		// Rename uploaded file to last row id
		rename($bandImageDir.basename($_FILES["uploadImage"]["name"]), $bandImageDir.$lastRow.".".$imageFileType);
	}

	mysql_close();
}
?>