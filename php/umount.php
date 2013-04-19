<?php
include('auth.php');
include('./config.php'); 
include('./netdisk.functions.php');

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>Unmount NDAS</title>
</head>
<body>
<h2>Unmount NDAS Device</h2>

<?php
if (userIsAdmin()) {

	//foreach ($_GET as $k=>$v)
	//	echo "<br>$k : $v";
		
	$device=isset($_GET['umount_devi'])? $_GET['umount_devi'] : die("Device not specified.");
	$path=isset($_GET['umount_path'])? $_GET['umount_path'] : die("Mount path not specified.");
	$slot=isset($_GET['umount_slot'])? $_GET['umount_slot'] : die("NDAS Slot not specified.");
	
	$output = Array();
	$return = null;
	$retval = '';
	$message = '';

	/* see if the path is a mount point */
	$command = escapeshellcmd("mountpoint $path 2>&1");
	exec($command,$output,$return);
	if ($return > 0) {
		$message = "|umount.php|$path is not used as a mount point at this time.";
		if ($ADMIN_LOG_LEVEL >= 2){
			ndasPhpLogger(2,$message);
		}
		die("Error: exec returned > 0.<br>$message");
	}
	
	/* make sure the device is mounted on the mount point.*/ 
	$command = escapeshellcmd("mount | grep $path 2>&1");
	exec($command,$output,$return);

	if ($return > 0) {
		if(strpos($output[0], $device) === false){
			$message = "|umount.php|$device is not mounted on $path";
		} else {
			$message = "grep for $path failed in search of mounted devices.";		
		} 
		if ($ADMIN_LOG_LEVEL >= 2){
			ndasPhpLogger(2,$message);
		}
		die("Error: strpos failed.<br>$message");
	} 

	/* see if the path is being used by any process obvious 
	 * This is not incredibly reliable. It only seems to work 
	 * if a process is really using the netdisk. So, even if 
	 * it is in an open window, it will not be shown in use, unless
	 * some I/O operation is going on at the very moment we make 
	 * this command. It must be based on the way NDAS is connecting
	 * this device.
	 */ 
	 
	$command = escapeshellcmd("lsof $path 2>&1");
	exec($command,$output,$return);
	if ($return == 0) {
		$message = "|umount.php|$path is apparently in use at this time.";
		if ($ADMIN_LOG_LEVEL >= 2){
			ndasPhpLogger(2,$message);
			foreach($output as $v){
				ndasErrorLogger(2,"|umount.php|$v");
				$message .= "<br>$v";
			}
		}
		die($message);
	}

	/* try unmounting */
	$command = escapeshellcmd("sudo /bin/umount $device 2>&1");
	exec($command,$output,$return);
	if ($return > 0) {
		$message = "|umount.php|Unmounting failed.";
		if ($ADMIN_LOG_LEVEL >= 1){
			ndasPhpLogger(1,$message);
			foreach($output as $v){
				ndasPhpLogger(2,"|umount.php|$v");
				$message .= "<br>$v";
			}
		}
		die($message);
	}
	echo "<b>Success!</b><br><br>Umounted $device.";

$refpage = $_SERVER['HTTP_REFERER'];

?>

<script>
	opener.location.href='<?php echo $refpage; ?>'
	
</script>

<?php		
} else {
	echo "Need admin (root) authority.";	
}
?>

<br>
<a href='javascript:self.close()'>Close window</a>
</body>
</html>
