<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_USLT extends frame {
	protected $tagcode = 'USLT';
	protected $tagname = 'Unsychronized lyric/text transcription';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;
	protected $language;
	private $description;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->encoding = ord(substr($this->value->data,0,1));
		$this->language = substr($this->value->data,1,3);
		$this->value->data = substr($this->value->data,4);
		$count = 0;
		$this->description = frame::code2text($this->encoding,frame::get_string($this->encoding,$this->value->data,$count));
		$this->value->data = frame::code2text($this->encoding,substr($this->value->data,$count));
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" size="20" style="width:200px" value="'.$this->description.'" /><br />';
		$text2display .= '<textarea name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" cols="45" rows="4" style="width:200px">'.$this->value->data.'</textarea>';
		return $text2display;
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
		$buffer = chr($code['spec'][0]);									// Encoding
		$buffer .= strtoupper($code['spec'][1]);								// Language
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'][0], false));		// Description
		$buffer .= chr(0).(($code['spec'][0]==1)?chr(0):'');							// EOS
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'][1], true));		// Value
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>