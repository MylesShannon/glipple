<?php
//*****************************************************************************
//
// Browser add-on for ampjuke
//
// You may use this code or any modified version of it on your website.
//
// NO WARRANTY
// This code is provided "as is" without warranty of any kind, either
// expressed or implied, including, but not limited to, the implied warranties
// of merchantability and fitness for a particular purpose. You expressly
// acknowledge and agree that use of this code is at your own risk.
//
//*****************************************************************************

require('logincheck.php');
if (!isset($_SESSION['admin']) || ($_SESSION['admin']<>'1')) {
	header("Location: logout.php");
}

require_once('./getid3/getid3.php');
require_once('sql.php');
require_once('scan2functions.php');



function addtrack($file,$details) {	//adds track specified by $file. Simplified version of step3 from scan2.php
	update_status(4,$details,'Adding: '.$file);
	$first_file=1;
	$previous_performer_id=0;
	$is_music=1;
	
	if (is_dir($file)) {
		update_status(2,$details,'Cannot add a directory: '.$file);
		return 0;
	}
	
	$extension=get_file_extension($file);
	if (($extension!="mp3") && ($extension!="ogg") && ($extension!="wma") && ($extension!="ape") && ($extension!="m4a")) { 
		update_status(2,$details,'Not a valid extension: '.$file);
		return 0;
	}	

	// Check we can READ from the f*cker:
	if (!is_readable($file)) {
		update_status(2,$details,'Cannot read: '.$file.' (missing permissions).');
		//report_file($filename_errors,'Error. Cannot read: <b>'.$file.'</b> (missing permissions)</td></tr>');
		return 0; 
	}
	$track=use_getid($file,$extension,$details);
	if ($track['performer']=='') { 
		update_status(2,$details,'No performer: '.$file);
		return 0;
	}	
	if ($track['title']=='') {
		update_status(2,$details,'No title: '.$file);
		return 0;
	}
	if ($track['album']=='') {  //Missing album is minor - warn and proceed 
		update_status(3,$details,'No album: '.$file);
	}	

	update_status(4,$details,'File looks valid');
	$qry='SELECT * FROM track WHERE path="'.$file.'"';
	$result=execute_sql($qry,0,1,$num_rows);
	if ($num_rows>0) {
		update_status(2,$details,'Deleted existing track: '.$file);						
		$row=mysql_fetch_array($result);
		// Delete it from TRACK table:
		$qry="DELETE FROM track WHERE id='".$row['id']."'"; // 0.8.1
		$result=execute_sql($qry,0,-1,$nr); // 0.8.1
		// Delete it from FAVORITES:
		$qry="DELETE FROM fav WHERE track_id=".$row['id'];
		$result=execute_sql($qry,0,-1,$nr);
		// Delete it from QUEUE:
		$qry="DELETE FROM queue WHERE track_id=".$row['id'];
		$result=execute_sql($qry,0,-1,$nr);							
	}
	// Made it so far. Start to add some data:
	$artist_id=find_key('performer',$track['performer'],$details);
	if ($artist_id==0) {
		add_key('performer',$track['performer'],'',0,$filename_new_stuff,$details);
		$artist_id=find_key('performer',$track['performer'],$details);
		}
	if (($track['album']!="")) {
		$album_id=find_key('album',$track['album'],$details);
		if ($album_id==0) {
			add_key('album',$track['album'],$artist_id,0,$filename_new_stuff,$details); 
			$album_id=find_key('album',$track['album'],$details);
			}	
		}else{		
		$album_id=0;
		update_status(4,$details,'No album (it is OK!)');
	}
	$now=date("U");
	// 0.6.0: FINAL check: Do we have title, artist_id and album_id already ?
	$qry="SELECT * FROM track WHERE performer_id='".$artist_id."'";
	if ($album_id<>0) {
		$qry.=" AND album_id='".$album_id."'";
	}	
	$qry.=' AND name="'.$track['title'].'"';
	$result=execute_sql($qry,0,10,$num_rows);
	if (($num_rows==0)) {
		$qry='INSERT INTO track VALUES("","'.$artist_id.'","';
		$qry.=$album_id.'","'.$track['track_number'].'","';
		$qry.=$track['title'].'",';
		$qry.='"'.$track['duration'].'","'.$now.'","0","';
		$qry.=$track['year'].'","'.$file.'")';
		$result=execute_sql($qry,0,-1,$num_rows);
		update_status(2,$details,'New track: '.$file);
		return 1;	
	}else{
	    update_status(2,$details,'Cannot add duplicate track: '.$file);
	}	
	
	
}  // end addtrack


