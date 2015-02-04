<?php include '../header.php'; ?>

<!--
<script type="text/javascript" src="http://54.148.79.138/public/js/jquery.jplayer.min.js"></script>
<link type="text/css" href="http://54.148.79.138/public/js/jPlayer/skins/blue.monday/jplayer.blue.monday.css" rel="stylesheet" />
<script type="text/javascript" src="http://54.148.79.138/public/js/initjplayer.js"></script>
-->
	
<h1>Radio</h1>

<!--
<script type="text/javascript" src="../public/js/jquery-2.1.1.js"></script>
<script type="text/javascript" src="../public/js/jquery.jplayer.min.js"></script>
<link type="text/css" href="../public/js/jPlayer/skins/blue.monday/jplayer.blue.monday.css" rel="stylesheet" />
<script type="text/javascript" src="../public/js/initjplayer.js"></script>
-->

<!-- HTML5 player
<p>
   <!--old radio 
   <audio controls autoplay>
		<source src="http://glipple.com:8000/radio" type="audio/mpeg">
	</audio>
</p>
-->
	
<!-- Mike's player
<p>
	<div id="jquery_jplayer_1" class="jp-jplayer" style="width: 0px; height: 0px;">
	<img id="jp_poster_0" style="width: 0px; height: 0px; display: none;">
	<audio id="jp_audio_0" preload="none" src="http://54.148.79.138/listen" title="Artist info"></audio></div>
<div id="jp_container_1" class="jp-audio-stream" role="application" aria-label="media player">
	<div class="jp-type-single">
		<div class="jp-gui jp-interface">
			<div class="jp-controls">
				<button class="jp-play" role="button" tabindex="0">play</button>
			</div>
			<div class="jp-volume-controls">
				<button class="jp-mute" role="button" tabindex="0">mute</button>
				<button class="jp-volume-max" role="button" tabindex="0">max volume</button>
				<div class="jp-volume-bar">
					<div class="jp-volume-bar-value" style="width: 80%;"></div>
				</div>
			</div>
		</div>
		<div class="jp-details">
			<div class="jp-title" aria-label="title">ABC Jazz</div>
		</div>
		<div class="jp-no-solution" style="display: none;">
			<span>Update Required</span>
			To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
		</div>
	</div>
</div>
</p>
-->							

<p>
	mount point: /radio 
	</br>
	<a href="http://54.148.79.138/listen">http://54.148.79.138/listen</a>
	</br>
	<a href="http://54.148.79.138:8000/radio">http://54.148.79.138:8000/radio</a>
</p>

<p>
	<div id="jquery_jplayer_1" class="jp-jplayer"></div>
  <div id="jp_container_1" class="jp-audio">
    <div class="jp-type-single">
      <div class="jp-gui jp-interface">
        <ul class="jp-controls">
          <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
          <!-- <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li> -->
          <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
          <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
          <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
          <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
        </ul>
        <div class="jp-progress">
          <div class="jp-seek-bar">
            <div class="jp-play-bar"></div>
          </div>
        </div>
        <div class="jp-volume-bar">
          <div class="jp-volume-bar-value"></div>
        </div>
        <div class="jp-time-holder">
          <div class="jp-current-time"></div>
          <div class="jp-duration"></div>
          <ul class="jp-toggles">
            <li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
            <li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
          </ul>
        </div>
      </div>
      <div class="jp-details">
        <ul>
          <li><span class="jp-title"></span></li>
        </ul>
      </div>
      <div class="jp-no-solution">
        <span>Update Required</span>
        To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
      </div>
    </div>
  </div>
</p>

<h2 id="playing" onload="json()"></h2>

<?php include '../views/title.php'; ?>