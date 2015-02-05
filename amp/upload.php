<?php
if (!isset($_SESSION['login'])) { 
	session_start(); 
	if (!isset($_SESSION['login'])) {
		include_once("disp.php");
		// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
		// then remember the url parameters as well:
		redir("login.php?".$_SERVER["QUERY_STRING"]);
	    exit;
	}		
	if ($_SESSION['can_upload']!="1") {
		include_once("disp.php");
		// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
		// then remember the url parameters as well:
		redir("login.php?".$_SERVER["QUERY_STRING"]);
	    exit;
	}		 
}	

require_once("disp.php");
require_once("db.php");
require_once("sql.php");
require_once("set_td_colors.php"); 
require_once("translate.php"); 


if ($allow_upload!=1) {
	redir("login.php");
	exit;
}
	
if (!isset($act)) {
	$act="new";
}	 

echo headline(xlate('Upload'),xlate('Upload'),''); 
print "\n\n\n <!-- ACTIONS TABLE START --> \n\n\n";
echo '<table class="ampjuke_actions_table">';
echo '</table>';
print "\n\n\n <!-- ACTIONS TABLE ENDS, NEW ROW FOR MAIN_CONTENT_TABLE: --> \n\n\n </td></tr><tr><td>";
print "\n\n\n <!-- CONTENT START --> \n\n\n";
echo std_table("ampjuke_content_table","ampjuke_content");


if ($act=="store") {
	set_time_limit(0); // Just in case...
	error_reporting(0);
 	// get/set some values:
	$uploaddir=$base_music_dir;
	if (strlen($_POST['subdir'])>1) {
		// 0.7.3: Prevent .. in the $_POST['subdir'] -> might actually let somebody step OUT
		// of the cwd...
		$_POST['subdir']=str_replace('../','',$_POST['subdir']);
		$uploaddir.='/'.$_POST['subdir'];
	}	
	// 0.6.3: If type=cover, we're uploading a cover:
	if ((isset($_POST['type'])) && ($_POST['type']=="cover")) {
		$uploaddir='./covers';
		$fn=$_POST['fn'];
	}
	// 0.7.9: If type=performerid, we're uploading a performer's picture:
	if ((isset($_POST['type'])) && ($_POST['type']=='performerid')) {
		$uploaddir='./lastfm';
		$fn=$_POST['fn'];
	}
	
	$overwrite=0;
	if (isset($_POST['overwrite'])) {
		$overwrite=1;
	}	

	if (!file_exists($uploaddir)) { // the upload-folder does not exist - TRY and create it:
		$ok=mkdir($uploaddir);
		if ($ok==FALSE) { // could not create folder - abort:
		 	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);						 
		 	echo '<td><b>Error:</b> Could not create the folder: <b>'.$uploaddir.'</b><br>';
			echo 'This is most likely because your web-user dont have access to create directories/folders ';
			echo 'within <b>'.$base_music_dir.'</b>.<br>';
			echo 'Please fix it and <a href="index.php?what=upload&act=new">try again</a>.<br>';
			echo 'More information can be found in <a href="http://www.ampjuke.org/faq.php?q_id=36">';
			echo 'this FAQ-entry</a>.<br>';
			die();
		}	
	}
	
	if (!is_writable($uploaddir)) { // cannot write to folder - abort:
		 	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);						 
		 	echo '<td><b>Error:</b> Could not write to the folder: <b>'.$uploaddir.'</b><br>';
			echo 'This is most likely because your web-user dont have access to write ';
			echo 'within <b>'.$uploaddir.'</b>.<br>';
			echo 'Please fix it and <a href="index.php?what=upload&act=new">try again</a>.';
			echo 'More information can be found in <a href="http://www.ampjuke.org/faq.php?q_id=36">';
			echo 'this FAQ-entry</a>.<br>';
			die();		
	}

	// Done checking - now try to upload:
	$n=1;	
	$count=0;
	while ($n<=$max_upload_files) {
		if ((isset($_FILES['file'.$n]['name'])) && ($_FILES['file'.$n]['error']==0)) {
		 	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);						 
		 	echo '<td>';
			$tmp_name=$uploaddir.'/'.$_FILES['file'.$n]['name'];
			// 0.6.3: Change $tmp_name if we're uploading a cover:
			if ((isset($_POST['type'])) && ($_POST['type']=="cover")) {
				$tmp_name=$uploaddir.'/'.$fn;
			}
			// 0.7.9: Do the same as above w. performers:
			if ((isset($_POST['type'])) && ($_POST['type']=='performerid')) {
				$tmp_name=$uploaddir.'/'.$fn.'.jpg';
			}
			
			$ok=1;
			if (file_exists($tmp_name)) { // This file already exists:
				if ($overwrite==0) { // Dont overwrite - abort:
					echo $n.' -> <b>Error:</b> Cannot upload '.$tmp_name.': File already exists.';
					$ok=0;
				}		
				if ($overwrite==1) { // we may overwrite, check we can write to the file:
					if (!is_writable($tmp_name)) {
						echo $n.' -> <b>Error:</b> Cannot upload and replace '.$tmp_name;
						$ok=0;
					}
				}		
			} 
			
			if ($ok==1) { // OK so far - file does not exist and/or we can overwrite:
				if (move_uploaded_file($_FILES['file'.$n]['tmp_name'], $tmp_name)) { // upload ok:
					echo $n.' -> Upload of <b>'.$tmp_name.'</b> OK.';
					chmod($tmp_name, octdec($upload_chmod));
					$count++;
				} else { // upload failed: 	
					echo $n.' -> <b>Error:</b> Upload of '.$tmp_name.' failed.<br>';
				}	
			}	
			echo '</td></tr>';			
		}	
		switch ($_FILES['file'.$n]['error']) {  
				case 1:
           			print 'The file: <b>'.$_FILES['file'.$n]['name'].'</b> is bigger than this PHP installation allows.';
           			break;
			    case 2:
			        print 'The file: <b>'.$_FILES['file'.$n]['name'].'</b> is bigger than this form allows.';
		           break;
			    case 3:
			        print 'Only part of the file: <b>'.$_FILES['file'.$n]['name'].'</b> was uploaded</p>';
		            break;
		}
		$n++;
	}
	
	// Offer to do a scan+import right away:
	if ($count>0) {
	 	echo '<tr><td>&nbsp<br>'.$count.' files uploaded. ';
	 	if (($_SESSION['admin']=="1") && (!isset($fn))) { // 0.6.3: added fn-check
			echo '<a href="scan2.php">';
			echo 'Click here to setup scan+import now.</a>';
		}
		echo '</td></tr>';	
	}		
}	



