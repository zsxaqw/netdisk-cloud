#!/bin/bash
cat <<EOF

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    </title>Mount NDAS Volume</title>
</head>
<body>
<h2>Mount NDAS</h2>
EOF
eval `echo $QUERY_STRING | sed -e 's|%2F|/|g' -e 's|&|\n|g' | grep ^mount_`

# It must be a "ndas-*" device or we quit the script.
needle='/dev/ndas-'
if [[ "$mount_devi" == "$needle"* ]]; then

	#Check if the mount point is an empty directory and mount if it is.
	RESULT=`[ "$(ls -A $mount_path)" ] && echo "Not Empty" || echo "Empty" 2>&1`
	if [ "$RESULT" = "Empty" ]; then 
		SUCCESS=0
		ROMOUNT=' '
		echo "Device:&nbsp;$mount_devi<br>Type:&nbsp;$mount_type<br>Directory:&nbsp;$mount_path<br>"
		if [ "$mount_type" = "ntfs" ]; then
			# check if it can be mounted rw
			RESULT=`sudo /bin/ntfs-3g.probe --readwrite $mount_devi 2>&1`
			if [ $? -ne 0 ] ; then
				ROMOUNT=" -o ro " 
			fi
			
			RESULT=`sudo /bin/ntfs-3g $ROMOUNT $mount_devi $mount_path 2>&1`
			if [ $? -ne 0 ] ; then
				SUCCESS=1
			fi
		else
			RESULT=`sudo /bin/mount -t $mount_type $mount_devi $mount_path 2>&1`
			# If it is exX file system, there could be ownership problems.
			# We have to see if this is a new disk. The owner must be set if it is.
			# Otherwise, root will own the filesystem and we can't write data.
			if [ $? -eq 0 ] ; then
				IS_OWNER_ROOT=`ls -d $mount_path -l | cut -d' ' -f 3  2>&1`
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
		fi

		if [ $SUCCESS -eq 0 ] ; then
			echo "<br>Success!<br>"
		else
			echo "<br>$RESULT<br>"
		fi
	
	else 
		echo "Error: Mount point is not empty."
	fi

else
	echo "ERROR: No Permission to mount device."
fi
echo "<script>"
echo "	opener.location.href='../web-ui/php/manage.php?slot=$mount_slot'"
echo "</script>"
cat <<EOF
<br>
<a href='javascript:self.close()'>Close window</a>
</body>
</html>
EOF
