//load profile on click
	$(".profile").click(function (event) {
		var pro = event.target.id;
		profile(pro);
	});
	function profile(pro) {
		$.ajax({
			url: "/views/profile.php?id="+pro,
			context: document.body,
			success: function(result) {
				$("#filler").html(result);
			}	
		});
	}