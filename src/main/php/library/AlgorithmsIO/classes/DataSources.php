<?php
// Manip files

class DataSources 
{
    private $auth;
    private $generic;
    private $debug;
    private $s3;

    public function __construct(){

        $this->debug = false;

        // Init Auth Class
        require_once('AlgorithmsIO/classes/Auth.php');
        $this->auth = new Auth();

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();
        
        require_once('AlgorithmsIO/classes/S3Usage.php');
        $this->s3 = new S3Usage();

        // Setting for the S3.php uses strtotime
        date_default_timezone_set('America/New_York');
    }
    public function setDebugTrue(){

        $this->debug = true;
    }
    public function upload( $request_vars ){
    // This function allows a user to upload a file into the system

        $authToken = $request_vars->getParam( 'authToken' );

        $this->auth->setAuthToken( $authToken );

        $returnVal = '';
        
        if( $this->auth->isValid() ){

            $userId = $this->auth->getUserId();

            $temp_dir = '/tmp/';
            $new_filename = $userId.'_'.md5( mt_rand( 1000, mt_getrandmax() ) . time() );
            $temp_upload_dir_and_filename = $temp_dir.$new_filename; 
            $isUnitTest = $request_vars->getParam('isUnitTest'); // Special parame to get it to work for unit testing

            if( $isUnitTest )
                copy( $_FILES['theFile']['tmp_name'], $temp_upload_dir_and_filename );

            if ( move_uploaded_file( $_FILES['theFile']['tmp_name'], $temp_upload_dir_and_filename ) || $isUnitTest ){
            // Valid upload file

                $basename = $this->s3->upload( $temp_upload_dir_and_filename );

                if( $basename != '' ){                                                                                            
                                                                                                       
                    unlink( $temp_upload_dir_and_filename ); // Remove this file from the local system since it is on S3 now

                    // Do Database stuff to track this file
                    $data['user_id'] = $userId;
                    $data['type'] = $request_vars->getParam( 'type' );
                    $data['location'] = 'S3,algorithms.io';
                    $data['filesystem_name'] = $new_filename; 
                    $data['name'] = $request_vars->getParam( 'friendly_name' );
                    $data['description'] = $request_vars->getParam( 'friendly_description' );
                    $data['version'] = $request_vars->getParam( 'version' );
                    $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
                    $data['size'] = $_FILES['theFile']['size'];
                    $data['created'] = 'NOW()';
                    $data['lastModified'] = 'NOW()';

                    $returnVal = $this->generic->save( 'datasources', $data );

                }
            }
        }
        return $returnVal;
    }
    public function files( $request_vars ){
        // This gets a list of all the files this user owns

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal = '';

        if( $this->auth->isValid() ){

            $userId = $this->auth->getUserId();

            $query = 'SELECT * from datasources WHERE customer_id_seq = '. $userId; 

            $returnVal = $this->generic->customQuery( 'datasources', $query );
        }

        return json_encode( $returnVal );
    }
    public function userOwnsFile( $request_vars ){
        // Checks if the user owns the datasource_id_seq passed in or not

        $userOwnsFile['results'] = false;

        $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );

        if( is_numeric( $datasource_id_seq ) ){

            $results = json_decode( $this->getAFile( $request_vars ), true );

            if( count( $results ) > 0 )
                $userOwnsFile['results'] = true;
        }

