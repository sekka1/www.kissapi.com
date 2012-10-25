<?php

class AuthToken
{
    private $generic_db;

    public function __construct(){
	$this->debug = false;
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic_db = new Generic();
    }
    public function createNewAuthToken( $user_id_seq, $description ){
        // Creates an auth token

        $new_authToken = md5( mt_rand( 1000, mt_getrandmax() ) . time() );

        //$data['id'] = $user_id_seq;
        $data['user_id'] = $user_id_seq;
        $data['token'] = $new_authToken;
        $data['description'] = $description;
        $data['created'] = 'NOW()';
        $data['lastModified'] = 'NOW()';

        $results = $this->generic_db->save( 'authtokens', $data );

        return $results;
    }
    public function retrieveUsersAuthTokens( $user_id_seq ){
        // Retrieves and returns all the auth token this user_id has

        $sql = "SELECT token FROM authtokens WHERE user_id = '". $user_id_seq."'";

        $results = $this->generic_db->customQuery( 'authtokens', $sql );

        return $results;
    }
    public function trustedPartnerCreateAuthToken( $authToken, $email, $user_id_seq ){
        // This method is for our trusted partners, allowing them to create auth token in this domain

        $new_authToken = '';

        if( $authToken != '' && $email != '' && $user_id_seq != '' ){

            //
            // Will need to further implement this auth checking here!!
            //

            if( $authToken == 'iMarketingB2B_29384jchdJ33940fjJdheuckeh' ){
                // Authenticated

                $id_seq = $this->createNewAuthToken( $user_id_seq, $email.','.$authToken );
            }
        }
        return $new_authToken;
    }
    private function debug($msg,$obj="") {
	if($this->debug) {
		if(gettype($obj)=="array" || gettype($obj)=="object") {
			error_log($msg.print_r($obj, true));
		} else {
			error_log($msg.$obj);
		}
	}
    }

    private function error($msg,$obj="") {
		if(gettype($obj)=="array" || gettype($obj)=="object") {
			error_log($msg.print_r($obj, true));
		} else {
			error_log($msg.$obj);
		}
    }

}
