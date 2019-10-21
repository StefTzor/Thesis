<?php 
require_once('load.php'); 

// Handle registration
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $register_status = $login->register($_POST);
}
?>

<?php include('header.php'); ?>
<div class="wrapper">
    <form action="" method="post">
        <h1 class="text-center">Εγγραφή Χρήστη</h1>
        <?php if ( isset( $register_status ) ) : ?>
        <?php ($register_status['status'] == true ? $class = 'success' : $class = 'error'); ?>
        <div class="message <?php echo $class; ?>">
            <p><?php echo $register_status['message']; ?></p>
        </div>
        <?php endif; ?>
        <input type="text" class="text" name="last_name" placeholder="Επώνυμο" style="text-transform:uppercase" pattern="[a-zA-Zα-ωΑ-Ω]{1,50}" required>
        <input type="text" class="text" name="first_name" placeholder="Όνομα" style="text-transform:uppercase" pattern="[a-zA-Zα-ωΑ-Ω]{1,50}" required>
        <input type="text" class="text" name="fathers_name" placeholder="Πατρώνυμο" style="text-transform:uppercase" pattern="[a-zA-Zα-ωΑ-Ω]{1,50}" required>
        <input type="number" class="text" name="aem" placeholder="Α.Ε.Μ." pattern="[0-9]" min="1" max="99999" required>


        <select class="text" id="registered" name="registered" required>
            <option value = "" disabled hidden selected>Περίοδος Εγγραφής</option>
        </select>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script>

            window.onload = populateSelect();

            function populateSelect() {

                // CREATE AN XMLHttpRequest OBJECT, WITH GET METHOD.
                var xhr = new XMLHttpRequest(), 
                    method = 'GET',
                    overrideMimeType = 'application/json',
                    url = 'fetch_reg.php';        // ADD THE URL OF THE FILE.

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            
                        // PARSE JSON DATA.
                        var reg_data = JSON.parse(xhr.responseText);

                        var ele = document.getElementById('registered');
                        for (var i = 0; i < reg_data.length; i++) {
                            // BIND DATA TO <select> ELEMENT.
                            ele.innerHTML = ele.innerHTML +
                                '<option value="' + reg_data[i].registered + '">' + reg_data[i].registered + '</option>';
                        }
                    }
                };
                xhr.open(method, url, true);
                xhr.send();
            }

        </script>
    

        <input type="email" class="text" name="email" placeholder="Email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
        <input type="text" class="text" name="username" placeholder="Username" pattern="[a-z0-9]{1,15}" title="Το Username πρέπει να είναι μόνο πεζά γράμματα και αριθμοί. π.χ. steftzor" required>
        <input type="password" class="text" name="password" placeholder="Κωδικός" minlength="6" required>
        <input type="submit" class="submit" value="ΕΓΓΡΑΦΗ">
    </form>
    <p><a href="index.php">Σύνδεση Χρήστη</a></p>
</div>
<?php include('footer.php'); ?>