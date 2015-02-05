<?php

if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	redir("login.php");
	exit;
}	

require_once("sql.php");
require_once("disp.php");
require_once("set_td_colors.php");

echo std_table("ampjuke_headline_table","");
echo '<tr><td align="center">';
echo '<img src="./ampjukeicons/ampjuke_welcome.gif" border="0"></td>';
echo '<tr><td align="center">';
echo xlate("Welcome").': <b>'.$_SESSION['login'].'</b> // ';

// 0.3.7: There cannot exist a "last login" for "anonymous" users, simply skip it:
if ($_SESSION['login']!="anonymous") {
	echo xlate("Last login").': <b>'.mydate($_SESSION['msg']).'</b> // ';
}

echo xlate("IP-address").': <b>'.$_SERVER['REMOTE_ADDR'].'</b>';
echo '</td></tr>';

$max_albums=get_num_rows('album','aid');
$max_performers=get_num_rows('performer','pid');

// 0.3.3: Get rid of that annoying "one too many number of performers":
$max_performers=$max_performers-1;

$max_tracks=get_num_rows('track','id');
$max_users=get_num_rows('user','id');

// facts:
echo '<tr><td align="center">';
echo xlate("Number of users").':<b>'.$max_users.'</b> // ';
echo xlate("Number of albums").':<b>'.$max_albums.'</b> // ';
echo xlate("Number of performers").':<b>'.$max_performers.'</b> // ';
echo xlate("Number of tracks").':<b>'.$max_tracks.'</b><br>';
echo '</td></tr>';

// 0.8.6: Display a message (+optional link) ?
if ((isset($jukebox_mode_enabled)) && ($jukebox_mode_enabled=='1')) {
	$msg='';
	if ((isset($jukebox_mode_welcome_msg)) && (strlen($jukebox_mode_welcome_msg)>3)) {
		$msg=$jukebox_mode_welcome_msg;
	}
	// Should be display it as a LINK ?
	if ((isset($jukebox_mode_welcome_link)) && (strlen($jukebox_mode_welcome_link)>5)) {
		$msg='<a href="'.$jukebox_mode_welcome_link.'" target="_blank">'.$msg.'</a>';
	} 
	if (strlen($msg)>2) {
		echo '<tr><td align="center">'.$msg.'</td></tr>';
	}
}

echo '</table>';

//
//
// Functions used w. the 3 boxes on the 'welcome' page	
//
//
function welcome_headline($hl) {
	$ret='<th align="left" class="content">'; 	
	$ret.=add_welcome_headline_hyperlink($hl); // 0.7.4: Added in order to make the hyperlink.
	$ret.='<img src="./ampjukeicons/mnu_arr.gif" border="0">';
	$ret.=xlate($hl);
	$ret.=':</a></th><tr><td>'; // 0.7.4: Added: </a>
	return $ret;
}	

// 0.7.4: Turns a headline on any of the three "boxes" on the 'welcome' page into a link:
function add_welcome_headline_hyperlink($hl) { 
	$ret='<a href="index.php?what=';
	
	switch ($hl) {
		case "Recently played tracks": $ret.='track&pagesel=track'; break;
		case "Recently added albums": $ret.='album'; break;
		case "Recently added performers": $ret.='performer&pagesel=performer'; break;
		case "Random tracks": $ret.='track&pagesel=track'; break;
		case "Random albums": $ret.='album'; break;
		case "Random performers": $ret.='performer&pagesel=performer'; break;
	}
		
	welcome_get_params($hl,$order_by,$sorttbl,$dir,$start,$req_filename,$c,$dummy1,$dummy2,$dummy3);
	$ret.='&order_by='.$order_by.'&sorttbl='.$sorttbl.'&dir='.$dir.'&start='.$start;
	$ret.='&from_welcome_page=1">';
	return $ret;
}	

