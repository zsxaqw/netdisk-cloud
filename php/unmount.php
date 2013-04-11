<?php 
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

include('netdisk.functions.php');

$umount_devi=isset($_GET['umount_devi'])? $_GET['umount_devi'] : false;
$umount_path=isset($_GET['umount_path'])? $_GET['umount_path'] : false;;
$umount_slot=isset($_GET['umount_slot'])? $_GET['umount_slot'] : false;

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	<title>Unmount NDAS</title>
</head>
<h2>Unmount NDAS</h2>
<?php
if ($umount_path !== false){

	$output = Array();
	$return = null;
	$message_type = 3; 
	$error_log = "./netdisk.error.log";
	$retry = '';

	/* Attempt to unmount the volume */
	$command = "sudo umount $umount_path 2>&1";
	exec($command,$results,$return);
	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|umount.php|unmount|failed on $umount_path.\n";
		foreach ($results as $v ){
				/* log any errors returned by the system */
				$message .= date('Y-m-d H:i:s'). "|umount.php|$v\n";
		}
		ndasPhpLogger(3,$message);
		echo "Failed to dismount!<br>$message";
	} else {
		echo "<br>OK!<br>";
	}

} else {
	echo "Invalid input.";	
}
?>

<script>
	opener.location.href='./manage.php?slot=<?php echo $umount_slot; ?>'
</script>
<br>
<a href='javascript:self.close()'>Close window</a>

</body>
</html>
