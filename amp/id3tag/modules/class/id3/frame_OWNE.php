<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_OWNE extends frame {
	protected $tagcode = 'OWNE';
	protected $tagname = 'Ownership frame';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;
	protected $currency;
	private $price;
	private $buy_year,$buy_month,$buy_day;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->encoding = ord(substr($this->value->data,0,1));
		$this->currency = substr($this->value->data,1,3);
		$this->value->data = substr($this->value->data,4);
		$count = 0;
		$this->price = frame::get_string(0,$this->value->data,$count);
		$this->buy_year = substr($this->value->data,$count,4);
		$count+=4;
		$this->buy_month = substr($this->value->data,$count,2);
		$count+=2;
		$this->buy_day = substr($this->value->data,$count,2);
		$count+=2;
		$this->value->data = frame::code2text($this->encoding,substr($this->value->data,$count));
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" size="20" style="width:200px" value="'.$this->price.'" /><br />';
		$text2display .= $this->display_date(1,$this->buy_year,$this->buy_month,$this->buy_day);
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][4]" size="20" style="width:200px" value="'.$this->value->data.'" />';
		return $text2display;
	}

	public function display_spec() {
		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= $this->display_currency(1);
		return $text2display;
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);				// Encoding
		$buffer .= strtoupper($code['spec'][1]);			// Currency
		$buffer .= frame::check_newline($code['data'][0],false);	// Price
		$buffer .= chr(0);						// EOS
		$buffer .= str_pad($code['data'][1],2,'0',STR_PAD_LEFT).str_pad($code['data'][2],2,'0',STR_PAD_LEFT).str_pad($code['data'][3],2,'0',STR_PAD_LEFT);	// Date
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'][4],true));	// Seller
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>