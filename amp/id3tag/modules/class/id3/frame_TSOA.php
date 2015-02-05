<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_TSOA extends frame_T {
	protected $tagcode = 'TSOA';
	protected $tagname = 'Album sort order';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static private $counter = 0;

	function __construct($name, TagValue $value){
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}
};
?>