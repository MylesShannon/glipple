<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_IPLS extends frame {
	protected $tagcode = 'IPLS';
	protected $tagname = 'Involved people list';
	protected $deprecated = true;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->encoding = ord(substr($this->value->data,0,1));
		$this->value->data = frame::code2text($this->encoding,substr($this->value->data,1));
	}

	public function display_data() {
		return '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" size="20" style="width:200px" value="'.$this->value->data.'" /><br />';
	}

	public function display_spec() {
		return $this->display_encoding();
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);				// Encoding
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'],false));					// Value
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>