<?php
include('auth.php');
// Login & Session example by sde
// logout.php

/* 
 * session should be started by auth.php. 
 * you must start session before destroying it 
 */

session_destroy();

header("location: $INSTALL_DIR");
?>
