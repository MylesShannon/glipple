<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_TKEY extends frame_T {
	protected $tagcode = 'TKEY';
	protected $tagname = 'Initial key';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TagValue $value){
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}

	public function display_data() {
		return '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" value="'.$this->value->data.'" size="20" maxlength="3" style="width:200px" />';
	}

	public function display_spec() {
		static $temp_object;

		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= '<select name="temptkey_'.$temp_object.'" size="1" style="width:120px" onchange="change_tkey(\''.$temp_object.'\',\''.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\');"><option value="">Select One</option>';
		for($i='A';$i<='G';$i++)
			$text2display .= '<option value="'.$i.'">'.$i.'</option>';
		$text2display .= '</select><br>';
		$text2display .= '<select name="temptkey2_'.$temp_object.'" size="1" style="width:120px" onchange="change_tkey(\''.$temp_object.'\',\''.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\');"><option value="">Select One</option>';
		$text2display .= '<option value="b"> b</option>';
		$text2display .= '<option value="#"> #</option>';
		$text2display .= '</select><br />';
		$text2display .= '<input type="checkbox" name="temptkey3_'.$temp_object.'" value="m" id="temptkey3_'.$temp_object.'" onclick="change_tkey(\''.$temp_object.'\',\''.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\');" /> <label for="temptkey3_'.$temp_object.'">Minor</label>';
		$temp_object++;
		return $text2display;
	}
};
?>