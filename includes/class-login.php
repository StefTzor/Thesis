<?php
class Login {
    public $user;
    
    public function __construct() {
        global $db;

        session_start();
        
        $this->db = $db;
    }
    
    public function verify_session() {
        $username = $_SESSION['username'];
        
        if ( empty( $username ) && ! empty( $_COOKIE['rememberme'] ) ) {
            list($selector, $authenticator) = explode(':', $_COOKIE['rememberme']);
            
            $results = $this->db->get_results("SELECT * FROM auth_tokens WHERE selector = :selector", ['selector'=>$selector]);
            $auth_token = $results[0];
            
            if ( hash_equals( $auth_token->token, hash( 'sha256', base64_decode( $authenticator ) ) ) ) {
                $username = $auth_token->username;
                $_SESSION['username'] = $username;
            }
        }
        
        $user =  $this->user_exists( $username );
        
        if ( false !== $user ) {
            $this->user = $user;
            
            return true;
        }
        
        return false;
    }
    
    public function verify_login($post) {
        if ( ! isset( $post['username'] ) || ! isset( $post['password'] ) ) {
            return false;
        }
        
        // Check if user exists
        $user = $this->user_exists( $post['username'] );
        
        if ( false !== $user ) {
            if ( password_verify( $post['password'], $user->password ) ) {
                $_SESSION['username'] = $user->username;
                
                if ( $post['rememberme'] ) {
                    $this->rememberme($user);
                }

                return true;
            }
        }
        
        return false;
    }
    
    public function register($post) {
        // Required fields
        $required = array( 'last_name', 'first_name', 'fathers_name', 'aem', 'registered', 'email', 'username', 'password' );
        
        foreach ( $required as $key ) {
            if ( empty( $post[$key] ) ) {
                return array('status'=>0,'message'=>sprintf('Παρακαλώ εισάγετε τα στοιχεία σας %s', $key));
            }
        }
        
        // Check if username exists already
        if ( false !== $this->user_exists( $post['username'] ) ) {
            return array('status'=>0,'message'=>'Το Username υπάρχει ήδη');
        }
        
        // Check if aem exists already
        if ( false !== $this->aem_exists( $post['aem'] ) ) {
            return array('status'=>0,'message'=>'Το Α.Ε.Μ. χρησιμοποιείτε ήδη σε λογαριασμό');
        }

        // Check if email exists already
        if ( false !== $this->email_exists( $post['email'] ) ) {
            return array('status'=>0,'message'=>'Το Email υπάρχει ήδη');
        }

        // Validate Credentials
        if ( false == $this->validate_cred( $post['aem'], mb_strtoupper($post['last_name'], 'UTF-8'), mb_strtoupper($post['first_name'], 'UTF-8'), mb_strtoupper($post['fathers_name'], 'UTF-8'), $post['registered'] ) ) {
            return array('status'=>0, 'message'=>'Λάθος στοιχεία');
        }

        // Create if they don't exist
        $insert = $this->db->insert('users', 
            array(
                'username'  =>  $post['username'], 
                'password'  =>  password_hash($post['password'], PASSWORD_DEFAULT),
                'aem'      =>  $post['aem'],
                'email'     =>  $post['email'],
            )
        );
        
        if ( $insert == true ) {
            return array('status'=>1,'message'=>'Ο Λογαριασμός δημιουργήθηκε επιτυχώς');
        }
        
        return array('status'=>0,'message'=>'Παρουσιάστηκε ένα άγνωστο σφάλμα');
    }

    public function register_professor($post) {
        // Required fields
        $required = array( 'name', 'email', 'username', 'password' );
        
        foreach ( $required as $key ) {
            if ( empty( $post[$key] ) ) {
                return array('status'=>0,'message'=>sprintf('Παρακαλώ εισάγετε τα στοιχεία σας %s', $key));
            }
        }
        
        // Check if username exists already
        if ( false !== $this->user_exists( $post['username'] ) ) {
            return array('status'=>0,'message'=>'Το Username υπάρχει ήδη');
        }

        // Check if email exists already
        if ( false !== $this->email_exists( $post['email'] ) ) {
            return array('status'=>0,'message'=>'Το Email υπάρχει ήδη');
        }

        // Create if they don't exist
        $insert = $this->db->insert('users', 
            array(
                'username'  =>  $post['username'], 
                'password'  =>  password_hash($post['password'], PASSWORD_DEFAULT),
                'email'     =>  $post['email'],
                'userType'  =>  'not_verified', 
            )
        );
        
        if ( $insert == true ) {
            return array('status'=>1,'message'=>'Account created successfully');
        }
        
        return array('status'=>0,'message'=>'An unknown error occurred.');
    }
    
