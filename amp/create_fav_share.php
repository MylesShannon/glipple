<?php
session_start();
if (isset($_SESSION['login'])) {
	require("db.php");
	require("sql.php");
	if (isset($_POST['username'])) {
		include_once("disp.php");
		$uid=get_user_id($_SESSION['login']);
		$qry="INSERT INTO fav_shares (owner_id, fav_name, share_id) VALUES";
		$qry.=" ('".$uid."', '".$_POST['fav_list']."', '".$_POST['username']."')";
		$result=execute_sql($qry,0,-1,$nr);
	}	
}
echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
echo '</script>';  
?>
