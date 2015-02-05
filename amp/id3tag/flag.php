<?php
define('IN_ID',true);
define('NO_HTML',true);
include('modules/header.php');

echo '<html>
<head>
<title>Flags Explanation</title>
<link type="text/CSS" rel="stylesheet" href="style.css">
</head>
<body bgcolor="#ffffff" alink="#ff0000" link="#0000ff" vlink="#800080" text="#000000" leftmargin=0 topmargin=0 marginheight=0 marginwidth=0>';
$table = new LSTable(2, 2, '100%', $null);
$table->setTitle('Flags Explanation');
$table->addCellAttribute(0, 0, 'colspan', '2');
$table->setText(0, 0, '
<b>1. Tag alter preservation</b><br><font class="littletext">This flag tells the software what to do with this frame if it is unknown and the tag is altered in any way. This applies to all kinds of alterations, including adding more padding and reordering the frames.<br><center>0 = Frame should be preserved.<br>1 = Frame should be discarded.</center></font><p>
<b>2. File alter preservation</b><br><font class="littletext">This flag tells the software what to do with this frame if it is unknown and the file, excluding the tag, is altered. This does not apply when the audio is completely replaced with other audio data.<br><center>0 = Frame should be preserved.<br>1 = Frame should be discarded.</center></font><p>
<b>3. Read only</b><br><font class="littletext">This flag, if set, tells the software that the contents of this frame is intended to be read only. Changing the contents might break something, e.g. a signature. If the contents are changed, without knowledge in why the frame was flagged read only and without taking the proper means to compensate, e.g. recalculating the signature, the bit should be cleared.</font><p>
<b>4. Compression</b><br><font class="littletext">This flag indicates whether or not the frame is compressed.<br><center>0 = Frame is not compressed.<br>1 = Frame is compressed using zlib with 4 bytes for \'decompressed size\' appended to the frame header.</center></font><p>
<b>5. Encryption</b><br><font class="littletext">This flag indicates wether or not the frame is enrypted. If set one byte indicating with which method it was encrypted will be appended to the frame header.<br><center>0 = Frame is not encrypted.<br>1 = Frame is encrypted.</center></font><p>
<b>6. Grouping identity</b><br><font class="littletext">This flag indicates whether or not this frame belongs in a group with other frames. If set a group identifier byte is added to the frame header. Every frame with the same group identifier belongs to the same group.<br><center>0 = Frame does not contain group information.<br>1 = Frame contains group information.</center></font></td></tr>');
$table->setText(1, 0, '<b>Checkbox Checked means value 1.</b>');
$table->addCellAttribute(1, 1, 'align', 'right');
$table->setText(1, 1, '[ <a href="javascript:close()">Close</a> ]');
$table->draw();

echo '</body>
</html>';
?>