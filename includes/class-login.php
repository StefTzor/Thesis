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

        $professor_name = mb_strtoupper($post['name'], 'UTF-8');
        // Create if they don't exist
        $insert = $this->db->insert('users', 
            array(
                'username'          =>  $post['username'], 
                'password'          =>  password_hash($post['password'], PASSWORD_DEFAULT),
                'email'             =>  $post['email'],
                'professor_name'    =>  $professor_name,
                'userType'          =>  'not_verified', 
            )
        );
        
        if ( $insert == true ) {
            return array('status'=>1,'message'=>'Ο Λογαριασμός δημιουργήθηκε επιτυχώς');
        }
        
        return array('status'=>0,'message'=>'Παρουσιάστηκε ένα άγνωστο σφάλμα');
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
    
}

$login = new Login;