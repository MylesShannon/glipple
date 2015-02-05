<?php
if(!defined('IN_ID'))die('You are not allowed to access to this page.');

abstract class frame {
	public static $ENCODING = array('ISO-8859-1', 'UTF-16');
	private static $zero = array(1, 2);

	protected $name,$value;

	private static $lang_array = array(
		'aar'=>'Afar','abk'=>'Abkhazian','ace'=>'Achinese','ach'=>'Acoli','ada'=>'Adangme','afa'=>'Afro-Asiatic (Other)',
		'afh'=>'Afrihili','afr'=>'Afrikaans','aka'=>'Akan','akk'=>'Akkadian','alb'=>'Albanian','ale'=>'Aleut','alg'=>'Algonquian Languages',
		'amh'=>'Amharic','ang'=>'English, Old (ca. 450-1100)','apa'=>'Apache Languages','ara'=>'Arabic','arc'=>'Aramaic','arm'=>'Armenian',
		'arn'=>'Araucanian','arp'=>'Arapaho','art'=>'Artificial (Other)','arw'=>'Arawak','asm'=>'Assamese','ath'=>'Athapascan Languages',
		'ava'=>'Avaric','ave'=>'Avestan','awa'=>'Awadhi','aym'=>'Aymara','aze'=>'Azerbaijani','bad'=>'Banda','bai'=>'Bamileke Languages',
		'bak'=>'Bashkir','bal'=>'Baluchi','bam'=>'Bambara','ban'=>'Balinese','baq'=>'Basque','bas'=>'Basa','bat'=>'Baltic (Other)',
		'bej'=>'Beja','bel'=>'Byelorussian','bem'=>'Bemba','ben'=>'Bengali','ber'=>'Berber (Other)','bho'=>'Bhojpuri','bih'=>'Bihari',
		'bik'=>'Bikol','bin'=>'Bini','bis'=>'Bislama','bla'=>'Siksika','bnt'=>'Bantu (Other)','bod'=>'Tibetan','bra'=>'Braj',
		'bre'=>'Breton','bua'=>'Buriat','bug'=>'Buginese','bul'=>'Bulgarian','bur'=>'Burmese','cad'=>'Caddo',
		'cai'=>'Central American Indian (Other)','car'=>'Carib','cat'=>'Catalan','cau'=>'Caucasian (Other)','ceb'=>'Cebuano',
		'cel'=>'Celtic (Other)','ces'=>'Czech','cha'=>'Chamorro','chb'=>'Chibcha','che'=>'Chechen','chg'=>'Chagatai','chi'=>'Chinese',
		'chm'=>'Mari','chn'=>'Chinook jargon','cho'=>'Choctaw','chr'=>'Cherokee','chu'=>'Church Slavic','chv'=>'Chuvash','chy'=>'Cheyenne',
		'cop'=>'Coptic','cor'=>'Cornish','cos'=>'Corsican','cpe'=>'Creoles and Pidgins, English-based (Other)',
		'cpf'=>'Creoles and Pidgins, French-based (Other)','cpp'=>'Creoles and Pidgins, Portuguese-based (Other)','cre'=>'Cree',
		'crp'=>'Creoles and Pidgins (Other)','cus'=>'Cushitic (Other)','cym'=>'Welsh','cze'=>'Czech','dak'=>'Dakota','dan'=>'Danish',
		'del'=>'Delaware','deu'=>'German','din'=>'Dinka','div'=>'Divehi','doi'=>'Dogri','dra'=>'Dravidian (Other)','dua'=>'Duala',
		'dum'=>'Dutch, Middle (ca. 1050-1350)','dut'=>'Dutch','dyu'=>'Dyula','dzo'=>'Dzongkha','efi'=>'Efik','egy'=>'Egyptian (Ancient)',
		'eka'=>'Ekajuk','ell'=>'Greek, Modern (1453-)','elx'=>'Elamite','eng'=>'English','enm'=>'English, Middle (ca. 1100-1500)',
		'epo'=>'Esperanto','esk'=>'Eskimo (Other)','esl'=>'Spanish','est'=>'Estonian','eus'=>'Basque','ewe'=>'Ewe','ewo'=>'Ewondo',
		'fan'=>'Fang','fao'=>'Faroese','fas'=>'Persian','fat'=>'Fanti','fij'=>'Fijian','fin'=>'Finnish','fiu'=>'Finno-Ugrian (Other)',
		'fon'=>'Fon','fra'=>'French','fre'=>'French','frm'=>'French, Middle (ca. 1400-1600)','fro'=>'French, Old (842- ca. 1400)',
		'fry'=>'Frisian','ful'=>'Fulah','gaa'=>'Ga','gae'=>'Gaelic (Scots)','gai'=>'Irish','gay'=>'Gayo','gdh'=>'Gaelic (Scots)',
		'gem'=>'Germanic (Other)','geo'=>'Georgian','ger'=>'German','gez'=>'Geez','gil'=>'Gilbertese','glg'=>'Gallegan',
		'gmh'=>'German, Middle High (ca. 1050-1500)','goh'=>'German, Old High (ca. 750-1050)','gon'=>'Gondi','got'=>'Gothic',
		'grb'=>'Grebo','grc'=>'Greek, Ancient (to 1453)','gre'=>'Greek, Modern (1453-)','grn'=>'Guarani','guj'=>'Gujarati','hai'=>'Haida',
		'hau'=>'Hausa','haw'=>'Hawaiian','heb'=>'Hebrew','her'=>'Herero','hil'=>'Hiligaynon','him'=>'Himachali','hin'=>'Hindi',
		'hmo'=>'Hiri Motu','hun'=>'Hungarian','hup'=>'Hupa','hye'=>'Armenian','iba'=>'Iban','ibo'=>'Igbo','ice'=>'Icelandic','ijo'=>'Ijo',
		'iku'=>'Inuktitut','ilo'=>'Iloko','ina'=>'Interlingua (International Auxiliary language Association)','inc'=>'Indic (Other)',
		'ind'=>'Indonesian','ine'=>'Indo-European (Other)','ine'=>'Interlingue','ipk'=>'Inupiak','ira'=>'Iranian (Other)','iri'=>'Irish',
		'iro'=>'Iroquoian uages','isl'=>'Icelandic','ita'=>'Italian','jav'=>'Javanese','jaw'=>'Javanese','jpn'=>'Japanese',
		'jpr'=>'Judeo-Persian','jrb'=>'Judeo-Arabic','kaa'=>'Kara-Kalpak','kab'=>'Kabyle','kac'=>'Kachin','kal'=>'Greenlandic',
		'kam'=>'Kamba','kan'=>'Kannada','kar'=>'Karen','kas'=>'Kashmiri','kat'=>'Georgian','kau'=>'Kanuri','kaw'=>'Kawi','kaz'=>'Kazakh',
		'kha'=>'Khasi','khi'=>'Khoisan (Other)','khm'=>'Khmer','kho'=>'Khotanese','kik'=>'Kikuyu','kin'=>'Kinyarwanda','kir'=>'Kirghiz',
		'kok'=>'Konkani','kom'=>'Komi','kon'=>'Kongo','kor'=>'Korean','kpe'=>'Kpelle','kro'=>'Kru','kru'=>'Kurukh','kua'=>'Kuanyama',
		'kum'=>'Kumyk','kur'=>'Kurdish','kus'=>'Kusaie','kut'=>'Kutenai','lad'=>'Ladino','lah'=>'Lahnda','lam'=>'Lamba','lao'=>'Lao',
		'lat'=>'Latin','lav'=>'Latvian','lez'=>'Lezghian','lin'=>'Lingala','lit'=>'Lithuanian','lol'=>'Mongo','loz'=>'Lozi',
		'ltz'=>'Letzeburgesch','lub'=>'Luba-Katanga','lug'=>'Ganda','lui'=>'Luiseno','lun'=>'Lunda','luo'=>'Luo (Kenya and Tanzania)',
		'mac'=>'Macedonian','mad'=>'Madurese','mag'=>'Magahi','mah'=>'Marshall','mai'=>'Maithili','mak'=>'Macedonian','mak'=>'Makasar',
		'mal'=>'Malayalam','man'=>'Mandingo','mao'=>'Maori','map'=>'Austronesian (Other)','mar'=>'Marathi','mas'=>'Masai','max'=>'Manx',
		'may'=>'Malay','men'=>'Mende','mga'=>'Irish, Middle (900 - 1200)','mic'=>'Micmac','min'=>'Minangkabau','mis'=>'Miscellaneous (Other)',
		'mkh'=>'Mon-Kmer (Other)','mlg'=>'Malagasy','mlt'=>'Maltese','mni'=>'Manipuri','mno'=>'Manobo Languages','moh'=>'Mohawk',
		'mol'=>'Moldavian','mon'=>'Mongolian','mos'=>'Mossi','mri'=>'Maori','msa'=>'Malay','mul'=>'Multiple Languages','mun'=>'Munda Languages',
		'mus'=>'Creek','mwr'=>'Marwari','mya'=>'Burmese','myn'=>'Mayan Languages','nah'=>'Aztec','nai'=>'North American Indian (Other)',
		'nau'=>'Nauru','nav'=>'Navajo','nbl'=>'Ndebele, South','nde'=>'Ndebele, North','ndo'=>'Ndongo','nep'=>'Nepali','new'=>'Newari',
		'nic'=>'Niger-Kordofanian (Other)','niu'=>'Niuean','nla'=>'Dutch','nno'=>'Norwegian (Nynorsk)','non'=>'Norse, Old','nor'=>'Norwegian',
		'nso'=>'Sotho, Northern','nub'=>'Nubian Languages','nya'=>'Nyanja','nym'=>'Nyamwezi','nyn'=>'Nyankole','nyo'=>'Nyoro','nzi'=>'Nzima',
		'oci'=>'Langue d\'Oc (post 1500)','oji'=>'Ojibwa','ori'=>'Oriya','orm'=>'Oromo','osa'=>'Osage','oss'=>'Ossetic',
		'ota'=>'Turkish, Ottoman (1500 - 1928)','oto'=>'Otomian Languages','paa'=>'Papuan-Australian (Other)','pag'=>'Pangasinan',
		'pal'=>'Pahlavi','pam'=>'Pampanga','pan'=>'Panjabi','pap'=>'Papiamento','pau'=>'Palauan','peo'=>'Persian, Old (ca 600 - 400 B.C.)',
		'per'=>'Persian','phn'=>'Phoenician','pli'=>'Pali','pol'=>'Polish','pon'=>'Ponape','por'=>'Portuguese','pra'=>'Prakrit uages',
		'pro'=>'Provencal, Old (to 1500)','pus'=>'Pushto','que'=>'Quechua','raj'=>'Rajasthani','rar'=>'Rarotongan','roa'=>'Romance (Other)',
		'roh'=>'Rhaeto-Romance','rom'=>'Romany','ron'=>'Romanian','rum'=>'Romanian','run'=>'Rundi','rus'=>'Russian','sad'=>'Sandawe',
		'sag'=>'Sango','sah'=>'Yakut','sai'=>'South American Indian (Other)','sal'=>'Salishan Languages','sam'=>'Samaritan Aramaic',
		'san'=>'Sanskrit','sco'=>'Scots','scr'=>'Serbo-Croatian','sel'=>'Selkup','sem'=>'Semitic (Other)','sga'=>'Irish, Old (to 900)',
		'shn'=>'Shan','sid'=>'Sidamo','sin'=>'Singhalese','sio'=>'Siouan Languages','sit'=>'Sino-Tibetan (Other)','sla'=>'Slavic (Other)',
		'slk'=>'Slovak','slo'=>'Slovak','slv'=>'Slovenian','smi'=>'Sami Languages','smo'=>'Samoan','sna'=>'Shona','snd'=>'Sindhi',
		'sog'=>'Sogdian','som'=>'Somali','son'=>'Songhai','sot'=>'Sotho, Southern','spa'=>'Spanish','sqi'=>'Albanian','srd'=>'Sardinian',
		'srr'=>'Serer','ssa'=>'Nilo-Saharan (Other)','ssw'=>'Siswant','ssw'=>'Swazi','suk'=>'Sukuma','sun'=>'Sudanese','sus'=>'Susu',
		'sux'=>'Sumerian','sve'=>'Swedish','swa'=>'Swahili','swe'=>'Swedish','syr'=>'Syriac','tah'=>'Tahitian','tam'=>'Tamil','tat'=>'Tatar',
		'tel'=>'Telugu','tem'=>'Timne','ter'=>'Tereno','tgk'=>'Tajik','tgl'=>'Tagalog','tha'=>'Thai','tib'=>'Tibetan','tig'=>'Tigre',
		'tir'=>'Tigrinya','tiv'=>'Tivi','tli'=>'Tlingit','tmh'=>'Tamashek','tog'=>'Tonga (Nyasa)','ton'=>'Tonga (Tonga Islands)','tru'=>'Truk',
		'tsi'=>'Tsimshian','tsn'=>'Tswana','tso'=>'Tsonga','tuk'=>'Turkmen','tum'=>'Tumbuka','tur'=>'Turkish','tut'=>'Altaic (Other)',
		'twi'=>'Twi','tyv'=>'Tuvinian','uga'=>'Ugaritic','uig'=>'Uighur','ukr'=>'Ukrainian','umb'=>'Umbundu','und'=>'Undetermined',
		'urd'=>'Urdu','uzb'=>'Uzbek','vai'=>'Vai','ven'=>'Venda','vie'=>'Vietnamese','vol'=>'Volapük','vot'=>'Votic',
		'wak'=>'Wakashan Languages','wal'=>'Walamo','war'=>'Waray','was'=>'Washo','wel'=>'Welsh','wen'=>'Sorbian Languages','wol'=>'Wolof',
		'xho'=>'Xhosa','yao'=>'Yao','yap'=>'Yap','yid'=>'Yiddish','yor'=>'Yoruba','zap'=>'Zapotec','zen'=>'Zenaga','zha'=>'Zhuang',
		'zho'=>'Chinese','zul'=>'Zulu','zun'=>'Zuni'
	);
	private static $currency_array = array(
		'ADP'=>'Andorran Peseta','AED'=>'UAE Dirham','AFA'=>'Afghani','ALL'=>'Lek','AMD'=>'Armenian Dram','ANG'=>'Netherlands Antillean Guilder',
		'AON'=>'New Kwanza','AOR'=>'Kwanza Reajustado','ARS'=>'Argentine Peso','ATS'=>'Shilling','AUD'=>'Australian Dollar','AWG'=>'Aruban Guilder',
		'AZM'=>'Azerbaijanian Manat','BAD'=>'Dinar','BBD'=>'Barbados Dollar','BDT'=>'Taka','BEF'=>'Belgian Franc','BGL'=>'Lev','BHD'=>'Bahraini Dinar',
		'BIF'=>'Burundi Franc','BMD'=>'Bermudian Dollar','BND'=>'Brunei Dollar','BOB'=>'Boliviano','BOV'=>'MVDol','BRL'=>'Brazilian Real',
		'BSD'=>'Bahamian Dollar','BTN'=>'Ngultrum','BWP'=>'Pula','BYB'=>'Belarussian Ruble','BZD'=>'Belize Dollar','CAD'=>'Candian Dollar',
		'CHF'=>'Swiss Franc','CLF'=>'Unidades de Formento','CLP'=>'Chilean Peso','CNY'=>'Yuan Renminbi','COP'=>'Colombian Peso','CRC'=>'Costa Rican Colon',
		'CUP'=>'Cuban Peso','CVE'=>'Cape Verde Escudo','CYP'=>'Cyprus Pound','CZK'=>'Czech Koruna','DEM'=>'Deutsche Mark','DJF'=>'Djibouti Franc',
		'DKK'=>'Danish Krone','DOP'=>'Dominican Peso','DZD'=>'Algerian Dinar','ECS'=>'Sucre','ECV'=>'Unidad de Valor Constante (UVC)','EEK'=>'Kroon',
		'EGP'=>'Egyptian Pound','ESP'=>'Spanish Peseta','ETB'=>'Ethiopian Birr','FIM'=>'Markka','FJD'=>'Fiji Dollar','FKP'=>'Falkland Islands Pound',
		'FRF'=>'French Franc','GBP'=>'Pound Sterling','GEL'=>'Lari','GHC'=>'Cedi','GIP'=>'Gibraltar Pound','GMD'=>'Dalasi','GNF'=>'Guinea Franc',
		'GRD'=>'Drachma','GTQ'=>'Quetzal','GWP'=>'Guinea-Bissau Peso','GYD'=>'Guyana Dollar','HKD'=>'Hong Kong Dollar','HNL'=>'Lempira','HRK'=>'Kuna',
		'HTG'=>'Gourde','HUF'=>'Forint','IDR'=>'Rupiah','IEP'=>'Irish Pound','ILS'=>'Shekel','INR'=>'Indian Rupee','IQD'=>'Iraqi Dinar',
		'IRR'=>'Iranian Rial','ISK'=>'Iceland Krona','ITL'=>'Italian Lira','JMD'=>'Jamaican Dollar','JOD'=>'Jordanian Dinar','JPY'=>'Yen',
		'KES'=>'Kenyan Shilling','KGS'=>'Som','KHR'=>'Riel','KMF'=>'Comoro Franc','KPW'=>'North Korean Won','KRW'=>'Won','KWD'=>'Kuwaiti Dinar',
		'KYD'=>'Cayman Islands Dollar','KZT'=>'Tenge','LAK'=>'Kip','LBP'=>'Lebanese Pound','LKR'=>'Sri Lanka Rupee','LRD'=>'Liberian Dollar',
		'LSL'=>'Loti','LTL'=>'Lithuanian Litas','LUF'=>'Luxembourg Franc','LVL'=>'Latvian Lats','LYD'=>'Libyan Dinar','MAD'=>'Moroccan Dirham',
		'MDL'=>'Moldovan Leu','MGF'=>'Malagasy Franc','MKD'=>'Denar','MMK'=>'Kyat','MNT'=>'Tugrik','MOP'=>'Pataca','MRO'=>'Ouguiya','MTL'=>'Maltese Lira',
		'MUR'=>'Mauritius Rupee','MVR'=>'Rufiyaa','MWK'=>'Kwacha','MXN'=>'Mexican Nuevo Peso','MYR'=>'Malaysian Ringgit','MZM'=>'Metical',
		'NAD'=>'Namibia Dollar','NGN'=>'Naira','NIO'=>'Cordoba Oro','NLG'=>'Netherlands Guilder','NOK'=>'Norwegian Krone','NPR'=>'Nepalese Rupee',
		'NZD'=>'New Zealand Dollar','OMR'=>'Rial Omani','PAB'=>'Balboa','PEN'=>'Nuevo Sol','PGK'=>'Kina','PHP'=>'Philippine Peso','PKR'=>'Pakistan Rupee',
		'PLN'=>'Zloty','PLZ'=>'Zloty','PTE'=>'Portuguese Escudo','PYG'=>'Guarani','QAR'=>'Qatari Rial','ROL'=>'Leu','RUR'=>'Russian Ruble',
		'RWF'=>'Rwanda Franc','SAR'=>'Saudi Riyal','SBD'=>'Solomon Islands Dollar','SCR'=>'Seychelles Rupee','SDD'=>'Sudanese Dinar','SEK'=>'Swedish Krona',
		'SGD'=>'Singapore Dollar','SHP'=>'St. Helena Pound','SIT'=>'Tolar','SKK'=>'Slovak Koruna','SLL'=>'Leone','SOS'=>'Somali Shilling',
		'SRG'=>'Surinam Guilder','STD'=>'Dobra','SVC'=>'El Salvador Colon','SYP'=>'Syrian Pound','SZL'=>'Lilangeni','THB'=>'Baht','TJR'=>'Tajik Ruble',
		'TMM'=>'Manat','TND'=>'Tunisian Dollar','TOP'=>'Pa\'anga','TPE'=>'Timor Escudo','TRL'=>'Turkish Lira','TTD'=>'Trinidad and Tobago Dollar',
		'TWD'=>'New Taiwan Dollar','TZS'=>'Tanzanian Shilling','UAG'=>'Hryvna','UAK'=>'Karbovanets','UGX'=>'Uganda Shilling','USD'=>'US Dollar',
		'UYU'=>'Peso Uruguayo','UZS'=>'Uzbekistan Sum','VEB'=>'Bolivar','VND'=>'Dong','VUV'=>'Vatu','WST'=>'Tala','XAF'=>'CFA Franc BEAC',
		'XCD'=>'East Caribbean Dollar','XEU'=>'Euro','XOF'=>'CFA Franc BCEAO','XPF'=>'CFP Franc','YER'=>'Yemeni Rial','YUM'=>'New Dinar',
		'ZAL'=>'Financial Rand','ZAR'=>'Rand','ZMK'=>'Kwacha','ZRN'=>'New Zaire','ZWD'=>'Zimbabwe Dollar'
	);