function welcome_get_params($hl,&$order_by,&$sorttbl,&$dir,&$start,
&$req_filename,&$c,&$welcome_table,&$table2,&$table3) {
	if ($hl=="Recently played tracks") {
		$order_by='track.last_played';
		$sorttbl="track";
		$dir="DESC";
		$start=0;
		$req_filename="disp_track.php";
	}	
	if ($hl=="Random tracks") {
		$order_by='rand()';
		$sorttbl="track";
		$dir="ASC";
		$start=0;
		$req_filename="disp_track.php";
	}		
	if ($hl=="Recently added tracks") {
		$order_by='track.id';
		$sorttbl="track";
		$dir="DESC";
		$start=0;
		$req_filename="disp_track.php";	
	}		
	if ($hl=="Recently added albums") {
		$order_by="aid";
		$dir="DESC";
		$sorttbl="album";
		$start=0;
		$req_filename="disp_album.php";
		// 0.8.2: Do we want to display IMAGES of albums instead ?
		if ($_SESSION['browse_albums_by_covers']=='1') {
			$req_filename='disp_album_by_cover.php';
		}
	}
	if ($hl=="Random albums") {
		$order_by="rand()";
		$dir="ASC";
		$sorttbl="album";
		$start=0;
		$req_filename="disp_album.php";
		// 0.8.2: Do we want to display IMAGES of albums instead ?
		if ($_SESSION['browse_albums_by_covers']=='1') {
			$req_filename='disp_album_by_cover.php';
		}
	}	
	if ($hl=="Recently added performers") {
		$order_by='performer.pid';
		$sorttbl="performer";
		$dir="DESC";
		$start=0;
		$req_filename="disp_performer.php";	
		// 0.8.2: Do we want to display IMAGES of performers/artists instead ?
		if ($_SESSION['browse_performer_by_picture']=='1') {
			$req_filename='disp_performer_by_picture.php';
		}
	}
	if ($hl=="Random performers") {
		$order_by='rand()';
		$sorttbl="performer";
		$dir="ASC";
		$start=0;
		$req_filename="disp_performer.php";	
		// 0.8.2: Do we want to display IMAGES of performers/artists instead ?
		if ($_SESSION['browse_performer_by_picture']=='1') {
			$req_filename='disp_performer_by_picture.php';
		}
	}
    // 0.8.8: RELATED tracks:
    if ($hl=='Related tracks') {
        $req_filename='welcome_lib.php';
        $_SESSION['welcome_func']='related_tracks';
    }
    // 0.8.8: RELATED performers:
    if ($hl=='Related performers') {
        $req_filename='welcome_lib.php';
        $_SESSION['welcome_func']='related_performers';
    }
	$c++;
	if ($c==1) { $welcome_table='ampjuke_content'; }
	if ($c==2) { $welcome_table='ampjuke_content2'; $table2=1; }
	if ($c==3) { $welcome_table='ampjuke_content3'; $table3=1; }
}


// 0.6.3: Set up stuff:
$table2=0;
$table3=0;
$c=0;
// also remember 'anonymous' !!!!!!!


// welcome_content_1 set ?
if ((isset($_SESSION['welcome_content_1'])) && ($_SESSION['welcome_content_1']!="")) { // 0.8.4
 	print "\n\n <!-- WELCOME 1 --> \n\n";
	echo std_table("ampjuke_content_table","ampjuke_welcome_1");		
	echo welcome_headline($_SESSION['welcome_content_1']);
	welcome_get_params($_SESSION['welcome_content_1'],
	$order_by,$sorttbl,$dir,$start,$req_filename,$c,$welcome_table,$table2,$table3);	
	$count=$_SESSION['welcome_num_items'];
	include($req_filename);		
	echo '</td></tr></table><br>';
}

// welcome_content_2 set ?
if ((isset($_SESSION['welcome_content_2'])) && ($_SESSION['welcome_content_2']!="")) { // 0.8.4
 	print "\n\n <!-- WELCOME 2 --> \n\n"; 
	echo std_table("ampjuke_content_table","ampjuke_welcome_2");		
	echo welcome_headline($_SESSION['welcome_content_2']);
	welcome_get_params($_SESSION['welcome_content_2'],
	$order_by,$sorttbl,$dir,$start,$req_filename,$c,$welcome_table,$table2,$table3);	
	$count=$_SESSION['welcome_num_items'];
	include($req_filename);		
	echo '</td></tr></table><br>';	
}


// welcome_content_3 set ?
if ((isset($_SESSION['welcome_content_3'])) && ($_SESSION['welcome_content_3']!="")) { // 0.8.4
 	print "\n\n <!-- WELCOME 3 --> \n\n"; 
	echo std_table("ampjuke_content_table","ampjuke_welcome_3");		
	echo welcome_headline($_SESSION['welcome_content_3']);
	welcome_get_params($_SESSION['welcome_content_3'],
	$order_by,$sorttbl,$dir,$start,$req_filename,$c,$welcome_table,$table2,$table3);	
	$count=$_SESSION['welcome_num_items'];
    include($req_filename);
	echo '</td></tr></table><br>';	
}

echo '</td></tr></table>'; // 0.8.5
?>

