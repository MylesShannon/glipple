$(document).ready(function() {	
	//dynamic tab loading
	//load index by default
	content('index');
	//load tab on what is clicked
    $(".link").click(function (event) {
        var page = event.target.id;
	if (page == 'logo')
	{
		page = 'index';
	}
		content(page);
    });
	function content(page) {
		$.ajax({
			url: "/views/"+page+".php",
			context: document.body,
			success: function(result) {
				$("#filler").html(result);
			}
		});
	}
	
});

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