if ($act=="new") { // Show upload form:
	echo '<form enctype="multipart/form-data" action="index.php?what=upload&act=store" method="POST">';
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="50000000" />'; // That's 50MB, people !!
	
	// WHAT to upload:
	// 0.6.3: Determine if it's music or a cover:
	$hl=xlate('Upload music');
	if ((isset($type)) && ($type=="cover")) {
		$hl=xlate('Upload cover');
	}
	// 0.7.9: Is it a performer ?
	if ((isset($type)) && ($type=='performerid')) {
		$hl=xlate('Upload performer picture');
	}
	
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="3" align="center"><p class="note"><b>';
	echo $hl; // 0.6.3
	echo '</b></p></td></tr>';

	$n=1;	
	// 0.7.9: Added 'performerid':
	if ((isset($type)) && (($type=="cover") || ($type=='performerid'))) {
		$n=$max_upload_files;
	}
	
	while ($n<=$max_upload_files) {
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		if (!isset($type)) {
			echo '<td>#'.$n.':</td><td>';
		}
		// 0.7.9: Added 'performerid':
		if ((isset($type)) && (($type=="cover") || ($type=='performerid'))) {
			echo '<td>'.xlate("Filename").':</td><td>';
		}
		echo '<input name="file' . $n . '" type="file" class="tfield" size="70">';
		echo '</td></tr>';
		$n++; // Love this !
	}		
	
	// WHERE to put it:
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td colspan="3" align="center"><p class="note"><b>';
	echo xlate('Other options');
	echo '</b></p></td></tr>';

	// 0.6.3: 
	if (!isset($type)) { // we're just uploading tracks:
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
		echo '<td valign="top">'.xlate('Upload to folder').': <b>'.$base_music_dir.'/</b>';
		echo '<td><input type="text" name="subdir" class="tfield" size="50"></tr>';
	} 


	// 0.6.3: Check that we can write to either $base_music_dir or './covers', - if not,
	// issue an error:
	$no_upload=0;
	if ((!is_writable($base_music_dir)) && (!isset($type))) {
		$no_upload=1;
		$err_dir=$base_music_dir;
	}
	if ((!is_writable('./covers')) && (isset($type)) && ($type=="cover")) {
		$no_upload=1;
		$err_dir='./covers';
	}
	// 0.7.9: Performers:
	if ((!is_writable('./lastfm')) && (isset($type)) && ($type=='performerid')) {	
		$no_upload=1;
		$err_dir='./lastfm';
	}	
	
	if ($no_upload==1) {
		echo '<br><p class="note"><b>Error: </b>You cannot write to <b>'.$err_dir.'</b>';
		echo ' and/or create folders within <b>'.$err_dir.'</b>.<br>';
		echo 'Upload is not possible, until you fix this (most likely missing permissions).</p>';
	}	
/* Code from previous version (replaced with the above code):
	// check that we can write to the dir.: 
	if ((!is_writable($base_music_dir)) && (!isset($type))) {
		echo '<br><p class="note"><b>Error: </b>You cannot write to <b>'.$base_music_dir.'</b>';
		echo ' and/or create folders within <b>'.$base_music_dir.'</b>.<br>';
		echo 'Upload is not possible, until you fix this (most likely missing permissions).</p>';
		$no_upload=1;
	}	
*/
	
	// overwrite OK ?
	echo '</td></tr>';
	fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);
	echo '<td>'.xlate('If file exists, overwrite it').':</td><td>';
	echo '<input type="checkbox" name="overwrite"'; 
	// 0.6.3: If we're uploading a cover, we might as well overwrite whatever is there
	// already. In other words: Checkbox for "overwrite" is checked per default:
	// 0.7.9: Added 'performerid';
	if ((isset($type)) && (($type=="cover") || ($type=='performerid'))) {
		echo ' checked';
	}
	echo '></td></tr>';

	// 0.6.3: If we're uploading a cover, we need the filename it's supposed to be stored as:
	if ((isset($type)) && ($type=="cover") && (isset($fn))) {
		echo '<input type="hidden" name="fn" value="'.$fn.'">';
		echo '<input type="hidden" name="type" value="cover">';
	}
	// 0.7.9: If we're uploading a performer's picture, we need the filename as well:
	if ((isset($type)) && ($type=='performerid') && (isset($fn))) {
		echo '<input type="hidden" name="fn" value="'.$fn.'">';
		echo '<input type="hidden" name="type" value="performerid">';
	}

	if ($no_upload==0) {	
		fancy_tr($tmpcount,$tdnorm,$tdalt,$tdhighlight);	
		echo '<td colspan="2" align="center"><input type="submit" value="'.xlate('Save & continue').'" />';
		echo '</td></tr></form>';
	}
}
?>
</table>
