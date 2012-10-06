<?php
include('auth.php');
include('header.php');

if ( ! $_GET['file']) {

   echo "No file is specified\n";

} else {

   $delete_file = urldecode($_GET['file']) ;
   $is_d = is_dir($delete_file);
   if ( is_file($delete_file) && unlink($delete_file) || 
        is_dir($delete_file) && rmdir($delete_file) )
   {
        echo "Deleted ";
		if ( $is_d ) 
        	echo "directory: "; 
        else
        	echo "file:"; 
		print(str_replace($WEB_ROOT,'',$delete_file));

   } else {
          echo "Fail to delete";
          print(str_replace($WEB_ROOT,'',$delete_file));
   } 
}
?> <br>
<form method=GET
    action="file.php">
<input name=path type=hidden value="<?php echo dirname($delete_file); ?>">
<input value="Back" type=submit>
</form>
</body>
</html>

