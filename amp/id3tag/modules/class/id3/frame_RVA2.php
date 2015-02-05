<?php
// PEAK NOT SUPPORTED
/* VERIFIED */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_RVA2 extends frame {
	protected $tagcode = 'RVA2';
	protected $tagname = 'Relative volume adjustment (2)';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	private $vol_ident;
	private $vol_type,$vol_adjust;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		if(!empty($this->value->data)) {
			$count = 0;
			$this->vol_ident = frame::get_string(0,$this->value->data,$count);
			$this->vol_type = ord(substr($this->value->data,$count,1));
			$this->vol_adjust = frame::get_short_signed(frame::get_int(substr($this->value->data,$count+1,2)))/512;
		}
		else
			$this->vol_type = -1;
	}

	public function display_data() {
		$text2display = '';
		$list_option = array('Master volume','Front right','Front left','Back right','Back left','Front centre','Back centre','Subwoofer');
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" value="'.$this->vol_ident.'" size="12" style="width:200px" /><br />';
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" size="1" style="width:200px"><option value="0">Type of Channel</option>';
		$c = count($list_option);
		for($i=0;$i<$c;$i++) {
			$text2display .= '<option value="'.$i.'"';
			if($this->vol_type==$i)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$list_option[$i].'</option>';
		}
		$text2display .= '</select><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][2]" value="'.$this->vol_adjust.'" size="12" style="width:200px" />';
		// PEAK NOT SUPPORTED
		return $text2display;
	}

	public function display_spec() {
		return '';
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = $code['data'][0].chr(0);				// Ident
		$buffer .= chr($code['data'][1]);				// Type
		if($code['data'][2]>=64)
			$temp = 0x7fff;
		elseif($code['data'][2]<-64)
			$temp = 0x8000;
		else
			$temp = frame::get_short_signed($code['data'][2]*512);
		$buffer .= frame::get_num($temp,2); 				// Vol Adjustment
		$buffer .= chr(0).chr(0);					// Peak NOT SUPPORTED
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>