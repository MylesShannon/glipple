<?php

exec("telnet localhost 1234 && request.on_air", $output);

var_dump($output);
?>