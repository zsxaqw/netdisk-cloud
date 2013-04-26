<?php

/* These functions allow the admin to perform tasks on the NetDISKs.
 * Some are used by the users too, in order to know if the drives are 
 * writeable for instance. Next line will act on an ajax request. 
 * post vars should be ndasfunction=ndasFunctionName & var(s) 
 * 
 * testCurrentUserSudoAbilities
 * ndasSetBlockDeviceScheduler 
 * ndasShowDiskInformation
 * ndasGetNdasDeviceSerialNumberFromName	ex: 44812187
 * ndasGetRegisteredNameFromDevice
 * ndasGetRegisteredNameFromSlot
 * ndasGetDeviceNameFromSlot
 * ndasIsDeviceEnabledRwOrRo
 * ndasIsBlockDeviceWritable          String RO, RW or Error #
 * ndasIsMountedVolumeWritable        String RO, RW or Error
 * 
 *
 */
$ndasAjaxRequest = isset($_REQUEST['ndasAjaxFunction']) ? $_REQUEST['ndasAjaxFunction'] : null;

/* Use the device name or serial number to loof for mounted partitions */
function ndasIsNdasDeviceMounted($ndas_dev){
	
	$is_mounted = FALSE;
	
	/* See if this device is mounted or not */
	$command = escapeshellcmd("sudo /bin/mount | grep $ndas_dev 2>&1");
	exec($command,$output,$return);
	if ($return === 0) {
		$is_mounted = TRUE;
		$message = date('Y-m-d H:i:s'). "|ndasIsNdasDeviceMounted|$ndas_dev is listed in mount output.\n";
		ndasPhpLogger(1, $message);
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s'). "|ndasIsNdasDeviceMounted|$v.\n";
			ndasPhpLogger(5,$message);
		}
	}
	return $is_mounted;
}

/* Need the NDAS Device name based on the serial number. */
function ndasGetNdasDeviceNameFromSerial($serial_no) {
	$return_this = "No NDAS Devices"; //no list in /proc/ndas/devices
	if ($device_dir = opendir('/proc/ndas/devices')) {
	    while (false !== ($device_name = readdir($device_dir))) {
	        if ($device_name != "." && $device_name != "..") {
	            $dev_serial = trim(file_get_contents('/proc/ndas/devices/'.$device_name.'/serial')); 
	            if (strpos($serial_no, $dev_serial) !== FALSE){
	            	$return_this = $device_name;
	            	//echo $entry .": ". file_get_contents('/proc/ndas/devices/'.$entry.'/serial') ."<br>";
	            }
	        }
	    } 
	    if($return_this === "No NDAS Devices")
	    	$return_this = "No match with $serial_no"; 	    	
	    closedir($handle);
	}
	return $return_this;
}
//echo ndasGetNdasDeviceNameFromSerial('44812187');
if ($ndasAjaxRequest == 'ndasGetNdasDeviceNameFromSerial'){
	$serial = isset($_REQUEST['serial'])? $_REQUEST['serial'] : FALSE ;
	if ( !$serial ) die("No Input");
	die( ndasGetNdasDeviceNameFromSerial($serial) );	
}
	
