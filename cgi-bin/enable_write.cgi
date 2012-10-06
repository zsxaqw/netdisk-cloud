#!/bin/sh
cat <<EOF

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>Enable NDAS RW</title>
</head>
<body>
<h2>Enable NDAS Device Read/Write</h2>
EOF
eval `echo $QUERY_STRING | sed -e 's|&|\n|g' | grep ^slot=`
echo "Enabling slot \"$slot\" in exclusive read/write mode. <br>" 
RESULT=`sudo /usr/sbin/ndasadmin enable -s $slot -o w 2>&1` 
if [ $? -eq 0 ] ; then

	sleep 3
	ID=`sudo /sbin/blkid 2>&1`
	echo "OK!<br>";

else

	echo "Operation failed.<br><br>Error: $RESULT<br>";

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
