<?php 
require_once('load.php'); 

// Handle registration
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $login->lost_password($_POST);
}
?>

<?php include('header.php'); ?>
<div class="wrapper">
    <form action="" method="post">
        <h1 class="text-center">Επαναφορά Κωδικού</h1>
        <?php if ( isset( $status ) ) : ?>
        <?php ($status['status'] == true ? $class = 'success' : $class = 'error'); ?>
        <div class="message <?php echo $class; ?>">
            <p><?php echo $status['message']; ?></p>
        </div>
        <?php endif; ?>
        <input type="text" class="text" name="email" placeholder="Πληκτρολογήστε το email σας" required>
        <input type="submit" class="submit" value="Submit">
    </form>
    <p><a href="index.php">Σύνδεση Χρήστη</a></p>
</div>
<?php include('footer.php'); ?>