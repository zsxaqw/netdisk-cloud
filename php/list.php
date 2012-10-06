<?php
include('auth.php');
include('netdisk.functions.php');

$page_title = "NETDISK CLOUD ADMIN";
include('header.php');

$admin = false;
if($_SESSION['USERGROUP'] == 'admin') {

	echo "<script language='javascript'>
	function ndasSlotInfo(url)
	{
		newwindow=window.open(url,'_infondas','width=400,height=400');
	}
	function ndasOpsReg()
	{
		window.open('/cgi-bin/register.cgi','_regndas','width=300,height=300');
	}
	function ndasOpsUnReg()
	{
		window.open('/cgi-bin/unregister.cgi','_unregndas','width=300,height=300');
	}
	function ndasOpsRO()
	{
		window.open('/cgi-bin/enable_read.cgi','_rondas','width=300,height=300');
	}
	function ndasOpsRW()
	{
		window.open('./enable_write.php','_rwndas','width=300,height=300');
	}
	function ndasOpsDis()
	{
		window.open('/cgi-bin/disable.cgi','_disndas','width=300,height=300');
	}
	</script>";
	echo "<table width=100%>";
	echo "<tr><td align=left>";
	echo "<h2>NETDISK Cloud Server Info</h2>";

	$lanhostname=exec("hostname -f");
	echo "HOST: ";
	if ( $lanhostname ) 
		echo $lanhostname;
	else
		echo "Unable to determine.";

	$ip= exec("ip -o -f inet addr | grep eth | cut -d/ -f 1 | cut -d' ' -f 7");
	echo "<br>LAN IP: ";
	if ( $ip ) 
		echo $ip;
	else
		echo "Unable to determine.";

	$wanip=file_get_contents($WAN_IP_DETECTION_URL);
	echo "<br>WAN IP: ";
	if ( $wanip ) 
		echo $wanip;
	else
		echo "Unable to determine.";

	echo "<br>SESSION: ". session_id();
	echo "</td></tr>
	<tr><td>";
	echo "<h2>Registered NDAS Devices</h2>
	<table border=1>";
	if ( !is_file('/proc/ndas/devs') ) {
		echo "NDAS device file cannot be found. NDAS might be stopped.";
	} else {
		$ndas_devs = fopen("/proc/ndas/devs","r");
		$count = 0;
		if ( !$ndas_devs ) {
			echo "Unable to read NDAS device file.";
		} else {
			echo "<tr><td>Name</td><td>Block Device</td><td>Key</td>";
			echo "<td>Slot</td><td>Status</td><td>Connection</td><td>Actions</td></tr>";
			while ( $line = fgets($ndas_devs, 1024) ) {
				if ( $count == 0 ) {
					$count++;
					continue;
				}

				//echo "<tr><td colspan=6>$line</td></tr>";
				$name = strtok($line," \t\n");
				$id = strtok(" \t\n");
				$key = strtok(" \t\n");
				$serial = strtok(" \t\n");
				$ndasversion = strtok(" \t\n");
				$online = strtok(" \t\n");
				$slots = trim(strtok("\t\n"));
		
				if ( $slots == "" ) { 
					$slot = 'N/A';
				} else {
					$slotArray=explode(" ",$slots);
					foreach ($slotArray as $slot){
						echo '<tr><td>'.$name.'</td>'; 

						/* Try to determine the block device file */
						$block_file = "/proc/ndas/slots/$slot/devname";
						if ( is_file($block_file) && $handle = fopen($block_file, "r") ) {
							$line = exec("cat $block_file");
							if (!empty($line)){
								$blockdev = strtok($line, " \t\n");
								if($online === 'Online'){
									$blockdev = '<a href="slotinfo.php?slot='.$slot.'" 
										onClick="ndasSlotInfo(this.href); return false;"
										title="Open the device detail window" 
										target="_infondas">'.$blockdev.'</a>';
								}						
							} else {
								$blockdev = "Unassigned";
							}
							fclose($handle);
						} else {
							$blockdev = "No Dev: ndas-$serial";
						}
						echo '<td>'.$blockdev.'</td>'; 

						echo '<td>'.$key.'</td>'; 
						echo '<td>'.$slot."</td>"; 
						echo '<td>'.$online."</td>";
						$status_file = "/proc/ndas/slots/".$slot."/info";
						if ( is_file($status_file) && $handle = fopen($status_file, "r") ) {
							fgets($handle);
							$line = fgets($handle);
							$status = strtok($line, " \t\n");
							fclose($handle);
						} else {
							$status = "N/A";
						}
						if ( $status == "Enabled") {
							//find the device name and see if it is enabled RO or RW
							$devname_file = "/proc/ndas/slots/".$slot."/devname";
				
							if ( is_file($devname_file) && $handle = fopen($devname_file, "r") ) {
								$line = fgets($handle);
								$devname_str = $line;
								fclose($handle);
							} else {
								$devname_str = "x";
							}				
							//now ask the netdisk.functions for the enabled mode
							$devmode = ndasIsDeviceEnabledRwOrRo($devname_str);
							$status .= " $devmode";
				
							echo "<td>".$status."</td>";
							echo "<td>";
							echo "<form style='display:inline' action=manage.php method=get>";                 
							echo "<input value=\"Manage partitions\" type=submit>"; 
							echo "<input name=slot value=\"".$slot."\" type=hidden>";
							echo "</form>"; 
							echo "<form style='display:inline' action=/cgi-bin/disable.cgi method=get
									target='_disndas' onSubmit='javascript:ndasOpsDis()'>";
							echo "<input value=Disable type=submit>";
							echo "<input name=slot value=\"".$slot."\" type=hidden>";
							echo "</form>";                                                      
							echo "</td>";
						} else {
							echo "<td>".$status."</td>";
						  echo "<td>";

						  if ( $status == "Disabled" && $online == 'Online') {
							  echo "<form style='display:inline' name=enable action=/cgi-bin/enable_read.cgi
										target='_rondas' onSubmit='javascript:ndasOpsRO()'>";
							  echo "<input value='Enable RO' type=submit>";                   
							  echo "<input name=slot value=\"".$slot."\" type=hidden>";   
							  echo "</form>";                                          
							  echo "<form style='display:inline' name='enable' action='./enable_write.php'
										target='_rwndas' onSubmit='javascript:ndasOpsRW()' method='post'>";
							  echo "<input value='Enable RW' type=submit>";                   
							  echo "<input name=slot value=\"".$slot."\" type=hidden>";   
							  echo "</form>";                                          
						  }
						  echo "<form style='display:inline' name=disable action=/cgi-bin/unregister.cgi method=GET
								target='_unregndas' onSubmit='javascript:ndasOpsUnReg()'>";
						  echo "<input value=Unregister type=submit>";               
						  echo "<input name=name value=\"".$name."\" type=hidden>";   
						  echo "</form>"; 
						  echo "</td></tr>";
						} //if/else ( $status == "Enabled")
					}//foreach ($slotArray as $slot)
				}//else (slot != '')

				$count++;
			}
		}
	}
	fclose($ndas_devs);
?>
	</table>

	</td></tr>
	<tr><td>
	<h2>Register a new NDAS device</h2>
	<table><tr><td colspan="3">Browse for a registration file and read it in.</td></tr>
	<tr><td><input type="file" id="files" name="file" ></td>
		<td><span class="readBytesButtons"><button>Read NDAS File</button></span></td>
		<td>&nbsp;</td></tr>
	<tr><td colspan="3" >Or enter the ID, Key and Name below then click the "Register" button.</td><tr>
	<form name='register' action='/cgi-bin/register.cgi' method=GET
		target='_regndas' onSubmit='javascript:ndasOpsReg()'>
	<tr><td>ID:
		<input name=id1 id=id1 maxlength=5 size=5 type=text class="ndasid">-
		<input name=id2 id=id2 maxlength=5 size=5 type=text class="ndasid">-
		<input name=id3 id=id3 maxlength=5 size=5 type=text class="ndasid">-
		<input name=id4 id=id4 maxlength=5 size=5 type=text class="ndasid"></td>
	<td>KEY:
		<input name=id5 id=key maxlength=5 size=5 type=text class="ndasid"></td>
	<td>NAME:
		<input name=id6 id=name type=text class="ndasname"></td></tr>
	<tr><td>&nbsp;</td><td>&nbsp;</td>
		<td><input name=register-button value="Register" type=submit ></td></tr>
	</table>
	</form>
	

  
<div id="byte_range"></div>
<div id="byte_content"></div>

<script>
function readBlob(opt_startByte, opt_stopByte) {
  
    var files = document.getElementById('files').files;
    if (!files.length) {
      alert('Please select a file!');
      return;
    }
    var file = files[0];

	 //make sure it is a proper type
	 var fname=file.name;
	 if (fname.substr(-4,4) != 'ndas'){
	 	alert('The file name should end with a ".ndas" extension');
	 	return;
	 }

    var start = parseInt(opt_startByte) || 0;
    var stop = parseInt(opt_stopByte) || file.size - 1;

    var reader = new FileReader();

    // If we use onloadend, we need to check the readyState.
    reader.onloadend = function(evt) {
      if (evt.target.readyState == FileReader.DONE) { // DONE == 2
        var ndasFile=evt.target.result;
			if (window.DOMParser)
			  {
			  parser=new DOMParser();
			  xmlDoc=parser.parseFromString(ndasFile,"text/xml");
			  }
			else // Internet Explorer
			  {
			  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
			  xmlDoc.async=false;
			  xmlDoc.loadXML(ndasFile);
			  } 
        	//we'll test each part for usability.
        	//name should not use spaces in this linux driver
        	var tname=xmlDoc.getElementsByTagName("name")[0].childNodes[0].nodeValue;
        	xname=tname.replace(/ /gi,'_');
        	
        	//id should be 20 characters
        	var xid=xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue.toUpperCase();
        	if (xid.length != 20){ 
        		alert('Error: NDAS ID is not 20 characters.'); 
        		return;
        	}
        	
        	//key should be 5 characters, but it might also be blank
        	var xkey=xmlDoc.getElementsByTagName("writeKey")[0].childNodes[0].nodeValue.toUpperCase();
        	if (xkey.length != 5){
        		if (xkey.length != 0){ 
        			alert('Error: Write Key should be blank or 5 characters long.'); 
        			return;
        		}
        	}
        	
			//basic check is complete. fill in the form
        	document.getElementById('id1').value = xid.substr(0,5); 
         	document.getElementById('id2').value = xid.substr(5,5); 
         	document.getElementById('id3').value = xid.substr(10,5); 
         	document.getElementById('id4').value = xid.substr(15,5); 
         	document.getElementById('key').value = xkey; 
         	document.getElementById('name').value = xname; 
         	document.getElementById('files').value = '';  
		}
    };

    var blob = file.slice(start, stop + 1);
    reader.readAsBinaryString(blob);
  }
  
  document.querySelector('.readBytesButtons').addEventListener('click', function(evt) {
    if (evt.target.tagName.toLowerCase() == 'button') {
      var startByte = evt.target.getAttribute('data-startbyte');
      var endByte = evt.target.getAttribute('data-endbyte');
      readBlob(startByte, endByte);
    }
  }, false);
</script>
	</td></tr>
	<tr><td>
	<h2>Mounted directories</h2>
	<table border=1>
	<tr><th>Block Device</th><th>Mount Path</th><th>Connection</th></tr>
<?php
		  $handle = fopen("/proc/mounts", "r"); 
		  while ( $line = fgets($handle, 1024) ) {
		    if ( strpos($line, "/dev/nd") === false ) continue;
		    $tok = strtok($line, " \t\n");
		    echo "<tr><td>".$tok."</td>";
		    $enabledmode = ndasIsBlockDeviceWritable($tok);
		    $tok = strtok(" \t\n");
		    echo "<td>";
			 $needle = $TOP_MOUNTABLE_DIRECTORY."/";
		    echo "<a href=\"file.php?path=".base64_encode($tok)."\">".str_replace($needle,'',$tok)."</a>";
		    echo "</td><td>$enabledmode</td></tr>";
		  }
		  echo "</td>\n";
		  fclose($handle);
	echo "</table>
	</td></tr>
	</table>
	<br>
	<a href=admin.php>Admin</a>";
}
else
{
	// user
	echo "<h1>Mounted directories</h1>";
	echo "<table border=1>
	<tr><td>Disk</td></tr>";
		  $handle = fopen("/proc/mounts", "r"); 
		  while ( $line = fgets($handle, 1024) ) {
			if ( strpos($line, "/dev/nd") === false ) continue;
			$tok = strtok($line, " \t\n");
			//echo "<tr><td>".$tok."</td>";
			$tok = strtok(" \t\n");
			echo "<tr><td>";
			 $needle = $TOP_MOUNTABLE_DIRECTORY."/";
		    echo "<a href=\"file.php?path=".base64_encode($tok)."\">".str_replace($needle,'',$tok)."</a>";
			echo "</td></tr>";
		  }
		  fclose($handle);
		  echo "</td>\n";
	echo "</table>";
}
?>
<br>
<a href="logout.php">Logout</a>
<hr>
<img src="http://ndas4linux.iocellnetworks.com/trac/files/ndas.for.linux.tux.100px.h.png">
</body>
</html>
