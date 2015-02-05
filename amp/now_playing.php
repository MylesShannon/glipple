<?php
switch($_REQUEST['action']) {
     case 'ampjuke_now_playing': 
		echo "ampjuke_now_playing|";
		if (file_exists('./tmp/np'.$_REQUEST['user_id'].'.txt')) {
			include('./tmp/np'.$_REQUEST['user_id'].'.txt');
		} else {
		 	echo '<b>AmpJuke</b>...and YOUR hits keep on coming !';
		} 	
		break;
	 // 0.7.2: introduced:	
     case 'ampjuke_now_playing_next': 
		echo "ampjuke_now_playing|";
		if (file_exists('./tmp/npnext'.$_REQUEST['user_id'].'.txt')) {
			include('./tmp/npnext'.$_REQUEST['user_id'].'.txt');
		}	
		break;		
	case 'ampjuke_now_playing_popout':
		echo "ampjuke_now_playing|";
		if (file_exists('./tmp/np'.$_REQUEST['user_id'].'pop.txt')) {
			include('./tmp/np'.$_REQUEST['user_id'].'pop.txt');		
		}
		break;
	case 'ampjuke_rocks':
		echo "ampjuke_now_playing|AmpJuke Rocks !"; break;
}
?>
