<?php
session_start();
if (!isset($_SESSION['login'])) { 
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}
	
parse_str($_SERVER["QUERY_STRING"]);

// 0.3.3:
if ($what=="filter_tracks") {
    $_SESSION['filter_tracks']=$set;
    $_SESSION['new_start']="0";
}

// 0.5.2: Toggle Yes -> No or No -> Yes for a certain column in fav_shares:
if ($what=="fav_share") {
	require_once("db.php");
	require_once("sql.php");
	require_once("disp.php");
	$qry="UPDATE fav_shares SET ".$col."='".$new."' WHERE id='".$id."'";
	$qry.=" AND owner_id='".get_user_id($_SESSION['login'])."' LIMIT 1";
	$result=execute_sql($qry,0,-1,$n);
	// If both columns are set to No (=not shared), just delete the entry:
	$qry="SELECT * FROM fav_shares WHERE id='".$id."'";
	$qry.=" AND owner_id='".get_user_id($_SESSION['login'])."' LIMIT 1";
	$result=execute_sql($qry,0,-1,$n);
	$row=mysql_fetch_array($result);
	if (($row['can_add']=="0") && ($row['can_delete']=="0")) {
		$qry="DELETE FROM fav_shares WHERE id='".$id."'";
		$qry.=" AND owner_id='".get_user_id($_SESSION['login'])."' LIMIT 1";
		$result=execute_sql($qry,0,-1,$n);
	}	
}	
		
echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
echo '</script>';  
?>
		
