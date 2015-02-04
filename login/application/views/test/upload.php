<div class="content">
    <h1>Test</h1>

<?php

$userID = Session::get('user_id');
	
//$session_id = new DateTime('2000-01-01');
//$session_id->format('YmdHis');
$session_id = time();
$session_id = $session_id * $userID;
$tmpDir = "/var/www/html/music";

//mkdir($tmpDir."/".$userID, 0777);
//mkdir($tmpDir."/".$userID."/".$session_id, 0777);
//$newscan = $tmpDir."/".$userID."/".$session_id."/";
$newscan = $tmpDir."/".$userID."/";

//$newscan = "/var/www/html/tmp/";
//echo $newscan."<br>";

$target_dir = $newscan;
$target_dir = $target_dir . basename( $_FILES["file"]["name"]);
//$uploadOk=1;
//echo $target_dir."<br>";

//print_r($_FILES);
//echo move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir);

if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir)) {
    echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
} else {
    echo "Sorry, there was an error uploading your file.";
}


//echo $userID;
//require_once('/var/www/html/application/views/id3/mysql.php');
//echo $userID;


//Rename file to primary ID from column in getid3 DB

$musicID = 123;
//$musicID = mysql_insert_id();
/*
mysql_select_db('getid3');
$result = mysql_query('select * from table');
if (!$result) {
    die('Query failed: ' . mysql_error());
}
*/

//$musicID = mysql_fetch_field($result, 1);
//printf ("musicID is %d\n", mysql_insert_id());
echo "<br>".$musicID;
//rename("name", $musicID.".mp3");
	
//rmdir($tmpDir."/".$userID."/".$session_id);
//rmdir($tmpDir."/".$userID);

?>
</div>