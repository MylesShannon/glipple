<div class='radio'>
      <div id="jquery_jplayer_1" class="jp-jplayer"></div>
      <div id="jp_container_1" class="jp-audio-stream">
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
  <!--          <div class="jp-progress">
              <div class="jp-seek-bar">
                <div class="jp-play-bar"></div>
              </div>
            </div>-->

            <div class="jp-volume-bar">
              <div class="jp-volume-bar-value"></div>
            </div>
        <!--  <div class="jp-time-holder">
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
      </div><!--
      <div id="jp_container_1" class="jp-audio-stream" role="application" aria-label="media player">
      <div class="jp-type-single">
      <div class="jp-gui jp-interface">
      <div class="jp-volume-controls">
        <button class="jp-mute" role="button" tabindex="0">mute</button>
        <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
        <div class="jp-volume-bar">
          <div class="jp-volume-bar-value" style="width: 80%;"></div>
        </div>
      </div>
      <div class="jp-controls">
        <button class="jp-play" role="button" tabindex="0">play</button>
      </div>
    </div>
    <div class="jp-details">
            <ul>
            <li><span class="jp-title"><div id="song_title" ></div></span></li>
            <script type="text/javascript" src="http://glipple.com/public/js/profile.js"></script>
            </ul></div>
    <div class="jp-no-solution" style="display: none;">
      <span>Update Required</span>
      To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
    </div>
  </div>
</div> -->
  </div>
  
  <script type="text/javascript" src="<?php echo URL; ?>public/js/initjplayer.js"></script>
  <!-- load Now Playing js -->
  <script type="text/javascript" src="<?php echo URL; ?>public/js/playing.js"></script>