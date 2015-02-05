<?php
// 0.7.8: Changed slightly: make_header.php no longer included, - instead use this:
session_start();
parse_str($_SERVER["QUERY_STRING"]);

$ok=0;
if (isset($_SESSION['login'])) { $ok++; }
if (isset($_SESSION['passwd'])) { $ok++; }
if ((isset($id)) && (is_numeric($id))) { $ok++; }

// 0.7.9: Error correction: ID is set, but is the NAME of a FAVORITE-list:
require_once("sql.php");
require_once('disp.php'); // 0.8.2
if ((isset($id)) && (!is_numeric($id))) {
//   $id=preg_replace("/[^0-9^a-z^A-Z^ ^_^-^(^)^:]/", "", $id);
	$id=my_filter_var($id); // 0.8.2
	$qry='SELECT fav_name FROM fav WHERE fav_name="'.$id.'"';
	$nr=0;
	$result=execute_sql($qry,0,1,$nr);
	if ($nr==1) {
		$ok++;
	}   
}   
// 0.7.9: Error correction: what=remove_duplicates -> ID is just there to show off...
if ($what=='duplicates_queue') {
   $ok++;
}   
// 0.7.9: Error correction: what=queue & id=all -> Entire queue, - it's OK!
if (($what=='queue') && ($id=='all')) {
   $ok++;
}   

if ($ok!=3) {
	session_destroy();
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
	die('<a href="./login.php">Login again, sorry</a>');
}


// 0.3.5: Confirm delete ?
if ($_SESSION['confirm_delete']=="1") {
   if (!isset($confirmed)) { // we haven't actually answered Yes/no:
	  // 0.5.0: New redirect method:
	  echo '<script type="text/javascript" language="javascript">';
	  $therest="";
	  // 0.5.6: Also need 'fav_name' AFTER asking is it's ok to delete:
	  if (isset($fav_name)) {
		 $therest.="&fav_name=".$fav_name;
	  }   

	  if (isset($replace)) {
		 $therest.="&replace=".$replace;
	  }   
	  // 0.6.4: Also need to know the name - 'special' - used w. headers:
	  if (isset($special)) {
		 $therest.='&special='.$special;
	  }

	  echo 'window.location.replace("delete_confirm.php?id='.$id.'&what='.$what.$therest.'");';
	  echo '</script>';   
	  exit;
   } else { // check that we didn't answer No previously
	  if ($confirmed=="no") {
		 echo '<script type="text/javascript" language="javascript">'; echo "history.go(-2);";
		 echo '</script>'; 
		 exit;
	  }
   }      
}   

//
include_once("disp.php"); // new in 0.5.0...

function delete_favorite_id($victim) {
   $delqry="DELETE FROM fav WHERE track_id=".$victim;
   $delresult=execute_sql($delqry,0,-1,$nr);
}   

// 0.7.4: Deletes all 'victim' entries in the queue-table:
function delete_queue_id($victim) {
   $delqry="DELETE FROM queue WHERE track_id=".$victim;
   $delresult=execute_sql($delqry,0,-1,$nr);
}   
   

