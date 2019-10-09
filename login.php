<?php include('header.php'); ?>
<div class="wrapper">
    <form action="index.php" method="post">
        <h1 class="text-center">Σύνδεση Χρήστη</h1>
        <?php if ( isset( $login_status ) && false == $login_status ) : ?>
        <div class="message error">
            <p>Το username και password είναι λάθος. Ξαναπροσπαθήστε.</p>
        </div>
        <?php endif; ?>
        <input type="username" class="text" name="username" placeholder="Username" pattern="[a-z0-9]{1,15}" title="Το Username πρέπει να είναι μόνο πεζά γράμματα και αριθμοί. π.χ. steftzor">
        <input type="password" class="text" name="password" placeholder="Κωδικός">
        <input type="submit" class="submit" value="ΣΥΝΔΕΣΗ">
        <p><input type="checkbox" name="rememberme" value="1"> Να με θυμάσαι</p>
    </form>
    <p><a href="lostpassword.php">Επαναφορά Κωδικού</a></p>
    <p><a href="register_professor.php">Εγγραφή Καθηγητή</a></p>
    <p><a href="register.php">Εγγραφή Φοιτητή</a></p>
</div>
<?php include('footer.php'); ?>