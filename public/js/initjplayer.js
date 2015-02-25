
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
		m4a: "http://glipple.com:8000/radio"
	},
	ready = false;
		
	$("#jquery_jplayer_1").jPlayer({
		ready: function (event) {
			ready = true;
			$(this).jPlayer("setMedia", stream);
		},
		stop: function() {
			$(this).jPlayer("clearMedia");
		},
		error: function(event) {
			if(ready && event.jPlayer.error.type === $.jPlayer.error.URL_NOT_SET) {
				// Setup the media stream again and play it.
				$(this).jPlayer("setMedia", stream).jPlayer("play");
			}
		},
		swfPath: "./js/jPlayer/jPlayer.swf",
		supplied: "m4a",
		preload: "none",
		wmode: "window",
		keyEnabled: true
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