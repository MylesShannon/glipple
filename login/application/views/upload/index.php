<div class="content">
    <h1>Upload</h1>
	
    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); 
	error_reporting(E_ALL ^ E_DEPRECATED);
	?>

    <?php 
	/*
	//include("./getid3/demos/demo.mysql.php")
	
	require_once '/var/www/html/getid3/getid3/getid3.php';
    require_once '/var/www/html/getid3/getid3/extension.cache.mysql.php';
	// 5th parameter (tablename) is optional, default is 'getid3_cache'
    $getID3 = new getID3_cached_mysql('localhost', 'getid3', 'root', 'dJc001Nfr35h', 'files');
    $getID3->encoding = 'UTF-8';
    $info1 = $getID3->analyze('1.mp3');
    //$info2 = $getID3->analyze('file2.wv');
	echo "Song: ".$info1;
	
	*/
	?>
	
	<?php
	/*
	// include getID3() library (can be in a different directory if full path is specified)
	require_once('getid3/getid3/getid3.php');

	$getID3 = new getID3;

	//$filename = "/var/www/html/music/Hydrate-Kenny_Beltrey.ogg";
	$filename = "/var/www/html/music/1.mp3";
	$ThisFileInfo = $getID3->analyze($filename);


	 Optional: copies data from all subarrays of [tags] into [comments] so
	 metadata is all available in one location for all tag formats
	 metainformation is always available under [tags] even if this is not called

	getid3_lib::CopyTagsToComments($ThisFileInfo);

	// echo $ThisFileInfo['comments_html']['title'][0]."<br>";
	// echo $ThisFileInfo['playtime_seconds']."<br>";
	// echo '<pre>'.htmlentities(print_r($ThisFileInfo, true)).'</pre>';
	*/
	?>
	
	<!--
	<form action="<?php echo URL ?>upload/upld" method='post' enctype='multipart/form-data'>
		Please choose a file: <input type='file' name='file'><br>
		<input type='submit' value='Upload File'>
	</form>
	-->

	<p>
<!-- 		<form action="<?php echo URL ?>upload/upld" id="songUpload" method="post" enctype="multipart/form-data">  add class="dropzone" for dropzone and remove content

		  Select songs: <input type="file" name="file" id="file">
		  <input type="submit">

		</form> -->

				<form action="<?php echo URL ?>upload/upld" id="songUpload" method="post" class="dropzone"> </form>
				<!-- add class="dropzone" for dropzone and remove content -->
				 



	</p>
</div>
