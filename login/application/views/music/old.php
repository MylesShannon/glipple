<div class="content">
    <h1>My Music</h1>

<?php

$userID = Session::get('user_id');

$session_id = time();
$session_id = $session_id * $userID;
$musicDir = "/var/www/html/music/".$userID;

// *** Scan ID3 and add to SQL DB "getid3.files" ***
//$newscan = $musicDir."/";
//require_once('/var/www/html/application/views/id3/mysql.php');

// *** Rename file to primary ID from column in getid3 DB ***

//$musicID = 123;
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
//echo "<br>".$musicID;
//rename("".mysql_insert_id()."", $musicID.".mp3");

// DB credentials 
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'dJc001Nfr35h';

$DB_DB = 'getid3';
$DB_TABLE = 'files';

// CREATE DATABASE `getid3`;

// Create connection
$conn = @mysql_connect($DB_HOST, $DB_USER, $DB_PASS);
// Check connection
if (!$conn){
	 echo( "<p>Unable to connect to database manager.</p>");
       die('Could not connect: ' . mysql_error());
	 exit();
} else {
  // echo("<p>Successfully Connected to MySQL Database Manager!</p>");
}
// Select and check DB selection
if (! @mysql_select_db($DB_DB ) ){
	 echo( "<p>Unable to  connect database...</p>");
	 exit();
} else {
  // echo("<p>Successfully Connected to Database '".$DB_DB."'!</p>");
}
echo("<p>Output all rows in getid3.files DB</p>");

// Print 'ID' and 'filename' from 'file' table
$result = mysql_query("SELECT * FROM `".$DB_TABLE."`")
or die(mysql_error());  

echo "<table border='1'>";
echo "<tr> <th>ID</th> <th>Path</th> </tr>";
// keeps getting the next row until there are no more to get
while($row = mysql_fetch_array( $result )) {
	// Print out the contents of each row into a table
	echo "<tr><td>"; 
	echo $row['ID'];
	echo "</td><td>"; 
	echo $row['filename'];
	echo "</td></tr>"; 
	
	//var_dump($row);
	//d($row); 
} 

echo "</table>";



mysql_close($conn);

?>
</div>