
// Mike's player
/*
$(document).ready(function(){

	var stream = {
		title: "ABC Jazz",
		mp3: "http://listen.radionomy.com/abc-jazz"
	},
	ready = false;

	$("#jquery_jplayer_1").jPlayer({
		ready: function (event) {
			ready = true;
			$(this).jPlayer("setMedia", stream);
		},
		pause: function() {
			$(this).jPlayer("clearMedia");
		},
		error: function(event) {
			if(ready && event.jPlayer.error.type === $.jPlayer.error.URL_NOT_SET) {
				// Setup the media stream again and play it.
				$(this).jPlayer("setMedia", stream).jPlayer("play");
			}
		},
		swfPath: "../dist/jplayer",
		supplied: "mp3",
		preload: "none",
		wmode: "window",
		useStateClassSkin: true,
		autoBlur: false,
		keyEnabled: true
	});


	//	$("#jplayer_inspector").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
});
*/

// Myles' player
$(document).ready(function(){
	var stream = {
		mp3: "http://glipple.com:8000/radio"
	},
	ready = false;
		
	$("#jquery_jplayer_1").jPlayer({
		ready: function (event) {
			ready = true;
			$(this).jPlayer("setMedia", stream).jPlayer("play");
			$(this).jPlayer("volume", 0.25);
		},
		play: function() {
			$(this).jPlayer("setMedia", stream).jPlayer("play");
		},
		pause: function() {
			$(this).jPlayer("setMedia", "/").jPlayer("play");
		},
		stop: function() {
			$(this).jPlayer("setMedia", "/").jPlayer("play");
		},
		/*
		error: function(event) {
			if(ready && event.jPlayer.error.type === $.jPlayer.error.URL_NOT_SET) {
				// Setup the media stream again and play it.
				$(this).jPlayer("setMedia", stream).jPlayer("play");
			}
		},
		*/
		swfPath: "./js/jPlayer/jPlayer.swf",
		supplied: "mp3",
		preload: "none",
		wmode: "window",
		useStateClassSkin: true,
		autoBlur: false,
		keyEnabled: true,

	});
	
});

// tut's player 
/*
$(document).ready(function(){
$("#jquery_jplayer_1").jPlayer({
ready: function () {
$(this).jPlayer("setMedia", {
title: "Bubble",
m4a: "http://www.jplayer.org/audio/m4a/Miaow-07-Bubble.m4a",
oga: "http://www.jplayer.org/audio/ogg/Miaow-07-Bubble.ogg"
});
},
cssSelectorAncestor: "#jp_container_1",
swfPath: "/js",
supplied: "m4a, oga",
useStateClassSkin: true,
autoBlur: false,
smoothPlayBar: true,
keyEnabled: true,
remainingDuration: true,
toggleDuration: true
});
});
*/

/*
status.formatType = 'mp3'
Browser canPlay('audio/mpeg')

status.src = 'http://glipple.com/radio'

status.media = {
 title: Glipple Radio
 mp3: http://glipple.com/radio
};

status.videoWidth = '0' | status.videoHeight = '0'
status.width = '0px' | status.height = '0px'
htmlElement.audio.canPlayType = function

$('#jquery_jplayer_1').jPlayer({
 swfPath: './js/jPlayer/jPlayer.swf',
 solution: 'html, flash',
 supplied: 'mp3',
 preload: 'none',
 volume: 0.8,
 muted: false,
 backgroundColor: '#000000',
 cssSelectorAncestor: '#jp_container_1',
 cssSelector: {
  videoPlay: '.jp-video-play',
  play: '.jp-play',
  pause: '.jp-pause',
  stop: '.jp-stop',
  seekBar: '.jp-seek-bar',
  playBar: '.jp-play-bar',
  mute: '.jp-mute',
  unmute: '.jp-unmute',
  volumeBar: '.jp-volume-bar',
  volumeBarValue: '.jp-volume-bar-value',
  volumeMax: '.jp-volume-max',
  playbackRateBar: '.jp-playback-rate-bar',
  playbackRateBarValue: '.jp-playback-rate-bar-value',
  currentTime: '.jp-current-time',
  duration: '.jp-duration',
  title: '.jp-title',
  fullScreen: '.jp-full-screen',
  restoreScreen: '.jp-restore-screen',
  repeat: '.jp-repeat',
  repeatOff: '.jp-repeat-off',
  gui: '.jp-gui',
  noSolution: '.jp-no-solution'
 },
 errorAlerts: false,
 warningAlerts: false
});
*/