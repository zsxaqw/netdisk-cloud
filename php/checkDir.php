<?php                                                                           
include('auth.php');

function checkDirPass($path)
{
	/* Use the session id to get the users name.
	 * Then see if access to the directory is allowed for that user. */ 
	  
	/* if user is admin, always return false to skip */
	if($_SESSION['USERID'] == 'admin')
	{
		return false;
	}
	if($path == $_SESSION['AUTHDIR'])
	{
		return false;
	}
	$dirkeys = unserialize(urldecode($_SESSION['DIRNAME'])); 
	//print_r($dirkeys);
//	return in_array($path,$dirkeys);
	if(in_array($path,$dirkeys))
	{
		//echo "true";
		$_SESSION['CDIR'] = $path;
		return true;
	}
	else
	{
		//echo "false";
		return false;
	}
}



if ( isset($_GET['path'])) {
	$dirPathEnc=$_GET['path'];
} else if ( !isset($_POST['path'])) {
	$dirPathEnc=$_POST['path'];
} else {
	die('Invalid string.');
}
$dirPath = base64_decode($dirPathEnc);
//die("Looking for: ".$dirPath);

// compare
if( checkDirPass($dirPath) )
{
	echo "<script>";
	echo "window.open('dirpass.php','dirpass','width=500, height=100')";
	echo "</script>";
}
else
{
	echo "<script>";
	echo "parent.location.href='file.php?path=$dirPathEnc'";
	echo "</script>";
}
?>
