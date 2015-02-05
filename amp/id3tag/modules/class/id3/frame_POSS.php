<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_POSS extends frame {
	protected $tagcode = 'POSS';
	protected $tagname = 'Position synchronisation frame';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
	}

	public function display_data() {
		return 'For Stream Only - Useless for mp3';
	}

	public function display_spec() {
		return 'Not Supported';
	}

	public function save($code) {
		return '';
	}
};
?>