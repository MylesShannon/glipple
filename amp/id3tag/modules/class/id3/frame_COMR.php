<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_COMR extends frame {
	protected $tagcode = 'COMR';
	protected $tagname = 'Commercial frame';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	protected $encoding;
	protected $currency = '';	// EMPTY AND REMAIN EMPTY
	private $price;
	private $valid_y,$value_m,$value_d;
	private $contact_url;
	private $receive_as;
	private $seller;
	private $description;
	private $mime;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->encoding = ord(substr($this->value->data,0,1));
		$this->value->data = substr($this->value->data,1);
		$count = 0;
		$this->price = frame::get_string(0,$this->value->data,$count);
		$this->valid_y = substr($this->value->data,$count,4);
		$count+=4;
		$this->valid_m = substr($this->value->data,$count,2);
		$count+=2;
		$this->valid_d = substr($this->value->data,$count,2);
		$count+=2;
		$this->contact_url = frame::get_string(0,$this->value->data,$count);
		$this->receive_as = ord(substr($this->value->data,$count,1));
		$count++;
		$this->seller = frame::code2text($this->encoding,frame::get_string($this->encoding,$this->value->data,$count));
		$this->description = frame::code2text($this->encoding,frame::get_string($this->encoding,$this->value->data,$count));
		$this->mime = frame::get_string(0,$this->value->data,$count);
		$this->value->data = substr($this->value->data,$count);
		$_SESSION[$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][type]'] = $this->mime;
		$_SESSION[$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]'] = $this->value->data;
	}

	public function display_data() {
		$text2display = '';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][0]" size="20" style="width:200px" value="'.$this->price.'" readonly="readonly" /><br />';
		$text2display .= $this->display_date(1,$this->valid_y,$this->valid_m,$this->valid_d);
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][4]" size="20" style="width:200px" value="'.$this->contact_url.'" />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][5]" size="20" style="width:200px" value="'.$this->seller.'" />';
		$text2display .= '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data][6]" size="20" style="width:200px" value="'.$this->description.'" />';
		$text2display .= '<input type="file" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']" style="width:200px" /><br />';
		if(!empty($this->value->data))
			$text2display .= '<a href="javascript:;" onclick="window.open(\'image.php?PHPSESSID='.session_id().'&name='.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\',\'display_image\',\'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=350,left=50,top=50\');">Current Image</a> <font class="littletext">('.$this->get_size(strlen($this->value->data),2).')</font>';
		return $text2display;
	}

	public function display_spec() {
		static $temp_object = 0;

		$list_option = array(
			'Other','Standard CD album with other songs','Compressed audio on CD','File over the Internet','Stream over the Internet',
			'As note sheets','As note sheets in a book with other sheets','Music on other media','Non-musical merchandise'
		);
		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= $this->display_currency('temp'.$temp_object,' onchange="change_comr(this,\''.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\');"');
		$text2display .= '<br />';
		$text2display .= '<input type="checkbox" name="tempcomr1_'.$temp_object.'" value="1" id="tempcomr2_'.$temp_object.'" onclick="change2_comr(this,\''.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']\');" /> <label for="tempcomr2_'.$temp_object.'">Delete Currency</label>';
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec][1]" size="1" style="width:120px"><option value="0">Receive As</option>';
		for($i=0;$i<count($list_option);$i++) {
			$text2display .= '<option value="'.$i.'"';
			if($this->receive_as==$i)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$list_option[$i].'</option>';
		}
		$text2display .= '</select>';
		$temp_object++;
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
		$buffer = chr($code['spec'][0]);				// Encoding
		$buffer .= frame::check_newline($code['data'][0],false);					// Price
		$buffer .= chr(0);						// EOS
		$buffer .= str_pad($code['data'][1],2,'0',STR_PAD_LEFT).str_pad($code['data'][2],2,'0',STR_PAD_LEFT).str_pad($code['data'][3],2,'0',STR_PAD_LEFT);	// Date
		$buffer .= frame::check_newline($code['data'][4],false);					// URL
		$buffer .= chr(0);						// EOS
		$buffer .= chr($code['spec'][1]);				// Receive As
		$buffer .= frame::text2code($this->encoding,frame::check_newline($code['data'][5],false));	// Seller
		$buffer .= chr(0).(($code['spec'][0]==1)?chr(0):'');		// EOS
		$buffer .= frame::text2code($this->encoding,frame::check_newline($code['data'][6],false));	// Description
		$buffer .= chr(0).(($code['spec'][0]==1)?chr(0):'');		// EOS
		$buffer .= $temp_mime;						// MIME
		$buffer .= chr(0);						// EOS
		$buffer .= $temp_fp;						// Picture
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>