<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_RVRB extends frame {
	protected $tagcode = 'RVRB';
	protected $tagname = 'Reverb';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	private $reverb_left,$reverb_right;
	private $reverb_bounce_left,$reverb_bounce_right;
	private $reverb_feedback_ll,$reverb_feedback_lr,$reverb_feedback_rr,$reverb_feedback_rl;
	private $premix_lr,$premix_rl;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->reverb_left = frame::get_int(substr($this->value->data,0,2));
		$this->reverb_right = frame::get_int(substr($this->value->data,2,2));
		$this->reverb_bounce_left = ord(substr($this->value->data,4,1));
		$this->reverb_bounce_right = ord(substr($this->value->data,5,1));
		$this->reverb_feedback_ll = ord(substr($this->value->data,6,1));
		$this->reverb_feedback_lr = ord(substr($this->value->data,7,1));
		$this->reverb_feedback_rr = ord(substr($this->value->data,8,1));
		$this->reverb_feedback_rl = ord(substr($this->value->data,9,1));
		$this->premix_lr = ord(substr($this->value->data,10,1));
		$this->premix_rl = ord(substr($this->value->data,11,1));
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" value="'.$this->reverb_left.'" size="12" style="width:200px" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][1]" value="'.$this->reverb_right.'" size="12" style="width:200px" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][2]" value="'.$this->reverb_bounce_left.'" size="12" style="width:200px" /><br />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][3]" value="'.$this->reverb_bounce_right.'" size="12" style="width:200px" /><br />';
		$text2display .= $this->display_percent(4,'data',$this->reverb_feedback_ll,'Reverb feedback, left to left','%',200).'<br />';
		$text2display .= $this->display_percent(5,'data',$this->reverb_feedback_lr,'Reverb feedback, left to right','%',200).'<br />';
		$text2display .= $this->display_percent(6,'data',$this->reverb_feedback_rr,'Reverb feedback, right to right','%',200).'<br />';
		$text2display .= $this->display_percent(7,'data',$this->reverb_feedback_rl,'Reverb feedback, right to left','%',200).'<br />';
		$text2display .= $this->display_percent(8,'data',$this->premix_lr,'Premix left to right','%',200).'<br />';
		$text2display .= $this->display_percent(9,'data',$this->premix_rl,'Premix right to left','%',200);
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
		$buffer = frame::get_num($code['data'][0],2);					// Reverb Left
		$buffer .= frame::get_num($code['data'][1],2);					// Reverb Right
		$buffer .= frame::get_num($code['data'][2],1);					// Reverb Bounces Left
		$buffer .= frame::get_num($code['data'][3],1);					// Reverb Bounces Right
		$buffer .= chr($code['data'][4]);						// Reverb Feedback, left to left
		$buffer .= chr($code['data'][5]);						// Reverb Feedback, left to right
		$buffer .= chr($code['data'][6]);						// Reverb Feedback, right to right
		$buffer .= chr($code['data'][7]);						// Reverb Feedback, right to left
		$buffer .= chr($code['data'][8]);						// Premix left to right
		$buffer .= chr($code['data'][9]);						// Premix right to left
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>