function addFolder ($path,$details){
	//$folders=check_all_folders($path,$folders,$total_folders_not_read,$complain_permissions,$folders_not_read,$details);
	//$folder=explode('||',$folders);
	//$x=0;
	
	if (is_file($path)){
		//update_status(1,$details,'Import: '.$path);
		addtrack($path,$details);
		}
	if (is_dir($path)){	
		$handle=opendir($path);
		update_status(1,$details,'Adding folder: '.$path);
		while (false !== ($rpath=readdir($handle)) ){
			if($rpath !="." && $rpath != ".."){
				addfolder ($path.'/'.$rpath,$details);
			}
		}
		
	}	
} //end add folder


function cleardb($path,$details){
	if (is_file($path)){
		$qry='SELECT * FROM track WHERE path="'.$path.'"';
		$result=execute_sql($qry,0,1,$num_rows,'');
		$row=mysql_fetch_array($result);
		//$tresult=execute_sql($qry,0,1,$num_rows);
		if ($num_rows>0){			
			update_status(2,$details,"Deleted existing track (".$row['id']."): ".$path);						
			// Delete it from TRACK table:
			$qry="DELETE FROM track WHERE id='".$row['id']."'"; // 0.8.1
			$result=execute_sql($qry,0,-1,$nr); // 0.8.1
			// Delete it from FAVORITES:
			$qry="DELETE FROM fav WHERE track_id=".$row['id'];
			$result=execute_sql($qry,0,-1,$nr);
			// Delete it from QUEUE:
			$qry="DELETE FROM queue WHERE track_id=".$row['id'];
			$result=execute_sql($qry,0,-1,$nr);

			/* sql.php contains bugs that prevents this from working
			// Dead albums:
			$chkqry="SELECT * FROM track WHERE album_id='".$row['album_id']."'";
			echo $chkqry;
			$chkresult=execute_sql($chkqry,0,10000000,$nr);
			if ($nr==0) { // there are no tracks for this album: delete it:
				$p=get_performer_name($row['aperformer_id']);
				$delqry="DELETE FROM album WHERE aid='".$row['album_id']."'";
				$delresult=execute_sql($delqry,0,-1,$n);
				update_status(3,$details,'Deleted album: '.$p);
			}
			*/
			
			// Dead performers:
			$chkqry="SELECT * FROM track WHERE performer_id='".$row['performer_id']."'";
			$chkresult=execute_sql($chkqry,0,10000000,$nr);
			
			if ($nr==0) { // this performer does not have any tracks, - deal with it:
				$p=get_performer_name($row['performer_id']);
				$delqry="DELETE FROM performer WHERE pid='".$row['performer_id']."'";
				$delresult=execute_sql($delqry,0,-1,$n);
				update_status(2,$details,'Deleted existing performer ('.$row['performer_id'].'): '.$p);
			}
		}
	}
	if (is_dir($path)){	
		$handle=opendir($path);
		update_status(1,$details,'Deleting all tracks in folder: '.$path);
		while (false !== ($rpath=readdir($handle)) ){
			if($rpath !="." && $rpath != ".."){
				cleardb($path.'/'.$rpath,$details);
			}
		}
	}
}

   

