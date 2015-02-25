// window.onload = loadXMLDoc('index');

$('#menu li a').click(function() {
	/*
	var page = $("#menu li a").val();
	$.ajax({
        url: "/views/"+page+".php",
        context: document.body,
        success: function(result) {
            $("#filler").html(result);
        }	
    });
	*/
	alert(page);
}

/*
function loadsearchresults(s)
{
	$.ajax({
        url: "/views/search.php?s="+s,
        context: document.body,
        success: function(result) {
            $("#filler").html(result);
        }	
    });

}
	function loadprofile(s)
{
	$.ajax({
        url: "/views/profile.php?id="+s,
        context: document.body,
        success: function(result) {
            $("#filler").html(result);
        }	
    });

}
*/