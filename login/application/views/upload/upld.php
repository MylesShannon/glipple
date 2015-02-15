<div class="content">
    <h1>Upload - upld</h1>

<?php

$server = "localhost";
$user = "root";
$pass = "4DaL0v3AM0n3y";
$db = "music";

mysql_connect($server, $user, $pass) or die(mysql_error());
mysql_select_db($db) or die(mysql_error());


$userID = Session::get('user_id');

$userDir = "/music/".$userID;

// Does the user have a music directory, if not create one
if (!is_dir($userDir)) 
{
	mkdir($userDir, 0777);
}
echo "File basename:".basename( $_FILES["file"]["name"]);


$target_dir = $userDir ."/". basename( $_FILES["file"]["name"]);

//$song_file = mime_content_type($_FILES["file"]["tmp_name"]);
//echo $song_file;
//echo $target_dir."<br>";
//print_r($_FILES);
//echo move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir);

if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir)) {
    echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
} else {
    echo "Sorry, there was an error uploading your file.";
}

$target_dir = basename( $_FILES["file"]["name"]);
// Call php to store ID3 information to DB
$tag = id3_get_tag($userDir."/".$target_dir);
$owner = $userID;

$title = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["title"]);
$artist = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["artist"]);
$album = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["album"]);
//$year = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["year"]);
$year = $tag["year"];
$genre = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["genre"]);
//$comment = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["comment"]);
$comment = $tag["comment"];
$track = preg_replace("/[^0-9\-\/ ]/", "", $tag["track"]);

// mysql_query("INSERT INTO `id3` (`id`, `owner`, `title`, `artist`, `album`, `year`, `genre`, `comment`, `track`, `timestamp`) VALUES(NULL, `$owner`, `$title`, `$artist`, `$album`, `$year`, `$genre`, `$comment`, `$track`, NULL);") or die(mysql_error());  

mysql_query("INSERT INTO id3 (id, owner, title, artist, album, year, genre, comment, track, path, timestamp) VALUES(NULL, '$owner', '$title', '$artist', '$album', '$year', '$genre', '$comment', '$track', '$path', NULL)") or die(mysql_error());  
$lastRow = mysql_insert_id();
$path = "/music/".$owner."/".$lastRow.".mp3";
mysql_query("UPDATE id3 SET path = '$path' WHERE id = '$lastRow'") or die(mysql_error());
// Rename uploaded file
// Get last db row id
//$result = mysql_query("SELECT * FROM id3") or die(mysql_error());  
//$lastRow = mysql_result($result, 1, 'id') or die(mysql_error());  

// Rename uploaded file to last row id
rename($userDir."/".$target_dir, $userDir."/".$lastRow.".mp3");

//list(mysql_insert_id(),$fileext) = explode(".",$imagename); 
//rename($userDir."/".mysql_insert_id(), $musicID.$fileext);
//rename($target_dir, $userDir."/".mysql_insert_id().".mp3");

//rmdir($tmpDir."/".$userID."/".$session_id);
//rmdir($tmpDir."/".$userID);

mysql_close();
?>
</div>