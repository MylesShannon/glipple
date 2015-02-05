<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_TFLT extends frame_T {
	protected $tagcode = 'TFLT';
	protected $tagname = 'File type';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TagValue $value){
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}

	public function display_spec() {
		static $temp_object;

		$text2display = '';
		$list_option = array(
			'MPG'	=>'MPEG Audio',
			'MPG/1'	=>'MPEG 1/2 layer I',
			'MPG/2'	=>'MPEG 1/2 layer II',
			'MPG/3'	=>'MPEG 1/2 layer III',
			'MPG/2.5'=>'MPEG 2.5',
			'MPG/AAC'=>'Advanced audio compression',
			'VQF'	=>'Transform-domain Weighted Interleave Vector Quantization',
			'PCM'	=>'Pulse Code Modulated audio'
		);
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= '<select name="temptflt_'.$temp_object.'" size="1" style="width:120px" onchange="change_tflt(this,\''.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\');"><option value="">Select One</option>';
		reset($list_option);
		while(list($key,$value_key) = each($list_option))
			$text2display .= '<option value="'.$key.'">'.$value_key.'</option>';
		$text2display .= '</select>';
		$temp_object++;

		return $text2display;
	}
};
?>