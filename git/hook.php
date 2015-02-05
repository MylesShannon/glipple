<?php
echo "hook.php";

exec("cd /var/www/html && git pull");

/*
if ( $_POST['payload'] ) {
  shell_exec( 'cd /srv/www/git-repo/ && git reset --hard HEAD && git pull' );
}
*/
?>