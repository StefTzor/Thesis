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
        include( 'home.php' );
        include( 'search.php' );
        include( 'subjects.php' );
    } elseif ( $userType == 'professor' ) {
        include( 'home.php' );
        include( 'search.php' );
        include( 'subjects.php' );
    } elseif ( $userType == 'not_verified' ) {
        include('header.php'); ?>
        <div class="wrapper text-center">
            <h1>Δεν έχει γίνει επαλήθευση του λογαριασμού σας.</h1>
            <h4>Επικοινωνήστε προσωπικά με τον διαχειριστή του συστήματος για την ταυτοποίηση του λογαριασμού σας.</h4>
            <p><a href="logout.php">Αποσύνδεση</a></p>
        </div>
        <?php include('footer.php');
    }

} else {
    include( 'login.php' );
}