/* See if the current user has permission to perform tasks */
function testCurrentUserSudoAbilities(){
	
	if(is_file('config.php')){
		include('config.php');
	} else {
		include('config.default.guess.php');
	}
	$webUser = get_current_user();
	$webRootPath = $WEB_ROOT . $INSTALL_DIR . "/";
	$sudoErrors = 0;
	$fatal_errors = array();

	$ntfs3g = findNtfs3g();
	
	$all_commands = array(
		'ndasadmin' => '/usr/sbin/ndasadmin',
		'mount' => '/bin/mount',
		'umount' => '/bin/umount',
		'blkid' => '/sbin/blkid',
		'chown' => '/bin/chown',
		'ntfs-3g' => $ntfs3g,
		'ntfs-3g.probe' => "$ntfs3g.probe",
		'setscheduler' => $webRootPath.'php/setscheduler.php'
		);

	/* see if the command programs exist. */
	foreach($all_commands as $ack => $acv){	
		if(!is_file($acv)){
			$fatal_errors[$ack] = "$ack command program could not be found at $acv";
			$message = date('Y-m-d H:i:s'). "|testCurrentUserSudoAbilities|missing|$acv.\n";
			ndasPhpLogger(1,$message);
			unset($all_commands[$ack]);
		}
	}

	/* setscheduler is just a php file */
	unset($all_commands['setscheduler']);
	
	// Missing command programs are noted, now test if user has sudo privileges

	foreach($all_commands as $ack => $acv){	
		$command = escapeshellcmd("sudo -l $acv 2>&1");
		$message = date('Y-m-d H:i:s'). "|testPermissions|attempt|$command.\n";
		ndasPhpLogger(5,$message);
		exec($command,$results,$return);
		if ($return > 0) {
			$sudoErrors ++;
			$message = date('Y-m-d H:i:s'). "|testSudoPermissions|failed|$command.\n";
			ndasPhpLogger(1,$message);
			$fatal_errors[$ack] = "sudo -l $acv returned: $return";
			foreach ($results as $v ){
				// log any errors returned by the system 
				$message = date('Y-m-d H:i:s'). "|testSudoPermissions|failed|output|$v.\n";
				ndasPhpLogger(1,$message);
				$fatal_errors[$ack] .= "|$v";
			}
		} 
		unset($results);
	}
	
	
	if (count($fatal_errors) > 0){
		echo "<h2>";
		if (count($fatal_errors) === 1){	
			echo "Fatal Error!";
		} else if (count($fatal_errors) > 1) {	
			echo "Fatal Errors!"; 
		}
		
		echo "</h2>
			<pre> ";
		
		foreach($fatal_errors as $k => $v){
			echo "	 $k: $v\n";
		}
		
		echo "</pre>";
	}
	
	if($sudoErrors > 0){
		echo "<h2>Permission Errors!</h2>";
		echo "<pre>
	/* Running as $webUser
	 * Running in $webRootPath
	 * $webUser needs permission to use some commands that manage NDAS Devices.
	 * To grant proper permission, run 'visudo' as the root user, and add the
	 * following section. Save and close 'visudo' then reload this page. */
	
	#************** NDAS Device Administrator Permissions ***************
	# User indicated below can perform NDAS Administrator tasks
	User_Alias NDASADMIN=$webUser

	# Commands required to manage NDAS devices from NETDISK-CLOUD as NDASADMIN
	Cmnd_Alias NDAS=/usr/sbin/ndasadmin,/bin/mount,/bin/umount,/sbin/blkid,/bin/chown, $ntfs3g, $ntfs3g.probe,". $webRootPath ."php/setscheduler.php

	# Allow NDASADMIN to execute NDAS commands without a password
	NDASADMIN ALL=(ALL:ALL) NOPASSWD: NDAS
	#********** End of NDAS Device Administrator Permissions *************
	</pre>";
	}	
}

/* Change the kernel queue scheduler on the disk for performance evaluations */
function ndasSetBlockDeviceScheduler($scheduler,$device){

	include("./config.php");
	
	$new_scheduler = isset($scheduler)? $scheduler : "trustmessiahjesus" ;
	$block_dev_name = isset($device)? $device : "Need scheduler file location.";
	$filename = "/sys/block/$block_dev_name/queue/scheduler";
	
	
	/* let us double check that the scheduler type exists on this device. */
	if (!is_file($filename)) {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|ndasSetBlockDeviceScheduler|Failed. $block_dev_name \n";
		ndasPhpLogger(2,$message);
		return "Block device name error: $filename";
	} 
	$curr_scheduler = file_get_contents($filename);
	if (!strpos($curr_scheduler, $new_scheduler)) {
		return "Invalid scheduler: $new_scheduler";
	}		
	
	/* try to set the new scheduler */
	$output = Array();
	$return = null;
	$message_type = 3; 
	$error_log = "./netdisk.error.log";
	$err_message = "There may be more information in the local log file.";
	
	$command = escapeshellcmd("sudo ".$WEB_ROOT . $INSTALL_DIR. "/php/setscheduler $new_scheduler $filename");
	exec($command,$results,$return);
	if($return !== 0) {
		$message = date('Y-m-d H:i:s'). "|setscheduler.php|set|failed|$command.\n";
		ndasPhpLogger(2,$message);
		$message = date('Y-m-d H:i:s'). "|setscheduler.php|set|failed|returned: $return.\n";
		ndasPhpLogger(2,$message);
		
		foreach ($results as $v ){
			/* log any errors returned by the system */
			$message = date('Y-m-d H:i:s'). "|setscheduler.php|set|failed|output|$v.\n";
			ndasPhpLogger(2,$message);
			if (strpos($v, 'Permission denied')) $err_message = "Permission denied.";
		}
	
		$message = "Could not set scheduler. $err_message" ;
	
	} else {
		
		$message = "Success! ";
		
	}
	return $message;
}
//echo ndasSetBlockDeviceScheduler('/dev/ndas-12345678-0','noop')
if ($ndasAjaxRequest == 'ndasSetBlockDeviceScheduler'){
	$new_scheduler = isset($_REQUEST['sch'])? $_REQUEST['sch'] : "trustmessiahjesus" ;
	$block_dev_name = isset($_REQUEST['dev'])? $_REQUEST['dev'] : die("Need scheduler file location.");
	if ( !$block_dev_name || !$new_scheduler ) die("No Input");
	die( ndasSetBlockDeviceScheduler($new_scheduler,$block_dev_name) );	
}


