<div class="content">
    <h1>Test</h1>

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

	<div id="testA"></div>
	
	<p>
	<?php
		$path = "/var/www/html/music/";
		
		for ($i = 2; $i <= 15; $i++)
		{
			echo "<br>";
			echo " LOOP ".$i.": ";
			
			$song = scandir($path);
			
			$song = $path.$song[$i];
			
			// @ = skip error msg - ogg files output nothing
			$tag = @id3_get_tag($song);
			//echo "Title: ".$tag['title'];
			
			echo "<br>";
		}
		?>
	</p>
</div>