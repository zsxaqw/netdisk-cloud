<?php
ini_set('display_errors', E_ALL);

include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

$id1 = isset($_REQUEST['id1'])? $_REQUEST['id1'] : "ooooo";
$id2 = isset($_REQUEST['id2'])? $_REQUEST['id2'] : "ooooo";
$id3 = isset($_REQUEST['id3'])? $_REQUEST['id3'] : "ooooo";
$id4 = isset($_REQUEST['id4'])? $_REQUEST['id4'] : "ooooo";
$id5 = isset($_REQUEST['id5'])? "-".$_REQUEST['id5'] : "";
$id6 = isset($_REQUEST['id6'])? str_replace(' ','_', $_REQUEST['id6']) : "NDAS-$id1";

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
   <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	<title>Register NDAS Device</title>
</head>
<h2>Register NDAS Device</h2>
<?php

//die("RESULT=``");
$ndas_id  = "$id1-$id2-$id3-$id4$id5";
if (strpos($ndas_id, 'ooooo')){
	echo "<br>Invalid NDAS ID<br>$ndas_id";
} else {

	include('netdisk.functions.php');

	$output = Array();
	$return = null;
	$message_type = 3; 
	$error_log = "./netdisk.error.log";
	$retry = '';

	// Try registering the device
	$command = "sudo /usr/sbin/ndasadmin register $id1-$id2-$id3-$id4$id5 --name \"$id6\" 2>&1";
	$message = date('Y-m-d H:i:s'). "|register.php|attempt|$command.\n";
	ndasPhpLogger(5,$message);
	exec($command,$results,$return);
	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|register.php|failed|$command.\n";
		ndasPhpLogger(1,$message);
		foreach ($results as $v ){
			// log any errors returned by the system 
			$message = date('Y-m-d H:i:s'). "|register.php|failed|output|$v.\n";
			ndasPhpLogger(1,$message);
			echo "An error occurred while trying to registere $id6. 
					There may be more information in the server log. ";				 
		}
	} else {
		echo "OK!<br>";
		echo "Registered $id6.";
		
		/* Log all return output in debugging mode. It could be useful
		 * if running a dbg version of ndas 
		 */
		$message = date('Y-m-d H:i:s'). "|register.php|returned|$return.\n";
		ndasPhpLogger(5,$message);
		foreach ($results as $v ){
			// log any errors returned by the system 
			$message = date('Y-m-d H:i:s'). "|register.php|results|$v.\n";
			ndasPhpLogger(5,$message);
		}
	}
}
?>

<script>
	opener.location.href='./list.php'
</script>
<br>
<a href='javascript:self.close()'>Close window</a>



</body>
</html>