/* Show all the disk information */
function ndasShowDiskInformation($func_slot){
	
	/* Each disk ultimately takes one slot. And there is some basic
	 * information about the disk availble in /proc/ndas/slots/SLOT#/
	 */
	$output = Array();
	include('./config.php'); /* needs web root folder for mkdir */
	
	$return_table = '<table class="netdisk-slot-details">';
	
	$reg_name = ndasGetRegisteredNameFromSlot($func_slot);
	$return_table .= "<tr><td>NDAS_Device</td>
		<td>$reg_name</td></tr>";
	
	$device_unit = file_get_contents('/proc/ndas/slots/'.$func_slot.'/unit'); 
	$return_table .= "<tr><td>Drive_Bay</td>
		<td>$device_unit</td></tr>";

	$blockdev = file_get_contents('/proc/ndas/slots/'.$func_slot.'/devname');
	$return_table .= "<tr><td>Block_Device</td>
		<td>/dev/$blockdev</td></tr>";
	
	$command = escapeshellcmd("cat /proc/ndas/slots/$func_slot/info* 2>&1");
	exec($command, $output, $return_var);
	if($return_var == 0){

		/* info has 4 fields and 2 rows */
		$info_fields[0] = 'Drive_Status';
		$info_fields[1] = 'Capacity';
		$info_fields[2] = 'RAID_Mode';
		$info_fields[3] = 'Major/Minor';

		$info2_fields[0] = 'Drive_Model';
		$info2_fields[1] = 'Firmware_Version';
		$info2_fields[2] = 'Serial_Number';
		$info2_fields[3] = 'RO_Hosts';
		$info2_fields[4] = 'RW_Hosts';  

		$str = preg_replace('/\s+/', ' ', $output[1]);
		$info_values = explode(' ',$str);

		for ($i=0;$i < count($info_fields); $i++){
			$return_table .= '<tr><td class="netdisk-details">'.$info_fields[$i].'</td>
				<td class="netdisk-details">'.$info_values[$i].'</td></tr>';
		}

		$blocksectors = file_get_contents('/proc/ndas/slots/'.$func_slot.'/sectors');
		$return_table .= "<tr><td>Drive_Sectors</td>
			<td>$blocksectors</td></tr>";
								
		$str = preg_replace('/\s+/', ' ', $output[3]);
		$info2_values = explode(' ',$str);

		while(count($info2_values) > 5) {
			$info2_values[1] = $info2_values[0] .' '.$info2_values[1];	
			array_shift($info2_values);	
		}
		for ($i=0;$i < count($info2_fields); $i++){
			$return_table .= '<tr><td class="netdisk-details">'.$info2_fields[$i].'</td>
				<td class="netdisk-details">'.$info2_values[$i].'</td></tr>';
		}
		
		/* this is the current scheduler. It can be changed for testing if
		 * users like to try for different performance. */
		$return_table .= '<tr><td>Scheduler</td><td>';

		$block_dev_name = str_replace('/dev/', '', $blockdev);
		$filename = "/sys/block/$block_dev_name/queue/scheduler";
		if (!is_file($filename)){
			$return_table .= "Indetectable";
		} else {
			$curr_scheduler = file_get_contents($filename);
			$schedulers = explode(" ",$curr_scheduler);
			foreach ($schedulers as $v){
				if(strpos( $v, "]" )) $return_table .= "$v ";
				else $return_table .= '<a href="setscheduler.php?sch='.$v
					.'&dev='.$block_dev_name.'">'.$v.'</a> ';
			}
		}
		$return_table .= '</td><tr>';
		$return_table .= '</table>';

		return $return_table;
	
	
	} else {
		return "Error: slot $return_var or info is invalid.";
	}  
}
//echo "Slot 1<br>". ndasShowDiskInformation(1);
if ($ndasAjaxRequest == 'ndasShowDiskInformation'){
	$post_slot = isset( $_POST['slot'] ) ? $_POST['slot'] : null;
	if (!$post_slot) die("No Input");
	die( ndasShowDiskInformation($post_slot) );	
}

