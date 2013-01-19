<?php
include('./config.php');

/* This error logger only records action and operations in this program.
 * The log file can be set in the config.php. 
 */
function ndasErrorLogger($level,$message){
	
	global $LOG_LEVEL;
	global $LOG_FILE;
	if ($level <= $LOG_LEVEL){
		$message_type = 3; 
		$error_log = $LOG_FILE;
		$message = date('Y-m-d H:i:s') . $message . "\n"; 
		error_log($message, $message_type, $error_log);
	}
}


/* These functions allow the admin to perform tasks on the NetDISKs.
 * Some are used by the users too, in order to know if the drives are 
 * writeable for instance. Next line will act on an ajax request. 
 * post vars should be ndasfunction=ndasFunctionName & var(s) */
$ndasAjaxRequest = isset($_POST['ndasAjaxFunction']) ? $_POST['ndasAjaxFunction'] : null;

/* Show all the disk information */
function ndasShowDiskInformation($func_slot){
	
	/* Each disk ultimately takes one slot. And there is some basic
	 * information about the disk availble in /proc/ndas/slots/SLOT#/
	 */
	$output = Array();
	$message_type = 3; 
	$error_log = "./netdisk.error.log";
	
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
	
	$command = "cat /proc/ndas/slots/$func_slot/info* 2>&1";
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
	if (!$post_slot) return "No Input";
	die( ndasShowDiskInformation($post_slot) );	
		
}


/* Return the Registered device name from the /dev/ndas-name-# */
function ndasGetRegisteredNameFromDevice($func_device) {

	/* this returns the name of the NDAS device name as set by the user when
	 * they registered it with the ID and Key. */
	global $LOG_LEVEL;
	$output = Array();
	$return_var = null;

	/* split the device name. */
	$explodedName = explode("-",$func_device);
	
	if (!isset($explodedName[1])){
		return "Invalid /dev/name";
	}
	
	/* 2nd part is the serial number */
	$command = "grep ". $explodedName[1] ."  /proc/ndas/devs | awk '{print $1}' 2>&1";
	exec($command, $output, $return_var);
	if ($return_var > 0) {
		$message = "|netdisk.functions.php|ndasGetRegisteredNameFromDevice|Failed. exec error# $return_var";
		if ($LOG_LEVEL > 0){
			ndasErrorLogger(1,$message);
		}
		$return_var = "Error: $return_var";
	} else {
		$return_var = $output[0];
		$message = "|netdisk.functions.php|ndasGetRegisteredNameFromDevice|Success. Retvar $return_var";
		if ($LOG_LEVEL > 0){
			ndasErrorLogger(3,$message);
		}
	}
	
	
	return $return_var;
				
}
//echo ndasGetRegisteredNameFromDevice('/dev/ndas-44700486-0p1');


/* Try to get the Registered Device name from the slot */
function ndasGetRegisteredNameFromSlot($func_slot) {

	/* this returns the name of the NDAS device as set by the user when they 
	 * registered it with the ID and Key. */
	$output = Array();
	$return_var = null;
	$slotArray = Array();
	$command = "cat /proc/ndas/devs | awk '{print $1\" \"$7\" \"$8}' 2>&1";
	exec($command, $output, $return_var);
	for($i=1;$i< count($output); $i++){
		$tmpArray = explode(" ", $output[$i]);
		for($j=1;$j < count($tmpArray); $j++){
				$slotArray[ $tmpArray[$j] ] = $tmpArray[0];
		}
		unset($tmpArray);
	}
	
	global $LOG_LEVEL;
	if ($LOG_LEVEL > 0){
		$message = "|netdisk.functions.php|getNdasRegisteredNameFromSlot|";
		ndasErrorLogger(3,$message);
	}

	return $slotArray[$func_slot];
					
}
//echo ndasGetRegisteredNameFromSlot(1)
	
