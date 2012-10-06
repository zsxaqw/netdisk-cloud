<?php 
include('auth.php');

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>NETDISK Cloud - Upload</title>
</head>
<body>
<h2>Upload Files</h2>
<img src="http://ndas4linux.iocellnetworks.com/trac/files/ndas.for.linux.tux.100px.h.png"><br><br>
<?php
$path = urldecode($_POST['path']);
$uploaddir = '/mnt/ndas/' . $path. '/' ;
if( empty($path) || ! $_FILES['file']['name'])
{
   echo "No file is specified\n";
}
else
{
   $uploadfile = $uploaddir . basename($_FILES['file']['name']);

   if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
   {
      echo "File \""; 
      print($_FILES['file']['name']);
      echo "\" is successfully uploaded into the directory \""; 
      print($path);
      echo "\"\n";
   }
   else
   {
      echo "No file is specified\n";
   }
}
?>
<br>
<?php
//	echo "<form method=GET";
//	echo "      action=file.php>";
//	echo "<input type=hidden name=path value=\"";
 //       print(urlencode($path)); 
  //      echo "\">";
?>
<!--
<input value="Back" type=submit>
</form>
-->
<input type=button onclick="javascript:self.close()" value=Close>
</body>
</html>

