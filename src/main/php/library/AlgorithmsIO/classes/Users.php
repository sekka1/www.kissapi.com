<?php

class Users
{
    private $generic_db;
    private $authEmail;
    private $authPassword;
    private $smtp;
    private $fromEmail;
    private $fromName;
    private $bccEmail;
    private $subject;
    private $domain;
    private $usersEmail;

    public function __construct(){

        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic_db = new Generic();

        $this->authEmail = 'no-reply@algorithms.io';
        $this->authPassword = 'y&=752QN';
        $this->smtp = 'smtp.gmail.com';
        $this->fromEmail = 'no-reply@algorithms.io';
        $this->fromName = 'Algorithms.io';
        $this->bccEmail = 'no-reply@algorithms.io';
        $this->bccName = 'no-reply';
        $this->subject = 'Algorithms.io Account Activation';
        $this->domain = 'www.algorithms.io';
    }
    public function setUserEmail( $email ){
        $this->usersEmail = $email;
    }
    public function initialSignUp( $request_vars ){
        // This is an initial user signup.  Put users info into user_signup table and send them an activiation email

        $email = $request_vars->getParam( 'email' );
        $password = $request_vars->getParam( 'password' );

        $results = '';

        if( $email != '' && $password != '' ){
            // Save user sign up info

            if( ! $this->accountExist( $email ) ){

                $data['email'] = $email;
                $data['password'] = md5( $password );
                $data['unique_id'] = md5( mt_rand( 1000, mt_getrandmax() ) . time() );

                // gk - 9/28 - this is correct.  we never changed this table with the new date field names
                $data['datetime_created'] = 'NOW()';
                $data['datetime_modified'] = 'NOW()';

                $results = $this->generic_db->save( 'user_signup', $data );

                if( $results > 0 ){
                    // Send user an email

                    $this->sendActivateLink( $data['email'], $data['unique_id'] );
                }
            }
        }
        return $results;
    }
    public function sendActivateLink( $email, $unique_id ){
        // Sends an email with the activation link to the user

        require_once('AlgorithmsIO/classes/Utilities.php');
        $utilities = new Utilities();

        date_default_timezone_set('America/New_York');

        $toEmail = $email;
        $body = 'First, thank you for signing up. In order to activate your account please go to the following address in your browser: https://'.$this->domain.'/signup/verify/email/'.$email.'/id/'.$unique_id;
        $utilities->email( $this->authEmail, $this->authPassword, $this->smtp, $this->fromEmail, $this->fromName, $toEmail, $email, $this->bccEmail, $this->bccName, $this->subject, $body );

    }
    public function accountExist( $email ){
        // Checks if the account exists in the Users table

        $doesExist = false;

        $sql = 'SELECT name FROM users WHERE name="'.$email.'"';

        $results = $this->generic_db->customQuery( 'users', $sql );

        if( count( $results ) > 0 )
            $doesExist = true;
         
        return $doesExist;
    }
    public function activateAccount( $email, $unique_id ){
        // Verifies and enables the new user account

        $isValid = $this->isValidActivationLink( $email, $unique_id );

        $result = 0;

        if( $isValid ){
            // Enable the account
            $user_id_seq = $this->enableAccount( $email, $unique_id );
            
            require_once('AlgorithmsIO/classes/AuthToken.php');
            $authToken = new AuthToken();

            $authToken->createNewAuthToken( $user_id_seq, 'Created on user sign up' );
        } else {
            $result = 'Is Not Valid';
        }
        return $result;
    }
    public function isValidActivationLink( $email, $unique_id ){
        // Verifies the user's activation link is a valid one

        $isValid = false;

        $sql = 'SELECT * FROM user_signup WHERE email = "'.$email.'" AND unique_id = "'.$unique_id.'"'; 
error_log($sql);
        $results = $this->generic_db->customQuery( 'user_signup', $sql );

        if( count( $results ) > 0 ){

            $isValid = true;
        }
        return $isValid;
    }
    public function enableAccount( $email, $unique_id ){
        // Enabled this new user's account.  Moves it from the user_signup table to the Users table so they can log in

        $sql = 'SELECT * FROM user_signup WHERE email = "'.$email.'" AND unique_id = "'.$unique_id.'"';

        $results = $this->generic_db->customQuery( 'user_signup', $sql );

        $data['name'] = $email;
        $data['username'] = $email;
        $data['password'] = $results[0]['password'];
        $data['created'] = 'NOW()';
        $data['lastModified'] = 'NOW()';
        
        $results_user_id_seq = $this->generic_db->save( 'users', $data );

        // Delete this entry from the user_signup table
        $this->generic_db->remove_noauth( 'user_signup', $results[0]['id_seq'], 'id_seq' );

        return $results_user_id_seq;
    }
    public function trustedPartnersUserCreation( $request_vars ){
        // This function lets our trusted partner to create an account into this domain and it will also issue an auth token
        // An auth token from the trusted partner has to be passed in and authenticated

        $authToken = $request_vars->getParam( 'authToken' ); 
        $email = $request_vars->getParam( 'email' );

        $data = array();
//echo $this->config->app->trustpartners->imarketingb2b;
        if( $authToken != '' && $email != '' ){

            //
            // Will need to further implement this auth checking here!!
            //
    
            if( $authToken == 'iMarketingB2B_29384jchdJ33940fjJdheuckeh' ){
                // Authenticated

                if( ! $this->accountExist( $email ) ){
                    // Create a user account 
                    $password = md5( mt_rand( 1000, mt_getrandmax() ) . time() );
                    $user_id_seq = $this->directAddUser( $email, $email, $password );

                    // Generate a new auth token 
                    require_once('AlgorithmsIO/classes/AuthToken.php');
                    $authTokenClass = new AuthToken();
                    $new_user_auth_token = $authTokenClass->trustedPartnerCreateAuthToken( $authToken, $email, $user_id_seq );

                    $data['outcome'] = 'Success';
                    $data['auth_token'] = $new_user_auth_token;
                } else {
                    // Account exist already

                    // Not sure how to handle this yet.  For now just return no new key but eventually we probably just want to make them an auth key under the same email.

                    $data['outcome'] = 'Account Already Exist';
                }
            }
        }
        return json_encode( $data );
    }
    private function directAddUser( $name, $username, $password ){
        // Adds a user directly into the Users table by passing the verification step

        $data['name'] = $name;
        $data['username'] = $username;
        $data['password'] = $password;
        $data['created'] = 'NOW()';
        $data['lastModified'] = 'NOW()';

        $results = $this->generic_db->save( 'users', $data );        

        return $results;
    }
    public function getUserIdByEmailAddress( $email ){
    // Returns the user_id_seq that is associated to this email address
    // Email addresses as a username in our system should be unique

        $user_id_seq = -1;

        $sql = 'SELECT id FROM users where username="'.$email.'"';

        $results = $this->generic_db->customQuery( 'users', $sql );
        //error_log(print_r($results,true));
        return $results[0]['id'];
    }
    public function updateUsersPassword( $password = null ){

        if( $password == null ){
            $password = substr( md5( mt_rand( 1000, mt_getrandmax() ) . time() ), 3, 13 );
        }

        $data['password'] = md5( $password );
        $results = $this->generic_db->edit_noauth( 'users', '"'.$this->usersEmail.'"', $data, 'username' );

        return $password;
    }
    /*
    * Inserts a row in the user_signup table so that the unit test can run through the verification workflow
    */
    public function unitTestVerificationInsert(){

        $sql = 'INSERT INTO user_signup VALUES("","unit_test_guy@algorithms.io","5f78fdf11512f09ef919cb60b0fbe799", "82c9e5a69cfae9a22d1e7f26dc732177", NOW(), NOW() )';

        $results = $this->generic_db->customQuery( 'user_signup', $sql, false );
    }
}

?>