	function __construct($name,$value){
		$this->name = $name;
		$this->value = $value;
	}

	protected function display_language($id=0) {
		$text2display = '';
		asort(self::$lang_array);
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec]['.$id.']" size="1" style="width:120px"><option value="XXX">No Languages</option>';
		reset(self::$lang_array);
		while(list($key,$key_value) = each(self::$lang_array)) {
			$text2display .= '<option value="'.$key.'"';
			if(strtoupper($this->language) == strtoupper($key))
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$key_value.'</option>';
		}
		$text2display .= '</select>';
		return $text2display;
	}

	protected function display_currency($id,$attrib='') {
		$text2display = '';
		asort(self::$currency_array);
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec]['.$id.']" size="1" style="width:120px"'.$attrib.'><option value="XXX">No Currency</option>';
		reset(self::$currency_array);
		while(list($key,$key_value) = each(self::$currency_array)) {
			$text2display .= '<option value="'.$key.'"';
			if(strtoupper($this->currency) == strtoupper($key))
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$key_value.'</option>';
		}
		$text2display .= '</select>';
		return $text2display;
	}

	protected function display_encoding($id=0) {
		$text2display = '';
		if(in_array('mbstring',get_loaded_extensions()))
			$encode_array = array('ISO-8859-1', 'Unicode');
		else
			$encode_array = array('ISO-8859-1');
		$text2display .= '<select name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][spec]['.$id.']" size="1" style="width:120px">';
		$c = count($encode_array);
		for($i=0;$i<$c;$i++) {
			$text2display .= '<option value="'.$i.'"';
			if($this->encoding==$i)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$encode_array[$i].'</option>';
		}
		$text2display .= '</select>';
		return $text2display;
	}

	protected function display_date($start,$year,$month,$day){
		$text2display = '';
		$text2display .= '<select type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]['.$start.']" size="1" style="width:99px"><option value="0">YYYY</option>';
		for($i=intval(date('Y'));$i>=1930;$i--){
			$text2display .= '<option value="'.$i.'"';
			if($i==$year)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.$i.'</option>';
		}
		$text2display .= '</select>';
		$start++;
		$text2display .= '<select type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]['.$start.']" size="1" style="width:49px"><option value="0">MM</option>';
		for($i=1;$i<=12;$i++){
			$text2display .= '<option value="'.$i.'"';
			if($i==$month)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.str_pad($i,2,'0',STR_PAD_LEFT).'</option>';
		}
		$text2display .= '</select>';
		$start++;
		$text2display .= '<select type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.'][data]['.$start.']" size="1" style="width:49px"><option value="0">DD</option>';
		for($i=1;$i<=31;$i++){
			$text2display .= '<option value="'.$i.'"';
			if($i==$day)
				$text2display .= ' selected="selected"';
			$text2display .= '>'.str_pad($i,2,'0',STR_PAD_LEFT).'</option>';
		}
		$text2display .= '</select>';
		return $text2display;
	}

	protected function display_percent($id=0,$type='spec',$rate=0,$text='Rating',$suffix=' / 100',$width=120){
		$text2display = '';
		$text2display .= '<select type="text" name="'.$this->name.'['.$this->tagcode.']['.$this->instanceNumber.']['.$type.']['.$id.']" size="1" style="width:'.$width.'px"><option value="0">'.$text.'</option>';
		$rate_on_100 = ($rate*100)/255;
		$step = 5;
		$special_display = false;
		for($i=0;$i<=100;$i+=5){
			if(($rate_on_100<=$i) && ($special_display==false)){
				$text2display .= '<option value="'.$rate.'" selected="selected">'.round($rate_on_100,2).$suffix.'</option>';
				$special_display = true;
			}
			if($i!=$rate_on_100){
				$text2display .= '<option value="'.intval(($i*255)/100).'"';
				if($i==$rate_on_100)
					$text2display .= ' selected="selected"';
				$text2display .= '>'.$i.$suffix.'</option>';
			}
		}
		$text2display .= '</select>';
		return $text2display;
	}

	public function display_flags(){
		$text2display = '<input type="checkbox" name="'.$this->name.'['.$this->value->id.']['.$this->instanceNumber.'][flag][1]" value="1"';
		if(($this->value->flag & 32768) == 32768)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><input type="checkbox" name="'.$this->name.'['.$this->value->id.']['.$this->instanceNumber.'][flag][2]" value="1"';
		if(($this->value->flag & 16384) == 16384)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><input type="checkbox" name="'.$this->name.'['.$this->value->id.']['.$this->instanceNumber.'][flag][3]" value="1"';
		if(($this->value->flag & 8192) == 8192)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><input type="checkbox" name="'.$this->name.'['.$this->value->id.']['.$this->instanceNumber.'][flag][4]" value="1"';
		if(($this->value->flag & 128) == 128)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><input type="checkbox" name="'.$this->name.'['.$this->value->id.']['.$this->instanceNumber.'][flag][5]" value="1"';
		if(($this->value->flag & 64) == 64)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><input type="checkbox" name="'.$this->name.'['.$this->value->id.']['.$this->instanceNumber.'][flag][6]" value="1"';
		if(($this->value->flag & 32) == 32)
			$text2display .= ' checked="checked"';
		$text2display .= ' />';
		return $text2display;
	}


	public static function get_flag($c) {
		$flag1 = 0;
		$flag2 = 0;
		for($i=0;$i<3;$i++)
			if(isset($c[$i+1]) && $c[$i+1]==1)
				$flag1 += 1<<(7-$i);
		for($i=0;$i<3;$i++)
			if(isset($c[$i+4]) && $c[$i+4]==1)
				$flag2 += 1<<(7-$i);
		return chr($flag1).chr($flag2);
	}

	public static function get_num($size,$n) {
		$value = str_repeat(chr(255),$n);
		for($i=0;$i<$n;$i++){
			$buffer = 0;
			for($j=0;$j<8;$j++)
				$buffer += (($size & pow(2,(8*$i+$j))) == pow(2,(8*$i+$j)))?(1<<$j):0;
			$value[$n-$i-1] = chr($buffer);
		}
		return $value;
	}

	protected static function get_int($val){
		$length = strlen($val);
		$buffer = (float)0;
		for($i=0;$i<$length;$i++){
			$buffer += ord($val[$i]) << (($length-$i-1)*8);
		}
		return $buffer;
	}

	protected static function get_short_signed($val){
		if($val<0) 	// two's complement
			return (($val*-1) ^ 0xFFFF) + 1;
		elseif(($val & 0x8000)==0x8000)
			return (($val ^ 0xFFFF) + 1)*-1;
		elseif($val>0xFFFF)
			return $val & 0xFFFF;
		else
			return $val;
	}

	protected static function text2code($encoding,$text) {
		if(in_array('mbstring',get_loaded_extensions())){
			if($encoding==1)
				return chr(0xfe).chr(0xff).mb_convert_encoding($text, self::$ENCODING[$encoding]);
			else
				return $text;
		}
		else
			return $text;
	}

	protected static function code2text($encoding,$text) {
		if(in_array('mbstring',get_loaded_extensions())){
			if($encoding==1)
				return mb_convert_encoding(substr($text,2), self::$ENCODING[0], self::$ENCODING[1]);	// We remove the 2 first chars
			else
				return $text;
		}
		else
			return $text;
	}

	protected static function check_newline($text,$authorized){
		if($authorized==true)
			return str_replace("\r\n", "\n", $text);
		else{
			$tmp = str_replace("\r\n", "", $text);
			return str_replace("\n", "", $tmp);
		}
	}

	protected static function get_string($encoding,$value,&$count) {
		$temp = '';
		$byte = '';
		$zerobyte = 0;
		while($zerobyte<self::$zero[$encoding]) {
			$byte = (isset($value{$count})?$value{$count}:chr(0));
			$temp .= $byte;
			$count++;
			if($byte=="\x00")
				$zerobyte++;
			else
				$zerobyte = 0;
		}
		$temp = substr($temp,0,strlen($temp)-1);	// We remove chr(0)
		return $temp;
	}

	protected static function get_size($octet,$round_size=0){
    		$unite_spec = array('byte','KB','MB','GB','TB');
		$count=0;
		while($octet>=1024){
			$count++;
			$octet/=1024;
		}
		if($round_size>0)
			$octet = round($octet,$round_size);
		return($octet.' '.$unite_spec[$count]);
	}

	public function getTagCode(){
		return $this->tagcode;
	}

	public function getTagName(){
		return $this->tagname;
	}

	public function getDeprecated(){
		return $this->deprecated;
	}
};
?>