// window.onload = loadXMLsongData('home');

loadXMLsongData();
setInterval('loadXMLsongData()', 3000);

function loadXMLsongData()
{
	var xmlhttp;
	var data;
	if (window.XMLHttpRequest)
		{
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			data = xmlhttp.responseText;
			data = data.replace(/(\r\n|\n|\r)/gm,"");
			document.getElementById("song_title").innerHTML=data;
			console.log(data);
			
		}
	}
	
	xmlhttp.open("GET","views/playing.php",true);
	
	xmlhttp.send();
}