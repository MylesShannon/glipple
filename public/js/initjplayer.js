
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
		mp3: "http://glipple.com:8000/radio",
	},
	ready = false;
		
	$("#jquery_jplayer_1").jPlayer({
		ready: function (event) {
			ready = true;
			$(this).jPlayer("volume", 0.25);
			$(this).jPlayer("setMedia", stream).jPlayer("play");
		},
		pause: function() {
			$(this).jPlayer("clearMedia");
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
		supplied: "mp3",
		preload: "none",
		wmode: "window",
		useStateClassSkin: true,
		autoBlur: false,
		keyEnabled: true,

	});
	
});