<?php 

require_once("load.php");

// Store the file name into variable 
$file = openssl_decrypt($_POST['url'], "AES-128-ECB", SECRETKEY);;
$filename = openssl_decrypt($_POST['file_name'], "AES-128-ECB", SECRETKEY);;
  
// Header content type 
header('Content-type: application/pdf'); 
  
header('Content-Disposition: inline; filename="' . $filename . '"'); 
  
header('Content-Transfer-Encoding: binary'); 
  
header('Accept-Ranges: bytes'); 
  
// Read the file 
@readfile($file); 
  
?> 