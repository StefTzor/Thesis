<?php
require_once('load.php');
    
// Verify session
if ( $login->verify_session() ) {
    $user = $login->user;
    $userType = $user->userType;
    
    include('header.php'); ?>

    <div class="search_wrapper">    
        <form method="post" action="search_result.php">
            <input type="number" class="select" id="search_aem" name="search_aem" placeholder="Α.Ε.Μ." pattern="[0-9]" min="1" max="99999" required>
            <select class="select" id="search_subject" name="search_subject" required>
                <option value = "" disabled hidden selected>Μάθημα</option>
                <?php
                $subjects = $db->query("SELECT * FROM subjects ORDER BY name asc");
                foreach ($subjects as $result) {             
                    ?> <option value = "<?php echo $result->id ?>"><?php echo $result->name ?></option> <?php
                }
                ?>
            </select>
            <select class="select" id="search_year" name="search_year">
                <option value = "" disabled hidden selected>Έτος</option>
            </select>
            <select class="select" id="search_exam_period" name="search_exam_period">
                <option value = "" disabled hidden selected>Εξεταστική Περίοδος</option>
            </select>
            
            <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
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
            -->

            <br><input type="submit" class="search" name="btnSearch" value="Αναζήτηση">
        </form>
    </div>
    <?php include('footer.php');
} else {
    include( 'login.php' );
}