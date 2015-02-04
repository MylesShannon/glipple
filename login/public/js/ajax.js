window.onload = loadXMLDoc('home');

function loadXMLDoc(content)
{
	var xmlhttp;
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
			document.getElementById("testA").innerHTML=xmlhttp.responseText;
		}
	}
	
	console.log(content);

	if(content == "home") {
		xmlhttp.open("GET","http://glipple.com/views/index.html",true);
	} else if(content == "test") {
		xmlhttp.open("GET","http://glipple.com/views/test.html",true);
	} else if(content == "radio") {
		xmlhttp.open("GET","http://glipple.com/views/radio.html",true);
	}else if(content == "help") {
		xmlhttp.open("GET","http://glipple.com/views/help.html",true);
	}else if(content == "blog") {
		xmlhttp.open("GET","http://glipple.com/views/blog.html",true);
	} else {
		xmlhttp.open("GET","http://glipple.com/views/error.html",true);
	}
	
	xmlhttp.send();
}