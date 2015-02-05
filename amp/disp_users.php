<?php
// 0.3.4: Added a check to see if the user is an administrator:
if (!isset($_SESSION['login']) && ($_SESSION['admin']!="0")) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}	

require_once("sql.php");
require_once("set_td_colors.php");
require_once("disp.php");
require_once('configuration.php'); // 0.7.6

// 0.8.0: Default sort:
if ($order_by=='') {
	$order_by='last_login';
	$dir='DESC';
}	

if ($act=="disp") {
    $qry="SELECT * FROM user";
    if ($order_by!="") {
	   $qry.=" ORDER BY $order_by $dir ";
    }

    $result=execute_sql($qry,0,1000000,$num_rows);
    echo headline($what,'','');

    if ($dir=="ASC") { $newdir="DESC"; } else { $newdir="ASC"; }
	print "\n\n\n <!-- ACTIONS TABLE START --> \n\n\n";
	echo '<table class="ampjuke_actions_table">'; 	
    echo '<tr><td><a href="index.php?what=users&act=create"><img src="./ampjukeicons/mnu_arr.gif" border="0">';
    echo xlate("Create new").'</a></td></tr></table>'; // 0.6.1: Added </table>
	print "\n\n\n <!-- ACTIONS TABLE ENDS, NEW ROW FOR MAIN_CONTENT_TABLE: --> \n\n\n </td></tr><tr><td>";


	print "\n\n\n <!-- CONTENT START --> \n\n\n";
	echo std_table("ampjuke_content_table","ampjuke_content");
    require("tbl_header.php");
    echo '<th> </th>'; // Delete option
    tbl_header("users",xlate("ID"),"left","id",$order_by,$dir,$newdir,$count,'&act='.$act);
    tbl_header("users",xlate("Username"),"left","name",$order_by,$dir,$newdir,$count,'&act='.$act);
    tbl_header("users",xlate("Administrator"),"left","admin",$order_by,$dir,$newdir,$count,'&act='.$act);

    // 0.3.6: Additional columns:
    tbl_header("users",xlate("Language"),"left","lang",$order_by,$dir,$newdir,$count,'&act='.$act);
    tbl_header("users",xlate("Download"),"left","can_download",$order_by,$dir,$newdir,$count,'&act='.$act);
    // 0.6.1: Upload:
	if ($allow_upload=="1") {	
	    tbl_header("users",xlate("Upload"),"left","can_upload",$order_by,$dir,$newdir,$count,'&act='.$act);
	}
	// 0.7.0: Downsampling/transcoding enabled:
	if ($lame_enabled==1) {
	tbl_header("users",xlate("Transcode"),"left","lame_local_enabled",$order_by,$dir,$newdir,$count,'&act='.$act);
	}
	// The rest:
    tbl_header("users",xlate("Last login"),"right","last_login",$order_by,$dir,$newdir,$count,'&act='.$act);
    tbl_header("users",xlate("IP-address"),"right","last_ip",$order_by,$dir,$newdir,$count,'&act='.$act);
    echo '<th> </th>'; // Edit option

    while ($row=mysql_fetch_array($result)) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
        echo '<td><a href="index.php?what=users&act=delete&id='.$row['id'];
		echo '">'.get_icon($_SESSION['icon_dir'],'delete',xlate("Delete")).'</a></td>';
    	echo '<td>'.$row['id'].'</td>';
    	
        echo '<td>'.$row['name'].'</td>';
 
        $adm=xlate("No");
		if ($row['admin']=="1") { $adm=xlate("Yes"); }
    	echo '<td>'.$adm.'</td>';

    	echo '<td>'.$row['lang'].'</td>';

    	// 0.3.6: download ?
    	if ($row['can_download']=="1") {
    		echo '<td>'.xlate("Yes");
    	} else {
			echo '<td>'.xlate("No");
		}		
		echo '</td>';
    	// 0.6.1: upload ?
		if ($allow_upload=="1") {
	    	if ($row['can_upload']=="1") {
    			echo '<td>'.xlate("Yes");
    		} else {
			 	echo '<td>'.xlate("No");
			}
			echo '</td>'; 	
		}
		// 0.7.0: Downsample/transcode ?
		if ($lame_enabled==1) {
			if ($row['lame_local_enabled']=="1") {
				echo '<td>';
				// echo '<td>'.xlate("Yes").' ';
				// 0.7.6: Changed. Display the parameters as well:
				if ($row['lame_local_parameters']<>'') {
					echo $row['lame_local_parameters'];
				} else {
					echo get_configuration('lame_parameters');
				}
			} else {
				echo '<td>'.xlate("No");
			}	
				
			echo '</td>';
		}	
		
		// The rest:
		if ($row['last_login']<>'') {
	        echo '<td align="right">'.mydate($row['last_login']).'</td>';
		} else {
	        echo '<td align="right">-</td>';
		}

    	echo '<td align="right">'.$row['last_ip'].'</td>';

    	echo '<td><a href="index.php?what=users&act=edit&id='.$row['id'].'">';
		echo get_icon($_SESSION['icon_dir'],'edit',xlate("Edit"));
		echo '</a>&nbsp&nbsp';


    	print "</tr> \n";
    }

    include("page_numbers.php");

} // if act=disp



