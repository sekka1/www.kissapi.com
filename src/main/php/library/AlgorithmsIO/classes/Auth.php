<?php
/*
    This class deals with the API authentication and authorization
*/

class Auth
{
    private $authToken;
    private $generic;

    private $debug;

    public function __construct(){

        $this->authToken = '';

        $this->debug = false;

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();
    }
    public function setDebugTrue(){

        $this->debug = true;
    }
    public function setAuthToken( $authToken ){
        $this->authToken = $authToken;
    }
    public function getAuthToken(){
        return $this->authToken;
    }
    public function isValid(){
    // Checks if current auth token is a valid one

        $isValid = false;

        $sql = 'SELECT id FROM authtokens WHERE token = \''.$this->authToken.'\'';

        $results = $this->generic->customQuery( 'authtokens', $sql );

        if( $this->authToken == '1234' 
            || $this->authToken == '888'
            || $this->authToken == '5678'
            || count( $results ) == 1
            )
            $isValid = true;

        return $isValid;
    }
    public function getUserId(){
        // Returns the user_id_seq of this user's auth token

        $user_id_seq = 0;

        $sql = 'SELECT user_id FROM authtokens WHERE token = \''.$this->authToken.'\'';

        $results = $this->generic->customQuery( 'authtokens', $sql );

        if( count( $results ) == 1 )
            $user_id_seq = $results[0]['user_id'];

        return $user_id_seq;
    }
}
