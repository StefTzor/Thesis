<?php
require_once('load.php');
    
// Verify session
if ( $login->verify_session() ) {
    $user = $login->user;
    $userType = $user->userType;

    $aem_to_search = $user->aem;
    
    include('header.php'); ?>

    <div class="search_wrapper">    
        <form method="post" action="search_result.php">
            <?php
            if ( $userType == 'professor' )
            {
                ?><input type="number" class="select" id="search_aem" name="search_aem" placeholder="Α.Ε.Μ." pattern="[0-9]" min="1" max="99999"><?php
            } elseif ( $userType == 'student')
            {
                ?><input type='hidden' name='aem_to_search' value='<?php echo "$aem_to_search";?>'/> <?php
            }?>
            <select class="select" id="search_subject" name="search_subject" required>
                <option value = "" disabled hidden selected>Μάθημα</option>
                <?php
                $subjects = $db->query("SELECT * FROM subjects ORDER BY name asc");
                foreach ($subjects as $result) {             
                    ?> <option value = "<?php echo $result->id ?>"><?php echo $result->name ?></option> <?php
                }
                ?>
            </select>
            <select class="select" id="search_year" name="search_year" onChange="change_period(this.value);">
                <option value = "" disabled hidden selected>Έτος</option>
                <?php
                $year = $db->query("SELECT DISTINCT YEAR(date) as date FROM user_files ORDER BY date DESC");
                foreach ($year as $result) {
                    $date = $result->date;            
                    ?> <option value = "<?php echo $date ?>"><?php echo $date ?></option> <?php
                }
                ?>
            </select>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                <script>
                document.getElementById('search_year').onclick = function () {
                    var e = document.getElementById('search_year');

                    function getSelectedOption(e) {
                        var opt;
                        for ( var i = 0, len = e.options.length; i < len; i++ ) {
                            opt = e.options[i];
                            if ( opt.selected === true ) {
                                break;
                            }
                        }
                        return opt;
                    }

                    // get selected option in sel (reference obtained above)
                    var opt = getSelectedOption(e);

                    // display its value and text
                    console.log( opt.value );
                }

                function change_period(value) {
                    if (value.length == 0) document.getElementById("search_exam_period").innerHTML = "<option></option>";
                    else {

                        <?php
                        /*$search_date_from = value
                        $month = $db->query("SELECT DISTINCT MONTH(date)AS date 
                        FROM user_files
                        WHERE user_files.date BETWEEN '$search_date_from' AND '$search_date_to' 
                        ORDER BY date DESC");
                        ChromePhp::log($month) */
                        ?>  

                        /*var period_options = "";
                        for (categoryId in mealsByCategory[value]) {
                            period_options += "<option>" +  + "</option>";
                        }
                        document.getElementById("search_exam_period").innerHTML = period_options; */
                    }
                }
                </script>
            <select class="select" id="search_exam_period" name="search_exam_period">
                <option value = "" disabled hidden selected>Εξεταστική Περίοδος</option>
                <option value = "ΧΕΙΜ" >ΧΕΙΜ</option>
                <option value = "ΕΑΡ" >ΕΑΡ</option>
                <option value = "ΣΕΠΤΕΜΒΡΙΟΣ" >ΣΕΠΤΕΜΒΡΙΟΣ</option>
            </select>

            <br><input type="submit" class="search" name="btnSearch" value="Αναζήτηση">
        </form>
    </div>
    <?php include('footer.php');
} else {
    include( 'login.php' );
}