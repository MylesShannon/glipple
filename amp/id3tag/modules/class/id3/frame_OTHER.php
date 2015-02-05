<?php
/* VERIFIED */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_OTHER extends frame {
	protected $tagcode = 'OTHER';
	protected $tagname = 'Unknown Frame';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}

	public function display_data() {
		$tagname = ($this->value->id=='OTHER')?'':$this->value->id;
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" value="'.$tagname.'" size="20" maxlength="4" style="width:200px" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" value="'.$this->value->data.'" size="20" style="width:200px" />';
		return $text2display;
	}

	public function display_spec() {
		// Do nothing
		return '';
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = frame::check_newline($code['data'][1],true);					// Value
		$size = strlen($buffer);
		$code['data'][0] = strtoupper($code['data'][0]);
		if(strlen($code['data'][0]) > 4){
			$code['data'][0] = substr($code['data'][0], 0, 4);
		} else if (strlen($code['data'][0]) < 4) {
			$code['data'][0] = str_pad($code['data'][0], 4, 'X', STR_PAD_RIGHT);
		}
		return $code['data'][0].$this->get_num($size,4).$flags.$buffer;
	}
}
?>