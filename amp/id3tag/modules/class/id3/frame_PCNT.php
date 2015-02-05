<?php
// KNOWN BUG :
// ONLY 2^31-1 is allowed for the counter
// We could take the number and handle it with string only or use GMP but only available on Linux
/* VERIFIED */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_PCNT extends frame {
	protected $tagcode = 'PCNT';
	protected $tagname = 'Play counter';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	private $count;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->count = frame::get_int($this->value->data);
	}

	public function display_data() {
		return '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" value="'.$this->count.'" size="12" style="width:200px" />';
	}

	public function display_spec() {
		return '';
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		// We calculate the number of byte required
		$byte_required = 0;
		$temp_value = (float)$code['data'];
		while((pow(2,(8+8*$byte_required))-1-$temp_value)<0)
			$byte_required++;
		$byte_required++;
		$buffer = frame::get_num((float)$code['data'],$byte_required);	// Counter
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>