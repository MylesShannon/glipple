<?php
echo '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>ID3Tag</title>
<link type="text/CSS" rel="stylesheet" href="./style.css" />

<?php
echo '<script language="JavaScript" type="text/javascript">function change_tflt(obj,textbox){var obj2 = eval("document.id3tag_modifier.elements[textbox+\'[data]\']");obj2.value = obj.options[obj.selectedIndex].value;obj.selectedIndex=0;}</script>';
echo '<script language="JavaScript" type="text/javascript">function change_tkey(code,textbox){var obj0 = eval("document.id3tag_modifier.elements[textbox+\'[data]\']");var obj1 = eval("document.id3tag_modifier.elements[\'temptkey_"+code+"\']");var obj2 = eval("document.id3tag_modifier.elements[\'temptkey2_"+code+"\']");var obj3 = eval("document.id3tag_modifier.elements[\'temptkey3_"+code+"\']");var m = "";if(obj3.checked)	m = "m";obj0.value = obj1.value + obj2.value + m;}</script>';
echo '<script language="JavaScript" type="text/javascript">function change_comr(obj,textbox){var obj0 = eval("document.id3tag_modifier.elements[textbox+\'[data][0]\']");var curr_currency = obj.options[obj.selectedIndex].text;var curr_currency_val = obj.options[obj.selectedIndex].value;var price = prompt( "Enter Price (decimal is .)\\nCurrent Currency : "+curr_currency,"");if(price!="" && price!=null){if(obj0.value.length!=0)obj0.value += \'/\';obj0.value += curr_currency_val + price;}obj.selectedIndex = 0;}</script>';
echo '<script language="JavaScript" type="text/javascript">function change2_comr(obj,textbox){var obj0 = eval("document.id3tag_modifier.elements[textbox+\'[data][0]\']");obj0.value = "";obj.checked = false;}</script>';
?>

</head>
<body>

<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>
<?php
if(isset($_GET['filename']))
	echo '?filename='.$_GET['filename'];
?>" name="id3tag_modifier" method="post">

<?php
/*
$table = new LSTable(1,1,'500',$null);
$table->setTitle('Select File');

// Scan Dir music
$handle = opendir($sys_conf['path']['music']);
$count = 0;
$text2display = '';
while ($file = readdir($handle)){
	if($file!='.' && $file!='..'){
		$text2display .= '<a href="'.$_SERVER['PHP_SELF'].'?filename='.$sys_conf['path']['music'].'/'.$file.'">'.$file.'</a><br />';
		$count++;
	}
}
if($count==0)
	$text2display .= '<div align="center">No File Found</div>';

$table->setText(0,0,$text2display);
$table->draw();
*/
?>