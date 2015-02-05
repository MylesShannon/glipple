<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_USER extends frame {
	protected $tagcode = 'USER';
	protected $tagname = 'Term of use';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;
	protected $language;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->encoding = ord(substr($this->value->data,0,1));
		$this->language = substr($this->value->data,1,3);
		$this->value->data = frame::code2text($this->encoding,substr($this->value->data,4));
	}

	public function display_data() {
		return '<textarea name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" cols="45" rows="4" style="width:200px">'.$this->value->data.'</textarea>';
	}

	public function display_spec() {
		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= $this->display_language(1);
		return $text2display;
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);								// Encoding
		$buffer .= strtoupper($code['spec'][1]);							// Language
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'],true));	// Value
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>