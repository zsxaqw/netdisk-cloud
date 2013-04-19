<?php 
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

$name = isset($_POST['name']) ? $_POST['name'] : null;
$slot = isset($_POST['slot']) ? $_POST['slot'] : null;

include('netdisk.functions.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	<title>Unregister NDAS Device</title>
</head>
<?php

echo "<body>";
echo '<h2>Unregister '.$name.'</h2><div style="padding:10px;">';

/* Test if there are amounted partitions and exit if needed. */
$is_mounted = FALSE;

if (!empty($slot)) {

	/* The multibay NDAS device could be requested. So search mount
	 * for the base device name. Not the full slot device name. 
	 */	
	$ndas_dev = "ndas-" . trim(ndasGetNdasDeviceSerialNumberFromName($name));
	$command = escapeshellcmd("sudo /bin/mount | grep $ndas_dev 2>&1");

	exec($command,$output,$return);
	if ($return === 0) {
		$is_mounted = TRUE;
		$message = date('Y-m-d H:i:s'). "|unregister.php|failed|$ndas_dev from $name is listed in mount output.\n";
		ndasPhpLogger(1, $message);
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s'). "|unregister.php|failed|$v.\n";
			ndasPhpLogger(5,$message);
		}
		echo '<p>Failed to unregister.</p>
			<p>Device `'. $name .'` may have a mounted volume.</p>
			<p>Check the logs for more infomation.</p>';
	}
}
	
if ($name && !$is_mounted) {

	$output = Array();
	$return = null;

	$command = escapeshellcmd('sudo /usr/sbin/ndasadmin unregister --name "'. $name .'" 2>&1');
	exec($command,$output,$return);

	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|unregister.php|failed|$command.\n";
		ndasPhpLogger(1, $message);
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s'). "|unregister.php|failed|$v.\n";
			ndasPhpLogger(5, $message);
		}
		$reg_name = ndasGetRegisteredNameFromSlot($slot);
		echo "<p>Failed to unregister. There may be more 
			information in the logs.</p>";
	} else {
		echo "<p>OK!</p>";
	}
} else {
	echo "<p>Failed. No name or mounted device.</p>";	
}
?>

<script>
	opener.location.href='./list.php'
</script>
<br>
<a href='javascript:self.close()'>Close window</a>
</div>
</body>
</html>
