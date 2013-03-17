<div id="footer">
<hr>
	<div class="footer-info"><img src="../img/ndas.for.linux.tux.100px.h.png"></div>
	
	<div class="footer-info">
		<a href="logout.php">Logout</a> <br>
		<a href="http://www.iocellnetworks.com">Visit Us</a> <br>
		<a href="https://github.com/iocellnetworks">Developers</a> <br>
		<a href="http://www.iocellnetworks.com">Freebies</a> <br>
		<a href="http://www.biblegateway.com" >Bible</a><br>
	</div>

	<?php
	if ($_SESSION['USERGROUP'] === 'admin'){
	?>
	<div class="footer-info">
		<a href="folder_manage.php">Folders</a> <br>
		<a href="user_manage.php">Users</a> <br>
		<a href="settings.php">Settings</a> <br>
		<a href="support.php">Support</a> <br>
	</div>
	
	<?php } ?>
</div>
</body>
</html>
