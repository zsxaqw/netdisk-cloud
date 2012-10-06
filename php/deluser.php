<?php
include('auth.php');
include('isadmin.php');

$deluserid = isset($_POST['deluserid'])? $_POST['deluserid'] : null;
echo "<title>Delete User</title>";

if(!empty($deluserid))
{
	// delete user info
	$ufile = "netdisk.user";
	if($deluserid === 'admin')
	{
		echo 'Error: Cannot delete admin.';
		exit;
	}
	if(!file_exists($ufile))
	{
		echo "<h3>User account file does not exist: ".$ufile."</h3>";
		exit;
	}
	$fp = fopen($ufile,"r+");
	if(!$fp)
	{
		echo "<h3>Cannot open the user account list: ".$ufile."</h3>";
		exit;
	}
	if(!flock($fp, LOCK_EX))
	{
		echo "<h3>Other connection is using the user information file</h3>";
		exit;
	}
	$find = false;
	$arruserid = array();
	$arruserpw = array();
	$arrusergp = array();
	while(!feof($fp))
	{
		if(fscanf($fp,"%s %s %s\n",$userid,$userpw,$usergrp))
		{
			array_push($arruserid,$userid);
			array_push($arruserpw,$userpw);
			array_push($arrusergp,$usergrp);
		}
	}
	$index = array_search($deluserid,$arruserid);
	if($index >= 0)
	{
		/* write the updataed netdisk.user file leaving out the deleted user. */
		rewind($fp);
		ftruncate($fp,0);
		$usercount = count($arruserid);
		for($i=0; $i<$usercount;$i++)
		{
			if($arruserid[$i] != $deluserid)
			{
				$line = sprintf("%s %s %s\n",$arruserid[$i],$arruserpw[$i],$arrusergp[$i]);
				fwrite($fp,$line);
			}
		}
	}
	flock($fp,LOCK_UN);
	fclose($fp);
	//
	if($index < 0)
	{
		echo "<h3>User ID '".$deluserid."' does not exist.</h3>";
	}
	else
	{
		echo"<script>
			opener.location.href='user_manage.php'
			//self.close();
			</script>";
		echo "<p>User ID '".$deluserid."' is successfully deleted.</p>";
	}
}
else
{
	echo "<h3>Abnormal Access</h3>";
}
echo "<a href=\"javascript:self.close()\">Close</a>";
?>
