//load profile on click

    $(".dl").click(function (event) {
		var id = event.target.id;
		$.ajax(
			{
				url: "update.php",
				type: "POST",
				data: {id : id},
				success: function(result){
					
				}
			});
	});

