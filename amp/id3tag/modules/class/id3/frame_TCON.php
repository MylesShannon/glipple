<?php
/* VERIFIED2 */
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

class frame_TCON extends frame_T {
	protected $tagcode = 'TCON';
	protected $tagname = 'Content type';
	protected $deprecated = false;
	protected $instanceNumber = NULL;
	private static $counter = 0;

	private static $genres = array(
		'Blues','Classic Rock','Country','Dance','Disco','Funk','Grunge', 
		'Hip-Hop','Jazz','Metal','New Age','Oldies','Other','Pop','R&B', 
		'Rap','Reggae','Rock','Techno','Industrial','Alternative','Ska', 
		'Death Metal','Pranks','Soundtrack','Euro-Techno','Ambient', 
		'Trip-Hop','Vocal','Jazz+Funk','Fusion','Trance','Classical', 
		'Instrumental','Acid','House','Game','Sound Clip','Gospel', 
		'Noise','Alt. Rock','Bass','Soul','Punk','Space','Meditative', 
		'Instrumental Pop','Instrumental Rock','Ethnic','Gothic', 
		'Darkwave','Techno-Industrial','Electronic','Pop-Folk', 
		'Eurodance','Dream','Southern Rock','Comedy','Cult','Gangsta Rap', 
		'Top 40','Christian Rap','Pop/Funk','Jungle','Native American', 
		'Cabaret','New Wave','Psychedelic','Rave','Showtunes','Trailer', 
		'Lo-Fi','Tribal','Acid Punk','Acid Jazz','Polka','Retro', 
		'Musical','Rock & Roll','Hard Rock','Folk','Folk/Rock', 
		'National Folk','Swing','Fast-Fusion','Bebob','Latin','Revival', 
		'Celtic','Bluegrass','Avantgarde','Gothic Rock','Progressive Rock', 
		'Psychedelic Rock','Symphonic Rock','Slow Rock','Big Band', 
		'Chorus','Easy Listening','Acoustic','Humour','Speech','Chanson', 
		'Opera','Chamber Music','Sonata','Symphony','Booty Bass','Primus', 
		'Porn Groove','Satire','Slow Jam','Club','Tango','Samba', 
		'Folklore','Ballad','Power Ballad','Rhythmic Soul','Freestyle', 
		'Duet','Punk Rock','Drum Solo','A Cappella','Euro-House','Dance Hall',
		// Extension
		'Goa','Drum & Bass','Club-House','Hardcore','Terror','Indie','BritPop',
		'Negerpunk','Polsk Punk','Beat','Christian Gangsta Rap','Heavy Metal',
		'Black Metal','Crossover','Contemporary Christian','Christian Rock',
		'Merengue','Salsa','Trash Metal','Anime','JPop','Synthpop');
	private $support_v1;

	function __construct($name, TagValue $value) {
		parent::__construct($name,$value);
		$this->instanceNumber = self::$counter++;
		$this->support_v1=false;
		if(!empty($this->value->data)){
			if($this->value->data{0} == '(' && !is_bool($temp = strpos($this->value->data,')',2))){
				if(is_numeric(substr($this->value->data,1,$temp-1))){
					$this->value->data = frame::code2text($this->encoding,substr($this->value->data,$temp+1));
					$this->support_v1=true;
				}
			}
		}
		else
			$this->support_v1=true;
	}

	public function display_data() {
		return '<input type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]" size="20" style="width:200px" value="'.$this->value->data.'" />';
	}

	public function display_spec() {
		$text2display = '';
		$text2display .= $this->display_encoding();
		$text2display .= '<br />';
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec][1]" size="1" style="width:120px">';
		$text2display .= '<option value="0"';
		if($this->support_v1==false)
			$text2display .= ' selected="selected"';
		$text2display .= '>No Support ID3v1.1</option>';
		$text2display .= '<option value="1"';
		if($this->support_v1==true)
			$text2display .= ' selected="selected"';
		$text2display .= '>Support ID3v1.1</option>';
		$text2display .= '</select>';
		return $text2display;
	}

	public function save($code) {
		if(isset($code['flag']))
			$flags = frame::get_flag($code['flag']);
		else
			$flags = chr(0).chr(0);
		$buffer = chr($code['spec'][0]);								// Encoding
		if($code['spec'][1]==1)
			if(!is_bool($val = array_search($code['data'],self::$genres)))
				$buffer .= '('.$val.')';							// ID3v1.1 Support
		$buffer .= frame::text2code($code['spec'][0],frame::check_newline($code['data'],false));	// Value
		$size = strlen($buffer);
		return $this->tagcode.frame::get_num($size,4).$flags.$buffer;
	}
};
?>