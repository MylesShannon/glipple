<?php
// band image upload

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";
$table = "profiles";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());

$userID = Session::get('user_id');

$bandImageDir = "/var/www/html/login/public/img/band/".$userID."/";

// Does the user have a band image directory, if not create one
if (!is_dir($bandImageDir)) 
{
	mkdir($bandImageDir, 0775);
}

$target_file = $bandImageDir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
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
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "jpeg" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// Change file extension to .jpg if image is .jpeg when renamed
if ( $imageFileType == 'jpeg') {
		$imageFileType = 'jpg';
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// mysql_query("INSERT INTO profiles (id, user_id, band_image, band_bio, timestamp) VALUES(NULL, '$userID', NULL, NULL, NULL)") or die(mysql_error());  
// mysql_query("INSERT INTO profiles (id, user_id, band_image, band_bio, timestamp) VALUES (NULL, '$userID', NULL, NULL, NULL) ON DUPLICATE KEY UPDATE user_id = '$userID'") or die(mysql_error());

// $result = mysql_query("SELECT user_id FROM '$table'") or die(mysql_error());
/*
$result = mysql_query("SELECT * FROM $table WHERE user_id = '$userID' ") or die(mysql_error());
$row = mysql_fetch_array($result) or die(mysql_error());;

if (mysql_num_rows($result) <= 0) {
    mysql_query("INSERT INTO '$table' (id, user_id, band_image, band_bio, timestamp) VALUES(NULL, '$userID', NULL, NULL, NULL) ") or die(mysql_error());
	$lastRow = mysql_insert_id() or die(mysql_error());;
	$newpath = $bandImageDir.$lastRow.".".$imageFileType;
	mysql_query("UPDATE '$table' SET band_image = '$newpath' WHERE id = '$lastRow'") or die(mysql_error());
} else {
	// $res = mysql_query("SELECT * FROM '$table' WHERE id LIKE ".Session::get('user_id')) or die(mysql_error());
	$id = $row['id'];
	$uppath = $bandImageDir.$id.".".$imageFileType;
    mysql_query("UPDATE '$table' SET band_image = '$uppath' WHERE id = '$id'") or die(mysql_error());
}
*/
if(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'")) && mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE band_image = NULL"))){
	// Row with user_id exists but band_image is NULL
	echo "Row exists but band_image NULL";
	$result = mysql_query("SELECT * FROM $table WHERE user_id LIKE $userID") or die(mysql_error());
	$row = mysql_fetch_array($result);
	$existingRow = $row['id'];
	$newpath = $bandImageDir.$existingRow.".".$imageFileType;
	mysql_query("UPDATE $table SET band_image = '$newpath' WHERE id = $existingRow") or die(mysql_error());
	// Rename uploaded file to last row id
	rename($bandImageDir.basename($_FILES["fileToUpload"]["name"]), $bandImageDir.$existingRow.".".$imageFileType);
} elseif(mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'")) && mysql_num_rows(!mysql_query("SELECT user_id FROM $table WHERE band_image = NULL"))){
	// Row with user_id exists but band_image is NOT NULL (should add check that 'band_image' does in fact equal id.jpg)
	// rename file to existing id after upload
	// set $existingRow to 'id' of that user's existing row id
	echo "Row exists but band_image is NOT NULL";
	$result = mysql_query("SELECT * FROM $table WHERE user_id LIKE $userID") or die(mysql_error());
	$row = mysql_fetch_array($result);
	$existingRow = $row['id'];
	rename($bandImageDir.basename($_FILES["fileToUpload"]["name"]), $bandImageDir.$existingRow.".".$imageFileType);
} elseif(!mysql_num_rows(mysql_query("SELECT user_id FROM $table WHERE user_id = '$userID'"))){
	// If row with user_id does not exist, insert new row and rename file to new row id
	echo "Row does not exist";
	mysql_query("INSERT INTO $table (id, user_id, band_image, band_bio, timestamp) VALUES(NULL, $userID, NULL, NULL, NULL) ") or die(mysql_error());
	$lastRow = mysql_insert_id() or die(mysql_error());;
	$newpath = $bandImageDir.$lastRow.".".$imageFileType;
	mysql_query("UPDATE $table SET band_image = '$newpath' WHERE id = $lastRow") or die(mysql_error());
	// Rename uploaded file to last row id
	rename($bandImageDir.basename($_FILES["fileToUpload"]["name"]), $bandImageDir.$lastRow.".".$imageFileType);
}

mysql_close();
?>