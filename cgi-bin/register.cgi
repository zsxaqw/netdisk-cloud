#!/bin/sh
cat <<EOF

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-US">
<head>
	<title>Register NDAS Device</title>
</head>
<body>
	<h2>Register NDAS Device</h2>
EOF
eval `echo $QUERY_STRING | sed -e 's|&|\n|g' | grep ^id[1-6]=`
echo "Register: $id6 <br>ID: $id1-$id2-$id3-$id4<br>KEY: $id5 <br><br>"
RESULT=`sudo /usr/sbin/ndasadmin register $id1-$id2-$id3-$id4-$id5 --name "$id6" 2>&1`
if [ $? -eq 0 ] ; then

	echo "Registration OK!<br>";
	sleep 3
else

	echo "Registration Failed.<br><br>Error: $RESULT<br>";

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