if (!isset($id) || ($id=="")) {
   echo 'You must specify something to delete.';
} else {
   $qry="";
   if ($what=="track") {
	  delete_favorite_id($id);
	  delete_queue_id($id); // 0.7.4

	  $qry='DELETE FROM track WHERE id='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
   }   

   if ($what=="albumid") {
	  $qry='DELETE FROM album WHERE aid='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
	  // 0.4.2: Delete from favorites as well:
	  $qry="SELECT id FROM track WHERE album_id=".$id;
	  $result=execute_sql($qry,0,100000000,$nr);
	  while ($row=mysql_fetch_array($result)) {
		 delete_favorite_id($row['id']);
		 delete_queue_id($row['id']); // 0.7.4
	  }
	  //   
	  $qry='DELETE FROM track WHERE album_id='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
   }

   if ($what=="performerid") {
	  $qry='DELETE FROM performer WHERE pid='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
	  // 0.4.2: Delete from favorites as well:
	  $qry="SELECT id FROM track WHERE performer_id=".$id;
	  $result=execute_sql($qry,0,100000000,$nr);
	  while ($row=mysql_fetch_array($result)) {
		 delete_favorite_id($row['id']);
		 delete_queue_id($row['id']); // 0.7.4
	  }
	  $qry='DELETE FROM track WHERE performer_id='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
	  $qry='DELETE FROM album WHERE aperformer_id='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
   }

   if ($what=="yearid") {
	  // 0.4.2: Delete from favorites as well:
	  $qry="SELECT id FROM track WHERE year=".$id;
	  $result=execute_sql($qry,0,10000000,$nr);
	  while ($row=mysql_fetch_array($result)) {
		 delete_favorite_id($row['id']);
		 delete_queue_id($row['id']); // 0.7.4         
	  }

	  $qry='DELETE FROM track WHERE year='.$id;
	  $result=execute_sql($qry,0,-1,$nr);
   }   

   if ($what=="favoriteid") {
   // 0.5.2: Remove stuff from a shared list ?
	  $uid=get_user_id($_SESSION['login']); // this is the default, - check for shared:
	  $qry="SELECT * FROM fav_shares WHERE share_id='".$uid."'";
	  $qry.=" AND fav_name='".$_SESSION['favoritelistname']."'";
	  $result=execute_sql($qry,0,10,$x);
	  if ($x<>0) { // yes: we're removing from a shared list -> change uid:
		 $row=mysql_fetch_array($result);
		 $uid=$row['owner_id'];
	  }      
	  $qry="DELETE FROM fav WHERE id='".$id."' AND fav_name='".$fav_name."'";
	  $qry.=" AND user_id='".$uid."'";
	  $result=execute_sql($qry,0,-1,$nr);
   }

   if ($what=="favorite") {
	  $qry="DELETE FROM fav WHERE fav_name='".$id."'";
	  $qry.=" AND user_id='".get_user_id($_SESSION['login'])."'";      
	  $result=execute_sql($qry,0,-1,$nr);
	  // 0.6.7: Check that favoritelist is set:
	  if (isset($_SESSION['favoritelist'])) {
		 if ($id==$_SESSION['favoritelist']) {
			$_SESSION['favoritelistname']="";
		 }   
	  }
   }   

   if ($what=="queue") {
	  $qry="DELETE FROM queue WHERE user_name='".$_SESSION['login']."'";
	  if ($id!="all") { // 0.6.4: Whoopps....
		 $qry.=" AND qid='".$id."'";
	  }
	  $result=execute_sql($qry,0,-1,$nr);
   }

	if ($what=="cover") {
//        $cmd='rm -f ./covers/"'.$id.'.jpg"';
//      exec($cmd);
// 0.7.9: Rather use "unlink" -> allows execution on Window$:
	  @unlink('./covers/'.$id.'.jpg');

	  if (isset($replace)) {
//            $cmd='cp ./covers/_blank.jpg ./covers/"'.$id.'.jpg"';
//            exec($cmd);
// 0.7.9: Rather use "copy" -> allows execution on Window$:
		 copy('./covers/_blank.jpg','./covers/'.$id.'.jpg');
		}
	}

	// 0.3.2: Remove Duplicates
	// Queue:
	if ($what=="duplicates_queue") {
		// Get user's tracks in the queue table:
		$qry="SELECT * FROM queue WHERE user_name='".$_SESSION['login']."'";
		$result=execute_sql($qry,0,-1,$nr);
		// Delete user's tracks from the queue table:
		$qry="DELETE FROM queue WHERE user_name='".$_SESSION['login']."'";
		$r=execute_sql($qry,0,-1,$nr);

		// Trim it:
		$n=0;
		while ($row=mysql_fetch_array($result)) {
			$input[$n]=$row['track_id'];
			$n++;
		}

		$new=array_unique($input);
		// Put the trimmed array back into the queue table:
		$n=0;
		while ($n<=count($new)-1) {
			$qry="INSERT INTO queue (user_name, track_id) ";
			$qry.="VALUES ('".$_SESSION['login']."', ".$new[$n].")";
			$res=execute_sql($qry,0,-1,$nr);
			$n++;
		}
	 }

	 // Favorite:
	 if ($what=="duplicates_favorite") {
		$qry="SELECT * FROM fav WHERE user_id='".get_user_id($_SESSION['login'])."'";
		$qry.=" AND fav_name='".$id."' AND track_id>'0'";
		$result=execute_sql($qry,0,-1,$nr);

//      $qry="DELETE FROM favorites WHERE fname='".$id."'";
//      $qry.=" AND fuser='".$_SESSION['login']."' AND fid>0";
// 0.5.0: changed to:
	  $qry="DELETE FROM fav WHERE fav_name='".$id."'";
	  $qry.=" AND user_id='".get_user_id($_SESSION['login'])."' AND track_id>0";
	  $r=execute_sql($qry,0,-1,$nr);
		// Trim it:
		$n=0;
		while ($row=mysql_fetch_array($result)) {
			$input[$n]=$row['track_id'];
			$n++;
		}

		$new=array_unique($input);

		// Put the trimmed array back into the queue table:
		$n=0;
		while ($n<=count($new)-1) {
//            $qry="INSERT INTO favorites (fuser, fname, track_id) ";
//            $qry.="VALUES ('".$_SESSION['login']."', '".$id."', ".$new[$n].")";
//          $res=execute_sql($qry,0,-1,$nr);
// 0.5.0: Changed to ******* A COPY FROM ADD2FAV.PHP ******* :
		 $x=$new[$n];
		 $pid=get_performer_id($x);
		 $aid=get_album_id($x);
		 $r=get_track_extras($x);
		 $uid=get_user_id($_SESSION['login']);
		 $qry="INSERT INTO fav (track_id, performer_id, album_id, name, duration,";
		 $qry.=" last_played, times_played, year, user_id, fav_name) VALUES";
		 $qry.=" ('".$x."', '".$pid."', '".$aid."', ";
		 $qry.='"'.$r['name'].'"';
		 $qry.=", '".$r['duration']."', ";
		 $qry.="'".$r['last_played']."', '".$r['times_played']."', ";
		 $qry.="'".$r['year']."', '".$uid."', '".$id."')";
		 $r=execute_sql($qry,0,-1,$nr);
		$n++;
		}
	 }
}



if (isset($jsb)) {
   if ($_SESSION['confirm_delete']=="1") { // 0.7.8: Step 3 pages back if "confirm_deletion" is set:
	  echo '<script type="text/javascript" language="javascript">'; echo "history.go(-3);";
	  echo '</script>';
   }   
   echo '<script type="text/javascript" language="javascript">'; echo "history.go(-2);";
   echo '</script>'; 
}   

echo '<script type="text/javascript" language="javascript">'; echo "history.go(-1);";
echo '</script>'; 
?>
</body>
</html>

