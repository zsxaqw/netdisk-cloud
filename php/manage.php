<?php
include('auth.php');
include('netdisk.functions.php');
include('is_admin.php');

$PAGE_TITLE = "NETDISK CLOUD DISK PARTITION MANAGEMENT";
include("header.php");

$slot=isset($_GET['slot'])? $_GET['slot'] : null;
if (empty($slot)) 
$slot=isset($_POST['slot'])? $_POST['slot'] : null;
if (empty($slot)) 
	die("slot is not set");

?>
<script language='javascript'>
	function ndasUnmountVol()
	{
		window.open('umount.php','_unmountndas','width=300,height=300');
	}
	function ndasMountVol()
	{
		window.open('mount.php','_mountndas','width=300,height=300');
	}
</script>
<h2>Mount and Unmount the file systems.</h2>
<table border=1>
<tr><th>NDAS Device</th><th>Block Device</th><th>Label</th><th>FS Type</th><th>Mount Path</th><th>Action</th></tr>
<?php
$mount_path=array();
$empty_pdirs=array();
$empty_udirs=array();


/* Sometimes the partition information is incomplete just after enabling. */
$output = null;
$return_var = null;
exec(escapeshellcmd("sudo /sbin/blkid ", $output, $return_var));

/* get the /dev/ndas- name based on the slot */
$devname_str = ndasGetDeviceNameFromSlot($slot);

/* search partitions on the ndas device. */
$devfile="/dev/$devname_str";
exec(escapeshellcmd("ls $devfile* | grep p"), $mount_path, $return_var);

for($i=0;$i<count($mount_path);$i++)
{
	$d=$mount_path[$i];
	/* find the ndas device name */
	$dev_serial=substr($mount_path[$i],10,8);

	$mounted=exec("sudo /sbin/blkid $mount_path[$i]");
	 if($mounted){
		$type=exec("sudo /sbin/blkid $mount_path[$i] | awk -F ' TYPE=\"' '{print $2}' | awk -F'\"' '{print $1}'");
		$label=exec("sudo /sbin/blkid $mount_path[$i] | awk -F ' LABEL=\"' '{print $2}' | awk -F'\"' '{print $1}'");
	} else {
		$type=null;
		$label=null;
	}
	$path=exec("cat /proc/mounts | grep '^".$d."' | cut -d' ' -f2");
	$needle=$WEB_ROOT.$INSTALL_DIR."/";
	$local_path=str_replace($needle,"",$path);
	
	$ndas_device = ndasGetNdasDeviceNameFromSerial($devname_str);

?>
	<tr>
	<td><?php echo $ndas_device ?></td>
	<td><?php echo $d; ?></td>
	<td><?php echo $label; ?></td>
	<td><?php echo $type; ?></td>
	<?php
	 if ( $path == "") { 
		exec("find ".$TOP_MOUNTABLE_DIRECTORY."/ -maxdepth $SUB_DIR_LEVELS -type d -empty", $empty_pdirs);
		sort($empty_pdirs);
	?>
		<form style='display:inline' action='mount.php' method='get'
				target='_mountndas' onSubmit='javascript:ndasMountVol()'>
		<td><select name="mount_path">
		<?php
			
			foreach($empty_pdirs as $pdir){
				echo '<option value="'.$pdir.'">'. str_replace($TOP_MOUNTABLE_DIRECTORY."/","",$pdir) .'</option>\n';
			}
			unset($empty_pdirs);
		?></select></td>
		<td>
		<input name="mount_slot" value="<?php echo $slot; ?>" type="hidden">
		<input name="mount_devi" value="<?php echo $d; ?>" type="hidden">
		<input name="mount_type" value="<?php echo $type; ?>" type="hidden">
		<input value="Mount" type="submit">
		</td>
		</form>
	<?php } else {  
		$showdir=base64_encode($path);
		?>
		<td><a href="file.php?path=<?php echo $showdir;?>"><?php echo $local_path; ?></a></td>
		<td>
		<form style='display:inline' action='umount.php' method='get'
				target='_unmountndas' onSubmit='javascript:ndasUnmountVol()'>
		<input name="umount_slot" value="<?=$slot?>" type="hidden">
		<input name="umount_devi" value="<?=$d?>" type="hidden">
		<input name="umount_path" value="<?=$path?>" type="hidden">
		<input value="Unmount" type="submit">
		</form>
	<?php } ?>
</td>
</tr>
<?php 
}
?>
</table>
<form method="GET" action="list.php">
<input value="Back" type="submit">
</form>
Please note that you can not add/delete/modify or format the partitions from this interface.<br>
To change the disk structure, access the NDAS device from the computers as the adminstrative user.<br>
<br>
<?php
include ('footer.php');
?>