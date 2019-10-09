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
    
    include('header.php'); ?>
    <style>
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

    </style>

    <div class="wrapper">
        <?php
            $subjects = $db->query("SELECT * FROM subjects");
            echo "<table><tbody><tr><th>Εξάμηνο</th><th>Μάθημα</th></tr>\r\n";
            foreach ($subjects as $result) {
                echo "<tr data-href='$result->id.php?subject=$result->name'>\r\n";
                echo "<td>" . $result->semester . "</td>\r\n";
                echo "<td>" . $result->name . "</td>\r\n";
                echo "</tr>\r\n";
                ?>
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
            }
            echo "</tbody></table>\r\n";
            ?>
    </div>
    <?php include('footer.php');
} else {
    include( 'login.php' );
}