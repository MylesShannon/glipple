<?php
	if(!defined('IN_ID'))die('You are not allowed to access to this page.');

	$tag1 = new mp3_id3v11();
	if($tag1->load_file($_GET['filename'])){

		/** WRITE TAG **/
		if(isset($_POST['id3v1_posted']) && intval($_POST['id3v1_posted'])==1){
			if(isset($_POST['id3v1_enable']) && intval($_POST['id3v1_enable'])==1)
				$tag1->set_tag($_POST['id3v1_title'],$_POST['id3v1_artist'],$_POST['id3v1_album'],$_POST['id3v1_year'],$_POST['id3v1_comment'],$_POST['id3v1_track'],$_POST['id3v1_genre']);
			else
				$tag1->remove_tag();
			$tag1->write_file();
		}

		/** READ TAG **/
		$tag1_readable = $tag1->read_tag();
		echo '<input type="hidden" name="id3v1_posted" value="1" />';

		/** DISPLAY **/
		$table = (isset($table))?$table:$null;
		$table1 = new LSTable(9,2,'100%',$table);
		$table1->setTemplate('tpl_NOTITLE');
		$table1->addRowAttribute(0,'class','table_title');
		$table1->addCellAttribute(0,0,'align','center');
		$table1->addCellAttribute(0,0,'colspan','2');
		$text2display = '<input type="checkbox" name="id3v1_enable" value="1" id="id3v1_enable"';
		if($tag1_readable == true)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><font color="#ffffff"><b><label for="id3v1_enable">ID3Tag v1.1</label></b></font>';
		$table1->setText(0,0,$text2display);
		if($tag1_readable==true){
			$temp_table = $tag1->get_tag();
			$title = $temp_table['title'];
			$artist = $temp_table['artist'];
			$album = $temp_table['album'];
			$year = $temp_table['year'];
			$comment = $temp_table['comment'];
			$track = $temp_table['track'];
			$genre = $temp_table['genre'];
		}
		else{
			$title = '';
			$artist = '';
			$album = '';
			$year = '';
			$comment = '';
			$track = NULL;
			$genre = 255;
		}
		$table1->setText(1,0,'Title');
		$table1->setText(1,1,'<input type="text" name="id3v1_title" value="'.$title.'" maxlength="30" size="30" style="width:200px" />');
		$table1->setText(2,0,'Artist');
		$table1->setText(2,1,'<input type="text" name="id3v1_artist" value="'.$artist.'" maxlength="30" size="30" style="width:200px" />');
		$table1->setText(3,0,'Album');
		$table1->setText(3,1,'<input type="text" name="id3v1_album" value="'.$album.'" maxlength="30" size="30" style="width:200px" />');
		$table1->setText(4,0,'Year');
		$table1->setText(4,1,'<input type="text" name="id3v1_year" value="'.$year.'" maxlength="4" size="4" style="width:50px" />');
		$table1->setText(5,0,'Comment');
		$table1->setText(5,1,'<input type="text" name="id3v1_comment" value="'.$comment.'" maxlength="28" size="28" style="width:200px" />');
		$table1->setText(6,0,'Track');
		$table1->setText(6,1,'<input type="text" name="id3v1_track" value="'.$track.'" size="2" style="width:50px" />');
		$table1->setText(7,0,'Genre');
		$text2display = '<select name="id3v1_genre" size="1" style="width:200px"><option value="255"></option>';
		$tag1_genres = $tag1->getGenres();
		$c = count($tag1_genres);
		for($i=0;$i<$c;$i++){
			$text2display .= '<option value="'.$i.'"';
			if($genre == $tag1_genres[$i])
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$tag1_genres[$i].'</option>';
		}
		$text2display .= '</select>';
		$table1->setText(7,1,$text2display);
		$table1->addCellAttribute(8,0,'align','center');
		$table1->addCellAttribute(8,0,'colspan','2');
		$table1->setText(8,0,'<input type="submit" value="Send" />');
		if(!($table instanceof LSTable))
			$table1->draw();
	}
	$tag1 = NULL;
?>