function showContent($path){ //echos a bunch of HTML table rows from a $path
   $tag='';
   if ($handle = opendir($path))
   {
       $up = substr($path, 0, (strrpos(dirname($path."/."),"/")));
       echo "<tr><td colspan='3'><img src='style/up2.gif' width='16' height='16' alt='up'/> <a href='".$_SERVER['PHP_SELF']."?path=$up'>Up one level</a></td>";
       echo "<td colspan=2 align='center'><U>Tag data</U></td></tr>";
       
       while (false !== ($file = readdir($handle)))
       {
           if ($file != "." && $file != "..")
           {
              $fName = $file;
              $file = $path.'/'.$file;
              if(is_file($file)) {
                if(get_file_extension($file)=='mp3'){
                    $filelink="<img src='ampjukeicons/silk16x16/mnu_album.png' width='16' height='16' alt='file'> <a href='"
				   			.$_SERVER['PHP_SELF']."?path=".$path."&addtrack=".$file."'>".$fName."</a>";
				   	}else{
				   	$filelink="<img src='style/file2.gif' width='16' height='16' alt='file'>".$fName;
				   	}		
			    //Set $track string, hyperlinked if found in DB
			    $track='no track';
              	$tag=use_getid($file,'mp3',0);
				$qry='SELECT * FROM track WHERE path="'.$file.'"';
				$result=execute_sql($qry,0,1,$num_rows,'');
				$row=mysql_fetch_array($result);
    			//echo $qry.'  num_rows='.$num_rows.'<BR>';
    			if($num_rows>0) {
        			$track='<IMG SRC="\ampjukeicons\silk16x16\mnu_track.png" BORDER="0" ALT=""> '
					.'<A HREF="play_action.php?act=play&id='.$row['id'].'">'.$tag['title'].'</A>'
					.'<A HREF="'.$_SERVER['PHP_SELF'].'?path='.$path.'&clear='.$file.'"><IMG SRC="M:\ampjukeicons\silk16x16\delete.png" BORDER="0" ALT=""></A>'; 
					//$track='<A HREF="play_action.php?act=play&id='.$row['id'].'">'.$tag['title'].'</A>';
        			}else{
        			$track=$tag['title'];
    			}
				$qry='SELECT * FROM performer WHERE pname="'.$tag['performer'].'"';
				$result=execute_sql($qry,0,1,$num_rows,'');
    			if( $tag['performer']<>'' && $num_rows>0 ) {
        			$row=mysql_fetch_array($result);
        			$performer='<IMG SRC="\ampjukeicons\silk16x16\mnu_performer.png" BORDER="0" ALT="">'
        			.'<A HREF="index.php?what=performerid&start=0&count=20&special='.$row['pid'].'">'.$tag['performer'].'</A>';
        		}else{
        			$performer=$tag['performer'];
    			}

                   echo "<tr><td>".$filelink."</td>"
                            ."<td align='right'>".date ('m/d/Y ', filemtime($file))."</td>"
                            ."<td align='right'>".round(filesize($file)/1024)." kB</td>"
                            //."<td align='right'>".$tag['path']."</td>"
                            ."<td align='right'>".$tag['duration']."</td>"
                            ."<td align='right'>".$performer."</td>"	
                            //."<td align='right'>".track($tag,2)."</td>"	
                            ."<td align='left'>".$track."</td></tr>"
                            ;
                           
                            
               } elseif (is_dir($file)) {
                   print "<tr><td colspan='2'><img src='style/dir2.gif' width='16' height='16' alt='dir'/> <a href='".$_SERVER['PHP_SELF']."?path=$file'>$fName</a></td></tr>";
               }
           }
       }

       closedir($handle);
   }	

}


// These retreive arguments from the URL and act accordingly
if (isset($_POST['submitBtn'])){  
	$actpath = isset($_POST['path']) ? $_POST['path'] : '.';	
} else {
	if (isset($_GET['path'])){
		$actpath = isset($_GET['path']) ? $_GET['path'] : '.';
	}elseif (isset($_POST['path'])){
	    $actpath = isset($_POST['path']) ? $_POST['path'] : '.'; 
	}else{
		require_once('db.php');
		$actpath = $base_music_dir;
	} 
}


