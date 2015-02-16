<?php
$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "login";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());

$userID = Session::get('user_id');

$bandImageDir = "/var/www/html/login/public/img/band/".$userID;

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
?>