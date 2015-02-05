<?php
#!/usr/bin/php
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

echo "hook.php";

exec("cd /var/www/html && git pull");

/*
if ( $_POST['payload'] ) {
  shell_exec( 'cd /srv/www/git-repo/ && git reset --hard HEAD && git pull' );
}
*/
?>