<?php
if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}	


function disp_user_list($uid,$fav_listname) {
	$qry="SELECT id,name FROM user"; // WHERE user_id='".get_user_id($_SESSION['login']);
	$qry.=" ORDER BY name";
	$result=execute_sql($qry,0,1000000,$nr);
//	echo '<table>';
	echo '<tr><td valign="top" class="content">';

	if ($nr>0) { // do we have one or more favorite lists:
		echo '<FORM name="shared_fav_list" METHOD="POST" action="create_fav_share.php">';
		echo '<input type="hidden" name="fav_list" value="'.$fav_listname.'">';
		echo xlate("Create new").':';
		echo '<SELECT NAME="username" class="tfield" ONCHANGE="Javascript:submit()">';
		echo '<OPTION VALUE="" selected>---</OPTION>';
		while ($row=mysql_fetch_array($result)) {
		 	if ($row['id']<>$uid) {
				echo '<OPTION VALUE="'.$row['id'].'">'.$row['name'].'</OPTION>';
			}	
		}
		echo '</SELECT></form>';	
	} 
	echo '</td><td> </td></tr>';
//	echo '</table>';
}	


require_once("sql.php");
require_once("disp.php");
require_once("set_td_colors.php");
$uid=get_user_id($_SESSION['login']);


if ($act=="disp") {
	$qry="SELECT * FROM fav_shares WHERE owner_id='".$uid."'";
	$qry.=" AND fav_name='".urldecode($id)."'";
	$result=execute_sql($qry,$start,$count,$num_rows);

	echo headline($what,urldecode($id),'');
	print "\n\n\n <!-- Now on to content --> \n\n\n </td></tr><tr><td>";
	echo std_table("ampjuke_content_table","ampjuke_content");
	require("tbl_header.php");
	echo '<th align="left">'.xlate("Username").'</th>';
	echo '<th> </th>';

	while ($row=mysql_fetch_array($result)) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>';
		echo get_username($row['share_id']);
		echo '</td>';

		echo '<td>';
		echo '<a href="?what=fav_share&act=remove&id='.urlencode($id).'&victim='.$row['id'];
		echo '">'.get_icon($_SESSION['icon_dir'],'delete','');
		echo ' ['.xlate("Delete").']</a>';
	
		print "</td></tr> \n";
	}
	disp_user_list($uid,urldecode($id));
	echo '</table>';	
} // act==disp

if ($act=="remove") { // we want to delete an entry we're sharing:
	$qry="SELECT * FROM fav_shares WHERE id='".$victim."'";
	$qry.=" AND owner_id='".$uid."' AND fav_name='".urldecode($id)."'"; // 0.8.3: Aaaawww, WTF is THIS ??: ....LIMIT 1";
	$result=execute_sql($qry,0,10,$nr);
	if ($nr==1) {
		$qry="DELETE FROM fav_shares WHERE id='".$victim."' LIMIT 1";
		$result=execute_sql($qry,0,-1,$x);
	}
	echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
	echo '</script>';  			
}	 
?>
