<?php
/*
get_configuration()
Gets a configuration value from db.php:
*/
function get_configuration($item, $entire_line = "0") { // 0.6.4: Added $entire_line
	$item='$'.$item;
	$handle=fopen("./db.php", "r");
	$found=0;
	$ret="";
	while (!feof($handle)) {
		$buffer=fgets($handle, 8192);
		if ($entire_line=="0") {
			$i=0;
			while ($found==0 && $i<=strlen($buffer)) {
				$tmp=explode("=", $buffer);
				if (strlen($tmp[0])>1) {
					if ($tmp[0]==$item) {
						$ret=rtrim($tmp[1]);
						$ret=ltrim($ret);
						$ret=str_replace(";","",$ret);
						$ret=str_replace('"','',$ret);
						$found=1;
						$i=strlen($buffer);
					}
				}
				$i++;
			}
		}
		// 0.6.4: Added this:
		if ($entire_line!="0") {
			$i=0;
			while ($found==0 && $i<=strlen($buffer)) {
				$tmp=explode('=', $buffer);
				if (strlen($tmp[0])>1) {
					if ($tmp[0]==$item) {
						// 0.7.7: Modified + NOT a solution I'm happy with, but here we go:
						if ($item=='$lyrics_path') {
							$ret=$tmp[1];
							if (isset($tmp[2])) { $ret.='='.$tmp[2]; }
							if (isset($tmp[3])) { $ret.='='.$tmp[3]; }
						}
						$a=array('"',';');
						$ret=str_replace($a,'',$ret);						
						$found=1;
						$i=strlen($buffer);
					}
				}
				$i++;
			}
		}				
	}
	fclose($handle);
	return $ret;
}	

// 0.7.1: Write/update $item in db.php with value $val:
function write_configuration($item,$val) {
	$in_handle=fopen("./db.php", "r");
	$out_handle=fopen("./db.tmp", "w");
	$found=0;
	
	while (!feof($in_handle)) {
	 	$buffer=fgets($in_handle,8192);
		$tmp=explode("=", $buffer);
		if (strlen($tmp[0])>1) {
			if ($tmp[0]==$item) {
			 	$found=1;
			 	fwrite($out_handle,$item.'='.$val . ';'. chr(13) . chr(10));
			} else {
			 	fwrite($out_handle,$buffer);
			}
		}
	}
	fclose($in_handle);
	fclose($out_handle);
	unlink("./db.php");		 	
	rename("./db.tmp", "./db.php");

	// If we didn't find the $item, then write it w. corresponding $val:
	if ($found==0) {
		$in_handle=fopen("./db.php", "r");
		$out_handle=fopen("./db.tmp", "w");

		while (!feof($in_handle)) {
	 		$buffer=fgets($in_handle,8192);
			if (substr($buffer,0,2)=='?>') { // =EOF -> insert value before that:
					fwrite($out_handle,$item.'='.$val.';' . chr(13) . chr(10));
				} 
			fwrite($out_handle,$buffer);
		}
		fclose($in_handle);
		fclose($out_handle);
		unlink("./db.php");		 	
		rename("./db.tmp", "./db.php");
	}		
}	

?>
