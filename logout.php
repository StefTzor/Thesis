<?php
session_start();
session_destroy();
setcookie("rememberme", "", time()-3600, '/');

include('header.php');
?>
<div class="wrapper text-center">
    <h1>Αποσυνδεθήκατε</h1>
    <p><a href="index.php">Επανασύνδεση</a></p>
</div>
<?php include('footer.php'); ?>