        return json_encode( $userOwnsFile );
    }
    public function getAFile( $request_vars ){
        // Gets one file attribute by the id_seq

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal = '';

        if( $this->auth->isValid() ){

            $userId = $this->auth->getUserId();

            $id_seq = $request_vars->getParam( 'datasource_id_seq' );

            $query = 'SELECT * from datasources WHERE user_id = '. $userId .' AND id = ' . $id_seq;

            $returnVal = $this->generic->customQuery( 'datasources', $query );
        }

        return json_encode( $returnVal );
    }
    public function getSourceFile( $request_vars ){
        // Returns the actual file on S3 via the datasource_id_seq
        
        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );                                                                             
                                                                                                                             
        $returnVal = '';                                                                                                     
                                                                                                                             
        if( $this->auth->isValid() ){                                                                                        
                                                                                                                             
            $getAFile = json_decode( $this->getAFile( $request_vars ), true );

            if( count( $getAFile ) > 0 )
                $returnVal = $this->s3->getTextObjectandReturnContents( $getAFile[0]['filesystem_name'] ); 
            else
                $returnVal = 'No File Found';
        }                                                                                                                    
        return $returnVal;
    }
    public function deleteAFile( $request_vars ){
        // deletes a file
        // INPUT: datasource_id_seq
        
        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );                                                                             
                                                                                                                             
        $returnVal = '';                                                                                                     
                                                                                                                             
        if( $this->auth->isValid() ){                                                                                        
                                                                                                                             
            $userId = $this->auth->getUserId();                                                                              
                                                                                                                             
            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );

            if( $datasource_id_seq != '' ){

                $fileAttributes = json_decode( $this->getAFile( $request_vars ), true );

                //Delete file from S3
                if( isset( $fileAttributes[0]['filesystem_name'] ) )
                    $this->s3->deleteObject( $fileAttributes[0]['filesystem_name'] );
                $this->s3->deleteObject( 'rec_'.$datasource_id_seq );

                $returnVal = $this->generic->remove_noauth( 'datasources', $datasource_id_seq, 'id' );
                $returnVal = $this->generic->remove_noauth( 'mapping_name_to_id', $datasource_id_seq, 'datasource_id_seq' );
                $returnVal = $this->generic->remove_noauth( 'mapping_fields_to_ratings_file', $datasource_id_seq, 'datasource_id_seq' );
            }
        }
        return $returnVal;
    }
    public function updateSourceFile( $request_vars ){
        // Updates a file on S3.  Will delete and put new files with the same name
        // INPUT: datasource_id_seq, json_data - json of new file content

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal['result'] = 'Failed';

        if( $this->auth->isValid() ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id' );
            $json_data = $request_vars->getParam( 'json_data' );

            if( $datasource_id_seq != '' ){

                $fileAttributes = json_decode( $this->getAFile( $request_vars ), true );
                $filesystem_name = $fileAttributes[0]['filesystem_name'];

                // Delete files from S3: source json data file and the rec file
                $this->s3->deleteObject( $fileAttributes[0]['filesystem_name'] );
                $this->s3->deleteObject( 'rec'.$datasource_id_seq );
                // Remove mapping_name_to_id
                $results = $this->generic->remove_noauth( 'mapping_name_to_id', $datasource_id_seq, 'datasource_id' );

                // Put new file to S3
                $basename = $this->s3->uploadText( $filesystem_name, $json_data );
        
                $returnVal['result'] = 'Success';
            }
        }

        return json_encode( $returnVal );
    } 
    public function appendToSourceFile( $request_vars ){
        // Takes content from one datasource file and appends it to another
        // INPUT: source_datasource_id_seq, addition_datasource_id_seq   
 
        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal['result'] = 'Failed'; 

        if( $this->auth->isValid() ){

            $source_datasource_id_seq = $request_vars->getParam( 'source_datasource_id' );
            $addition_datasource_id_seq = $request_vars->getParam( 'addition_datasource_id' );            

            $json_data = $request_vars->getParam( 'json_data' );

            if( is_numeric( $source_datasource_id_seq ) && is_numeric( $addition_datasource_id_seq ) ){

                // Set the request var with this datasource_id_seq and get the file
                $data['datasource_id_seq'] = $source_datasource_id_seq;
                $request_vars->setParams( $data );
                $source_datasource_id_seq_content = json_decode( $this->getSourceFile( $request_vars ), true );

                // Set the request var with this datasource_id_seq and get the file
                $data['datasource_id_seq'] = $addition_datasource_id_seq;
                $request_vars->setParams( $data );
                $addition_datasource_id_seq_content = json_decode( $this->getSourceFile( $request_vars ), true );

                // Combine the 2 data sets
                foreach( $addition_datasource_id_seq_content['data'] as $aRow ){

                    array_push( $source_datasource_id_seq_content['data'], $aRow );
                }

                // Update the source_datasource_id_seq with this new data
                $data['datasource_id_seq'] = $source_datasource_id_seq;
                $data['json_data'] = json_encode( $source_datasource_id_seq_content );
                $request_vars->setParams( $data );

                $this->updateSourceFile( $request_vars ); 
                
                $returnVal['result'] = 'Success'; 
            }
        }
        return json_encode( $returnVal );
    }
}
