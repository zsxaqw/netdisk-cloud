<?php 
//ini_set('upload_max_filesize','8M');
//ini_set('post_max_size','8M');
//phpinfo();
//die();

include('auth.php');

$path = urldecode($_POST['path']);
$uploaddir = $path.'/' ;

if( empty($path) )
{
   die("Destination error: $path"."<br>FileMax: ". ini_get('upload_max_filesize') ."<br>PostMax: ".ini_get('post_max_size') );
}
else if( ! $_FILES['fileToUpload']['name'])
{
   die("No file yo!");
}

else
{
	// make a note of the current working directory, relative to root. 
	$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']); 

	// make a note of the directory that will recieve the uploaded file 
	$uploadsDirectory = $path; 

	// make a note of the location of the upload form in case we need it 
	$uploadForm = 'http://' . $_SERVER['HTTP_HOST'] . $directory_self . 'choose_file.php'; 

	// possible PHP upload errors 
	$errors = array(1 => 'php.ini max file size exceeded'.
						"<br>FileMax: ". ini_get('upload_max_filesize') .
						"<br>PostMax: ".ini_get('post_max_size') , 
		            2 => 'html form max file size exceeded', 
		            3 => 'file upload was only partial', 
		            4 => 'no file was attached'); 


	// check for PHP's built-in uploading errors 
	($_FILES['fileToUpload']['error'] == 0) 
		or die('Error: '. $errors[$_FILES['fileToUpload']['error']]); 
		 
	// check that the file we are working on really was the subject of an HTTP upload 
	@is_uploaded_file($_FILES['fileToUpload']['tmp_name']) 
		or die('not an HTTP upload'); 

   $uploadfile = $uploaddir . basename($_FILES['fileToUpload']['name']);

   if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadfile))
   {
      echo "Success!";
	  
   }
   else
   {
      echo "Failed<br>
		Error: ". $_FILES['fileToUpload']['error'];
   }
}
?>

