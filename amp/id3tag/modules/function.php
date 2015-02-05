<?php
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

function next_color($restart=0){
	static $color=0;
	if($restart==1){$color = null;}
	if($color==1){$couleur='row1';$color=2;}
	else{$couleur='row2';$color=1;}
	return $couleur;
}

function insertRow(LSTable $table, $n = 1){
	$table->insertRows($row = $table->numRows(), $n);
	return $row;
}

/**
 * Loads a class automatically.
 *
 * @param string $class_name
 */
function __autoload($class_name) {
	global $sys_conf;

	if(file_exists($sys_conf['path']['real'].'/modules/class/'.$class_name.'.php'))
		require_once $sys_conf['path']['real'].'/modules/class/'.$class_name.'.php';
	elseif(file_exists($sys_conf['path']['real'].'/modules/class/id3/'.$class_name.'.php'))
		require_once $sys_conf['path']['real'].'/modules/class/id3/'.$class_name.'.php';
	else
		require_once $sys_conf['path']['real'].'/modules/class/id3/frame_OTHER.php';
}

function array_in($needle, $haystack){
	for($i=0;$i<count($haystack);$i++)
		if($needle == $haystack[$i]['id'])
			return $i;
	return -1;
}

function unset_array(&$haystack,$remove){
	$temp_array = array();
	for($i=0;$i<count($haystack);$i++){
		if($i != $remove)
			$temp_array[] = $haystack[$i];
	}
	$haystack = $temp_array;
}

function getClass($name, $tag){
	$class_specific = 'frame_'.$tag->id;

	if(class_exists($class_specific))
		return new $class_specific($name,$tag);
	else
		return new frame_OTHER($name,$tag);
}

function size_human($octet,$round=2){
	$unite_spec = array('bytes','KB','MB','GB','TB');
	$count=0;
	while($octet>=1024){
		$count++;
		$octet/=1024;
	}
	if($round>=0)
		$octet = round($octet,$round);
	return($octet.' '.$unite_spec[$count]);
}
?>