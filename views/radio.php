<div class='radio'>
		<div id="jquery_jplayer_1" class="jp-jplayer"></div>
		<div id="jp_container_1" class="jp-audio-stream">
			<div class="jp-type-single">
				<div class="jp-gui jp-interface">
					<ul class="jp-controls">
					<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
					<!-- <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
					<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li> -->
					<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
					<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
					<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
					</ul>
					<!--<div class="jp-progress">
						<div class="jp-seek-bar">
							<div class="jp-play-bar"></div>
						</div>
					</div>-->
					<div class="jp-volume-bar">
						<div class="jp-volume-bar-value"></div>
					</div>
					<!--<div class="jp-time-holder">
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
					</div>-->
				</div>
				<div class="jp-details">
					
					<span class="jp-title"><div id="song_title" ></div></span>
					<script type="text/javascript" src="http://glipple.com/public/js/profile.js"></script>
					
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>
</div>

<?php
require_once('./getid3/getid3/getid3.php');
$Path="/media/music/18/148.m4a";
$getID3 = new getID3;
$OldThisFileInfo = $getID3->analyze($Path);

//$owner = $userID;

//mysql_select_db($musicdb) or die(mysql_error());
//$result = mysql_query("SELECT * FROM users WHERE user_id LIKE ".$userID) or die(mysql_error());  
//$usernamequery = mysql_fetch_array($result);

//$artist=$usernamequery['user_name'];

/*
$artist = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['artist'][0]);
$album = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['album'][0]);
//$year = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["year"]);
$year = $tag['tags']['id3v2']['year'][0];
$genre = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['tags']['id3v2']['genre'][0]);
//$comment = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag["comment"]);
//$comment = $tag['tags']['id3v2']['comments'][0];
$track = preg_replace("/[^0-9\-\/ ]/", "", $tag['tags']['id3v2']['track_number'][0]);
$title = preg_replace("/[^0-9a-zA-Z!?\- ]/", "", $tag['comments']['title']);
*/

//getid3_lib::CopyTagsToComments($trackInfo);

if(isset($OldThisFileInfo['comments']['picture'][0])){
     $Image='data:'.$OldThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($OldThisFileInfo['comments']['picture'][0]['data']);
}
?>
  
<img id="FileImage" width="150" src="<?php echo @$Image;?>" height="150">