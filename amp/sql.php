<?php
/*
execute_sql:
The SQL "wrapper".
If $count=-1 there will not be returned any $num_rows
*/
function execute_sql($qry,$start,$count,&$num_rows) {
// You can uncomment the "global" stuff if you want to get performance stats. 
// (also see "make_header.php" and "index.php"
/*
global $sql_statements; 
global $sql_txt;
*/
include('db.php');
$connection=mysql_connect($db_host,$db_user,$db_password) or die('SQL: Could not connect.');
mysql_select_db($db_name) or die('SQL: Could not select database !');

// 0.6.7: Use $ampjuke_tbl_prefix in "appropriate" places before executing the query:
if ((isset($ampjuke_tbl_prefix))) {
	$qry=str_replace("FROM user", "FROM ".$ampjuke_tbl_prefix."user", $qry);
	$qry=str_replace("FROM album", "FROM ".$ampjuke_tbl_prefix."album", $qry);
	$qry=str_replace("FROM performer", "FROM ".$ampjuke_tbl_prefix."performer", $qry);
	$qry=str_replace(", performer WHERE", ", ".$ampjuke_tbl_prefix."performer WHERE", $qry);
	$qry=str_replace("FROM track", "FROM ".$ampjuke_tbl_prefix."track", $qry);
	$qry=str_replace("track.", $ampjuke_tbl_prefix."track.", $qry);	
	$qry=str_replace("performer.pid", $ampjuke_tbl_prefix."performer.pid", $qry);		
	$qry=str_replace("album.aid FROM", $ampjuke_tbl_prefix."album.aid FROM", $qry);			
	$qry=str_replace("performer.pname", $ampjuke_tbl_prefix."performer.pname", $qry);		
	$qry=str_replace("OUTER JOIN performer ON album", "OUTER JOIN ".$ampjuke_tbl_prefix."performer ON ".$ampjuke_tbl_prefix."album", $qry);			
	$qry=str_replace("ORDER BY album.", "ORDER BY ".$ampjuke_tbl_prefix."album.", $qry);		
	$qry=str_replace("FROM fav", "FROM ".$ampjuke_tbl_prefix."fav", $qry);
	$qry=str_replace("FROM queue", "FROM ".$ampjuke_tbl_prefix."queue", $qry);
	$qry=str_replace("WHERE album.aid", "WHERE ".$ampjuke_tbl_prefix."album.aid", $qry);
	$qry=str_replace("LEFT JOIN track ON", "LEFT JOIN ".$ampjuke_tbl_prefix."track ON", $qry);
	$qry=str_replace("ON track.album_id = album.aid", "ON ".$ampjuke_tbl_prefix."track.album_id = ".$ampjuke_tbl_prefix."album.aid", $qry);	
	$qry=str_replace("LEFT JOIN performer ON", "LEFT JOIN ".$ampjuke_tbl_prefix."performer ON", $qry);
	$qry=str_replace("ON track.performer_id = performer.pid", "ON ".$ampjuke_tbl_prefix."track.performer_id = ".$ampjuke_tbl_prefix."performer.pid", $qry);	
	$qry=str_replace("= album.aid", "= ".$ampjuke_tbl_prefix."album.aid", $qry);				
	$qry=str_replace("fav.", $ampjuke_tbl_prefix."fav.", $qry);
	$qry=str_replace("UPDATE ", "UPDATE ".$ampjuke_tbl_prefix, $qry);
	$qry=str_replace("INSERT INTO ", "INSERT INTO ".$ampjuke_tbl_prefix, $qry);	
	$qry=str_replace("ALTER TABLE ", "ALTER TABLE ".$ampjuke_tbl_prefix, $qry); // 0.7.4: ...!
}

if ($count==-1) { // 0.7.3: Introduced: Only execute sql if count=-1...
	$result=mysql_query($qry);
}	
if ($count!=-1) { // ...otherwise: include LIMIT and then execute sql
	$qry.=" LIMIT $start,$count"; 
	$result=mysql_query($qry) or die('CANNOT: '.$qry);
	$num_rows=mysql_num_rows($result); // 0.7.3: Moved here
}	

// You can uncomment the next two statements if you want performance stats.:
/*
$sql_statements++;
$sql_txt.='['.$sql_statements.'] '.$qry.'<br>';	
*/

return $result;
}

function get1row($qry) {
	global $sql_statements;
	global $sql_txt;
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
	$sql_statements++;
	$sql_txt.='['.$sql_statements.'] '.$qry.'<br>';
	return $row;
}	

// 0.7.4: The stuff below is replaced with this - more simple - function:
function get_num_rows($tbl,$field) {
	include('db.php');
	$ret=0;
	$connection=mysql_connect($db_host,$db_user,$db_password) or die('SQL: Could not connect.');
	mysql_select_db($db_name) or die('SQL: Could not select database !');

	if ((isset($ampjuke_tbl_prefix))) {
		$tbl=$ampjuke_tbl_prefix.$tbl;
	}
	
	$qry="SELECT COUNT(".$field.") FROM ".$tbl;
	$result=mysql_query($qry);
	return mysql_result($result,0);
}	
?>