    public function lost_password($post) {
        // Verify email submitted
        if ( empty( $post['email'] ) ) {
            return array('status'=>0,'message'=>'Παρακαλώ εισάγετε το Email σας');
        }
        
        // Verify email exists
        if ( ! $user = $this->user_exists( $post['email'], 'email' ) ) {
            return array('status'=>0,'message'=>'Αυτό το Email δεν υπάρχει στο αρχείο μας');
        }
        
        // Create tokens
        $selector = bin2hex(random_bytes(8));
        $token = random_bytes(32);

        $url = sprintf('%sreset.php?%s', ABS_URL, http_build_query([
            'selector' => $selector,
            'validator' => bin2hex($token)
        ]));

        // Token expiration
        $expires = new DateTime('NOW');
        $expires->add(new DateInterval('PT01H')); // 1 hour
        
        // Delete any existing tokens for this user
        $this->db->delete('password_reset', 'email', $user->email);
        
        // Insert reset token into database
        $insert = $this->db->insert('password_reset', 
            array(
                'email'     =>  $user->email,
                'selector'  =>  $selector, 
                'token'     =>  hash('sha256', $token),
                'expires'   =>  $expires->format('U'),
            )
        );
        
        // Send the email
        if ( false !== $insert ) {
            // Recipient
            $to = $user->email;
            
            // Subject
            $subject = 'Your password reset link';
            
            // Message
            $message = '<p>We recieved a password reset request. The link to reset your password is below. ';
            $message .= 'If you did not make this request, you can ignore this email</p>';
            $message .= '<p>Here is your password reset link:</br>';
            $message .= sprintf('<a href="%s">%s</a></p>', $url, $url);
            $message .= '<p>Thanks!</p>';
            
            // Headers
            $headers = "From: " . ADMIN_NAME . " <" . ADMIN_EMAIL . ">\r\n";
            $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
            $headers .= "Content-type: text/html\r\n";
            
            // Send email
            $sent = mail($to, $subject, $message, $headers);
        }
        
        if ( false !== $sent ) {
            // If they're resetting their password, we're making sure they're logged out
            session_destroy();
            
            return array('status'=>1,'message'=>'Check your email for the password reset link');
        }
        
        return array('status'=>0,'message'=>'There was an error sending your password reset link');
    }
    
    public function reset_password($post) {
        // Required fields
        $required = array( 'selector', 'validator', 'password' );
        
        foreach ( $required as $key ) {
            if ( empty( $post[$key] ) ) {
                return array('status'=>0,'message'=>'There was an error processing your request. Error Code: 001');
            }
        }
        
        extract($post);
        
        // Get tokens
        $results = $this->db->get_results("SELECT * FROM password_reset WHERE selector = :selector AND expires >= :time", ['selector'=>$selector,'time'=>time()]);
        
        if ( empty( $results ) ) {
            return array('status'=>0,'message'=>'There was an error processing your request. Error Code: 002');
        }
        
        $auth_token = $results[0];
        $calc = hash('sha256', hex2bin($validator));
        
        // Validate tokens
        if ( hash_equals( $calc, $auth_token->token ) )  {
            $user = $this->user_exists($auth_token->email, 'email');
            
            if ( false === $user ) {
                return array('status'=>0,'message'=>'There was an error processing your request. Error Code: 003');
            }
            
            // Update password
            $update = $this->db->update('users', 
                array(
                    'password'  =>  password_hash($password, PASSWORD_DEFAULT),
                ), $user->ID
            );
            
            // Delete any existing tokens for this user
            $this->db->delete('password_reset', 'email', $user->email);
            
            if ( $update == true ) {
                // New password. New session.
                session_destroy();
            
                return array('status'=>1,'message'=>'Password updated successfully. <a href="index.php">Login here</a>');
            }
        }
        
        return array('status'=>0,'message'=>'There was an error processing your request. Error Code: 004');
    }
    
    private function rememberme($user) {
        $selector = base64_encode(random_bytes(9));
        $authenticator = random_bytes(33);
        
        // Set rememberme cookie
        setcookie(
            'rememberme', 
            $selector.':'.base64_encode($authenticator),
            time() + 864000,
            '/',
            '',
            false,  //for HTTPS set to true!
            true
        );
        
        // Clean up old tokens
        $this->db->delete('auth_tokens', 'username', $user->username);
        
        // Insert auth token into database
        $insert = $this->db->insert('auth_tokens', 
            array(
                'selector'  =>  $selector, 
                'token'     =>  hash('sha256', $authenticator),
                'username'  =>  $user->username,
                'expires'   =>  date('Y-m-d\TH:i:s', time() + 864000),
            )
        );
    }
    
    private function user_exists($where_value, $where_field = 'username') {
        $user = $this->db->get_results("
            SELECT * FROM users 
            WHERE {$where_field} = :where_value", 
            ['where_value'=>$where_value]
        );
        
        if ( false !== $user ) {
            return $user[0];
        }
        
        return false;
    }

