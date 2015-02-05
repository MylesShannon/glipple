<?php
/* VERIFIED */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_EQU2 extends frame {
	protected $tagcode = 'EQU2';
	protected $tagname = 'Equalization (2)';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	private $equa_interp;
	private $equa_ident;
	private $equa_frequency;
	private $equa_volume;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		if(!empty($this->value->data)) {
			$this->equa_interp = ord(substr($this->value->data,0,1));
			$count = 1;
			$this->equa_ident = frame::get_string($this->value->data,$count);
			$this->equa_frequency = frame::get_int(substr($this->value->data,$count,2))/2;
			$this->equa_volume = frame::get_short_signed(frame::get_int(substr($this->value->data,$count+2,2)))/512;
		}
		else
			$this->equa_interp = -1;
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" value="'.$this->equa_ident.'" size="12" style="width:200px" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" value="'.$this->equa_frequency.'" size="12" style="width:200px" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][2]" value="'.$this->equa_volume.'" size="12" style="width:200px" />';
		// PEAK NOT SUPPORTED
		return $text2display;

	}

	public function display_spec() {
		$text2display = '';
		$list_option = array('Band','Linear');
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec][1]" size="1" style="width:120px"><option value="0">Interp. Method</option>';
		$c = count($list_option);
		for($i=0;$i<$c;$i++) {
			$text2display .= '<option value="'.$i.'"';
			if($this->equa_interp==$i)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$list_option[$i].'</option>';
		}
		$text2display .= '</select>';
		return $text2display;
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][1]);			// Interpolation Method
		$buffer .= $code['data'][0].chr(0);			// Ident
		if($code['data'][1]<0)
			$code['data'][1] = 0;

		elseif($code['data'][1]>32767)
			$code['data'][1] = 32767;
		$temp = intval($code['data'][1]*2);
		$buffer .= frame::get_num($temp,2);			// Frequency
		if($code['data'][2]>=64)
			$temp = 0x7fff;
		elseif($code['data'][2]<-64)
			$temp = 0x8000;
		else
			$temp = frame::get_short_signed($code['data'][2]*512);
		$buffer .= frame::get_num($temp,2); // Volume
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>