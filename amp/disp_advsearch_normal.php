<?php // 0.7.8: Advanced search (adv. search): Search options rather than just simple text-input.
$max_results=50;


if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}

if ((!isset($free_txt)) && (!isset($act))) {
	if (isset($_POST['free_txt'])) {
		$free_txt=preg_replace ("/[^0-9^a-z^A-Z^_^ ^.^(^)^+^#]/","",$_POST['free_txt']);
		$loc=$base_http_prog_dir.'/index.php?what=advsearch&album_ltr='.$_POST['album_ltr'];
		$loc.='&performer_ltr='.$_POST['performer_ltr'].'&year='.$_POST['year'].'&free_txt='.$free_txt;
	
		echo '<script type="text/javascript" language="javascript">'; 
		echo 'window.location.replace("'.$loc.'");';
		echo '</script>';   
		die();
	}
}


//require_once("sql.php");
//require_once("set_td_colors.php");
//require_once("disp.php");


function disp_performer_selection($default) {
//	$pqry="SELECT * FROM performer ORDER BY pname";
	$pqry="SELECT DISTINCT pid,pname FROM performer ORDER BY pname ASC";
	$presult=execute_sql($pqry,0,10000000,$nr);
	echo '<SELECT NAME="performer_ltr" class="tfield">';
	echo '<OPTION VALUE=""';
	if ($default=="") {
		echo ' selected';
	}
	echo '>[Select...]</OPTION>';	
	$prevletters='';
	while ($prow=mysql_fetch_array($presult)) {
		if (substr($prow['pname'],0,1)<>$prevletters) {
			echo '<OPTION VALUE="'.substr($prow['pname'],0,1).'"';
			if (substr($prow['pname'],0,1)==$default) {
				echo ' selected';
			}
			echo '>'.substr($prow['pname'],0,1).'..</OPTION>';
			$prevletters=substr($prow['pname'],0,1);
		}
	}
	echo '</SELECT>';
}	

function disp_album_selection($default) {
//	$pqry="SELECT * FROM album ORDER BY aname ASC";
	$pqry="SELECT DISTINCT aname FROM album ORDER BY aname ASC";
	$presult=execute_sql($pqry,0,10000000,$nr);
	echo '<SELECT NAME="album_ltr" class="tfield">';
	echo '<OPTION VALUE=""';
	if ($default=="") {
		echo ' selected';
	}
	echo '>[Select...]</OPTION>';	
	$prevletters='';
	while ($prow=mysql_fetch_array($presult)) {
		if (substr($prow['aname'],0,1)<>$prevletters) {
			echo '<OPTION VALUE="'.substr($prow['aname'],0,1).'"';
			if (substr($prow['aname'],0,1)==$default) {
				echo ' selected';
			}
			echo '>'.substr($prow['aname'],0,1).'..</OPTION>';
			$prevletters=substr($prow['aname'],0,1);
		}
	}
	echo '</SELECT>';
}

function disp_year_selection($default) {
	$pqry="SELECT DISTINCT year FROM track WHERE year<>'' ORDER BY year DESC";
	$presult=execute_sql($pqry,0,1000,$nr);
	echo '<SELECT NAME="year" class="tfield">';
	echo '<OPTION VALUE=""';
	if ($default=="") {
		echo ' selected';
	}
	echo '>[Select...]</OPTION>';	
	while ($prow=mysql_fetch_array($presult)) {
		echo '<OPTION VALUE="'.$prow['year'].'"';
		if ($prow['year']==$default) {
			echo ' selected';
		}
		echo '>'.$prow['year'].'</OPTION>';
		$y--;
	}	
	echo '</SELECT>';
}


// Always show the form:
echo headline($what,'Advanced search','');

echo std_table("ampjuke_actions_table","");
echo '<tr><td> </td></tr>';
echo '</table>';

echo std_table("ampjuke_content_table","ampjuke_content");

echo '<FORM NAME="adv_search" method="POST" action="index.php?what=advsearch">';	
echo '<tr><td>'.xlate('Performer').':</td><td>';
$dp='';
if ((isset($performer_ltr)) && ($performer_ltr<>'')) {
	$dp=$performer_ltr;
}
disp_performer_selection($dp);
echo '</td></tr>';

echo '<tr><td>'.xlate('Year').':</td><td>';
$dp='';
if ((isset($year)) && ($year<>'')) {
	$dp=$year;
}
disp_year_selection($dp);
echo '</td></tr>';

echo '<tr><td>'.xlate('Album').':</td><td>';
$dp='';
if ((isset($album_ltr)) && ($album_ltr<>'')) {
	$dp=$album_ltr;
}
disp_album_selection($dp);
echo '</td>';

echo '<tr><td>'.xlate('Title').':</td><td><input type="text" class="tfield" name="free_txt" size="20"';
$dp='';
if ($free_txt<>'') {
	$dp=$free_txt;
}
echo ' VALUE="'.$dp.'">';
echo '</td></tr>';

