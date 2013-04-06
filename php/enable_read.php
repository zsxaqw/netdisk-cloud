<?php 
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

$slot = isset($_POST['slot']) ? $_POST['slot'] : null;

include('netdisk.functions.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	<title>Enable NDAS RO</title>
</head>
<?php

echo "<body>";

?>
<?php
if ($slot){

	$output = Array();
	$return = null;
	$message_type = 3; 
	$error_log = $LOCAL_LOG_FILE;

 	echo "Enable slot \"$slot\" in read only mode. <br>";
			
	$command = "sudo /usr/sbin/ndasadmin enable -s $slot -o r 2>&1";
	exec($command,$output,$return);
	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|enable_read.php|enablero|failed|$command.\n";
		error_log($message, $message_type, $error_log);
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s'). "|enable_read.php|enablero|failed|$v.\n";
			error_log($message, $message_type, $error_log);
		}
		$reg_name = ndasGetRegisteredNameFromSlot($slot);
		echo "<br>Failed to enable $reg_name in read only mode. There may be more 
			information in the logs.<br>";
	} else {
		echo "<br>OK!<br>";
	}
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