/* Try to get the serial number from the name */
function ndasGetNdasDeviceSerialNumberFromName($dev_name) {

	/* this only returns the 8 digit part of the number. 
	 * Ex: 44812788
	 * You may have to add /dev/ndas- or other identity parts on the fly. 
	 */
	include('./config.php'); 
	$output = Array();
	$return = null;

	$serial_number = file_get_contents("/proc/ndas/devices/$dev_name/serial");
	if ($serial_number) {
		return $serial_number;
	} else {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|ndasGetNdasDeviceSerialNumberFromName|Failed to read /proc/ndas/devices/$dev_name/serial.\n";
		ndasPhpLogger(5,$message);
		return FALSE;
	}				
}
//echo  ndasGetNdasDeviceSerialNumberFromName('ndas_device_1') 
if ($ndasAjaxRequest == 'ndasGetNdasDeviceSerialNumberFromName'){
	$post_name = isset( $_POST['name'] ) ? $_POST['name'] : null;
	if (!$post_name) die("No Input");
	die( ndasGetDeviceNameFromSlot($post_name) );	
}



/* Return the Registered device name from the /dev/ndas-name-# */
function ndasGetRegisteredNameFromDevice($func_device) {

	/* this returns the name of the NDAS device name as set by the user when
	 * they registered it with the ID and Key. */
	include('./config.php'); /* needs web root folder for mkdir */
	$output = Array();
	$return_var = null;

	/* split the device name. */
	$explodedName = explode("-",$func_device);
	
	if (!isset($explodedName[1])){
		return "Invalid /dev/name";
	}
	
	/* 2nd part is the serial number */
	$command = escapeshellcmd("grep ". $explodedName[1] ."  /proc/ndas/devs | awk '{print $1}' 2>&1");
	exec($command, $output, $return_var);
	if ($return_var > 0) {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|ndasGetRegisteredNameFromDevice|Failed. Retvar $return_var\n";
		ndasPhpLogger(4,$message);
		return "Error: $return_var";
	} else {
		return $output[0];
	}				
}
//echo ndasGetRegisteredNameFromDevice('/dev/ndas-44700486-0p1');
if ($ndasAjaxRequest == 'ndasGetRegisteredNameFromDevice'){
	$post_device = isset( $_POST['device'] ) ? $_POST['device'] : null;
	if (!$post_device) die("No Input");
	die( ndasGetRegisteredNameFromDevice($post_device) );	
}