    private function aem_exists($where_value, $where_field = 'aem') {
        $user = $this->db->get_results("
            SELECT * FROM users 
            WHERE {$where_field} = :where_value", 
            ['where_value'=>$where_value]
        );
        
        if ( false !== $user ) {
            return $user[0];
        }
        
        return false;
    }

    private function aem_student_exists($where_value, $where_field = 'aem') {
        $user = $this->db->get_results("
            SELECT * FROM students 
            WHERE {$where_field} = :where_value", 
            ['where_value'=>$where_value]
        );
        
        if ( false !== $user ) {
            return $user[0];
        }
        
        return false;
    }

    private function email_exists($where_value, $where_field = 'email') {
        $user = $this->db->get_results("
            SELECT * FROM users 
            WHERE {$where_field} = :where_value", 
            ['where_value'=>$where_value]
        );
        
        if ( false !== $user ) {
            return $user[0];
        }
        
        return false;
    }

    private function validate_cred($post_aem, $post_last_name, $post_first_name, $post_fathers_name, $post_registered, $where_field = 'aem') {
        $user = $this->db->get_cred("
            SELECT * FROM students 
            WHERE {$where_field} = :where_value", 
            ['where_value'=>$post_aem]
        );
        
        $dec_user = json_decode($user);
        $user_aem = $dec_user[0]->aem;
        $user_last_name = $dec_user[0]->last_name;
        $user_first_name = $dec_user[0]->first_name;
        $user_fathers_name = $dec_user[0]->fathers_name;
        $user_registered = $dec_user[0]->registered;

        if ( $post_aem == $user_aem ) {
            ChromePhp::log('Σωστό Α.Ε.Μ.');
            if ( $post_last_name == $user_last_name ) {
                ChromePhp::log('Σωστό Επίθετο');
                if ( $post_first_name == $user_first_name ) {
                    ChromePhp::log('Σωστό Όνομα');
                    if ( $post_fathers_name == $user_fathers_name ) {
                        ChromePhp::log('Σωστό Πατρώνυμο');
                        if ( $post_registered == $user_registered ) {
                            ChromePhp::log('Σωστή Περίοδος Εγγραφής');
                            return $user[0];
                        }
                    }
                }
            }

        }

        ChromePhp::log('Λάθος στοιχεία');
        return false;
    }

    public function search($post) {

        $search_aem = $post['search_aem'];
        ChromePhp::log($search_aem);
        $search_subject = $post['search_subject'];
        ChromePhp::log($search_subject);
        if ( $post['search_year'] ) {
            $search_year = $post['search_year'];
        }
        ChromePhp::log($search_year);
        if ( $post['search_exam_period'] ) {
            $search_exam_period = $post['search_exam_period'];
        }
        ChromePhp::log($search_exam_period);

        // Check if aem exists
        if ( false == $this->aem_student_exists( $search_aem ) ) {
            ChromePhp::log('Here');
            return array('status'=>0,'message'=>'Το Α.Ε.Μ. δεν υπάρχει');
        }

        // Create if they don't exist
        $search = $this->db->query("SELECT user_files.file_path, user_files.file_name, user_files.date, students.aem, students.last_name, students.first_name
        FROM user_files 
        INNER JOIN students ON user_files.aem=students.aem
        WHERE user_files.aem='$search_aem'
        AND user_files.subject_id='$search_subject'
        ORDER BY date desc,students.aem desc");
        
        echo "<th>Α.Ε.Μ.</th><th>Επώνυμο</th><th>Όνομα</th><th>Ημερομηνία</th></tr>\r\n";
        foreach ($search as $result) {
            $url = $result["file_path"]."/".$result["file_name"];
			$file_name = $result["file_name"];

			$file_date = $result["date"];
			$file_date = formatToGreekDate($file_date);
								
			$student_aem = $result["aem"];
			$student_last_name = $result["last_name"];
			$student_first_name = $result["first_name"];
			echo "<tr data-href=$url>\r\n";
            echo "<td>" . $student_aem . "</td>\r\n";
			echo "<td>" . $student_last_name . "</td>\r\n";
			echo "<td>" . $student_first_name . "</td>\r\n";
			echo "<td>" . $file_date . "</td>\r\n";
            echo "</tr>\r\n";
            ?>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
            <script>
            	$(document).ready(function(){
            		$('table tr').click(function(){
            			window.location = $(this).data('href');
            			return false;
            		});
            	});
            </script>
            <?php
        }

        ChromePhp::log('Error');
        return array('status'=>0,'message'=>'Παρουσιάστηκε ένα άγνωστο σφάλμα');
    }

    function formatToGreekDate($date){
        //Expected date format yyyy-mm-dd hh:MM:ss
        $greekMonths = array('Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλίος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος');
    
        $time = strtotime($date);
        $newformat = date('Y-m-d',$time);

        return $greekMonths[date('m', strtotime($newformat))-1]. ' '. date('Y', strtotime($newformat));
    }
    
}

$login = new Login;