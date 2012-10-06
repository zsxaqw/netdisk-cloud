<?php
/* This is just the example to get, set and delete he users session id. */
 
if (session_id()){ 
	session_destroy();
}


session_start();
//session_regenerate_id(true);

/* Delete the saved session data based on the id and/or current session 
 * for the user. It happens on logout, when someone sends a fake a url or 
 * form data as a username or when folder access is requested but the session
 * has expired. */
function deleteSavedSessionId($uname){
	$ufile = "netdisk.sessions";
	$reval = 0;
	if(!file_exists($ufile))
	{
		return "netdisk_error: dssi 1";
	}
	$fp = fopen($ufile,"r");
	if(!$fp)
	{
		return "netdisk_error: dssi 2";
	}
	if(!flock($fp, LOCK_EX))
	{
		return "netdisk_error: dssi 3";
	}

	$userSessionsArray = Array();
	$currentSession = session_id();
	
	/* Read each line of the session file while searching for the existing user
	 * or current session id. Skip blank lines and the user we want to delete
	 * and add all other existing sessions to the array. */
	$userid = null;
	$usersession = null;
	while(!feof($fp))
	{
		fscanf($fp," %s %s\n",$userid,$usersession);
		if ( !empty($userid) && !empty($usersession) 
			&& ($usersession != $currentSession) && ($userid != $uname)){
			$userSessionsArray[$userid] = $usersession;
		}
	}

	flock($fp,LOCK_UN);
	fclose($fp);
	
	/* Write all the users and sessions back to the file. */
	$fp = fopen($ufile,"w");
	if(flock($fp, LOCK_EX))
	{
		foreach ($userSessionsArray as $k => $v){
			if( !empty($v) && !empty($k) ) {
				$stringAllData = $k . " " . $v . "\n";
				fwrite($fp,$stringAllData);
			}
		}
		flock($fp,LOCK_UN);
		fclose($fp);
		return 0;

	} else {
		/* Return 4 and let them log in again. */
		fclose($fp);
		return "netdisk_error: dssi 4";
	}
}

echo "<h3>Test get,set,delete session id</h3>";
echo deleteSavedSessionId('jake')
?>

