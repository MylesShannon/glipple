<?php
session_start();
parse_str($_SERVER["QUERY_STRING"]);

// Remember where we came from:
if (!isset($_SESSION['screensaver_referer'])) {
	$_SESSION['screensaver_referer']=$_SERVER["HTTP_REFERER"];
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="en-gb"';
echo '" xml:lang="en-gb">';
echo '<head><title>Screensaver [AmpJuke...and YOUR hits keep on coming!]</title>';
echo '<link rel="shortcut icon" href="favicon.ico" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';
echo '<link rel="stylesheet" type="text/css" href="./screensaver.css">'; 

include("db.php"); 
echo '<META HTTP-EQUIV="refresh" CONTENT="'.$screensaver_reload_time.';URL='.$base_http_prog_dir.'/screensaver.php">';

echo '<script type="text/javascript" src="jquery-1.8.1.min.js"></script>';

echo '</head><body bgcolor="#000">';
?>
<script type="text/javascript">
// Setup event in case the user resizes the browser window
var currheight = document.documentElement.clientHeight;
window.onresize = function(){
    if(currheight != document.documentElement.clientHeight) {
        location.replace('./screensaver.php?rez=1');
    }    
}
</script>
<?php
require_once("disp.php");

	
// *****************************************************************************************
// 						WIDTH/HEIGHT STUFF
// *****************************************************************************************


// Get/set width/height:
if (isset($rez)) { // we have new dimensions for the browser window, forget the old stuff:
	unset($_SESSION['width']);
	unset($_SESSION['height']);
}

// Did we get width/height from URL ?
if (isset($width)) {
	if (($width>5000) || ($width<10)) { $width=10; }
	if (($height>5000) || ($height<10)) { $height=10; }
	$_SESSION['width']=only_digits($width);
	$_SESSION['height']=only_digits($height);
	// redirect as well:
	redir('./screensaver.php?from_url=1');
	die();
}

// Do we have the width/height in the session ? If yes: transfer them to the script:
if (isset($_SESSION['width'])) {
	$width=$_SESSION['width'];
	$height=$_SESSION['height'];
}

// Do we have jack sh*t ? If yes: Transfer current width & height of browser window/tab to PHP:
if (!isset($width)) {
?>
<script type="text/javascript">
height=$(window).height();
width=$(window).width();
if (width > 0 && height >0) {
    window.location.href = "./screensaver.php?width=" + width + "&height=" + height;
} else 
    exit();
</script>
<?php
}


if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
	exit;
}	

include_once("sql.php");


// *****************************************************************************************
// 				Calculate number of columns and number of rows + INITIALIZE
// *****************************************************************************************
$width=only_digits($width);
$height=only_digits($height);
if (($width>5000) || ($width<10)) { $width=10; }
if (($height>5000) || ($height<10)) { $height=10; }
$mydim=$screensaver_preferred_size; // Preferred size in pixels of each cover
$cover_dim='width="'.$mydim.'px" height="'.$mydim.'px" border="0"'; // Append this to covers displayed
$col_count=round($width / $mydim,0) - 1;
$row_count=round($height / $mydim,0) - 1;
// Init.:
$start=0;
$ampjuke_animated_objects=1;
$tmpstart=$start;

// Determine WHAT should be displayed (album or performer images or pick by random):
$show=rand(1,10); // Pick by random (default)
if ($screensaver_images=='Albums') {
	$show=1; // force to display album images only
}
if ($screensaver_images=='Performers') {
	$show=10; // force to display artist/performer images only
}

