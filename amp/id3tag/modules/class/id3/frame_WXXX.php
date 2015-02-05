<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_WXXX extends frame {	// Special Frame, so it is not the same pattern as W Frame
	protected $tagcode = 'WXXX';
	protected $tagname = 'User defined URL link frame';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;
	private $description;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->encoding = ord(substr($value->data,0,1));

		$this->value->data = substr($this->value->data,1);
		$count = 0;
		$this->description = frame::code2text($this->encoding,frame::get_string($this->encoding,$this->value->data,$count));
		$this->value->data = substr($this->value->data,$count);
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" size="20" style="width:200px" value="'.$this->description.'" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" size="20" style="width:200px" value="'.$this->value->data.'" />';
		return $text2display;
	}

	public function display_spec() {	// Same as frame_W
		return $this->display_encoding();
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);								// Encoding
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'][0],false));	// Description
		$buffer .= chr(0).(($code['spec'][0]==1)?chr(0):'');						// EOS
		$buffer .= frame::check_newline($code['data'][1],false);					// Value
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>