<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

abstract class frame_NOTSUPPORTED extends frame {
	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
	}

	public function display_data() {
		return 'Not Supported';
	}

	public function display_spec() {
		$hidden = '<input type="hidden" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][notsupported]" value="1" />';
		return $hidden.'Not Supported';
	}

	public function save($code) {
		return '';
	}
};
?>