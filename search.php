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
                ?><input type="number" class="select" id="search_aem" name="search_aem" placeholder="Α.Ε.Μ." pattern="[0-9]" min="1" max="99999" required><?php
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
            <select class="select" id="search_year" name="search_year">
                <option value = "" disabled hidden selected>Έτος</option>
            </select>
            <select class="select" id="search_exam_period" name="search_exam_period">
                <option value = "" disabled hidden selected>Εξεταστική Περίοδος</option>
            </select>

            <br><input type="submit" class="search" name="btnSearch" value="Αναζήτηση">
        </form>
    </div>
    <?php include('footer.php');
} else {
    include( 'login.php' );
}