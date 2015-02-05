<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_TCOP extends frame_T {
	protected $tagcode = 'TCOP';
	protected $tagname = 'Copyright message';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TagValue $value){
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}

	public function display_spec() {
		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= 'ie. : 2004 Someone';
		return $text2display;
	}
};
?>