#!/bin/sh
cat <<EOF

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    </title>Unmount NDAS Volume</title>
</head>
<body>
<h2>Unmount NDAS</h2>
EOF
eval `echo $QUERY_STRING | sed -e 's|%2F|/|g' -e 's|&|\n|g' | grep ^umount_`

echo "Unmounting \"$umount_devi\" <br>"
RESULT=`sudo /bin/umount -v $umount_devi 2>&1`
if [ $? -eq 0 ] ; then
	echo "<div class=title>Successfully unmounted</div>"
else
	echo "<div class=title>?=$?<br>RESULT=$RESULT</div>"
fi
echo "<script>"
echo "	opener.location.href='../web-ui/php/manage.php?slot=$umount_slot'"
echo "</script>"
cat <<EOF
<br>
<a href='javascript:self.close()'>Close window</a>
</body>
</html>
EOF