echo '<tr><td colspan="2" align="center"><input type="submit" value="'.xlate('Search').'">';

echo '</FORM></table><br><hr><br>';


if (isset($free_txt)) { // A search was actually entered, go ahead with the hard part:
	// Setup stuff:
//	$free_txt=preg_replace ("/[^0-9^a-z^A-Z^_^ ^.^(^)^+^#]/","",$_POST['free_txt']);
	
	// Prepare FIRST query:
	$qry="SELECT * FROM track ";
	$and_needed=0;
	// Did we post a year ?
	if ($year<>'') { 
		$qry.="WHERE year='".$year."'";
		$and_needed=1;
	}
	// Did we post something in free text ?
	if (($free_txt<>'') && (isset($free_txt))) {
		if ($and_needed==1) { 
			$qry.=' AND track.name LIKE "%'.$free_txt.'%"';; 
		} else {
			$qry.='WHERE track.name LIKE "%'.$free_txt.'%"';
		}		
	}
	$qry.=" ORDER BY track.name ASC";
	$result=execute_sql($qry,0,1000000,$num_rows);

	$result_id=array();
	$idx=0;
	
	// With the FIRST query, find out if we need to refine furhter (based on artist and/or album):
	while ($row=mysql_fetch_array($result)) {
		$ok=1; // Assume we're ok (current row is need in output):
		
		$found_perf=0;		
		if ($performer_ltr<>'') { // See if the performer of this track is within criteria:
			$pname=get_performer_name($row['performer_id']);
			if (substr($pname,0,1)==$performer_ltr) {
				$found_perf=1;
			}
		}	
		if (($performer_ltr<>'') && ($found_perf==0)) {
			$ok=0;
		}

		$found_alb=0;		
		if (($ok==1) && ($album_ltr<>'')) {
			$aname=get_album_name($row['album_id']);
			$aname=str_replace('[','',$aname);
			$aname=str_replace(']','',$aname);
			$aname=ltrim($aname);
			if (substr($aname,0,1)==$album_ltr) {
				$found_alb=1;
			}	
		} 
		if (($album_ltr<>'') && ($found_alb==0)) {
			$ok=0;
		}
		
		// Made it so far ?
		if ($ok==1) {
			$result_id[$idx]=$row['id'];
			$idx++;
		}	
	}	
	// Punch out the results:
	// ...first some headers:
	echo std_table("ampjuke_content_table","ampjuke_content2");	
	$table2=1;
	echo '<tr><td colspan="10" align="center">'.xlate('Matches').': <b>'.$idx.'</b> ';
	if ($idx>$max_results) {
		echo xlate('(<i>Will only show').' '.$max_results.'</i>)';
	}
	echo '</td></tr>';
	echo '</table>';	

	echo std_table("ampjuke_content_table","ampjuke_content3");	
	$table3=1;
	echo '<th align="left">'.xlate('Title').'</th>';
	echo '<th align="left">'.xlate('Performer').'</th>';
	echo '<th align="left">'.xlate('Album').'</th>';
	echo '<th align="left">'.xlate('Year').'</th>';
	echo '<th align="right">'.xlate('Duration').'</th>';
	if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
	|| ($_SESSION['ask4favoritelist']=="1") ) {
		echo '<th> </th>';
	}

	if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
		echo '<th> </th>';
	}


	$i=0;
	while (($i<$idx) && ($i<=$max_results)){
		$qry="SELECT * FROM track WHERE id=".$result_id[$i];
		$result=execute_sql($qry,0,1,$nr);
		$row=mysql_fetch_array($result);
		
		echo '<tr>';
		echo '<td class="content">'.add_play_link("play",$row['id'],$row['name']).'</td>'; // 0.7.8: Changed. See disp.php
		
		echo '<td class="content">';
		echo add_performer_link(get_performer_name($row['performer_id']),$row['performer_id'],$_SESSION['disp_small_images']).'</td>';
		
		$a=get_album_name($row['album_id']);
		$a=str_replace(array('[',']'),'',$a);
		echo add_album_link($a,$row['album_id'],$_SESSION['disp_small_images']);		
		
		echo add_year_link($row['year'],$row['year']);		
		
		display_duration($row['duration']);		

		// 0.8.5: if...
		if ((isset($_SESSION['favoritelistname']) && ($_SESSION['favoritelistname']!="")) 
		|| ($_SESSION['ask4favoritelist']=="1") ) {
			echo '<td>'.add_add2fav_link("track",$result_id[$i],$_SESSION['hide_icon_text']).'</td>'; 
		}
		
		// 0.8.5: if...
		if (($_SESSION['can_download']=="1") && ($_SESSION['disp_download']=="1")) {
			echo '<td class="content">';
			echo add_download_link("track",'',$result_id[$i],$_SESSION['hide_icon_text']).'</td>';
		}
	
		echo '</tr>';
		$i++;
	}	
	echo '</table>';
}
?>

