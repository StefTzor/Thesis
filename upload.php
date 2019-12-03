<?php
	require_once("load.php");
	if(isset($_POST["btnSubmit"])){		
		$errors = array();
		
		$extension = array("pdf");
		
		$bytes = 1024;
		$allowedKB = 20000;
		$totalBytes = $allowedKB * $bytes;
		
		if(isset($_FILES["files"])==false)
		{
			echo "<b>Δεν έχετε επιλέξει αρχεία</b>";
			return;
		}
		
		$connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$connect->set_charset("utf8mb4");	
		
		foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name)
		{
			$uploadThisFile = true;
			
			$file_name=$_FILES["files"]["name"][$key];
			$file_tmp=$_FILES["files"]["tmp_name"][$key];
			
			$ext=pathinfo($file_name,PATHINFO_EXTENSION);

			if(!in_array(strtolower($ext),$extension))
			{
				array_push($errors, "Ο τύπος του αρχείου είναι λάθος. ".$file_name);
				$uploadThisFile = false;
			}				
			
			if($_FILES["files"]["size"][$key] > $totalBytes){
				array_push($errors, "Το μέγεθος κάθε αρχείου πρέπει να είναι λιγότερο από 20MB. ".$file_name);
				$uploadThisFile = false;
			}
			
			/*if(file_exists("uploads/".$_FILES["files"]["name"][$key]))
			{
				array_push($errors, "Το αρχείο υπάρχει ήδη. ". $file_name);
				$uploadThisFile = false;
			} */
			
			if($uploadThisFile){
				if(isset($_POST['sender'])) $sender=$_POST['sender'];
				if(isset($_POST['professor_name'])) $professor_name=$_POST['professor_name'];

				$date = date("Y-m-d");
				$date_month = strtotime($date);
				$date_month = date('m',$date_month);
				$date_year = strtotime($date);
				$date_year = date('Y',$date_year);

				$filename = basename($file_name,$ext);
				$newFileName = $sender."_".$date."_".$filename.$ext;
				$aem = (int)basename($file_name,".$ext"); 

				if ( $date_month >= 1 && $date_month <= 4 )
				{
					$date_month = 'ΧΕΙΜ';
					$exam_period = 1;
				} elseif ( $date_month >= 5 && $date_month <= 8 )
				{
					$date_month = 'ΕΑΡ';
					$exam_period = 2;
				} elseif ( $date_month >= 9 && $date_month <= 12 )
				{
					$date_month = 'ΣΕΠΤΕΜΒΡΙΟΣ';
					$exam_period = 3;
				}

				$filename = basename($file_name,$ext);
				$newFileName = $sender."_".$date_year."-".$exam_period."_".$filename.$ext;
				$aem = (int)basename($file_name,".$ext"); 

				$upload_file =  '/home/grapta/' . $sender . '/' . $date_year . '/' . $date_month . '/' . $newFileName;
				$file_path_db = '/home/grapta/' .  $sender . '/' . $date_year . '/' . $date_month;

				$mk_new_file_path = '/home/grapta/' . $sender . '/' . $date_year . '/' . $date_month . '/';

				$chmod_subject = '/home/grapta/' . $sender . '/';
				$chmod_year = '/home/grapta/' . $sender . '/' . $date_year . '/';
				$chmod_month = '/home/grapta/' . $sender . '/' . $date_year . '/' . $date_month . '/';

				if (!file_exists($mk_new_file_path)) {
					mkdir($mk_new_file_path, 0777, true);
				}
				
				chmod($chmod_subject, 0777);
				chmod($chmod_year, 0777);
				chmod($chmod_month, 0777);

				if(file_exists($upload_file))
				{
					//array_push($errors, "To αρχείο" . " '" . $file_name . "' " . "υπάρχει ήδη");
					//Delete file record from the database
					$delete_query = "DELETE FROM user_files WHERE file_name='$newFileName'";
					mysqli_query($connect, $delete_query);	

					move_uploaded_file($_FILES["files"]["tmp_name"][$key], $upload_file);
					chmod($upload_file, 0777);
					$query = "INSERT INTO user_files(file_path, file_name, date, aem, subject_id, professor_name) VALUES ('$file_path_db', '".$newFileName."', curdate(), $aem, $sender, '$professor_name')";
					mysqli_query($connect, $query);				
				}
				else
				{	
					move_uploaded_file($_FILES["files"]["tmp_name"][$key], $upload_file);
					chmod($upload_file, 0777);
					$query = "INSERT INTO user_files(file_path, file_name, date, aem, subject_id, professor_name) VALUES ('$file_path_db', '".$newFileName."', curdate(), $aem, $sender, '$professor_name')";
					mysqli_query($connect, $query);	
				}
						
			}
		}
		
		mysqli_close($connect);
		
		$count = count($errors);
		
		if($count != 0){
			foreach($errors as $error){
				echo $error."<br/>";	//Send errors to the upload form.
			}
		}		
	} else {
		include( 'login.php' );
	}
?>
