<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_TLEN extends frame_T {
	protected $tagcode = 'TLEN';
	protected $tagname = 'Length';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TAgValue $value){
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}

	public function display_spec() {
		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= 'in milliseconds';
		return $text2display;
	}
};
?>