<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_RVAD extends frame_NOTSUPPORTED {
	protected $tagcode = 'RVAD';
	protected $tagname = 'Relative volume adjustment';
	protected $deprecated = true;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TagValue $value){
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}
};
?>