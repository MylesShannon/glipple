<div class="content">
    <h1>Upload - upld</h1>

<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once('./getid3/getid3/getid3.php');
$getID3 = new getID3;

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";
$usersdb = "login";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


$userID = Session::get('user_id');

$userDir = "/media/music/".$userID;

// Does the user have a music directory, if not create one
if (!is_dir($userDir)) 
{
	mkdir($userDir, 0775);
}

$filename=basename( $_FILES["file"]["name"]);
// echo "File basename:".$filename;




 $uploadok = 1;
 if (!isset($_FILES["file"]))

 {
 	// we need to make dropzone error
	$uploadok =0;
	echo "No submission!";
}
	
$target_dir = $userDir ."/". basename( $_FILES["file"]["name"]);
$songFileType = pathinfo($filename,PATHINFO_EXTENSION);

echo $songFileType.'<br />';
echo $filename.'<br />';

if ($songFileType != 'mp3'){
	$uploadok =0;
	echo "File type not supported";
}

//$song_file = mime_content_type($_FILES["file"]["tmp_name"]);
//echo $song_file;
//echo $target_dir."<br>";
//print_r($_FILES);
//echo move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir);
if ($uploadok == 0){

	    echo "<br />Your file was not uploaded.";
}
	else{
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir)) {
	    echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
	
// Call php to store ID3 information to DB
$tag =  $getID3->analyze($target_dir);

$owner = $userID;


$title = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['title'][0]);

mysql_select_db($usersdb) or die(mysql_error());
$result = mysql_query("SELECT * FROM users WHERE user_id LIKE ".$userID) or die(mysql_error());  
$usernamequery = mysql_fetch_array($result);

//$artist=$usernamequery['user_name'];

$artist = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['artist'][0]);
$album = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['album'][0]);
//$year = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["year"]);
$year = $tag['tags']['id3v2']['year'][0];
$genre = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['genre'][0]);
//$comment = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["comment"]);
$comment = $tag['id3v1']['comment'];
$track = preg_replace("/[^0-9\-\/ ]/", "", $tag['tags']['id3v1']['track'][0]);

// mysql_query("INSERT INTO `id3` (`id`, `owner`, `title`, `artist`, `album`, `year`, `genre`, `comment`, `track`, `timestamp`) VALUES(NULL, `$owner`, `$title`, `$artist`, `$album`, `$year`, `$genre`, `$comment`, `$track`, NULL);") or die(mysql_error());  
mysql_select_db($db) or die(mysql_error());

mysql_query("INSERT INTO id3 (id, owner, title, artist, album, year, genre, comment, track, path, timestamp) VALUES(NULL, '$owner', '$title', '$artist', '$album', '$year', '$genre', '$comment', '$track', NULL, NULL)") or die(mysql_error());  
$lastRow = mysql_insert_id();
$path = "/media/music/".$owner."/".$lastRow.$songFileType;

mysql_query("UPDATE id3 SET path = '$path' WHERE id = '$lastRow'") or die(mysql_error());
// Rename uploaded file
// Get last db row id
//$result = mysql_query("SELECT * FROM id3") or die(mysql_error());  
//$lastRow = mysql_result($result, 1, 'id') or die(mysql_error());  

// Rename uploaded file to last row id
rename($target_dir, $userDir."/".$lastRow.$songFileType);

//list(mysql_insert_id(),$fileext) = explode(".",$imagename); 
//rename($userDir."/".mysql_insert_id(), $musicID.$fileext);
//rename($target_dir, $userDir."/".mysql_insert_id().".mp3");

//rmdir($tmpDir."/".$userID."/".$session_id);
//rmdir($tmpDir."/".$userID);




	} else {
	    echo "Sorry, there was an error uploading your file.";
	}
}

mysql_close();
?>
</div>