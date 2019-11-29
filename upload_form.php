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
	$professor_name = $user->professor_name;
    
    if ( $userType == 'student' )
    {
		include('header.php'); 
		$sender = $_GET['id'];
		$subject_name = $_GET['subject'];
		ChromePhp::log($sender, $subject_name);
		?>
        <div class="wrapper text-center">
            <h2><?php echo $subject_name; ?></h2>
            <p><a href="logout.php">Αποσύνδεση</a></p>
        </div>
		<?php
		include('view.php');
		include('footer.php');
    } elseif ( $userType == 'professor' ) {
		include('header.php'); 
		$sender = $_GET['id'];
		$subject_name = $_GET['subject'];
		ChromePhp::log($sender, $subject_name);
		?>
        <div class="wrapper text-center">
            <h2><?php echo $subject_name; ?></h2>
            <p><a href="logout.php">Αποσύνδεση</a></p>
        </div>

            <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
		<style>
		body {font-family: Arial, Helvetica, sans-serif;}

		/* The Modal (background) */
		.modal {
		display: none; /* Hidden by default */
		position: fixed; /* Stay in place */
		z-index: 1; /* Sit on top */
		padding-top: 100px; /* Location of the box */
		left: 0;
		top: 0;
		width: 100%; /* Full width */
		height: 100%; /* Full height */
		overflow: auto; /* Enable scroll if needed */
		background-color: rgb(0,0,0); /* Fallback color */
		background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
		}

		/* Modal Content */
		.modal-content {
		position: relative;
		background-color: #fefefe;
		margin: auto;
		padding: 0;
		border: 1px solid #888;
		width: 80%;
		box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
		-webkit-animation-name: animatetop;
		-webkit-animation-duration: 0.4s;
		animation-name: animatetop;
		animation-duration: 0.4s
		}

		/* Add Animation */
		@-webkit-keyframes animatetop {
		from {top:-300px; opacity:0} 
		to {top:0; opacity:1}
		}

		@keyframes animatetop {
		from {top:-300px; opacity:0}
		to {top:0; opacity:1}
		}

		/* The Close Button */
		.close {
		color: white;
		float: right;
		font-size: 28px;
		font-weight: bold;
		}

		.close:hover,
		.close:focus {
		color: #000;
		text-decoration: none;
		cursor: pointer;
		}

		.modal-header {
		padding: 2px 16px;
		background-color: #dc3545;
		color: white;
		}

		.modal-body {
			padding: 2px 6px 2px 20%;
		}

		.modal-footer {
		padding: 2px 16px;
		background-color: #dc3545;
		color: white;
		}
		</style>
        
		<div class="container">			
			<div class="page-header">
				<h2>Αποθήκευση Γραπτών</h2>
			</div>
			<div class="panel panel-default">
				<div class="panel-body">
					<a href="index.php" class="btn btn-info">Μαθήματα</a>
					<hr>
					<form method="post" enctype="multipart/form-data" name="formUploadFile" id="uploadForm" action="upload.php">
						<div class="form-group">
						<p class="help-block"><span class="label label-info">Προσοχή:<br>Τα ονόματα των αρχείων πρέπει να περιέχουν μόνο τον Αριθμό Μητρώου του φοιτητή. π.χ. '2432.pdf'<br>Επιλέξτε μόνο αρχεία PDF με μέγεθος λιγότερο από 20MB.<br>Ο μέγιστος αριθμός αρχείων ανά κάθε μεταφόρτωση είναι 300.</span></p>
							<label for="exampleInputFile">Επιλέξτε τα αρχεία που θέλετε να ανεβάσετε:</label>
							<input type='hidden' name='sender' value='<?php echo "$sender";?>'/>
							<input type='hidden' name='professor_name' value='<?php echo $professor_name;?>'/>
							<input type="file" id="exampleInputFile" name="files[]" multiple="multiple" accept="application/pdf" >
						</div>			
						<button type="submit" class="btn btn-primary" name="btnSubmit" >Μεταφόρτωση</button>
					</form>
					<br>
					<label for="Progressbar">Πρόοδος:</label>
					<div class="progress" id="Progressbar">
						<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%" id="divProgressBar">
							<div class="percent" id="percent">0%</div>
						</div>						
					</div>
					<br>
					<a href="view.php?sender=<?php echo $sender ?>&subject=<?php echo $subject_name ?>" class="btn btn-info">Προβολή αποθηκευμένων γραπτών</a>
					<hr>
					<!-- <div id="status"></div> -->
					
					<!-- The Modal -->
					<div id="myModal" class="modal">

					<!-- Modal content -->
					<div class="modal-content">
					<div class="modal-header">
						<span class="close">&times;</span>
					</div>
					<div class="modal-body">
						<div id="status"></div>	<!-- Return status and errors from upload attemps -->
					</div>
					<div class="modal-footer">
					</div>
					</div>

					</div>
				</div>
			</div>
		</div>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jQuery.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>
		
		<script src="js/jQuery.Form.js"></script>
		
		<script type="text/javascript">
			$(document).ready(function(){			
				
				var divProgressBar=$("#divProgressBar");
				var percent = $("#percent");
				var status=$("#status");

				// Get the modal
				var modal = document.getElementById("myModal");

				// Get the <span> element that closes the modal
				var span = document.getElementsByClassName("close")[0];

				// When the user clicks on <span> (x), close the modal
				span.onclick = function() {
				modal.style.display = "none";
				}

				// When the user clicks anywhere outside of the modal, close it
				window.onclick = function(event) {
				if (event.target == modal) {
					modal.style.display = "none";
				}
				}
				
				$("#uploadForm").ajaxForm({
					
					dataType:"json",
					
					beforeSend:function(){
						divProgressBar.css({});
						divProgressBar.width(0);
						divProgressBar.removeClass("progress-bar bg-success progress-bar-striped");
						divProgressBar.removeClass("progress-bar bg-danger progress-bar-striped");
						divProgressBar.addClass("progress-bar progress-bar-striped progress-bar-animated");
						modal.style.display = "none";
					},
					
					uploadProgress:function(event, position, total, percentComplete){
						var pVel=percentComplete+"%";
						divProgressBar.width(pVel);
						percent.html(pVel);
						if ( pVel == '100%' )
						{
							divProgressBar.removeClass("progress-bar progress-bar-striped progress-bar-animated");
							divProgressBar.addClass("progress-bar bg-success progress-bar-striped");
						}
					},
					
					complete:function(data){
						status.html(data.responseText);
						if ( data.responseText )
						{
							divProgressBar.removeClass("progress-bar progress-bar-striped progress-bar-animated");
							divProgressBar.addClass("progress-bar bg-danger progress-bar-striped");
							percent.html("Σφάλμα");
							modal.style.display = "block";
						}
					}
				});
			});
		</script>

        <?php include('footer.php');
    }

} else {
    include( 'login.php' );
}