if (isset($_POST['jump'])){
    $up = substr($actpath, 0, (strrpos(dirname($actpath."/."),"/")));
    $handle=opendir(dirname($actpath));
    if ($_POST['jump']=="next"){ //jump down
        //echo $actpath." in ".dirname($actpath).":<BR>";
        while ( (FALSE !== ($file = readdir($handle))) && (($up.'/'.$file) !== $actpath) );
        //echo $file." found!!<BR>";
       if (FALSE !== ($file = readdir($handle))){
           $actpath=($up.'/'.$file);//advance unless we are at the bottom   
        }
    }
    
    if ($_POST['jump']=="previous"){ //jump up
        //echo $actpath." in ".dirname($actpath).":<BR>";
        $curpath=$actpath;        
        while ( (FALSE !== ($file = readdir($handle))) && (($up.'/'.$file) !== $curpath) ){
            $actpath=($up.'/'.$file); //keep updating $actpath until one before $curpath
        }        
        //echo $file." found!!<BR>";                  
    }    
closedir($handle);

}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
   <title>AmpJuke browser</title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="main">
      <div class="caption">AMPJUKE FILE BROWSER <?php echo $actindex; ?> </div>



      <div id="icon">&nbsp;</div>
      <table>
         <tr><td>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="path">
                Path: <input class="text" name="path" type="text" size="40" value="<?php echo $actpath; ?>">
                    <input class="text" type="submit" name="submitBtn" value="Change" />                 
            </form>            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <CENTER>
                 <input name="path" type="hidden" value="<?php echo $actpath; ?>">
                 <input name="addfolder" type="hidden" value="<?php echo $actpath; ?>">
                 <INPUT TYPE="image" SRC="ampjukeicons\aesthetica\database_add.png"><BR>
                 <INPUT TYPE="submit" value="Add All">
                 </CENTER>
             </form>
             <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="path">
                <CENTER>
                 <input name="path" type="hidden" value="<?php echo $actpath; ?>">
                 <input name="clear" type="hidden" value="<?php echo $actpath; ?>">
                 <INPUT TYPE="image" SRC="ampjukeicons\aesthetica\database_remove.png"><BR>
                 <INPUT TYPE="submit" value="Remove All">
                 </CENTER>
             </form>
             <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="path">
                <CENTER>
                 <input name="path" type="hidden" size="40" value="<?php echo $actpath; ?>">
                 <input name="jump" type="hidden" size="40" value="previous">
                 <INPUT TYPE="image" SRC="ampjukeicons\aesthetica\up.png"><BR>
                 <INPUT TYPE="submit" NAME="Add" value="Previous Folder">
                 </CENTER>
             </form>
             <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="path">
                <CENTER>
                 <input name="path" type="hidden" size="40" value="<?php echo $actpath; ?>">
                 <input name="jump" type="hidden" size="40" value="next">
                 <INPUT TYPE="image" SRC="ampjukeicons\aesthetica\down.png"><BR>
                 <INPUT TYPE="submit" NAME="Add" value="Next Folder">
                 </CENTER>
             </form>
        </td></tr>
    </table>
     
      <br/>

      <div class="caption">ACTUAL PATH: <?php echo $actpath ?></div>
      
	  <div id="icon2">&nbsp;</div>
      <div id="result">
            	
	  <table>	  
<?php	  
	  if (isset($_POST['addtrack']) )	 addtrack($_POST['addtrack'],2);
	  if (isset($_POST['addfolder'])) addfolder($_POST['path'],2);
	  if (isset($_POST['clear'])) cleardb($_POST['clear'],2);
	  if (isset($_GET['addtrack']) )	 addtrack($_GET['addtrack'],2);
	  if (isset($_GET['addfolder'])) addfolder($_GET['path'],2);
	  if (isset($_GET['clear'])) cleardb($_GET['clear'],2);
	
?>	
      </table>
	    </div>
	    
		<div id="result">	
		<table width="100%">
<?php
			showContent($actpath);        
?>
        </table>
     </div>
	<div id="source">File Browser v0.3</div>
    </div>
</body>   
