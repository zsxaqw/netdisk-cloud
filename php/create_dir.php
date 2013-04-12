<?php
include('auth.php');
if ( !userIsAdmin() ){
	die('Administrative access is required.');	
}
?>
<html>
<head>
<title>Create Directory</title>
</head>
<body>
<h2>Create Directory</h2>
<?php


$postpath = isset($_POST['path'])? $_POST['path'] : null;
$path = base64_decode($postpath); 
$newdir = isset($_POST['dir'])?$_POST['dir']:null;
$uploaddir = realpath($path);

if(empty($newdir))
{
	echo "Current Path: ". $path ."<br>";
	echo "<form action=\"create_dir.php\" method=POST>";
	echo "<label for=file>Directory: </label>";
	echo '<input type=hidden name=path value="'. base64_encode($path) .'">';
	echo "<input type=text name=dir value=$newdir>";
	echo "<center><input type=submit value=\"Create\"></center>";
}
else
{
	$newpath = $uploaddir .'/'. $newdir;
	
	//echo "<p>adduserid: ".$adduserid." adduserpw: ".$adduserpw."</p>";
	$ret = mkdir($newpath,0755);
	if($ret == TRUE)
	{
		echo "<h3>Success</h3>
			<p>Created subdirectory $newdir under $uploaddir.</p>
			<script>
				opener.location.href='./file.php?path=$postpath'
			</script>";

	}
	else
	{
		echo "<h3>Error: $ret</h3>
			Failed to create: ".$newdir;
	}
	echo "<input type=button onclick=\"javascript:self.close()\" value=Close>";
}
?>

</body>
</html>
