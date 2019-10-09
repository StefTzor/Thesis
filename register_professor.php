<?php 
require_once('load.php'); 

// Handle registration
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $register_status = $login->register_professor($_POST);
}
?>

<?php include('header.php'); ?>
<div class="wrapper">
    <form action="" method="post">
        <h1 class="text-center">Εγγραφή Καθηγητή</h1>
        <?php if ( isset( $register_status ) ) : ?>
        <?php ($register_status['status'] == true ? $class = 'success' : $class = 'error'); ?>
        <div class="message <?php echo $class; ?>">
            <p><?php echo $register_status['message']; ?></p>
        </div>
        <?php endif; ?>
        <input type="text" class="text" name="name" placeholder="Ονοματεπώνυμο " style="text-transform:uppercase" pattern="[a-zA-Zα-ωΑ-Ω\s]{1,255}" required>

        <input type="email" class="text" name="email" placeholder="Email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
        <input type="text" class="text" name="username" placeholder="Username" pattern="[a-z0-9]{1,15}" title="Το Username πρέπει να είναι μόνο πεζά γράμματα και αριθμοί. π.χ. steftzor" required>
        <input type="password" class="text" name="password" placeholder="Κωδικός" minlength="6" required>
        <input type="submit" class="submit" value="ΕΓΓΡΑΦΗ">
    </form>
    <p><a href="index.php">Σύνδεση Χρήστη</a></p>
</div>
<?php include('footer.php'); ?>