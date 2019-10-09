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
    <div class="wrapper text-center">
        <h2>Καλωσήρθατε  <?php echo $user->username;?></h2>
        <p><a href="logout.php">Αποσύνδεση</a></p>
    </div>
    <?php include('footer.php');

} else {
    include( 'login.php' );
}