/* Try to get the Registered Device name from the slot */
function ndasGetRegisteredNameFromSlot($func_slot) {

	/* this returns the name of the NDAS device as set by the user when they 
	 * registered it with the ID and Key. 
	 */
	include("./config.php");
	$output = Array();
	$return_var = null;
	$slotArray = Array();
	$command = escapeshellcmd("cat /proc/ndas/devs | awk '{print $1\" \"$7\" \"$8}' 2>&1");
	if(is_file('/proc/ndas/devs')){
		exec($command, $output, $return_var);
		for($i=1;$i< count($output); $i++){
			$tmpArray = explode(" ", $output[$i]);
			for($j=1;$j < count($tmpArray); $j++){
					$slotArray[ $tmpArray[$j] ] = $tmpArray[0];
			}
			unset($tmpArray);
		}
		
		return $slotArray[$func_slot];
	} else {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|getNdasRegisteredNameFromSlot|/ndas/devs does not exist..\n";
		ndasPhpLogger(4,$message);
		return "Error: /ndas/devs does not exist.";	
	}				
}
//echo ndasGetRegisteredNameFromSlot(1)
if ($ndasAjaxRequest == 'ndasGetRegisteredNameFromSlot'){
	$post_slot = isset( $_POST['slot'] ) ? $_POST['slot'] : null;
	if (!$post_slot) die("No Input");
	die( ndasGetRegisteredNameFromSlot($post_slot) );	
}

	
/* Try to get the /dev/ndas- name from the slot */
function ndasGetDeviceNameFromSlot($func_slot) {

	/* this only returns the ndas-serialnumber string. You may have to add 
	 * /dev or other identity parts on the fly. */
	include('./config.php'); /* needs web root folder for mkdir */
	$output = Array();
	$return = null;

	$block_file = "/proc/ndas/slots/$func_slot/devname";
	if ( is_file($block_file) && $handle = fopen($block_file, "r") ) {
		$line = exec("cat $block_file");
		if (!empty($line)){
			$blockdev = strtok($line, " \t\n");
		} else {
			$blockdev = "Unassigned";
		}
		fclose($handle);
		return $blockdev;
		
	} else {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|getNdasDeviceNameFromSlot|$block_file does not exist.\n";
		ndasPhpLogger(4,$message);
		return "GetDevnameError: 1";
	}				
}
//echo  ndasGetDeviceNameFromSlot(1) 
if ($ndasAjaxRequest == 'ndasGetDeviceNameFromSlot'){
	$post_slot = isset( $_POST['slot'] ) ? $_POST['slot'] : null;
	if (!$post_slot) die("No Input");
	die( ndasGetDeviceNameFromSlot($post_slot) );	
}

/* ntfs-3g has been installed in various places. Rather than mess around
 * we are just going to look for it in some common spots. */
function findNtfs3g(){
	
	if (is_file('/usr/bin/ntfs-3g'))
		$ntfs3g = '/usr/bin/ntfs-3g';
	else if (is_file('/bin/ntfs-3g'))
		$ntfs3g = '/bin/ntfs-3g';
	else 
		$ntfs3g = '/bin/ntfs-3g';
		
	return $ntfs3g;
}

/* Use ntfs-3g.probe to determine if the ndas device is enabled ro or rw */
function ndasIsDeviceEnabledRwOrRo($device) {

	/* This might not be completely reliable. It sends a Read-only error, 
	 * but after that Read Write is just assumed.
	 *
	 *	Note: It might fail when using a shared rw 
	 *
	 * ) simply probe devname with the tool 
	 * ) test the result for read-only
	 * ) return ro, rw, or error 
	 *	Some possible errors: 
	 *		2 - device does not exist 
	 */
	
	include('./config.php'); 
	$output = Array();
	$return = null;
	$retval = '';
		
	/* determine if the device exists. */
	$command = escapeshellcmd("/bin/ls /dev/$device") ." 2>&1";
	exec($command,$output,$return);
	$message = date('Y-m-d H:i:s'). 
	"|netdisk.functions.php|isNdasDeviceEnabledRwOrRo|command|$command.\n";
	ndasPhpLogger(5,$message);

	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). 
		"|netdisk.functions.php|isNdasDeviceEnabledRwOrRo|ls|$device does not exist.\n";
		ndasPhpLogger(1,$message);
		return "Error: 2 $device" ;
	} 
	
	$ntfs3g = findNtfs3g();
	$command = escapeshellcmd("sudo $ntfs3g.probe --readwrite /dev/$device 2>&1");
	exec($command,$output,$return);
	if ($return > 0) {
		if ($return == 19){
			$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
				$return."|user has no permission for this tool\n";
			ndasPhpLogger(3,$message);
			return "Error 19: $return";
		}
		/* Assuming RW, unless changed by scanning the output messages. */
		$retval = 'RW';
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasDeviceEnabledRwOrRo|ntfs-3g.probe|returned: " . 
				$return."|$v\n";
			ndasPhpLogger(2,$message);
			if(strpos($v,'Read-only file system')){
				$retval = 'RO';			
			} 
			if(strpos($v,'as read-only')){
				$retval = 'RO';			
			} 
		}
		return $retval;
	} else {
		return 'RW';	
	}
}
//echo ndasIsDeviceEnabledRwOrRo('ndas-44809965-1');
if ($ndasAjaxRequest == 'ndasIsDeviceEnabledRwOrRo'){
	$post_device = isset( $_POST['device'] ) ? $_POST['device'] : null;
	if (!$post_device) die("No Input");
	die( ndasIsDeviceEnabledRwOrRo($post_device) );	
}


