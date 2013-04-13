<?php
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}

include('netdisk.functions.php');

$mount_devi=isset($_GET['mount_devi'])? $_GET['mount_devi'] : false;
$mount_path=isset($_GET['mount_path'])? $_GET['mount_path'] : false;;
$mount_slot=isset($_GET['mount_slot'])? $_GET['mount_slot'] : false;;
$mount_type=isset($_GET['mount_type'])? $_GET['mount_type'] : false;;

function isEmptyDir($dir){
     return (($files = @scandir($dir)) && count($files) <= 2);
} 
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    <link rel="stylesheet" href="../css/styles.css" type="text/css">
    <title>Mount NDAS Volume</title>
</head>
<body>
<b>Mount NDAS:</b><br><br>
<?php

/* It must be a "ndas-*" device or we quit the script. */
$needle='/dev/ndas-';
if ( strpos($mount_devi, $needle) >= 0 ) {

	#Check if the mount point is an empty directory and mount if it is.
	if (is_dir($mount_path)) {
		if ( isEmptyDir($mount_path) ){
			$SUCCESS=0;
			$ROMOUNT=' ';
			echo "Device:&nbsp;$mount_devi
				<br>Type:&nbsp;$mount_type
				<br>Directory:&nbsp;". 
				str_replace("$TOP_MOUNTABLE_DIRECTORY/", '', $mount_path)."<br>";
			
			/* use netdisk.function to find out if the partition is writeable */
			$writeable = trim(ndasIsBlockDeviceWritable($mount_devi));
			if ($writeable === 'RW'){
				echo "Mode: Read / Write<br>";
				
			} else {
				$ROMOUNT=" -o ro ";
				if ($writeable === 'RO') {
					echo "Mode: Read Only<br>";  
				} else {
					echo "Mode: Attempting Read Only Mode with error: ($writeable)<br>";  
				}
			}

			$output = Array();
			$return = null;
			$retval = '';

			/* ntfs file system uses different mounting options. */
			if ($mount_type === "ntfs") {
				$ntfs3g = findNtfs3g();
				$command ="sudo $ntfs3g $ROMOUNT $mount_devi $mount_path 2>&1";
				exec($command,$output,$return);
				if ($return > 0) {
					$message = date('Y-m-d H:i:s'). "|mount.php|failed 
						to mount $mount_devi on $mount_path\n";
					foreach ($output as $v ){
						$message .= date('Y-m-d H:i:s') .
						"|mount.php|output|$v\n" ;
					}
					ndasPhpLogger(3,$message);
					$SUCCESS=1;
				}
				
			} else {
				/* other file systems can use the standard mount command */
				# If it is exX file system, there could be ownership problems.
				# We have to see if this is a new disk. The owner must be set if it is.
				# Otherwise, root will own the filesystem and we can't write data.

				$command ="sudo /bin/mount -t $mount_type $mount_devi $mount_path $ROMOUNT 2>&1";
				exec($command,$output,$return);
				if ($return > 0) {
					$message = date('Y-m-d H:i:s'). "|mount.php|failed 
						to mount $mount_devi on $mount_path\n";
					foreach ($output as $v ){
						$message .= date('Y-m-d H:i:s') .
						"|mount.php|output|$v\n" ;
					}
					ndasPhpLogger(1,$message);
					$SUCCESS=1;
				} else {
					
					/* If the NDAS device is enabled read only, we are done, 
					 * if not, we must check the ownership of the drive, and
					 * make the web account the owner or there may be trouble
					 * accessing files via web interfaces. 
					 */ 
					if (empty($ROMOUNT)) {
						echo "<pre>";
						echo print_r(posix_getpwuid(fileowner($mount_path )));
						echo "</pre>";
/*						IS_OWNER_ROOT=`ls -d $mount_path -l | cut -d' ' -f 3  2>&1`
					if [ "$IS_OWNER_ROOT" = "root" ] ; then
						CHANGE_OWNER=1
					else
						IS_GROUP_ROOT=`ls -d $mount_path -l | cut -d' ' -f 4  2>&1`
						if [ "$IS_GROUP_ROOT" = "root" ] ; then
							CHANGE_OWNER=1
						fi				
					fi
					if [ $CHANGE_OWNER -eq 1 ] ; then
						# Discover current apache username in case we neet to give
						# ownership to the web server.
						WWW=`ps aux | grep apache | grep -c www-data  2>&1` 
						if [ $WWW -gt 0 ]; then
							RESULT=`sudo /bin/chown www-data:www-data $mount_path  2>&1`
						else
							HTTPD=`ps aux | grep apache | grep -c httpd  2>&1`
							if  [ $HTTPD -gt 0 ]; then
								RESULT=`sudo /bin/chown httpd:httpd $mount_path  2>&1`
							else 
								NOBODY=`ps aux | grep apache | grep -c nobody  2>&1`
								if [ $NOBODY -gt 0 ]; then
									RESULT=`sudo /bin/chown nobody:nobody $mount_path  2>&1`
								else 
									ROOTAP=`ps aux | grep apache | grep -c root  2>&1`
									if [ $ROOTAP -gt 0 ]; then
										# already root so we don't need to change the owner. 
										RESULT="WARN: Apache is root user."
									else
										RESULT="ERROR: Mounted, but ownership is not set."
									fi
								fi
							fi
						fi
					fi
					# See if that even worked
					if [ "$RESULT" != "0" ]; then
						SUCCESS=1
					fi
				else
					SUCCESS=1
				fi

				else
					echo "ERROR: No Permission to mount device."
				fi
*/
				
					} else {
						$SUCCESS = 0;
					} /* if exX and is read only device */
				
				}/* mounting the exX volume suceeded */ 
					
			} /* if ntfs or not */
		
			if ($SUCCESS === 0 ) 
				echo "<br>Success!<br>";
			else
				echo "<br>$RESULT<br>";
		
		
		} else {
			echo "ERROR: $mount_path is not empty.";
		}
		
	} else {
		echo "ERROR: Path `". $mount_path. "` cannot be used as a mountpoint.";
	}
	
} else {
	echo "ERROR: $mount_devi seems not to be an NDAS device.";
}
?>
<script>
	opener.location.href='manage.php?slot=<?php echo $mount_slot; ?>'
</script>
<br>
<a href='javascript:self.close()'>Close window</a>
</body>
</html>
