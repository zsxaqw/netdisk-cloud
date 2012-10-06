<?php
include('auth.php');
include('is_admin.php');

$moduserid = isset($_POST['moduserid'])? $_POST['moduserid'] : null;
$modify = isset($_POST['modify'])? $_POST['modify'] : null;
echo "<title>Modify User</title>";

if(!empty($moduserid))
{

	/* read the user file into a temporary array */
	$ufile = "netdisk.user";
	if(!file_exists($ufile))
	{
		echo "<h3>user information file is not exist: ".$ufile."</h3>";
		exit;
	}
	$fp = fopen($ufile,"r+");
	if(!$fp)
	{
		echo "<h3>Cannot open user information file: ".$ufile."</h3>";
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
	$arrusergrp = array();
	while(!feof($fp))
	{
		if(fscanf($fp,"%s %s %s\n",$userid,$userpw,$usergrp))
		{
			array_push($arruserid,$userid);
			array_push($arruserpw,$userpw);
			array_push($arrusergrp,$usergrp);
		}
	}
	
	/* find the array location of the user information */
	$index = array_search($moduserid,$arruserid);
	if($index >= 0)
	{
		/* capture the user index. */
		$userIndex = $index;
	}


	/* admin has filled in the modify form */
	if($modify == 'OK')
	{
		$moduserpw = isset($_POST['moduserpw'])? $_POST['moduserpw'] : null;
		$modusergrp = isset($_POST['modusergrp'])? $_POST['modusergrp'] : null;

		// modify user info
		/* encrypt the password if it was changed. */
		if (!empty($moduserpw)){
			$arruserpw[$index] = openssl_encrypt($moduserpw,$ENC_METHOD,$ENC_KEY,false,$ENC_16_CHAR_VECTOR);
		}

		/* change the users group if needed */
		if ($arrusergrp[$index] !== $modusergrp && !empty($modusergrp)){
			$arrusergrp[$index] = $modusergrp;
		}

		// write to netdisk.user file
		rewind($fp);
		ftruncate($fp,0);
		$usercount = count($arruserid);
		for($i=0; $i<$usercount;$i++){
			$line = sprintf("%s %s %s\n",$arruserid[$i],$arruserpw[$i],$arrusergrp[$i]);
			//echo "<p>line: ".$line."</p>";
			fwrite($fp,$line);
		}
		//
		flock($fp,LOCK_UN);
		fclose($fp);
		//
		if($index < 0)
		{
			echo "<h3>User ID '".$moduserid."' is not exist.</h3>";
		}
		else
		{
			echo"<script>
				opener.location.href='user_manage.php'
				//self.close();
				</script>";
			echo "<p>User ID '".$moduserid."' is successfully modified.</p>";
		}
	}
	else /* display the existing values in the modify form. */
	{
		$modusergrp = $arrusergrp[$userIndex];
		echo "<form action=\"moduser.php\" method=POST>";
		echo "<table>
			<td>User ID:</td><td>$moduserid</td>
			</tr>
			<tr>
			<td>Password:</td><td><input type='text' name='moduserpw' value=''></td>
			</tr>
			<tr>
			<td>Group:</td><td><select name='modusergrp'>
				<option value='user' ";
			if ($modusergrp === 'user') echo "selected ";
		echo ">user</option>
				<option value='admin' ";
			if ($modusergrp === 'admin') echo "selected ";
		echo ">admin</option>
				</select>
			</td>
			</tr>
			</table>
			<br>
			<input type='hidden' name='moduserid' value='$moduserid'>
			<input type='hidden' name='modify' value='OK'>
			<input type='submit' value='Modify'></form>";
	}
}
else
{
	echo "<h3>Abnormal Access</h3>
	<a href=\"javascript:self.close()\">Close</a>";
}
?>
