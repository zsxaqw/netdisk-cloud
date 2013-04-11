<?php 
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

include('netdisk.functions.php');

$ndas_dev=isset($_GET['device'])? $_GET['device'] : false;
$slot=isset($_GET['slot'])? $_GET['slot'] : false;

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	<title>Disable NDAS</title>
</head>
<h2>Disable NDAS</h2>
<?php

$is_mounted = FALSE;
$output = Array();
$return = null;

if ($ndas_dev !== false){

	/* See if this device is mounted or not */
	if (ndasIsNdasDeviceMounted($ndas_dev)) {
		$is_mounted = TRUE;
		$message = date('Y-m-d H:i:s'). "|disable.php|failed|$ndas_dev using slot $slot is listed in mount output.\n";
		ndasPhpLogger(1, $message);
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s'). "|disable.php|failed|$v.\n";
			ndasPhpLogger(5,$message);
		}
		echo '<p>Failed to disable slot '.$slot.'.</p>
			<p>Device `'. $ndas_dev .'` may be a mounted volume.</p>
			<p>Check the logs for more infomation.</p>';
	}
}	

if($slot && !$is_mounted){
	/* Attempt to disable the volume */
	$command = "sudo /usr/sbin/ndasadmin disable -s $slot 2>&1";
	exec($command,$results,$return);
	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|disable.php|failed to disable slot $slot.\n";
		ndasPhpLogger(1,$message);
		foreach ($results as $v ){
				/* log any errors returned by the system */
				$message = date('Y-m-d H:i:s'). "|disable.php|failed|$v\n";
				ndasPhpLogger(3,$message);
		}
		echo "Failed to dismount! There may be more information in the logs.";
	} else {
		echo "<br>OK!<br>";
	}

} else {
	echo "Error: Invalid slot or mounted device.";	
}
?>

<script>
	opener.location.href='./list.php';
</script>
<br>
<a href='javascript:self.close()'>Close window</a>

</body>
</html>
