<?php
	if(!defined('IN_ID'))die('You are not allowed to access to this page.');
	define('FORM_VALUE','id3v2_val');
	function draw_line($table, $line, $tag){
		static $del_counter = 0;

		$class = getClass(constant('FORM_VALUE'), $tag);

		// TAG
		$add_text = $class->getDeprecated()?' <font color="#ff0000">Deprecated</font>':'';
		$table->setText($line,0,'<font class="littletext">'.$class->getTagName().' ('.$class->getTagCode().')'.$add_text.'</font>');

		// FLAG
		$table->addCellAttribute($line,1,'align','center');
		$table->setText($line,1,$class->display_flags());

		// Special
		$table->addCellAttribute($line,2,'align','center');
		$table->setText($line,2,$class->display_spec());

		// Data
		$table->addCellAttribute($line,3,'align','center');
		$table->setText($line,3,$class->display_data());


		$table->setText($line,4,'<a href="'.$_SERVER['PHP_SELF'].'?filename='.$_GET['filename'].'&amp;del_id='.$del_counter++.'&amp;del_code='.$tag->id.'"><img src="images/x.gif" width="15" height="15" border="0" alt="X" /></a>');
	}

	$tag_explain = array(
		'AENC'=>'Audio encryption',
		'APIC'=>'Attached picture',
		'ASPI'=>'Audio seek point index',
		'COMM'=>'Comments',
		'COMR'=>'Commercial frame',
		'ENCR'=>'Encryption method registration',
		'EQU2'=>'Equalization (2)',
		'EQUA'=>'Equalization',					/* Deprecated */
		'ETCO'=>'Event timing codes',
		'GEOB'=>'General encapsulated object',
		'GRID'=>'Group identification registration',
		'IPLS'=>'Involved people list',				/* Deprecated */
		'LINK'=>'Linked information',
		'MCDI'=>'Music CD identifier',
		'MLLT'=>'MPEG location lookup table',
		'OWNE'=>'Ownership frame',
		'PCNT'=>'Play counter',
		'POPM'=>'Popularimeter',
		'POSS'=>'Position synchronisation frame',
		'PRIV'=>'Private frame',
		'RBUF'=>'Recommended buffer size',
		'RVA2'=>'Relative volume adjustment (2)',
		'RVAD'=>'Relative volume adjustment',			/* Deprecated */
		'RVRB'=>'Reverb',
		'SEEK'=>'Seek frame',
		'SIGN'=>'Signature frame',
		'SYLT'=>'Synchronized lyric/text',
		'SYTC'=>'Synchronized tempo codes',
		'TALB'=>'Album/Movie/Show title',
		'TBPM'=>'BPM (beats per minute)',
		'TCOM'=>'Composer',
		'TCON'=>'Content type',
		'TCOP'=>'Copyright message',
		'TDAT'=>'Date',						/* Deprecated */
		'TDEN'=>'Encoding time',
		'TDLY'=>'Playlist delay',
		'TDOR'=>'Original release year',
		'TDRC'=>'Recording time',
		'TDRL'=>'Release time',
		'TDTG'=>'Tagging time',
		'TENC'=>'Encoded by',
		'TEXT'=>'Lyricist/Text writer',
		'TFLT'=>'File type',
		'TIME'=>'Time',						/* Deprecated */
		'TIPL'=>'Involved people list',
		'TIT1'=>'Content group description',
		'TIT2'=>'Title/songname/content description',
		'TIT3'=>'Subtitle/Description refinement',
		'TKEY'=>'Initial key',
		'TLAN'=>'Language(s)',
		'TLEN'=>'Length',
		'TMCL'=>'Musition credits list',
		'TMED'=>'Media type',
		'TMOO'=>'Mood',
		'TOAL'=>'Original album/movie/show title',
		'TOFN'=>'Original filename',
		'TOLY'=>'Original lyricist(s)/text writer(s)',
		'TOPE'=>'Original artist(s)/performer(s)',
		'TORY'=>'Original release year',			/* Deprecated */
		'TOWN'=>'File owner/licensee',
		'TPE1'=>'Lead performer(s)/Soloist(s)',
		'TPE2'=>'Band/orchestra/accompaniment',
		'TPE3'=>'Conductor/performer refinement',
		'TPE4'=>'Interpreted, remixed, or otherwise modified by',
		'TPOS'=>'Part of a set',
		'TPRO'=>'Produced notice',
		'TPUB'=>'Publisher',
		'TRCK'=>'Track number/Position in set',
		'TRDA'=>'Recording dates',				/* Deprecated */
		'TRSN'=>'Internet radio station name',
		'TRSO'=>'Internet radio station owner',
		'TSIZ'=>'Size',						/* Deprecated */
		'TSOA'=>'Album sort order',
		'TSOP'=>'Performer sort order',
		'TSOT'=>'Title sort order',
		'TSRC'=>'ISRC (international standard recording code)',
		'TSSE'=>'Software/Hardware and settings used for encoding',
		'TSST'=>'Set subtitle',
		'TYER'=>'Year',						/* Deprecated */
		'TXXX'=>'User defined text information frame',
		'UFID'=>'Unique file identifier',
		'USER'=>'Terms of use',
		'USLT'=>'Unsychronized lyric/text transcription',
		'WCOM'=>'Commercial information',
		'WCOP'=>'Copyright/Legal information',
		'WOAF'=>'Official audio file webpage',
		'WOAR'=>'Official artist/performer webpage',
		'WOAS'=>'Official audio source webpage',
		'WORS'=>'Official internet radio station homepage',
		'WPAY'=>'Payment',
		'WPUB'=>'Publishers official webpage',
		'WXXX'=>'User defined URL link frame'
	);

	$tag_avail = array(
		'APIC',
		'COMM',
		'COMR',
		'EQU2',
		'IPLS',
		'OWNE',
		'PCNT',
		'POPM',
		'RVA2',
		'RVRB',
		'TALB',
		'TBPM',
		'TCOM',
		'TCON',
		'TCOP',
		'TDAT',
		'TDEN',
		'TDLY',
		'TDOR',
		'TDRC',
		'TDRL',
		'TDTG',
		'TENC',
		'TEXT',
		'TFLT',
		'TIME',
		'TIPL',
		'TIT1',
		'TIT2',
		'TIT3',
		'TKEY',
		'TLAN',
		'TLEN',
		'TMCL',
		'TMED',
		'TMOO',
		'TOAL',
		'TOFN',
		'TOLY',
		'TOPE',
		'TORY',
		'TOWN',
		'TPE1',
		'TPE2',
		'TPE3',
		'TPE4',
		'TPOS',
		'TPRO',
		'TRCK',
		'TRDA',
		'TRSN',
		'TRSO',
		'TSIZ',
		'TSOA',
		'TSOP',
		'TSOT',
		'TSRC',
		'TSSE',
		'TSST',
		'TXXX',
		'TYER',
		'USER',
		'USLT',
		'WCOM',
		'WCOP',
		'WOAF',
		'WOAR',
		'WOAS',
		'WORS',
		'WPAY',
		'WPUB',
		'WXXX',
		'OTHER'
	);

	$tag2 = new mp3_id3v2();
	if($tag2->load_file($_GET['filename'])){

		/** WRITE TAG **/
		if(isset($_POST['id3v2_posted']) && intval($_POST['id3v2_posted'])==1){
			if(isset($_POST['id3v2_enable']) && $_POST['id3v2_enable']==1){
				$buffer = '';
				if(isset($_POST[constant('FORM_VALUE')])){
					$tagInFile = $tag2->get_tag();
					$tag_write = $_POST[constant('FORM_VALUE')];
					reset($tag_write);
					// We read EACH tag sent
					while(list($key1,$val1) = each($tag_write)){
						// We read EACH value sent by the tag (all instances)
						while(list($key2,$val2) = each($val1)){
							$out = '';
							if(isset($val2['notsupported']) && intval($val2['notsupported'])===1){
								// We take from the file directly
								// But we need to take the GOOD number... so we take it
								// and we delete it from the table, if we find another one with
								// the same tag, we will write it :)
								while(list($tkey, $tval) = each($tagInFile)){
									if($tval->id==$key1){
										$out = $tval->id.frame::get_num($tval->size,4).frame::get_flag($tval->flag).$tval->data;
										unset($tagInFile[$tkey]);
									}
								}
							} else {
								$tag = new TagValue();
								$tag->id = $key1;
								$class = getClass('', $tag);
								$out =  $class->save($val2);
							}
							$buffer .= $out;


						}
					}
				}

				$tag2->set_version($_POST['id3v2_header_version1'],$_POST['id3v2_header_version2']);
				$tag2->set_padding($_POST['id3v2_header_padding']);
				// HeaderFlags
				$header_flags = 0;
				$header_ext_flags = 0;
				$header_flags += (isset($_POST['id3v2_header_unsynchronisation']) && $_POST['id3v2_header_unsynchronisation']==1)?128:0;
				$header_flags += (isset($_POST['id3v2_header_extended']) && $_POST['id3v2_header_extended']==1)?64:0;
				$header_flags += (isset($_POST['id3v2_header_experimental']) && $_POST['id3v2_header_experimental']==1)?32:0;
				$header_ext_flags += (isset($_POST['id3v2_header_extended_crc']) && $_POST['id3v2_header_extended_crc']==1)?32768:0;
				$tag2->set_header_flags($header_flags);
				$tag2->set_extended_flags($header_ext_flags);
				$tag2->write_file($buffer);
			}
			else{
				$tag2->remove_tag();
				$tag2->write_file();

			}
		}

		/** DEL TAG **/
		if(isset($_GET['del_id']) && isset($_GET['del_code'])){
			$tag2->remove_frame($_GET['del_id'], $_GET['del_code']);
			$buffer = $tag2->get_buffer();
			$tag2->write_file($buffer);
		}


		echo '<script language="JavaScript" type="text/javascript">function addtag(form){form.id3v2_posted.value=0;form.submit();}</script>';
		/** READ TAG **/
		$tag2_readable = $tag2->read_tag();
		$version2 = $tag2->get_version();
		$padding_size = $tag2->get_padding();
		$frame_known = $tag2->get_frame_known();
		$frame_unknown = $tag2->get_frame_unknown();
		$real_size = size_human($tag2->get_size(),2);
		if($tag2->get_size() >= 1024)
			$real_size2 = ' <font class="littletext">('.$tag2->get_size().' bytes)</font>';
		else
			$real_size2 = '';
		echo '<input type="hidden" name="id3v2_posted" value="1">';

		/** START DISPLAY HEADER **/
		$table = (isset($table))?$table:$null;
		$table2 = new LSTable(3,1,'100%',$table);
		$table2->setTemplate('tpl_NOTITLE');
		$table2->addRowAttribute(0,'class','table_title');
		$table2->addCellAttribute(0,0,'align','center');
		$table2->addCellAttribute(0,0,'colspan','2');
		$text2display = '<input type="checkbox" name="id3v2_enable" value="1" id="id3v2_enable"';
		if($tag2_readable == true)
			$text2display .= ' checked="checked"';
		$text2display .= ' /><font color="#ffffff"><b><label for="id3v1_enable">ID3Tag v2.4</label></b></font>';
		$table2->setText(0,0,$text2display);

		$table2_1 = new LSTable(2,2,'100%',$table2);
		$table2_1->setTemplate('tpl_NOPAD');
		$table2_1->addAllCellsInRowAttribute(0, 'width', '50%');
		$table2_1->addRowAttribute(0,'height','20');
		$table2_1->addRowAttribute(0,'class','table_title2');
		$table2_1->addAllCellsInRowAttribute(0,'align','center');
		$table2_1->setText(0,0,'<font color="#ffffff"><b>Header</b></font>');
		$table2_1->setText(0,1,'<font color="#ffffff"><b>Other Flags</b></font>');
		$table2_1->addAllCellsInRowAttribute(1,'valign','top');

		$table2_2 = new LSTable(6,2,'100%',$table2_1);
		$table2_2->setTemplate('tpl_NOSPACE');
		$table2_2->addCellAttribute(0,0,'width','50%');
		$table2_2->setText(0,0,'Version');
		$table2_2->setText(0,1,'2.<input type="text" name="id3v2_header_version1" value="'.$version2[0].'" size="1" style="width:25px" />.<input type="text" name="id3v2_header_version2" value="'.$version2[1].'" size="1" style="width:25px" />');
		$table2_2->setText(1,0,'Size <font class="littletext">(including padding)</font>');
		$table2_2->setText(1,1,$real_size.$real_size2);
		$table2_2->setText(2,0,'Padding <font class="littletext">(byte)</font>');
		$table2_2->setText(2,1,'<input type="text" name="id3v2_header_padding" value="'.$padding_size.'" size="3" style="width:40px" />');
		$table2_2->addRowAttribute(3,'class','table_title2');
		$table2_2->addCellAttribute(3,0,'align','center');
		$table2_2->addCellAttribute(3,0,'colspan','2');
		$table2_2->setText(3,0,'<font color="#ffffff"><b>Frames</b></font>');
		$table2_2->setText(4,0,'Known');
		$table2_2->setText(4,1,$frame_known);
		$table2_2->setText(5,0,'Unknown');
		$table2_2->setText(5,1,$frame_unknown);

		$table2_3 = new LSTable(5,2,'100%',$table2_1);
		$table2_3->setTemplate('tpl_NOSPACE');
		$table2_3->addCellAttribute(0,0,'width','50%');
		$table2_3->addCellAttribute(0,0,'colspan','2');
		$text2display = '<input type="checkbox" name="id3v2_header_extended" value="1" id="id3v2_header_extended"';
		if($tag2->get_header_flags() & 64)
			$text2display .= ' checked="checked"';
		$text2display .=' /><label for="id3v2_header_extended">Extended Header</label>';
		$table2_3->setText(0,0,$text2display);
		$table2_3->addCellAttribute(1,0,'colspan','2');
		$text2display = '<input type="checkbox" name="id3v2_header_extended_crc" value="1" id="id3v2_header_extended_crc"';
		if(($id3tag_crc32 = $tag2->get_extended_crc32()) !== FALSE)
			$text2display .= ' checked="checked"';
		$text2display .=' /><label for="id3v2_header_extended_crc">CRC32 Available</label>';
		if($id3tag_crc32 !== FALSE)
			$text2display .= sprintf(' <font class="littletext">(0x%08X)</font>',$id3tag_crc32);
		$table2_3->setText(1,0,$text2display);
		$table2_3->addCellAttribute(2,0,'colspan','2');
		$text2display = '<input type="checkbox" name="id3v2_header_experimental" value="1" id="id3v2_header_experimental"';
		if($tag2->get_header_flags() & 32)
			$text2display .= ' checked="checked"';
		$text2display .=' /><label for="id3v2_header_experimental">Experimental Tag</label>';
		$table2_3->setText(2,0,$text2display);

		$table2_3->addCellAttribute(3,0,'colspan','2');
		$text2display = '<input type="checkbox" name="id3v2_header_unsynchronisation" value="1" id="id3v2_header_unsynchronisation"';
		if($tag2->get_header_flags() & 128)
			$text2display .= ' checked="checked"';
		$text2display .=' /><label for="id3v2_header_unsynchronisation">Unsynchronisation</label> <font class="littletext">(Not Supported)</font>';
		$table2_3->setText(3,0,$text2display);

		$table2_3->addRowAttribute(4,'height','20');
		$table2_3->addRowAttribute(4,'class','table_title2');
		$text2display = '<option value="">Insert New Tag</option>';
		$c = count($tag_avail);
		for($i=0;$i<$c;$i++)
			$text2display .= '<option value="'.$tag_avail[$i].'">'.$tag_avail[$i].'</option>';
		$table2_3->setText(4,0,'<font color="#ffffff"><b>Add Tag : </b></font><select name="add_tag" size="1" style="width:150px">'.$text2display.'</select><input type="button" value="Continue" onclick="addtag(this.form);" />');
		/** END DISPLAY HEADER **/

		/** START DISPLAY TAGS **/
		$table2_4 = new LSTable(2,5,'100%',$table2);
		$table2_4->setTemplate('tpl_NOTITLE');
		$table2_4->addCellAttribute(0,0,'align','center');
		$table2_4->addCellAttribute(0,0,'colspan','5');
		$table2_4->addRowAttribute(0,'height','20');
		$table2_4->addRowAttribute(0,'class','table_title2');
		$table2_4->setText(0,0,'<font color="#ffffff"><b>Available Frames</b></font>');
		$table2_4->addRowAttribute(1,'class','table_title3');
		$table2_4->addAllCellsInRowAttribute(1,'align','center');
		$table2_4->setText(1,0,'<font color="#ffffff"><b>Tags</b></font>');
		$table2_4->addCellAttribute(1,1,'width','120');
		$table2_4->setText(1,1,'<font color="#ffffff"><b><a href="javascript:;" onClick="window.open(\'flag.php\',\'FlagExplain\',\'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=350,left=50,top=20\');" style="color:#ffffff">Flags</a></b></font>');
		$table2_4->addCellAttribute(1,2,'width','120');
		$table2_4->setText(1,2,'<font color="#ffffff"><b>Special</b></font>');
		$table2_4->addCellAttribute(1,3,'width','200');
		$table2_4->setText(1,3,'<font color="#ffffff"><b>Data</b></font>');
		$table2_4->addCellAttribute(1,4,'width','15');
		$all_tags = $tag2->get_tag();
		$c = count($all_tags);
		$table2_4->insertRows(2,$c);
		for($i=0;$i<$c;$i++)
			draw_line($table2_4,$i+2,$all_tags[$i]);
		if(isset($_POST['add_tag']) && !empty($_POST['add_tag']) && (!isset($_POST['id3v2_posted']) || $_POST['id3v2_posted']==0)){
			$add_tag = new TagValue();
			$add_tag->id = $_POST['add_tag'];
			$table2_4->insertRows($table2_4->numRows(), 1);
			draw_line($table2_4,$i+2,$add_tag);
			$table2_4->addRowAttribute($i+2,'class','newtag');
			$table2_4->setText($i+2,0,$table2_4->text($i+2,0).' <font class="littletext" color="#ff0000">(Not added yet)</font>');
		}
		/** END DISPLAY TAGS **/
		$table2->insertRows($r = $table2->numRows(), 1);
		$table2->addCellAttribute($r,0,'align','center');
		$table2->setText($r,0,'<input type="submit" value="Send" />');
	}
	$tag2 = NULL;
?>