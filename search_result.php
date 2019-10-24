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
	
	#search_table {
	  	border-collapse: collapse; /* Collapse borders */
	  	width: 100%; /* Full-width */
	  	border: 1px solid #ddd; /* Add a grey border */
	  	font-size: 16px; /* Increase font-size */
	}
	
	#search_table th, #search_table td {
	  	text-align: left; /* Left-align text */
	  	padding: 12px; /* Add padding */
	}
	
	#search_table tr {
	  	/* Add a bottom border to all table rows */
	  	border-bottom: 1px solid #ddd;
	}
	
	#search_table tr.header, #search_table tr:hover {
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

        function aem_student_exists($where_value, $where_field = 'aem') {
            $search = $db->get_results("
                SELECT * FROM students 
                WHERE {$where_field} = :where_value", 
                ['where_value'=>$where_value]
            );
            
            if ( false !== $search ) {
                return $search[0];
            }
            
            return false;
        }

        function formatToGreekDate($date){
            //Expected date format yyyy-mm-dd hh:MM:ss
            $greekMonths = array('Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλίος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος');
    
            $time = strtotime($date);
            $newformat = date('Y-m-d',$time);
    
            return $greekMonths[date('m', strtotime($newformat))-1]. ' '. date('Y', strtotime($newformat));
        }
        
        $search_aem = $_POST['search_aem'];
        if ( !is_null($_POST['aem_to_search']) )
        {
            $search_aem = $_POST['aem_to_search'];
        }
        $search_subject = $_POST['search_subject'];
        if ( $_POST['search_year'] ) {
            $search_year = $_POST['search_year'];
        }
        
        if ( $_POST['search_exam_period'] ) {
            $search_exam_period = $_POST['search_exam_period'];
        }
        ChromePhp::log($search_aem);
        ChromePhp::log($search_subject);
        ChromePhp::log($search_year);
        ChromePhp::log($search_exam_period);

        // Check if aem exists
        /*if ( false == aem_student_exists( $search_aem ) ) {
            ChromePhp::log('Το Α.Ε.Μ. δεν υπάρχει');
            return array('status'=>0,'message'=>'Το Α.Ε.Μ. δεν υπάρχει');
        }*/

        // Search
        $search = $db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, students.aem, students.last_name, students.first_name
        FROM user_files 
        INNER JOIN students ON user_files.aem=students.aem
        WHERE user_files.aem='$search_aem'
        AND user_files.subject_id='$search_subject'
        ORDER BY date DESC,students.aem DESC");

        ?>
        <a href="index.php" class="btn btn-info">Μαθήματα</a>
        <table id="search_table">
            <tbody>
                <tr class="header">
                    

                    <th>Α.Ε.Μ.</th><th>Επώνυμο</th><th>Όνομα</th><th>Ημερομηνία</th></tr>
                    <?php
                    foreach ($search as $result) {
                        $url = $result->file_path .  "/". $result->file_name;

                        $file_name = $result->file_name;
                    
                        $file_date = $result->date;
                        $file_date = formatToGreekDate($file_date);
                    
                        $student_aem = $result->aem;
                        $student_last_name = $result->last_name;
                        $student_first_name = $result->first_name;

                        echo "<tr data-href=$url>\r\n";
                        echo "<td>" . $student_aem . "</td>\r\n";
                        echo "<td>" . $student_last_name . "</td>\r\n";
                        echo "<td>" . $student_first_name . "</td>\r\n";
                        echo "<td>" . $file_date . "</td>\r\n";
                        echo "</tr>\r\n";
                    }
                    ?>
            </tbody>
        </table>
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
                
        ChromePhp::log('Error');
        return array('status'=>0,'message'=>'Παρουσιάστηκε ένα άγνωστο σφάλμα');
        
    } else {
		include( 'login.php' );
	}
?>