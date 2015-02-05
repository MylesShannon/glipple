<?php // 0.8.8: Scheduler VERSION 1

if (!isset($_SESSION['login'])) {
	include_once("disp.php");
	// 0.6.3: If we need to redirect (session timeout), but we have enabled "remember me"
	// then remember the url parameters as well:
	redir("login.php?".$_SERVER["QUERY_STRING"]);
    exit;
}

if (!isset($act)) {
    $act='disp';
}

//require_once("sql.php");
//require_once("set_td_colors.php");
//require_once("disp.php");

echo headline($what,'Scheduler','');
echo std_table("ampjuke_actions_table","");
echo '<tr><td> </td></tr>';
echo '</table>';
echo std_table("ampjuke_content_table","ampjuke_content");


if ($act=='disp') {
    echo '<FORM NAME="scheduler" method="POST" action="./?what=scheduler&act=store">';	
    echo '<tr><td>'.xlate('Scheduler').':</td><td>';
    echo '</td></tr>';

    echo '<tr><td colspan="2"><textarea name="schedule" rows="20" cols="50">';
    echo file_get_contents('./schedule.php');
    echo '</textarea>';

    echo '</td></tr>';

    echo '<tr><td colspan="2" align="center"><input type="submit" value="'.xlate('Save & continue').'">';

    echo '</FORM></table><br><hr><br>';
}


if ($act=='store') {
    //var_dump($_POST['schedule']);
    $handle=fopen('./schedule.new', 'w');
    fwrite($handle,$_POST['schedule']);
    fclose($handle);
    //echo '<br>'.file_get_contents('./schedule.new');
    //kill('./schedule.php');
    rename('./schedule.new','schedule.php');
    $data=file_get_contents('./schedule.php');
    $data=str_replace(chr(10),'<br>',$data);
    echo '<tr><td>'.xlate('Scheduler').':</td><td>';
    echo '</td></tr>';
    
    echo '<tr><td colspan="2">'.$data.'</td></tr></table>';
}

?>

