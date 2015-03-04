//update number of downloads
    $(".dl").click(function (event) {
		var id = event.target.id;
		update(id);
	});

	function update(id) {
		$.ajax(
			{
				url: "/views/update.php",
				type: "POST",
				data: {id : id},
				success: function(){
					
				}
			});
	}


