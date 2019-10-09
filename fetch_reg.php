<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("load.php");
$connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);  

$sql = "SELECT DISTINCT registered FROM `students`"; 

/* change character set to utf8 */
 if (!$connect->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
    exit();
} else {
    //printf("Current character set: %s\n", $connect->character_set_name());
} 
           
$result = mysqli_query($connect, $sql);  
           
$json_array = array();  
while($row = mysqli_fetch_assoc($result))  
{  
  $json_array[] = $row;  
} 

print json_encode($json_array, JSON_UNESCAPED_UNICODE);

?>