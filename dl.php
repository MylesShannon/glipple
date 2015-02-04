<?php
$filename = $_GET['file'];
$id = $_GET['id'];
$file = $id.$filename;
if (isset($file))
{
// Prevents access to something that is not in the same directory as this script
if(substr_count($file) > 0)
{
die('This should not happen.');
};
$len = filesize($file);
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: public');
header('Content-Description: File Transfer');
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename='.$file);
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.$len);
readfile($file);
}
else
{
die('No file specified.');
};
?>