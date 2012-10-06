<?php 
include('auth.php');
if( !userIsAdmin() ) {
	die("Administrative priviledge is required.");
}

include ('netdisk.functions.php');

$block_dev_name = isset($_REQUEST['dev'])? $_REQUEST['dev'] : die("Need scheduler file location.");
$filename = "/sys/block/$block_dev_name/queue/scheduler";

$new_scheduler = isset($_REQUEST['sch'])? $_REQUEST['sch'] : "trustmessiahjesus" ;

/* let us double check that the scheduler type exists on this device. */
$curr_scheduler = file_get_contents($filename);
if (!strpos($curr_scheduler, $new_scheduler)) {
	die("Invalid scheduler: $new_scheduler");
}		

/* try to set the new scheduler */
$output = Array();
$return = null;
$message_type = 3; 
$error_log = "./netdisk.error.log";
$err_message = "There may be more information in the local log file.";

$command = "sudo ".$WEB_ROOT . $INSTALL_DIR. "/php/setscheduler $new_scheduler $filename";
exec($command,$results,$return);
if($return !== 0) {
	$message = date('Y-m-d H:i:s'). "|setscheduler.php|set|failed|$command.\n";
	$message = date('Y-m-d H:i:s'). "|setscheduler.php|set|failed|returned: $return.\n";
	error_log($message, $message_type, $error_log);
	foreach ($results as $v ){
		/* log any errors returned by the system */
		$message = date('Y-m-d H:i:s'). "|setscheduler.php|set|failed|output|$v.\n";
		error_log($message, $message_type, $error_log);
		if (strpos($v, 'Permission denied')) $err_message = "Permission denied.";
	}

	$message = "Could not set scheduler. $err_message" ;

} else {
	
	$message = "Success! ";
	
}
			
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>NDAS Drive Scheduler</title>
	<link rel="stylesheet" href="../css/styles.css" type="text/css" />

</head>
<body>
<h2>Set Device Scheduler</h2>
<center>
<br><br>
<?php


echo $message;


?>
<br>
<a href='javascript:window.history.back()'>Back</a> or <a href='javascript:self.close()'>Close window</a>
</center>
</body>
</html>