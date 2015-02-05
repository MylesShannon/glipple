<html>
    <head>
        <title>AmpJuke Flash Player</title>
		<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
		<META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
		<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
        <script type="text/javascript" src="./xspf/swfobject.js"></script>
        <script type="text/javascript">


        function createPlayer(theFile) {
            var flashvars = {
                    file:theFile, 
					bufferlength:"20", 
					quality:"high",
                    autostart:"true",
                    playlistsize:"100",
                    repeat:"list", 
					displaytitle:"true",
                    stretching:"uniform",
                    volume:"50",  	
					state:"BUFFERING",
                    playlist:"bottom"
            }
            var params = {
                    allowfullscreen:"true", 
                    allowscriptaccess:"always"
            }
            var attributes = {
                    id:"player1",  
                    name:"player1"
            }
            swfobject.embedSWF("./xspf/player.swf", "placeholder1", "400", "370", "9.0.115", false, flashvars, params, attributes);
        }
        </script>
    </head>
<?php
die('Sorry...');
parse_str($_SERVER["QUERY_STRING"]);
session_start();
$ok=0;
if (isset($_SESSION['login'])) { $ok++; }
if (isset($_SESSION['passwd'])) { $ok++; }
if ($ok!=2) { 
	session_destroy();
	include_once('disp.php');
	redir("login.php");
    die('Not logged in.');
}

$f='./tmp/'.$u.'.xspf?r='.rand(0,32000); // Because of FRIGGIN Internet Explorer...
?>	
    <body onload="createPlayer('<?php echo $f;?>')" bgcolor="#cccccc">
            <div id="placeholder1"></div>
    </body>
</html>

