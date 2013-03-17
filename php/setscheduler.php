<?php 
include('auth.php');
if( !userIsAdmin() ) {
	die("Administrative priviledge is required.");
}

include ('netdisk.functions.php');

$new_scheduler = isset($_REQUEST['sch'])? $_REQUEST['sch'] : null;
$block_dev_name = isset($_REQUEST['dev'])? $_REQUEST['dev'] : null;
$message = '';

if ( !$block_dev_name ) $message = "Need block device name.<br>";
if ( !$new_scheduler ) $message .= "Need scheduler setting.<br>";
			
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>NDAS Drive Scheduler</title>
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />

</head>
<body>
<h2>Set Device Scheduler</h2>
<center>
<br><br>
<?php

if (empty($message))
	$message = ndasSetBlockDeviceScheduler($new_scheduler,$block_dev_name);

echo $message;

?>
<br>
<a href='javascript:window.history.back()'>Back</a> or <a href='javascript:self.close()'>Close window</a>
</center>
</body>
</html>