if ($act=="edit") {
	$qry="SELECT * FROM user WHERE id='".$id."' LIMIT 1";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
	$id=$row['id'];
	$username=$row['name'];
	$admin=$row['admin'];
//	$passwd=$row['password'];
	$lang=$row['lang']; 
	$act="create"; // smart !
    // 0.3.3: Avoid "create new" as headline:
    $act2=1;
    // 0.3.6: can download ?
    $can_download=$row['can_download'];
    // 0.6.1: can upload ?
    $can_upload=$row['can_upload'];
	// 0.7.0: transcode ?
	$lame_local_enabled=get_local_lame($row['id'],$lame_local_parameters);
	// 0.8.4: Email adr.:
	$email=$row['email'];
}	



if ($act=="create") {
	echo '<FORM NAME"="userform" METHOD="POST" action="index.php?what=users&act=store">';
	if (isset($id)) { echo '<input type="hidden" name="id" value="'.$id.'">'; }
	// 0.3.3: Changed to accomodate the diff. btw. "create" and "edit":

	if (!isset($act2)) {
        headline("","Create new","");
        $passwd='ampjuke_password';
        $pws=xlate('Password');
    } else {
        headline("","Edit","");
        $pws=xlate('Change password').' ('.xlate('Leave blank to keep current password').')';
    }

	echo std_table("ampjuke_content_table","ampjuke_content");
	// Username:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Username").':</td>';
	echo '<td align="left"><input type="text" name="username" class="tfield"';
	if (isset($username)) { echo ' value="'.$username.'"'; }
	echo '></td></tr>';

	// 0.8.4: Email address:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate('E-mail address').':</td>';
	echo '<td align="left"><input type="text" name="email" class="tfield"';
	if (isset($email)) { echo ' value="'.$email.'"'; }
	echo '></td></tr>';
	
	// Admin ?
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Administrator").'? :</td>';
	echo '<td><input type="checkbox" name="admin"';
	if (isset($admin) && ($admin=="1")) { echo ' checked'; }
	echo '></td></tr>';

	// Password (change):
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.$pws.':</td>';
	echo '<td><input type="text" name="passwd" class="tfield"';
	if (isset($passwd)) { echo ' value="'.$passwd.'"'; }
	echo '></td></tr>';

	// Language:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Language").':</td><td>';
	if (!isset($lang)) {
		$lang="EN";
	}	
	disp_language_options($lang);
	echo '</td></tr>';

	// 0.3.6: Can the user download ?
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate("Download").'? :</td>';
	echo '<td><input type="checkbox" name="can_download" class="tfield"';
	if ((isset($can_download) && ($can_download=="1"))) { echo ' checked'; }
	echo '></td></tr>';
	// 0.6.1: Can the user upload ?
	if ($allow_upload==1) { // 0.7.0: Added this check
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td>'.xlate("Upload").'? :</td>';
		echo '<td><input type="checkbox" name="can_upload" class="tfield"';
		if ((isset($can_upload) && ($can_upload=="1"))) { echo ' checked'; }
		echo '></td></tr>';
	}	
	// 0.7.0: Downsample/transcode:
	if ($lame_enabled==1) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<tr><td colspan=3"> </td></tr>';	 	
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		if (!isset($lame_local_parameters)) { $lame_local_parameters=''; }
		echo '<td>'.xlate("Transcode").' ? :</td>';
		echo '<td><input type="checkbox" name="lame_local_enabled" class="tfield"';
		if ((isset($lame_local_enabled) && ($lame_local_enabled=="1"))) { echo ' checked'; }
		echo '>';
		echo '<input type="text" name="lame_local_parameters" class="tfield" size="40"';
		echo ' value="'.$lame_local_parameters.'">';
		echo ' <i>Global setting:</i><b class="tfield">'.$lame_parameters.'</b>';
		echo '</td></tr>';
	} else { // LAME not enabled, but we still want to remember current settings:
		if ((isset($lame_local_enabled) && ($lame_local_enabled=="1"))) { 
			echo '<input type="hidden" name="lame_local_enabled" value="1">';
		}
		echo '<input type="hidden" name="lame_local_parameters" value="'.$lame_local_parameters.'">';	
	}	


	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<tr><td colspan=3"> </td></tr>';	 	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="2" align="center"><input type="submit" class="tfield" value="'.xlate("Save & continue").'" ';
    echo '</td></tr>';
	echo '</table></FORM>';
}	



