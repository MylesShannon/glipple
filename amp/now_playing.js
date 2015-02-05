function createRequestObject() {
     var ro;
     var browser = navigator.appName;
     if(browser == "Microsoft Internet Explorer") {
	      ro = new ActiveXObject("MSXML2.XMLHTTP.3.0");
          // ro = new ActiveXObject("Microsoft.XMLHTTP")
     } else {
          ro = new XMLHttpRequest();
     }
     return ro;
}

 var http = createRequestObject();

function sndReq(action,user_id) {
	 num = Math.floor(Math.random()*31999); // because of Microsoft Internet Explorer !!!
     http.open('get', 'now_playing.php?action='+action+'&user_id='+user_id+'&rand='+num, true);
     http.onreadystatechange = handleResponse;
     http.send(null);
}

function handleResponse() {
     if(http.readyState == 4){
		if (http.status == 200) {
          var response = http.responseText;
          var update = new Array();

          if(response.indexOf('|') != -1) {
               update = response.split('|');
               document.getElementById(update[0]).innerHTML = update[1];
          }
		}
     }
}

