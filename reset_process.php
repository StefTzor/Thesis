<?php 
require_once('load.php'); 

// Handle registration
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $login->reset_password($_POST);
} else {
    $status = array('status'=>0,'message'=>'Υπήρξε κάποιο πρόβλημα κατά την επαναφορά του κωδικού σας. <a href="lostpassword.php">Προσπαθήστε ξανά</a>.');
}
?>

<?php include('header.php'); ?>
<div class="wrapper">
    <h1 class="text-center">Επαναφορά Κωδικού</h1>
    <?php if ( isset( $status ) ) : ?>
    <?php ($status['status'] == true ? $class = 'success' : $class = 'error'); ?>
    <div class="message <?php echo $class; ?>">
        <p><?php echo $status['message']; ?></p>
    </div>
    <?php endif; ?>
</div>
<?php include('footer.php'); ?>