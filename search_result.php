<!DOCTYPE html>
<html lang="en">
<?php include('header.php'); ?>
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
<div class="container">			
			<div class="panel panel-default">
				<div class="panel-body">
<?php
    require_once("load.php");
	if(isset($_POST["btnSearch"])){
    
        function __construct() {
        global $db;
        
        session_start();
        
        //$this->db = $db;
        }
        
        if ( $_POST['search_aem'] )
        {
            $search_aem = $_POST['search_aem']; //Professor
        } elseif ( $_POST['aem_to_search'] )
        {
            $search_aem = $_POST['aem_to_search']; //Student's AEM
        } else
        {
            $search_aem = false;
        }

        $search_subject = $_POST['search_subject'];

        if ( !is_null($_POST['search_year']) ) {
            $search_year = $_POST['search_year'];
            $year = true;
        }
        
        if ( !is_null($_POST['search_exam_period']) ) {
            $search_exam_period = $_POST['search_exam_period'];
            if ( $search_exam_period == 'ΧΕΙΜ' )
			{
                $search_exam_period_from = '01-01';
                $search_exam_period_to = '04-30';
			} elseif ( $search_exam_period == 'ΕΑΡ' )
			{
				$search_exam_period_from = '05-01';
                $search_exam_period_to = '08-31';
			} elseif ( $search_exam_period == 'ΣΕΠΤΕΜΒΡΙΟΣ' )
			{
				$search_exam_period_from = '09-01';
                $search_exam_period_to = '12-31';
			}
            $exam_period = true;
        }

        // Search
        if ( $search_aem && $year && $exam_period )
        {
            $search_date_from = $search_year . '-' . $search_exam_period_from;
            $search_date_to = $search_year . '-' . $search_exam_period_to;
            $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, user_files.professor_name, students.aem, students.last_name, students.first_name
            FROM user_files 
            INNER JOIN students ON user_files.aem=students.aem
            WHERE user_files.aem='$search_aem'
            AND user_files.subject_id='$search_subject'
            AND user_files.date BETWEEN '$search_date_from' AND '$search_date_to'
            ORDER BY date DESC,students.aem DESC");
        } elseif ( $search_aem && $year && !$exam_period)
        {
            $search_date_from = $search_year . '-01-01';
            $search_date_to = $search_year . '-12-31';
            $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, user_files.professor_name, students.aem, students.last_name, students.first_name
            FROM user_files 
            INNER JOIN students ON user_files.aem=students.aem
            WHERE user_files.aem='$search_aem'
            AND user_files.subject_id='$search_subject'
            AND user_files.date BETWEEN '$search_date_from' AND '$search_date_to'
            ORDER BY date DESC,students.aem DESC");
        } elseif ( $search_aem && !$year && !$exam_period )
        {
            $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, user_files.professor_name, students.aem, students.last_name, students.first_name
            FROM user_files 
            INNER JOIN students ON user_files.aem=students.aem
            WHERE user_files.aem='$search_aem'
            AND user_files.subject_id='$search_subject'
            ORDER BY date DESC,students.aem DESC");
        } elseif ( !$search_aem && $year && $exam_period )
        {
            $search_date_from = $search_year . '-' . $search_exam_period_from;
            $search_date_to = $search_year . '-' . $search_exam_period_to;
            $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, user_files.professor_name, students.aem, students.last_name, students.first_name
            FROM user_files 
            INNER JOIN students ON user_files.aem=students.aem
            WHERE user_files.subject_id='$search_subject'
            AND user_files.date BETWEEN '$search_date_from' AND '$search_date_to'
            ORDER BY date DESC,students.aem DESC");
        } elseif (!$search_aem && $year && !$exam_period )
        {
            $search_date_from = $search_year . '-01-01';
            $search_date_to = $search_year . '-12-31';
            $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, user_files.professor_name, students.aem, students.last_name, students.first_name
            FROM user_files 
            INNER JOIN students ON user_files.aem=students.aem
            WHERE user_files.subject_id='$search_subject'
            AND user_files.date BETWEEN '$search_date_from' AND '$search_date_to'
            ORDER BY date DESC,students.aem DESC");
        } else
        {
            $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, user_files.professor_name, students.aem, students.last_name, students.first_name
            FROM user_files 
            INNER JOIN students ON user_files.aem=students.aem
            WHERE user_files.subject_id='$search_subject'
            ORDER BY date DESC,students.aem DESC");
        }

        ?>
        <a href="index.php" class="btn btn-info">Μαθήματα</a>
        <div class="container">			
			<div class="panel panel-default">
				<div class="panel-body">
                    <table id="myTable">
                        <tbody>
                            <tr class="header">
                                <?php
                                if (!empty($search))
                                {
                                    ?>
                                    <th>Α.Ε.Μ.</th><th>Επώνυμο</th><th>Όνομα</th><th>Καθηγητής</th><th>Ημερομηνία</th></tr>
                                    <?php
                                    foreach ($search as $result) {
                                        $url = $result->file_path . "/". $result->file_name;
                                        $url_encrypted = urlencode(base64_encode($url));
                                        $file_name = $result->file_name;
                                        $file_name_encrypted = urlencode(base64_encode($file_name));

                                        $file_date = $result->date;
                                    
		            			    	$file_date_month = strtotime($file_date);
		            			    	$file_date_month = date('m',$file_date_month);
		            			    	$file_date_year = strtotime($file_date);
		            			    	$file_date_year = date('Y',$file_date_year);
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
                                    
                                        $student_aem = $result->aem;
                                        $student_last_name = $result->last_name;
                                        $student_first_name = $result->first_name;
                                        $professor_name = $result->professor_name;
                                    
                                        ?>
                                        <tr data-href='viewpdf.php?url=<?php echo $url_encrypted; ?>&name=<?php echo $file_name_encrypted; ?>'>
		            			    	<?php
                            	    	echo "<td>" . $student_aem . "</td>\r\n";
		            			    	echo "<td>" . $student_last_name . "</td>\r\n";
                                        echo "<td>" . $student_first_name . "</td>\r\n";
                                        echo "<td>" . $professor_name . "</td>\r\n";
		            			    	echo "<td>" . $file_date . "</td>\r\n";
                                        echo "</tr>\r\n";
                                    }
                                } else
                                {
                                    ?>
						            <p>Δεν υπάρχουν αποθηκευμένα γραπτά.</p>
					                <?php
                                }
                                    ?>
                        </tbody>
                    </table>
                </div>
			</div>
		</div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script>
        	$(document).ready(function(){
        		$('table tr').click(function(){
        			window.location = $(this).data('href');
        	 		return false;
        		});
        	});
        </script>
        <?php
        
    } else {
		include( 'login.php' );
	}
?>