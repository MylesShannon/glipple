<?php
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";

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
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
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

mysql_query("INSERT INTO profiles (id, user_id, band_image, band_bio, timestamp) VALUES(NULL, '$userID', NULL, NULL, NULL)") or die(mysql_error());  
$lastRow = mysql_insert_id();
$path = $bandImageDir.$lastRow.".".$imageFileType;
mysql_query("UPDATE profiles SET band_image = '$path' WHERE id = '$lastRow'") or die(mysql_error());

// Rename uploaded file to last row id
rename($userDir.basename($_FILES["fileToUpload"]["name"]), $bandImageDir.$lastRow.".".$imageFileType);

mysql_close();
?>