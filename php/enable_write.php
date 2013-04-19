<?php 
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

$slot = isset($_POST['slot']) ? $_POST['slot'] : null;
$requestrw = isset($_POST['requestaccess']) ? $_POST['requestaccess'] : false; 

include('netdisk.functions.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	<title>Enable NDAS RW</title>
</head>
<?php

if ( $requestrw === "y" ) {
	echo "<body onload=\"onTimer()\">";
	}
else {
	echo "<body>";
	}
?>
<?php
if ($slot){

	$output = Array();
	$return = null;
	$message_type = 3; 
	$error_log = "./netdisk.error.log";
	$retry = '';

	/* request rw access or attempt to enable */
	if ( $requestrw === "y" ) {
		$command = escapeshellcmd("sudo /usr/sbin/ndasadmin request -s $slot 2>&1");
		$message = date('Y-m-d H:i:s'). "|enable_write.php|requestrw|attempt|$command.\n";
		ndasPhpLogger(5,$message);
		exec($command,$results,$return);
		if ($return > 0) {
			$message = date('Y-m-d H:i:s'). "|enable_write.php|requestrw|failed|$command.\n";
			ndasPhpLogger(1,$message);
			foreach ($results as $v ){
				/* log any errors returned by the system */
				$message = date('Y-m-d H:i:s'). "|enable_write.php|requestrw|failed|output|$v.\n";
				ndasPhpLogger(1,$message);
				echo "An error occurred with the write permission request. 
						There may be more information in the server log. ";				 
			}
		} else {
			echo "OK! A write permission request was sent. <br>";
			echo "Try again<div style=\"display:inline\" id=\"changewhendone\"> 
				to enable in <div style=\"display:inline\" id=\"mycounter\"></div> seconds.</div>
				<script>
				i = 16;
				function onTimer() {
	  				i--;
					if (i < 0) {
		  				document.getElementById('changewhendone').innerHTML = ' now!';
		  				document.getElementById('enablerwform').style.display = 'inline';	  				
					} else {
						document.getElementById('mycounter').innerHTML = i;
						setTimeout(onTimer, 1000);
					}
				}
				
				</script>";
			echo "<form style='display:none' id='enablerwform' 
					name='enable' action='./enable_write.php' method='post'>";
			echo "<input value='Enable RW' type=submit>";                   
			echo "<input name=slot value=\"".$slot."\" type=hidden>";   
			echo "</form>";				  
		}
		
	} else {

		echo "Enable slot \"$slot\" in exclusive read/write mode. <br>";
				
		$command = escapeshellcmd("sudo /usr/sbin/ndasadmin enable -s $slot -o w 2>&1");
		exec($command,$output,$return);
		if ($return > 0) {
			$message = date('Y-m-d H:i:s'). "|enable_write.php|enablerw|failed|$command.\n";
			error_log($message, $message_type, $error_log);
			foreach ($output as $v ){
				$message = date('Y-m-d H:i:s'). "|enable_write.php|enablerw|failed|$v.\n";
				error_log($message, $message_type, $error_log);
			
				/* check if the write key exists */
				if (strpos($v,'valid write key')){
					/* see if the write key is mentioned in devs */
					$dev_name = ndasGetRegisteredNameFromSlot($slot);
					$command = 'grep '.$dev_name.' /proc/ndas/devs | awk \'{print $3}\' 2>&1';
					exec($command,$results,$return);
					if($results[0] !== 'Yes'){
						echo "Write Key is missing!<br>";					
					}
									
				}
				
				/* see if it mentions request permission */
				if (strpos($v,'exclusively accessed by other')){
					echo '<br>Exclusive write permission is required but another
						host may have the write control of this device. You can
						send a request to surrender write control.<br>
						<form name="requestrwcontrol" action="enable_write.php"
							method="post">
						<table width="280"><tr><td align="right"><input type="submit" 
							value="Send Request"></td></tr></table>
						<input type="hidden" name="slot" value="'.$slot.'">
						<input type="hidden" name="requestaccess" value="y">
						</form>';
				}
			}
		} else {
			echo "<br>OK!<br>";
		}
	}//request access or attempt to enable
} else {
	echo "Invalid input.";	
}
?>

<script>
	opener.location.href='./list.php'
</script>
<br>
<a href='javascript:self.close()'>Close window</a>



</body>
</html>
