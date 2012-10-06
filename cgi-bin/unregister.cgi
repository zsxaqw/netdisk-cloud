#!/bin/sh
cat <<EOF

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>Unregister NDAS Device</title>
</head>
<body>
	<h2>Unregister NDAS Device</h2>
EOF
eval `echo $QUERY_STRING | sed -e 's|&|\n|g' | grep ^name=`
echo "Unregistering \"$name\" <br>" 
RESULT=`sudo /usr/sbin/ndasadmin unregister --name "$name" 2>&1`
if [ $? -eq 0 ] ; then

	echo "OK!<br>";
	sleep 3
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
