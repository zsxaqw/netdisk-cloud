#!/bin/sh
cat <<EOF

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    <title>Disable NDAS Device</title>
</head>
<body>
EOF

eval `echo $QUERY_STRING | sed -e 's|&|\n|g' | grep ^slot=`
<h2>Disable NDAS Slot 
echo "<h2>Disable NDAS Slot $slot</h2>"

# Must see if it is mounted and unmount if needed.
DEVICE_FILE=/dev/`cat /proc/ndas/slots/$slot/devname` 
LINES=`df -k | grep ^$DEVICE_FILE | cut -d' ' -f1`
if [ "$LINES" != "" ]; then
	echo "File systems in use<br>$LINES<br><br>Choose 'Manage Partitions' to attempt dismounting. Then try again to disable this slot."
else
    echo "Attempting to disable slot \"$slot\"<br>"
    RESULT=`sudo /usr/sbin/ndasadmin disable -s $slot 2>&1`
    if [ $? -eq 0 ] ; then
        echo "OK!"
		sleep 3
    else
        echo "Operation failed! Error: $RESULT"
    fi
fi

cat <<EOF
<script>
	opener.location.href='../web-ui/php/list.php'
</script>
<br>
<a href='javascript:self.close()'>Close window</a>
</body>
</html>
EOF
