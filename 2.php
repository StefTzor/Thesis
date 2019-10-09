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
    
    if ( $userType == 'student' )
    {
		include('header.php'); 
		$sender = __FILE__;
		$sender=(int)basename($sender,".php");
		$subject_name = $_GET['subject'];
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
		$subject_name = $_GET['subject'];
		$sender = __FILE__;
		$sender=(int)basename($sender,".php");
		?>
        <div class="wrapper text-center">
            <h2><?php echo $subject_name; ?></h2>
            <p><a href="logout.php">Αποσύνδεση</a></p>
        </div>

            <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        
		<div class="container">			
			<div class="page-header">
				<h1>Αποθήκευση Γραπτών</h1>
			</div>
			<div class="panel panel-default">
				<div class="panel-body">
					<a href="index.php" class="btn btn-info">Μαθήματα</a>
					<hr>
					<form method="post" enctype="multipart/form-data" name="formUploadFile" id="uploadForm" action="upload.php">
						<div class="form-group">
							<label for="exampleInputFile">Επιλέξτε τα αρχεία που θέλετε να ανεβάσετε:</label>
							<input type='hidden' name='sender' value='<?php echo "$sender";?>'/> 
							<input type="file" id="exampleInputFile" name="files[]" multiple="multiple" accept="application/pdf" >
							<p class="help-block"><span class="label label-info">Σημείωση:</span> Επιλέξτε μόνο αρχεία pdf με μέγεθος λιγότερο από 1.5MB</p>
						</div>			
						<button type="submit" class="btn btn-primary" name="btnSubmit" >Upload</button>
					</form>
					<br/>
					<label for="Progressbar">Πρόοδος:</label>
					<div class="progress" id="Progressbar">
						<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%" id="divProgressBar">
							<span class="sr-only">45% Complete</span>
						</div>						
					</div>
					<a href="view.php?sender=<?php echo $sender ?>&subject=<?php echo $subject_name ?>" class="btn btn-info">Προβολή αποθηκευμένων γραπτών</a>
					<hr>
					<div id="status">
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
				var status=$("#status");
				
				$("#uploadForm").ajaxForm({
					
					dataType:"json",
					
					beforeSend:function(){
						divProgressBar.css({});
						divProgressBar.width(0);
					},
					
					uploadProgress:function(event, position, total, percentComplete){
						var pVel=percentComplete+"%";
						divProgressBar.width(pVel);
					},
					
					complete:function(data){
						status.html(data.responseText);
					}
				});
			});
		</script>
        <?php include('footer.php');
    }

} else {
    include( 'login.php' );
}