/* Check if a partition is writeable prior to mounting. */ 
function ndasIsBlockDeviceWritable($device){
	/* Notes: www-user needs mkdir and mount permission.
	 *	 		 Returns RO, RW or Error #
	 *	Some possible errors: 
	 *		2 - ndas device has no partitions 
	 *		3 - partition type could not be determined
	 *		4 - www-user could not make the temp directory for test mounting
	 *		5 - non-empty temporary directory
	 *		6 - failed to mount.
	 *		7 - www-user has not mount permission.
	 *		8 - failed to unmount after rw mounting on tmp folder
	 *		9 - tmp folder is not empty. Device still mounted? 
	 *		 - no write permission on the mounted partition
	 *		 - could not delete test file
	 *		 - Unknown error.
	 */
	
	include('./config.php'); 
	$output = Array();
	$return = null;
	$retval = '';
	$ONLYRO = false;
	
	/* ) determine if a partition exists. devname-#p# typically */
	$command = escapeshellcmd("/bin/ls $device | grep p  2>&1");
	exec($command,$output,$return);
	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|isNdasBlockDeviceWritable|ls|No partitions on $device\n";
		ndasPhpLogger(3,$message);
		return "Error: 2 ";
	} 
	
	/* if we got here, we have at least one partition on the device. */
	$partitionToTest = $device;
	unset($output);
	unset($return);
	unset($v);
	
	/* ) get the file system type blkid */
	$command = escapeshellcmd('sudo /sbin/blkid -s TYPE -o value '.$partitionToTest.'  2>&1');
	exec($command,$output,$return);
	if ($return > 0) {
		$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|isNdasBlockDeviceWritable|blkid|$v\n";
		ndasPhpLogger(3,$message);
		return "Error: 3";
	} 
	
	/* We can test writeable or not with file system methods. */
	$partitionFileSystemType = $output[0];
	unset($output);
	unset($return);
	unset($v);

	/* If it is ntfs, we can use ntfs-3g.probe to determine if it can be 
	 * mounted rw the ro option must be set if it cannot be mounted rw. 
	 * With this tool, successful test returns 0 but that does not mean
	 * the volume is RW. So we are doing several tests.
	 */
	if ($partitionFileSystemType == 'ntfs') {
		$ntfs3g = findNtfs3g();
		$command = escapeshellcmd("sudo $ntfs3g.probe --readwrite $partitionToTest 2>&1");
		exec($command,$output,$return);
		
		if ($return == 19){
			$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
				$return."|user has no permission for this tool\n";
			ndasPhpLogger(3,$message);
			return "3gProbeErr: $return";
		}
		if ($return > 0){
			$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
				$return;
			ndasPhpLogger(3,$message);
			return "3gProbeErr: $return";
		}

		/* Look for read-only errors in the output and log any messages just in
		 * case admin wants to check later. */
		foreach ($output as $v ){
			$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
				$return."|$v\n";
			ndasPhpLogger(3,$message);
			if(strpos($v,'Read-only file system') || strpos($v,'as read-only')){
				$ONLYRO = true;
			} 
		}
		unset($output);

		if ($ONLYRO === true ) {
			/* there is some reason that this partition is RO. So test if device 
			 * can be mounted RO. It is safe even if errors on RW. 
			 */
			$ntfs3g = findNtfs3g();
			$command = escapeshellcmd("sudo $ntfs3g.probe --readonly $partitionToTest 2>&1");
			exec($command,$output,$return);
			if ($return == 0) 
				return 'RO';
			else 
				return '3gRoErr: '.$return;		
		} 
		
		/* Since we could not prove it is RO, we assume RW on ntfs is ok. */
		return "RW";
			
	} else {
		/* create a temp folder */  
		$tempMountingDirectory = "/tmp" . str_replace('/dev', '', $device); 
		if (!is_dir($tempMountingDirectory) ) {
			if (!mkdir($tempMountingDirectory,0777)){
				$message = date('Y-m-d H:i:s') . 
					"|netdisk.functions.php|isNdasBlockDeviceWritable|mkdir|web user could " .
					"not mkdir($tempMountingDirectory).\n";
				ndasPhpLogger(3,$message);
				return "Error 4"; 
			}
		}	
		/* what if it was there and is not empty? There is a risk of losing data. */
		if (count(glob("$tempMountingDirectory/*")) !== 0) {
			$message = date('Y-m-d H:i:s') . 
				"|netdisk.functions.php|isNdasBlockDeviceWritable|mkdir|attempted to mount " .
				"ndas device to non-empty directory.\n";
			ndasPhpLogger(3,$message);
			return "Error: 5"; 	
		}
		
		/* see what we get with mount command 
			-n "no mtab entry" 
			-w "explicit read/write" */
		$command = escapeshellcmd("sudo /bin/mount -nw -t $partitionFileSystemType $partitionToTest $tempMountingDirectory 2>&1");
		exec($command,$output,$return);
		if ($return > 0) {
			$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|isNdasBlockDeviceWritable|$command\n";
			ndasPhpLogger(4,$message);
			foreach ($output as $v) { 
				$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|isNdasBlockDeviceWritable|mount|$v\n";
				ndasPhpLogger(4,$message);
				if (strpos($v,'write-protected') > 0 ) {
					$retval .= "RO ";
	 			} else if (strpos($v,'permission') > 0 ) {
					$retval .= "Error: 7 ";
	 			} else {
	 				$retval .= "MountErr: $return ";
				}
			}
		} else {
			$retval = "RW ";
			/* If the file system mounted rw, we must unmount. We did not write 
			 * in the mtab entry before so it must dismount here with the directory. */
			$command = escapeshellcmd("sudo /bin/umount -l $tempMountingDirectory 2>&1");
			exec($command,$output,$return);
			if ($return > 0) {
				foreach ($output as $v) { 
					$message = date('Y-m-d H:i:s'). "|netdisk.functions.php|isNdasBlockDeviceWritable|umount|$v\n";
					ndasPhpLogger(4,$message);
				}
				$retval .= "Error: 8 ";
			}
		}
		unset($output);
		unset($return);
		unset($v);

		/* Delete the temp directory if possible. */
		if (is_dir($tempMountingDirectory) ) {
			/* what if it is not empty? There is a risk of losing data. */
			if (count(glob("$tempMountingDirectory/*")) !== 0) {
				$message = date('Y-m-d H:i:s') . 
					"|netdisk.functions.php|isNdasBlockDeviceWritable|rmdir|temp dir is not empty.\n";
				ndasPhpLogger(4,$message);
				$retval .= "Error: 9 "; 	
			} else if (!rmdir($tempMountingDirectory)){
				$message = date('Y-m-d H:i:s') . 
					"|netdisk.functions.php|isNdasBlockDeviceWritable|rmdir|web user could " .
					"not remove temp directory with php mkdir.\n";
				ndasPhpLogger(1,$message);
				$retval .= "Error 10 "; 
			}
		}	
		return $retval;
	} 
}
//echo ndasIsDeviceWritable('ndas-44700486-0');
if ($ndasAjaxRequest == 'ndasIsBlockDeviceWritable'){
	$post_device = isset( $_POST['device'] ) ? $_POST['device'] : null;
	if (!$post_device) die("No Input");
	die( ndasIsBlockDeviceWritable($post_device) );	
} 