if ($act=="store") {
	if (isset($_POST['admin'])) { $adm="1"; } else { $adm="0"; }
	// 0.3.6: download ?
	if (isset($_POST['can_download'])) { $cd="1"; } else { $cd="0"; }
	// 0.6.1: upload ?
	if ($allow_upload==1) { // 0.7.0: Added this check
		if (isset($_POST['can_upload'])) { $cu="1"; } else { $cu="0"; }
	}	
	// 0.7.0: downsample ?
	if (isset($_POST['lame_local_enabled'])) { $lle="1"; } else { $lle="0"; }
	
	if (isset($_POST['id'])) {
	 	// 0.7.4: Update password-salt + password:
	 	if (isset($_POST['passwd']) && ($_POST['passwd']<>"")) {
		 	$salt=generate_password_salt();
		 	$_POST['passwd']=md5($salt.$_POST['passwd']);
		}	 	
		$qry="UPDATE user SET name='".$_POST['username']."', ";
		$qry.=" email='".$_POST['email']."', "; // 0.8.4
		$qry.="admin='".$adm."'";
        $qry.=", lang='".$_POST['lang']."', can_download='".$cd."'"; // 0.3.6: can_download
        // 0.6.1: can_upload:
        $qry.=", can_upload='".$cu."'";
		// 0.7.0: downsample:
		$qry.=", lame_local_enabled='".$lle."'";
		// 0.7.4: password+password-salt:
		if (isset($_POST['passwd']) && ($_POST['passwd']<>"")) {
		 	$qry.=", password='".$_POST['passwd']."'";
			$qry.=", password_salt='".$salt."'";
		}	
		$qry.=", lame_local_parameters='".$_POST['lame_local_parameters']."'";
		$qry.=" WHERE id='".$_POST['id']."' LIMIT 1"; 
        if ($_SESSION['login']==$_POST['username']) {
            $_SESSION['lang']=$_POST['lang'];
        }
	} else { // We're creating a new one:
		// 0.4.2: Check if a value for LANG was POST'ed:
		if (isset($_POST['lang'])) {
			$lang=$_POST['lang'];
		} else {
			$lang="EN";
		}
		$salt=generate_password_salt(); // 0.7.4
		$_POST['passwd']=md5($salt.$_POST['passwd']); // 0.7.4
		$qry="INSERT INTO user (name, email, admin, password, password_salt, last_login, lang, count, can_download, can_upload, cssfile)"; 
		$qry.=" VALUES ('".$_POST['username']."', '".$_POST['email']."', '".$adm."', '".$_POST['passwd']."', '".$salt."', '".date('U')."' ,'".$lang."', '20'"; // 0.8.4: Added email+date('U')
		$qry.=", '".$cd."', '".$cu."'";
		$qry.=", 'AmpJukeStandard.css'";
		$qry.=")";
	} // if id was set...
	$result=execute_sql($qry,0,-1,$nr);
	redir("index.php?what=users&act=disp");
}



if ($act=="delete") {
//	delete_user($id);
//	redir("index.php?what=users&act=disp");	

	// get the user name (used for deletion of favorites):
	$qry="SELECT id,name FROM user WHERE id='".$id."'";
	$result=execute_sql($qry,0,-1,$nr);
	$row=mysql_fetch_array($result);
	$name=$row['name'];

	// delete the user-account:
	$qry="DELETE FROM user WHERE id=".$id." LIMIT 1";
	$result=execute_sql($qry,0,-1,$nr);

	// delete the FAVORITES as well:
	$qry="DELETE FROM fav WHERE user_id='".$id."'";
	$result=execute_sql($qry,0,-1,$nr);

	// 0.5.2: Delete any entries in fav_shares:
	$qry="DELETE FROM fav_shares WHERE share_id='".$id."'";
	$result=execute_sql($qry,0,-1,$nr);

	// 0.7.4: Delete queue-entries:
	$qry="DELETE FROM queue WHERE user_name='".$name."'";
	$result=execute_sql($qry,0,-1,$nr);
	
	redir("index.php?what=users&act=disp");

}

?>	

