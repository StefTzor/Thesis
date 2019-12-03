<?php 

require_once("load.php");

// Store the file name into variable
$url = $_GET['url'];
$file = base64_decode(urldecode($url));
$file_name = $_GET['name'];
$filename = base64_decode(urldecode($file_name));

// Quick check to verify that the file exists
if( !file_exists($file) ) die("File not found");
  
// Header content type 
header('Content-type: application/pdf'); 
  
header('Content-Disposition: inline; filename="' . $filename . '"'); 
  
header('Content-Transfer-Encoding: binary'); 
  
header('Accept-Ranges: bytes'); 
  
// Read the file 
@readfile($file); 
  
?> 