/* Check if a mounted partition is writeable or not */
function ndasIsMountedVolumeWritable($directory) {
	
	include('./config.php'); 

	/* Just try touching a file.  */
	$filename = "ndastestfile-".time().".txt";
	$touchfile = $directory."/".$filename;
	
	/* using @ suppresses an error message report to the browser. */
	if (@touch($touchfile)) {
 		/* Delete the file and report RW */
		unlink($touchfile);
		return "RW";
	} else {
		return "RO";	
	}

}
//echo ndasIsMountedVolumeWritable(/dev/ndas-44700486-0p2);
if ($ndasAjaxRequest == 'ndasIsMountedVolumeWritable'){
	$post_device = isset( $_POST['device'] ) ? $_POST['device'] : null;
	if (!$post_device) die("No Input");
	die( ndasIsMountedVolumeWritable($post_device) );	

}

/* Log errors from the web user to a local file using php's error_log 
function. It will only record messages at the log_level set in config.php*/
function ndasPhpLogger($log_level,$log_message){
	include('./config.php');
	$message_type = isset($PHP_LOG_TYPE)? $PHP_LOG_TYPE : 3; 
	$error_log = $LOCAL_LOG_FILE;
	if( $log_level <= $ADMIN_LOG_LEVEL || $log_level <= $USER_LOG_LEVEL){
		error_log($log_message, $message_type, $error_log);
	}
}
?>