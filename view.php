<!DOCTYPE html>
<html lang="en">
	<header>
		<!-- Bootstrap -->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<style>
			.images{
				width:150px;
				height:150px;
				cursor:pointer;
				margin:10px;
			}
			.images:hover{
				-webkit-transform: scale(1.2);
				-moz-transform: scale(1.2);
				-o-transform: scale(1.2);
				transform: scale(1.2);
				transition: all 0.3s;
				-webkit-transition: all 0.3s;
			}

			table, th, td {
      			border: 1px solid black;
      			border-collapse: collapse;
				cursor: pointer;
				}
				
			th, td {
    		  padding: 15px;
    		  text-align: center;
    		}
		
    		tr:hover td {
    		    background-color: #b7e5eb;
    		}
		
    		table tr[data-href] {
    		    cursor: pointer;
    		}
		
			tr:nth-child(even) {background-color: #e0dede;}
			
			#myInput {
				background-image: url('/grapta/css/searchicon.png'); /* Add a search icon to input */
			  	background-position: 10px 12px; /* Position the search icon */
			  	background-repeat: no-repeat; /* Do not repeat the icon image */
			  	width: 100%; /* Full-width */
			  	font-size: 16px; /* Increase font-size */
			  	padding: 12px 20px 12px 40px; /* Add some padding */
			  	border: 1px solid #ddd; /* Add a grey border */
			  	margin-bottom: 12px; /* Add some space below the input */
			}
			
			#myTable {
			  	border-collapse: collapse; /* Collapse borders */
			  	width: 100%; /* Full-width */
			  	border: 1px solid #ddd; /* Add a grey border */
			  	font-size: 16px; /* Increase font-size */
			}
			
			#myTable th, #myTable td {
			  	text-align: left; /* Left-align text */
			  	padding: 12px; /* Add padding */
			}
			
			#myTable tr {
			  	/* Add a bottom border to all table rows */
			  	border-bottom: 1px solid #ddd;
			}
			
			#myTable tr.header, #myTable tr:hover {
			  	/* Add a grey background color to the table header and on hover */
			  	background-color: #f1f1f1;
			}
			
		</style>
	</header>
	<?php
	require_once('load.php');

	// Handle logins
	if($_SERVER["REQUEST_METHOD"] == "POST") {
    	$login_status = $login->verify_login($_POST);
	}

	// Verify session
	if ( $login->verify_session() ) {
    	$user = $login->user;
		$userType = $user->userType;
		$aem = $user->aem;
		$server_name = $_SERVER['SERVER_NAME'];
    
		function formatToGreekDate($date){
			//Expected date format yyyy-mm-dd hh:MM:ss
			$greekMonths = array('Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλίος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος');
		
			$time = strtotime($date);
			$newformat = date('Y-m-d',$time);

			return $greekMonths[date('m', strtotime($newformat))-1]. ' '. date('Y', strtotime($newformat));
		}
		
		if ( $userType == 'student' )
    	{
        	include('header.php'); ?>
        	<body>
		<div class="container">			
			<div class="panel panel-default">
				<div class="panel-body">
					<a href="index.php" class="btn btn-info">Μαθήματα</a>
					<h4>Αποθηκευμένα γραπτά:</h3>
					<br/>
					<?php 
						require_once("load.php");
						$connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
						$connect->set_charset("utf8");
						
						$query = "SELECT * FROM user_files WHERE subject_id = '$sender' AND aem = '$aem'";
						
						$result = mysqli_query($connect, $query);
						
						if(mysqli_num_rows($result) > 0)
						{
							?>
							<table id="myTable">
							<tbody>
							<tr class="header">
							<?php
							echo "<th>Καθηγητής</th><th>Εξεταστική</th></tr>\r\n";
							while($row = mysqli_fetch_assoc($result))
							{
								$url = $row["file_path"]."/".$row["file_name"];
								$file_name = $row["file_name"];

								$file_date = $row["date"];
								$file_date_month = strtotime($file_date);
								$file_date_month = date('m',$file_date_month);
								$file_date_year = strtotime($file_date);
								$file_date_year = date('Y',$file_date_year);
								//$file_date = formatToGreekDate($file_date);

								if ( $file_date_month >= 1 && $file_date_month <= 4 )
								{
									$file_date = 'ΧΕΙΜ' . " - " . $file_date_year;
								} elseif ( $file_date_month >= 5 && $file_date_month <= 8 )
								{
									$file_date = 'ΕΑΡ' . " - " . $file_date_year;
								} elseif ( $file_date_month >= 9 && $file_date_month <= 12 )
								{
									$file_date = 'ΣΕΠΤΕΜΒΡΙΟΣ' . " - " . $file_date_year;
								}
								
								$professor_name = $row["professor_name"];

								?>
								<form style="display:none" action="viewpdf.php" method="POST" id="view_form">
								<input type="hidden" name="url" value="<?php echo openssl_encrypt($url, "AES-128-ECB", SECRETKEY);?>" />
								<input type="hidden" name="file_name" value="<?php echo openssl_encrypt($file_name, "AES-128-ECB", SECRETKEY);?>" />
								</form>
								<?php
                				echo "<td>" . $professor_name . "</td>\r\n";
								echo "<td>" . $file_date . "</td>\r\n";
                				echo "</tr>\r\n";
					?>
								
								<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                				<script>
                    				$(document).ready(function(){
                        				$('table tr').click(function(){
                            				$("#view_form").submit();
                            				return false;
                        				});
                    				});
                				</script>
					
					<?php
							}
							echo "</tbody></table>\r\n";
						}
						else
						{
					?>
						<p>Δεν υπάρχουν αποθηκευμένα γραπτά.</p>
					<?php
						}
					?>					
				</div>
			</div>
		</div>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jQuery.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>		
	</body>
</html>
<?php include('footer.php');
			} elseif ( $userType == 'professor' ) {
				include('header.php'); 
				$subject_name = $_GET['subject'];
				?>
		<body>
		<div class="wrapper text-center">
            <h2><?php echo $subject_name; ?></h2>
            <p><a href="logout.php">Αποσύνδεση</a></p>
        </div>
		<div class="container">
			<div class="page-header">
				<h2>Προβολή αποθηκευμένων γραπτών</h1>
			</div>			
			<div class="panel panel-default">
				<div class="panel-body">
					<a href="index.php" class="btn btn-info">Μαθήματα</a>
					<h4>Αποθηκευμένα γραπτά:</h3>
					<br/>
					<?php 
						require_once("load.php");
						$connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
						$connect->set_charset("utf8");
						
						$sender = $_GET['sender'];
						
						$query = "SELECT user_files.file_path, user_files.file_name, user_files.date, students.aem, students.last_name, students.first_name
						FROM user_files 
						INNER JOIN students ON user_files.aem=students.aem
						WHERE user_files.subject_id='$sender'
						ORDER BY date desc,students.aem desc";

						$result = mysqli_query($connect, $query);
						
						if(mysqli_num_rows($result) > 0)
						{
							?>
							
							<input type="text" id="myInput" onkeyup="myFunction()" placeholder="Αναζήτηση">
							
							<table id="myTable">
							<tbody>
							<tr class="header">
							<?php
							echo "<th>Α.Ε.Μ.</th><th>Επώνυμο</th><th>Όνομα</th><th>Ημερομηνία</th></tr>\r\n";
							while($row = mysqli_fetch_assoc($result))
							{
								$url = $row["file_path"]."/".$row["file_name"];
								$file_name = $row["file_name"];

								$file_date = $row["date"];
								$file_date_month = strtotime($file_date);
								$file_date_month = date('m',$file_date_month);
								$file_date_year = strtotime($file_date);
								$file_date_year = date('Y',$file_date_year);
								//$file_date = formatToGreekDate($file_date);

								if ( $file_date_month >= 1 && $file_date_month <= 4 )
								{
									$file_date = 'ΧΕΙΜ' . " - " . $file_date_year;
								} elseif ( $file_date_month >= 5 && $file_date_month <= 8 )
								{
									$file_date = 'ΕΑΡ' . " - " . $file_date_year;
								} elseif ( $file_date_month >= 9 && $file_date_month <= 12 )
								{
									$file_date = 'ΣΕΠΤΕΜΒΡΙΟΣ' . " - " . $file_date_year;
								}
								
								$student_aem = $row["aem"];
								$student_last_name = $row["last_name"];
								$student_first_name = $row["first_name"];
								?>
								<form style="display:none" action="viewpdf.php" method="POST" id="view_form">
								<input type="hidden" name="url" value="<?php echo openssl_encrypt($url, "AES-128-ECB", SECRETKEY);;?>" />
								<input type="hidden" name="file_name" value="<?php echo openssl_encrypt($file_name, "AES-128-ECB", SECRETKEY);;?>" />
								</form>
								<?php
                				echo "<td>" . $student_aem . "</td>\r\n";
								echo "<td>" . $student_last_name . "</td>\r\n";
								echo "<td>" . $student_first_name . "</td>\r\n";
								echo "<td>" . $file_date . "</td>\r\n";
                				echo "</tr>\r\n";
					?>
								
								<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                				<script>
                    				$(document).ready(function(){
                        				$('table tr').click(function(){
                            				//window.location = $(this).data('href');
											$("#view_form").submit();
                            				return false;
                        				});
                    				});
                				</script>
					
					<?php
							}
							echo "</tbody></table>\r\n";
							?>
							<script>
							function filterTable(event) {
							    var filter = event.target.value.toUpperCase();
							    var rows = document.querySelector("#myTable tbody").rows;
														
							    for (var i = 0; i < rows.length; i++) {
							        var firstCol = rows[i].cells[0].textContent.toUpperCase();
							        var secondCol = rows[i].cells[1].textContent.toUpperCase();
									var fourthCol = rows[i].cells[3].textContent.toUpperCase();
							        if (firstCol.indexOf(filter) > -1 || secondCol.indexOf(filter) > -1 || fourthCol.indexOf(filter) > -1) {
							            rows[i].style.display = "";
							        } else {
							            rows[i].style.display = "none";
							        }      
							    }
							}
							
							document.querySelector('#myInput').addEventListener('keyup', filterTable, false);
							</script>
							<?php
						}
						else
						{
					?>
						<p>Δεν υπάρχουν αποθηκευμένα γραπτά.</p>
					<?php
						}
					?>					
				</div>
			</div>
		</div>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jQuery.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>		
	</body>
</html>
<?php include('footer.php');
    }

} else {
    include( 'login.php' );
}