/* Try to get the /dev/ndas- name from the slot */
function ndasGetDeviceNameFromSlot($func_slot) {

	/* this only returns the ndas-serialnumber string. You may have to add 
	 * /dev or other identity parts on the fly. */
	$output = Array();
	$return = null;
	global $LOG_LEVEL;
	
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
		$message = "|netdisk.functions.php|getNdasDeviceNameFromSlot|$block_file does not exist.";
		if ($LOG_LEVEL > 0){
			ndasErrorLogger(1,$message);
		}
		return "GetDevnameError: 1";
	}				
}
//echo  ndasGetDeviceNameFromSlot(1) 

/* Use ntfs-3g.probe to determine if the ndas device is enabled ro or rw */
function ndasIsDeviceEnabledRwOrRo($device) {

	/* This might not be completely reliable. It sends a Read-only error, 
	 * but after thatn Read Write is just assumed.
	 *
	 *	Note: It might fail when using a shared rw 
	 *
	 * ) simply probe devname with the tool 
	 * ) test the result for read-only
	 * ) return ro, rw, or error 
	 *	Some possible errors: 
	 *		2 - device does not exist 
	 */
	
	$output = Array();
	$return = null;
	$retval = '';
	global $LOG_LEVEL;
			
	/* determine if the device exists. */
	$command = "ls /dev/$device  2>&1";
	exec($command,$output,$return);
	if ($return > 0) {
		if ($LOG_LEVEL >= 2){
			$message = "|netdisk.functions.php|isNdasDeviceEnabledRwOrRo|ls|$device does not exist.";
			ndasErrorLogger(2,$message);
		}
		return "Error: 2";
	} 
	
	$command = "sudo /bin/ntfs-3g.probe --readwrite /dev/$device 2>&1";
	exec($command,$output,$return);
	if ($return > 0) {
		if ($return == 19){
			if ($LOG_LEVEL > 0){
				$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
				$return."|user has no permission for this tool.";
				ndasErrorLogger(1,$message);
			}
			return "Error 19: $return";
		}
		/* Assuming RW, unless changed by scanning the output messages. */
		$retval = 'RW';
		foreach ($output as $v ){
			if ($LOG_LEVEL >= 3){
				$message = date('Y-m-d H:i:s') .
				"|netdisk.functions.php|isNdasDeviceEnabledRwOrRo|ntfs-3g.probe|returned: " . 
				$return."|$v";
				ndasErrorLogger(3,$message);
			}
			if(strpos($v,'Read-only file system')){
				$retval = 'RO';			
			} 
		}
		return $retval;
	} else {
		return 'RW';	
	}
}
/*
echo "<br><br>DeviceEnabled:";
echo "<br>&nbsp;&nbsp;&nbsp;ndas-44809965-1: ";
echo ndasIsDeviceEnabledRwOrRo('ndas-44809965-1');
echo "<br>&nbsp;&nbsp;&nbsp;ndas-44700486-0: ";
echo ndasIsDeviceEnabledRwOrRo('ndas-44700486-0');
*/


