<?php
// KNOWN BUG :
// ONLY 2^31-1 is allowed for the counter
// We could take the number and handle it with string only or use GMP but only available on Linux
/* VERIFIED */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_POPM extends frame {
	protected $tagcode = 'POPM';
	protected $tagname = 'Popularimeter';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	private $count;
	private $email;
	protected $rating;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$count = 0;
		if($this->value->data){
			$this->email = frame::get_string(0,$this->value->data,$count);
			$this->rating = ord(substr($this->value->data,$count,1));
			$this->count = frame::get_int(substr($this->value->data,$count+1));
		}
		else{
			$this->email = '';
			$this->rating = 0;
			$this->count = 0;
		}
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" value="'.$this->email.'" size="12" style="width:200px" />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" value="'.$this->count.'" size="12" style="width:200px" />';
		return $text2display;
	}

	public function display_spec() {
		return $this->display_percent(1,'spec',$this->rating,'Rating',' / 100',120);
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = frame::check_newline($code['data'][0],false);			// Email or User
		$buffer .= chr(0);							// EOS
		$buffer .= chr($code['spec'][1]);					// Rating
		// We calculate the number of byte required
		$byte_required = 0;
		$temp_value = (float)$code['data'][1];
		while((pow(2,(8+8*$byte_required))-1-$temp_value)<0)
			$byte_required++;
		$byte_required++;
		$buffer .= frame::get_num((float)$code['data'][1],$byte_required);	// Counter
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>