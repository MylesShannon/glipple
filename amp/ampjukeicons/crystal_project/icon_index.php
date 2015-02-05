<?php
switch ($wanted_icon) {
 	// left side menu:
	case 'menu_track': $icon='agt_mp3.png'; break;
	case 'menu_album': $icon='cdrom_unmount.png'; break;
	case 'menu_performer': $icon='kuzer.png'; break;
	case 'menu_year': $icon='cal.png'; break;
	case 'menu_random': $icon='kblackbox.png'; break;
	case 'menu_favorite': $icon='folder_favorites.png'; break;
	case 'menu_queue': $icon='database.png'; break;
	case 'menu_upload': $icon='folder_tar.png'; break;
	case 'menu_logout': $icon='logout.png'; break;
	case 'menu_settings': $icon='advancedsettings.png'; break;
	case 'menu_search': $icon='search.png'; break;
	case 'menu_scan_music': $icon='hdd_unmount.png'; break;
	case 'menu_useradm': $icon='kuser.png'; break;
	case 'menu_configuration': $icon='service_manager.png'; break;
	// Headline:
	case 'arrow_up': $icon='2uparrow.png'; break;
	case 'arrow_down': $icon='2downarrow.png'; break;
	case 'filter_add': $icon='viewmag+.png'; break;
	case 'filter_remove': $icon='viewmag-.png'; break;
	case 'jump2letter': $icon='goto.png'; break;
	case 'more_information': $icon='info.png'; break;
	// Actions - typically the ones you'll find everywhere...:
	case 'favorite_add': $icon='folder_green.png'; break;
	case 'favorite_remove': $icon='folder_red.png'; break;
	case 'play': $icon='player_play.png'; break;
	case 'Play': $icon='player_play.png'; break;
	case 'Queue': $icon='db_add.png'; break;
	case 'queue_add': $icon='db_add.png'; break;
	case 'queue_remove': $icon='db_remove.png'; break;
	case 'download': $icon='tar.png'; break;
	case 'lyrics': $icon='reminders.png'; break;
	case 'delete': $icon='edittrash.png'; break;
	case 'edit': $icon='edit.png'; break;
	// pagenumber:
	case 'page_prev': $icon='1leftarrow.png'; break;
	case 'page_next': $icon='1rightarrow.png'; break;
	case 'page_one': $icon='2leftarrow.png'; break;
	case 'page_last': $icon='2rightarrow.png'; break;
}
?>