/* Find out if the partition on the ndas block device is writeable. */ 
function ndasIsBlockDeviceWritable($device){
	/* Notes: www-user needs mkdir and mount permission.
	 *	 		 shared rw is still writeable so return 0.
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
	
	include('./config.php'); /* needs web root folder for mkdir */
	$output = Array();
	$return = null;
	$retval = '';
		
	/* ) determine if a partition exists. devname-#p# typically */
	$command = "ls $device | grep p  2>&1";
	exec($command,$output,$return);
	if ($return > 0) {
		if ($LOG_LEVEL >= 2){
			$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|ls|No partitions on $device";
			ndasErrorLogger(2,$message);
		}
		return "Error: 2";
	} 
	
	/* if we got here, we have at least one partition on the device. */
	$partitionToTest = $device;
	unset($output);
	unset($return);
	unset($v);
	
	/* ) get the file system type blkid */
	$command = 'sudo /sbin/blkid -s TYPE -o value '.$partitionToTest.'  2>&1';
	exec($command,$output,$return);
	if ($return > 0) {
		if ($LOG_LEVEL >= 2){
			$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|blkid|$v";
			ndasErrorLogger(2,$message);
		}
		return "Error: 3";
	} 
	
	/* if we got here, we have at least one partition on the device. */
	$partitionFileSystemType = $output[0];
	unset($output);
	unset($return);
	unset($v);

	/* If it is ntfs, we can use ntfs-3g.probe to determine if it can be mounted rw */
	if ($partitionFileSystemType == 'ntfs') {
		$command = "sudo /bin/ntfs-3g.probe --readwrite $partitionToTest 2>&1";
		exec($command,$output,$return);
		
		if ($return > 0) {
			if ($return == 19){
				if ($LOG_LEVEL > 0){
					$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
					$return."|user has no permission for this tool.";
					ndasErrorLogger(1,$message);
				}
				return "3gProbeErr: $return";
			}

			/* Print any messages just in case admin wants to check later */
			foreach ($output as $v ){
				if ($LOG_LEVEL > 0){
					$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|ntfs-3g.probe|return: " . 
					$return."|$v";
					ndasErrorLogger(2,$message);
				}
			}
			unset($output);
			
			/* test if device can be mounted RO. It is safe even if errors on RW. */
			$command = "sudo /bin/ntfs-3g.probe --readonly $partitionToTest 2>&1";
			exec($command,$output,$return);
			if ($return == 0) 
				return 'RO';
			else 
				return '3gRoErr: '.$return;		

		} else {
			return 'RW';	
		}
	} else {
		/* create a temp folder */  
		$tempMountingDirectory = $WEB_ROOT . $INSTALL_DIR . 
			str_replace('/dev', '', $device); 
		if (!is_dir($tempMountingDirectory) ) {
			if (!mkdir($tempMountingDirectory,0777)){
				if ($LOG_LEVEL > 0){
					$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|mkdir|web user could " .
					"not mkdir($tempMountingDirectory).";
					ndasErrorLogger(1,$message);
				}
				return "Error 4"; 
			}
		}	
		/* what if it was there and is not empty? There is a risk of losing data. */
		if (count(glob("$tempMountingDirectory/*")) !== 0) {
			if ($LOG_LEVEL > 0){
				$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|mkdir|attempted to mount " .
				"ndas device to non-empty directory.";
				ndasErrorLogger(1,$message);
			}
			return "Error: 5"; 	
		}
		
		/* see what we get with mount command 
			-n "no mtab entry" 
			-w "explicit read/write" */
		$command = "sudo /bin/mount -nw -t $partitionFileSystemType $partitionToTest $tempMountingDirectory 2>&1";
		exec($command,$output,$return);
		if ($return > 0) {
			if ($LOG_LEVEL > 0){
				$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|$command|failed.";
				ndasErrorLogger(2,$message);
			}

			foreach ($output as $v) { 
				if ($LOG_LEVEL >= 3){
					$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|mount|$v.";
					ndasErrorLogger(3,$message);
				}
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
			/* If the file system mounted rw, we must unmuont. We did not write 
			 * in the mtab entry before so it must dismount here with the directory. */
			$command = "sudo /bin/umount -l $tempMountingDirectory 2>&1";
			exec($command,$output,$return);
			if ($return > 0) {
				foreach ($output as $v) { 
					if ($LOG_LEVEL >= 3){
						$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|umount|$v.";
						ndasErrorLogger(3,$message);
					}
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
				if ($LOG_LEVEL >= 2){
					$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|rmdir|temp dir is not empty.";
					ndasErrorLogger(2,$message);
				}
				$retval .= "Error: 9 "; 	
			} else if (!rmdir($tempMountingDirectory)){
				if ($LOG_LEVEL > 0){
					$message = "|netdisk.functions.php|isNdasBlockDeviceWritable|rmdir|web user could " .
					"not remove temp directory with php rmdir.";
					ndasErrorLogger(2,$message);
				}
				$retval .= "Error 10 "; 
			}
		}	
		return $retval;
	} 
}
 
/*
echo DeviceWritable:
echo "<br>&nbsp;&nbsp;&nbsp;ndas-44700486-0: ";
echo ndasIsDeviceWritable('ndas-44700486-0');
echo "<br>&nbsp;&nbsp;&nbsp;ndas-44809965-1: ";
echo ndasIsDeviceWritable('ndas-44809965-1');
*/
?>
