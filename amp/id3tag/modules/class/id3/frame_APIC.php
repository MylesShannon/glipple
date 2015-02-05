<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

if(!defined('FORM_VALUE'))
	define('FORM_VALUE','id3v2_val');

class frame_APIC extends frame {
	protected $tagcode = 'APIC';
	protected $tagname = 'Attached picture';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;
	private $mime,$picture_type,$description;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;

		$this->encoding = ord(substr($this->value->data,0,1));
		$this->value->data = substr($this->value->data,1);

		if(!empty($this->value->data)) {
			$count = 0;
			$this->mime = frame::get_string(0,$this->value->data,$count);
			$this->picture_type = ord(substr($this->value->data,$count,1));
			$count++;
			$this->description = frame::code2text($this->encoding,frame::get_string($this->encoding,$this->value->data,$count));

			if($this->encoding==1)
				$count++;
			$this->value->data = substr($this->value->data,$count);			// Picture
			$_SESSION[$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][type]'] = $this->mime;
			$_SESSION[$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]'] = $this->value->data;
		}
		else {
			$this->picture_type = -1;
			$this->description = '';
		}
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" style="width:200px" value="'.$this->description.'" maxlength=64><br>';
		$text2display .= '<input type="file" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']" style="width:200px"><br>';
		if(!empty($this->value->data))
			$text2display .= '<a href="javascript:;" onClick="window.open(\'image.php?PHPSESSID='.session_id().'&name='.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\',\'display_image\',\'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=350,left=50,top=50\');">Current Image</a> <font class="littletext">('.$this->get_size(strlen($this->value->data),2).')</font>';
		return $text2display;
	}

	public function display_spec() {
		$text2display = '';
		$list_option = array('Other','32x32 pixels \'file icon\' (PNG only)','Other file icon','Cover (front)','Cover (back)',
			'Leaflet page','Media (e.g. lable side of CD)','Lead artist/lead performer/soloist','Artist/performer',
			'Conductor','Band/Orchestra','Composer','Lyricist/text writer','Recording Location','During recording',
			'During performance','Movie/video screen capture','A bright coloured fish','Illustration','Band/artist logotype',
			'Publisher/Studio logotype'
		);
		$text2display .= $this->display_encoding($this->encoding);
		$text2display .= '<br />';
		$text2display .=  '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec][1]" size="1" style="width:120px"><option value="0">Picture Type</option>';
		$c = count($list_option);
		for($i=0;$i<$c;$i++) {
			$text2display .= '<option value="'.$i.'"';
			if($this->picture_type==$i)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$list_option[$i].'</option>';
		}
		$text2display .= '</select>';
		return $text2display;
	}

	public function save($code) {
		static $counter = 0;

		$temp_file = $_FILES[constant('FORM_VALUE')];
		if(isset($temp_file['error'][$this->tagcode])){
			if($counter==0){									// We search for the first counter
				reset($temp_file['error'][$this->tagcode]);
				list($counter) = each($temp_file['error'][$this->tagcode]);
			}
			$temp_error = $temp_file['error'][$this->tagcode][$counter];
			$temp_mime = $temp_file['type'][$this->tagcode][$counter];
		}
		else
			$temp_error = 1;

		$temp_fp = '';
		if($temp_error==0){
			if(move_uploaded_file($temp_file['tmp_name'][$this->tagcode][$counter],constant('TEMP_FOLDER').$temp_file['name'][$this->tagcode][$counter])) {
				if($fp = fopen(constant('TEMP_FOLDER').$temp_file['name'][$this->tagcode][$counter],'rb')){
					while(!feof($fp))
						$temp_fp .= fread($fp,255);
					fclose($fp);
					unlink($temp_file['name'][$this->tagcode][$counter]);
				}

			}
		}
		else{
			// We take Session
			if(isset($_SESSION[constant('FORM_VALUE').'['.$this->tagcode.']['.$counter.'][data]']) && isset($_SESSION[constant('FORM_VALUE').'['.$this->tagcode.']['.$counter.'][type]'])){
				$temp_mime = $_SESSION[constant('FORM_VALUE').'['.$this->tagcode.']['.$counter.'][type]'];
				$temp_fp = $_SESSION[constant('FORM_VALUE').'['.$this->tagcode.']['.$counter.'][data]'];
			}
		}

		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);								// Encoding
		$buffer .= $temp_mime;										// MIME
		$buffer .= chr(0);										// EOS
		$buffer .= chr($code['spec'][1]);								// Picture Type
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'],false));	// Description
		$buffer .= chr(0).(($code['spec'][0]==1)?chr(0):'');						// EOS
		$buffer .= $temp_fp;										// Picture
		$size = strlen($buffer);
		$counter++;
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>