// *****************************************************************************************
// 				DISPLAY IT
// *****************************************************************************************
if ($show<=5) {
	// ALBUM qry:
	$qry="SELECT aid, aname, IFNULL(pid, '0') AS pid, IFNULL(pname, 'Various Artists') ";
	$qry.="AS pname FROM album LEFT OUTER JOIN performer ON album.aperformer_id=performer.pid";
	$qry.=" ORDER BY rand() ASC ";

	$result=execute_sql($qry,0,$col_count * $row_count,$num_rows);

	echo '<table border="0" cellspacing="0" cellpadding="0" magin="0" rules="none" align="center">';

	$c_count=$col_count;
	while ($row=mysql_fetch_array($result)) {
		if ($c_count==$col_count) {
			echo '<tr>';
		}
		
		// First, the album image:
		echo '<td>';
		echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'">'; // 0.8.5
		echo '<a href="index.php?what=albumid&start=0&count='.$_SESSION['count'];
		echo '&special='.$row['aid'].'&order_by=track.track_no"';
		echo ' title="'.$row['pname'].' - '.$row['aname'].'">';
		if (file_exists('./covers/'.$row['aid'].'.jpg')) { // Show the actual image:
			echo '<img src="./covers/'.$row['aid'].'.jpg" '.$cover_dim.' title="'.$row['pname'].' - '.$row['aname'].'">';
		} else { // Show the default image:
			echo '<img src="./covers/_blank.jpg" '.$cover_dim.' title="'.$row['pname'].' - ">';
		}
		echo '</a></p></td>';
		$ampjuke_animated_objects++;
		// Find out if it's time to switch to a new row:
		$c_count--;
		if ($c_count==0) {
			print "</tr> \n\n";
			//echo '<br>';
			$c_count=$col_count;
		}
	}
} else { // we want to display PERFORMER images:
	$qry="SELECT pid, pname FROM performer WHERE pid>1";
	$qry.=" ORDER BY rand() ASC";
	
	echo '<table border="0" cellspacing="0" cellpadding="0" magin="0" rules="none" align="center">';
	
	$result=execute_sql($qry,0,$col_count * $row_count,$num_rows);
	$c_count=$col_count;
	while ($row=mysql_fetch_array($result)) {
		if ($c_count==$col_count) {
			echo '<tr>';
		}	

		// First, the image:
		echo '<td>';
		if (file_exists('./lastfm/'.$row['pid'].'.jpg')) {
			$img='<img src="./lastfm/'.$row['pid'].'.jpg" border="0" '.$cover_dim.' title="'.$row['pname'].'">';
		} else {
			$img='<img src="./covers/_blank.jpg" border="0" '.$cover_dim.' title="'.$row['pname'].'">';
		}	
		echo '<p class="ampjuke_animation_'.$ampjuke_animated_objects.'">'.add_performer_link($img,$row['pid'],'0');
		echo '</p>';
		$ampjuke_animated_objects++; // 0.8.5

		// Calculate row-stuff:
		echo '</td>';
		$c_count--;
		if ($c_count==0) {
			echo '</tr>';
			$c_count=$col_count;
		}	
	}
}
echo '<tr><td colspan="'.$col_count.'" align="center">';
echo '<a href="'.$_SESSION['screensaver_referer'].'">AmpJuke...and YOUR hits keep on coming!';
echo '</td></tr>';
echo '<table>';
?>

<script type="text/javascript">
// Run the actual eye-candy stuff (ie. the screensaver) using jQuery:
$(document).ready(function(){
   	var i=0;
    
	// Show all images:
    for (i=0;i<=<?php echo $col_count * $row_count ?>;i++)	{	
   		$(".ampjuke_animation_"+i).animate({opacity: 0},1).delay(i*25).animate({opacity: 1.0},i*25);	    
    }
	
	// Loop, pick a "victim" and dim it, then bring it back.
	brightness=0.1;
   	for (i=0;i<=<?php echo $screensaver_iterations ?>;i++)	{	
		n=Math.floor((Math.random()*<?php echo $col_count * $row_count?>)+1);
		brightness=brightness+0.1;
		if (brightness>1) {
			brightness=0.1;
		}
		$(".ampjuke_animation_"+n).fadeTo(<?php echo $screensaver_ms_fade_factor ?>,brightness).delay(<?php echo $screensaver_ms_delay_factor ?>)		
   	}
});


</script>

</body>
</html>

