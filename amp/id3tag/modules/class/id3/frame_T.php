<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

abstract class frame_T extends frame {
	protected $encoding;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->encoding = ord(substr($this->value->data,0,1));
		$this->value->data = frame::code2text($this->encoding,substr($this->value->data,1));
	}

	public function display_data() {
		return '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" value="'.$this->value->data.'" size="20" style="width:200px" />';
	}

	public function display_spec() {
		return $this->display_encoding();
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);									// Encoding
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'],false));		// Value
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>