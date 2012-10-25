<?php
// Handles the mapping functionality to massage the user's data into what the recommendation engine wants

class Mapping
{
    private $auth;
    private $generic;
    private $s3;
    private $DataSources;
    private $debug;
    private $fileUploadUrl;

    public function __construct(){

        $this->fileUploadUrl = 'http://www.algorithms.io/data/index/class/DataSources/method/upload/';

        $this->debug = false;

        // Init Auth Class
        require_once('AlgorithmsIO/classes/Auth.php');
        $this->auth = new Auth();

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();

        require_once('AlgorithmsIO/classes/S3Usage.php');
        $this->s3 = new S3Usage();

        require_once('AlgorithmsIO/classes/DataSources.php');
        $this->DataSources = new DataSources();
    }
    public function setDebugTrue(){

        $this->debug = true;
    }
    public function userFields( $request_vars ){
        // Maps the users fields to what is needed by the recommendation engine
        // Mapping fields onto user id, item id, and preference
        // The user inputs what fields in the file maps to the user id and item id

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );
        $userOwnsFile = json_decode( $this->DataSources->userOwnsFile( $request_vars ), true );
        $userId = $this->auth->getUserId();

        $returnVal = '';

        if( $this->auth->isValid() && $userOwnsFile['results'] > 0 ){

            $data['customer_id_seq'] = $userId;
            $data['datasource_id_seq'] = $request_vars->getParam( 'datasource_id_seq' );
            $data['field_user_id'] = $request_vars->getParam( 'field_user_id' );
            $data['field_item_id'] = $request_vars->getParam( 'field_item_id' );
            $data['field_preference'] = $request_vars->getParam( 'field_preference' );
            //$data['datetime_created'] = 'NOW()';
            //$data['datetime_modified'] = 'NOW()';

            $returnVal = $this->generic->save( 'mapping_fields_to_ratings_file', $data );
        }else{
            $returnVal = 'error';
        }

        return json_encode( $returnVal );
    }
    public function getUserFields( $request_vars ){
        // Gets the user's field mapping for a requested datasource_id_seq
        // INPUT: datasource_id_seq

        $authToken = $request_vars->getParam( 'authToken' );                                                                     
        $this->auth->setAuthToken( $authToken );                                                                                 
                                                                                                                                 
        $returnVal = '';                                                                                                         
                                                                                                   
        if( $this->auth->isValid() ){
        
            $userId = $this->auth->getUserId();

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' ); 

            $query = 'SELECT * FROM mapping_fields_to_ratings_file WHERE datasource_id_seq = '.$datasource_id_seq.' ORDER BY datetime_created desc LIMIT 1'; 

            $returnVal = $this->generic->customQuery( 'mapping_fields_to_ratings_file', $query );
        }
        return json_encode( $returnVal );
    }
    public function isLoadedUserFieldsMapping( $request_vars ){
        // Returns a boolean on whether the user has entered in a user field mapping for this data
        // INPUT: datasource_id_seq
        
        $authToken = $request_vars->getParam( 'authToken' );                                                                     
        $this->auth->setAuthToken( $authToken );                                                                                            
                                                                                                                                            
        $returnVal = 'false';                                                                                                          
                                                                                                                                            
        if( $this->auth->isValid() ){ 
    
            $getUserFields = json_decode( $this->getUserFields( $request_vars ), true );

            if( count( $getUserFields ) == 1 )
                $returnVal = 'true';
        }
        return $returnVal;
    }
    public function createArbitraryIdMapping( $request_vars ){
        // This is the arbitrary mapper for data in a json data structure.  For large files this was very memory intensive.  We are trying to do everything in a CSV format now.  The equivilent to this function is in ArbitraryMaping.php

        // This function takes the user's datasource file that has been up loaded and takes the information the user has given us about what mapping of columns are used for the user_id and item_id to create a recommendation file.  Arbitrary IDs has to be created b/c most user id and item ids that the users has the recommendation system wont like the format.  So we are mapping it out and just putting in some generic numbers onto them so the recommendation system does not barf on it.
        // INPUT: datasource_id_seq
        // OUTPUT: rec file on S3

        //ini_set('memory_limit', '8024M');
        //ini_set('max_execution_time', '600');

        $authToken = $request_vars->getParam( 'authToken' );                                                                     
        $this->auth->setAuthToken( $authToken );

        $returnVal['results'] = 'Failed';

        if( $this->auth->isValid() ){

            $userId = $this->auth->getUserId();

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );

            // Get this files name
            $files = json_decode( $this->DataSources->getAFile( $request_vars ), true ); 
            //print_r( $files );

            // Get Content from S3
            $contents = json_decode( $this->s3->getTextObjectandReturnContents( $files[0]['filesystem_name'] ), true );
            $returnVal['json_last_error'] = json_last_error();
            //print_r( $contents );

            if( $returnVal['json_last_error'] == 0 ){
            
                // Get Mapping schema
                $userFieldMappings = json_decode( $this->getUserFields( $request_vars ), true ); 
                //print_r( $userFieldMappings );

                // Create arbitrary Mapping for the User ID
                $userIDMapping = $this->createXToIdMapping( $contents['data'], $userFieldMappings[0]['field_user_id'] );         
                //print_r( $userIDMapping );
                // Map the data to the arbitrary id
                $newDataWithUserMapped = $this->mapXtoID( $contents['data'], $userIDMapping, $userFieldMappings[0]['field_user_id'] );
                //print_r( $newDataWithUserMapped );
//system( 'echo "memory_get_usage: ' . memory_get_usage( true ) . ' - memory_get_peak_usage: ' . memory_get_peak_usage( true ) . '" >> /tmp/output.txt'); 
//echo 'memory_get_usage: ' . memory_get_usage( true ) . ' - memory_get_peak_usage: ' . memory_get_peak_usage( true );

                // Create arbitrary Mapping for the Item ID
                $itemIDMapping = $this->createXToIdMapping( $contents['data'], $userFieldMappings[0]['field_item_id'] );
                //print_r( $itemIDMapping );
                // Map the data to the arbitrary id
                $newDataWithUserAndItemMapped = $this->mapXtoID( $newDataWithUserMapped, $itemIDMapping, $userFieldMappings[0]['field_item_id'] );
                //print_r( $newDataWithUserAndItemMapped );
system( 'echo "memory_get_usage: ' . memory_get_usage( true ) . ' - memory_get_peak_usage: ' . memory_get_peak_usage( true ) . '" >> /tmp/output.txt');

                // $newDataWithUserAndItemMapped - this array holds the mapped values for all the user's data onto an arbitrary ID
                // $userIDMapping - Holds the user mapping to the arbitrary ID 
                // $itemIDMapping - holds the item mapping to the arbitrary ID

                // Insert $userIDMapping and $itemIDMapping into DB
                $this->addMappingNameToId( 'user_id', $datasource_id_seq, $userIDMapping );
                $this->addMappingNameToId( 'item_id', $datasource_id_seq, $itemIDMapping );
                //echo 'Created Mapping for users and items<br/>';
system( 'echo "memory_get_usage: ' . memory_get_usage( true ) . ' - memory_get_peak_usage: ' . memory_get_peak_usage( true ) . '" >> /tmp/output.txt');

                // Put the rec file onto S3 with the proper format
                $recString = $this->createRecFile( $newDataWithUserAndItemMapped, $userFieldMappings );    
                $this->s3->uploadText( 'rec_'.$datasource_id_seq, $recString ); 
                //echo 'Uploaded recommendation file to s3<br/>'; 

                $returnVal['results'] = 'Success';
            }
        }
        return json_encode( $returnVal );
    }
    public function getArbitraryIdMapping( $request_vars ){
        // Gets all the arbitrary id mapping for via a datasource_id_seq
        // INPUT: datasource_id_seq

        $authToken = $request_vars->getParam( 'authToken' );                                                                  
        $this->auth->setAuthToken( $authToken );                                                                             

        $returnVal = '';                                                                                                     

        if( $this->auth->isValid() ){
    
            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
    
            $query = 'SELECT * FROM mapping_name_to_id WHERE datasource_id_seq = '.$datasource_id_seq;

            $returnVal = json_encode( $this->generic->customQuery( 'mapping_name_to_id', $query ) );
        }
        return $returnVal;
    }
    public function getArbitraryFieldMappedValueByOriginalName( $request_vars ){
        // Gets one field_mapped_value (aka arbitrary id) via a field_original_name (the item or user )
        // INPUT: datasource_id_seq, field_original_name
        // RETURN: the field_mapped_value of this item/user

        $authToken = $request_vars->getParam( 'authToken' );
        $userOwnsFile = json_decode( $this->DataSources->userOwnsFile( $request_vars ), true );
        $this->auth->setAuthToken( $authToken );

        $returnVal = '';

        if( $this->auth->isValid() && $userOwnsFile['results'] > 0 ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $field_original_name = $request_vars->getParam( 'field_original_name' );
            $type = $request_vars->getParam( 'type' );

            if( is_numeric( $datasource_id_seq ) && $field_original_name != '' && $type != '' ){

                $query = 'SELECT * FROM mapping_name_to_id WHERE datasource_id_seq = '.$datasource_id_seq.' AND field_original_name = "'.$field_original_name .'" AND type = "'.$type.'"';

                $returnVal = json_encode( $this->generic->customQuery( 'mapping_name_to_id', $query ) );
            }
        }
        return $returnVal;

    }
    public function getArbitraryFieldOriginalNameValueByMappedValue( $request_vars ){
        // Gets one field_original_name value via a mapped field value (aka arbitrary id)
        // INPUT: datasource_id_seq, field_mapped_value
        // RETURN: returns the field_original_name value

        $authToken = $request_vars->getParam( 'authToken' );
        $userOwnsFile = json_decode( $this->DataSources->userOwnsFile( $request_vars ), true );
        $this->auth->setAuthToken( $authToken );

        $returnVal = '';

        if( $this->auth->isValid() && $userOwnsFile['results'] > 0 ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $field_mapped_value = $request_vars->getParam( 'field_mapped_value' );
            $type = $request_vars->getParam( 'type' );

            if( is_numeric( $datasource_id_seq ) && is_numeric( $field_mapped_value ) && $type != '' ){

                $query = 'SELECT * FROM mapping_name_to_id WHERE datasource_id_seq = '.$datasource_id_seq.' AND field_mapped_value = '.$field_mapped_value.' AND type = "'.$type.'"';

                $returnVal = json_encode( $this->generic->customQuery( 'mapping_name_to_id', $query ) );
            }
        }
        return $returnVal;
    }
    public function getArbitraryIdMappingSearch( $request_vars ){
        // Same as getArbitraryIdMapping but this function intakes one real item name and returns all the matches it finds in
        // this datasource_id_seq.
        // INPUT: datasource_id_seq, type (user_id, item_id), real item name (regex)

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal = array();

        if( $this->auth->isValid() ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $type = $request_vars->getParam( 'type' );
            $item = $request_vars->getParam( 'item' );
            $searchField = $request_vars->getParam( 'searchField' );

            // Get arbitrary mapping for this datasource_id_seq
            $getArbitraryIdMapping = json_decode( $this->getArbitraryIdMapping( $request_vars ), true );

            // Look through the returned list to see if there is a match
            foreach( $getArbitraryIdMapping as $anItem ){

                if( $anItem['type'] == $type ){

                    if( $searchField == 'field_mapped_value' ){
                        // Searching based on the field_mapped_value field.  Mapping number to the field_original_name

                        if( $item == $anItem['field_mapped_value'] )
                            array_push( $returnVal, $anItem );

                    } elseif( $searchField == 'field_original_name' || $searchField == '' ){
                        // Searching based on the field_original_name field.  Mapping the field_original_name (real item number) to the arbitrary number

                        if( preg_match( '/'.$item.'/', $anItem['field_original_name'] ) ){

                            array_push( $returnVal, $anItem );
                        }
                    }
                }
            }
            return json_encode( $returnVal );
        }
        return $returnVal;
    }
    public function getValueMapping( $request_vars ){
    // Maps the arbitrary ID back to the real user/item name or vice versa
    // Searches the mapping_name_to_id table by either the id or the real name to return the other value

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal = array();

        if( $this->auth->isValid() ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $type = $request_vars->getParam( 'type' );
            $item = $request_vars->getParam( 'item' );
            $searchField = $request_vars->getParam( 'searchField' );

            if( is_numeric( $datasource_id_seq ) && 
                ( $type == 'user' || $type == 'item' ) &&
                $item != '' &&
                ( $searchField == 'id' || $searchField == 'original_name' ) ){

                if( $searchField == 'id' && is_numeric( $item ) ){
                // Searching via an ID trying to find the original name

                    $query = 'SELECT * FROM mapping_name_to_id WHERE datasource_id_seq='.$datasource_id_seq.' AND type="'.$type.'_id" AND field_mapped_value='.$item;

                    $returnVal = $this->generic->customQuery( 'mapping_name_to_id', $query );

                }
                if( $searchField == 'original_name' ){
                // Searching via the original name for the mapped id

                    $query = 'SELECT * FROM mapping_name_to_id WHERE datasource_id_seq='.$datasource_id_seq.' AND type="'.$type.'_id" AND field_original_name LIKE "'.$item.'%"';

                    $returnVal = $this->generic->customQuery( 'mapping_name_to_id', $query );
                }
            }
        }
        return json_encode( $returnVal );
    }
    public function isLoadedArbitraryIdMapping( $request_vars ){
        // Returns a boolean on whether the arbitrary id mapping has been created for this file/datasource
        // INPUT: datasource_id_seq

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );

        $returnVal = 'false';

        if( $this->auth->isValid() ){

            $getArbitraryIdMapping = json_decode( $this->getArbitraryIdMapping( $request_vars ), true );

            if( count( $getArbitraryIdMapping ) > 0 )
                $returnVal = 'true';
        }
        return $returnVal;
    }
    public function segmentData( $request_vars ){
        // Segment up the data by a field that the user provides.  With each segment a new file will be created and added to this user's lists of files
        // INPUT: datasource_id_seq, segmentField
        // OUTPUT: Files for each segment and a json array of the new files and attributes

        //ini_set('memory_limit', '4024M');
        //ini_set('max_execution_time', '600');

        $authToken = $request_vars->getParam( 'authToken' );                                                                  
        $this->auth->setAuthToken( $authToken );

        $returnVal = '';

        if( $this->auth->isValid() ){

            $userId = $this->auth->getUserId();
            $segmentField = $request_vars->getParam( 'segmentField' );

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );

            // Get this files name
            $files = json_decode( $this->DataSources->getAFile( $request_vars ), true );
            print_r( $files );

            // Get Content from S3
            $contents = json_decode( $this->s3->getTextObjectandReturnContents( $files[0]['filesystem_name'] ), true );
            //echo $contents;
            //print_r( $contents );

            $segmentedData_array = array();

            // Put each of the segments into a new array
            foreach( $contents['data'] as $aLine ){

                $aLine[$segmentField] = strtolower( $aLine[$segmentField] );

                if( $aLine[$segmentField] == '' )
                    $aLine[$segmentField] = 'blank';

                // Go through the datasource and segment it into various arrays
                if( array_key_exists( $aLine[$segmentField], $segmentedData_array ) ){
                    // Push data into this array index

                    array_push( $segmentedData_array[$aLine[$segmentField]], $aLine );
                } else {
                    // This array doesnt exist yet.  Create and then push into it

                    $segmentedData_array[$aLine[$segmentField]] = array();

                    array_push( $segmentedData_array[$aLine[$segmentField]], $aLine );

                }
            }
            //print_r( $segmentedData_array );

            // Craete new files for each of the segments
            require_once('AlgorithmsIO/classes/Utilities.php');
            $utilities = new Utilities();

            $new_file_datasource_id_seq_array = array();

            // Loop through the array; create a new file and upload it via the upload API into this user's space
            foreach( $segmentedData_array as $key=>$aSegment ){

                // Put this segment into a temp array
                $temp_array['data'] = $aSegment;
                //print_r( $temp_array );

                // Output it to a file in json format
                $temp_json = json_encode( $temp_array );
                $temp_file = '/tmp/'.$datasource_id_seq.'__tmp.txt';
                $fp = fopen( $temp_file, 'w');
                fwrite($fp, $temp_json);
                fclose($fp);

                // Upload it to this user's file list
                $url = $this->fileUploadUrl;
                $post_params['theFile'] = '@'.$temp_file;
                $post_params['authToken'] = $authToken; 
                $post_params['type'] = 'Auto Generated - Segment';
                $post_params['friendly_name'] = $key; 
                $post_params['friendly_description'] = 'Segment: '.$key;
                $post_params['version'] = '1';

                $outcome = $utilities->curlPost( $url, $post_params );

                $outcome = str_replace( " \n", "", $outcome );

                // Prep the return data json for this file
                $output_data['datasource_id_seq'] = $outcome;
                $output_data['type'] = $post_params['type'];
                $output_data['friendly_name'] = $key;
                $output_data['friendly_description'] = $post_params['friendly_description'];
                $output_data['version'] = $post_params['version'];

                if( is_numeric( $outcome ) )
                    array_push( $new_file_datasource_id_seq_array, $output_data );

                // Delete the temp file
                unlink( $temp_file );
            }
            $returnVal = json_encode( $new_file_datasource_id_seq_array );
            //print_r( $new_file_datasource_id_seq_array );
        }
        return $returnVal;
    }
    private function mapXtoID( $data, $IDMapping, $itemToMap ){
        // Makes the mapping per the user id mapping array

        foreach( $data as $key=>$val ){

            $data[$key][$itemToMap] = $IDMapping[$val[$itemToMap]];
        }

        return $data;
    }

    private function createXToIdMapping( $data, $itemToMap ){
        // Manip the user into an arbitrary ID

        $user_name_mapping = array(); // key=user name, value = new user id
        $user_id_counter = 1;

        foreach( $data as $aLine ){

            if( ! array_key_exists( $aLine[$itemToMap], $user_name_mapping ) ){

                $user_name_mapping[$aLine[$itemToMap]] = $user_id_counter;

                $user_id_counter++;
            }

        }
        return $user_name_mapping;
    }
    public function addMappingNameToId( $type, $datasource_id_seq, $IDMapping ){
        // Inserts an Array of arbitrary id mapping to its real name into the DB
        // Type - user_id, item_id
        // $datasource_id_seq - id_seq of a file
        // $IDMapping - array holding the mapping $key=>$val

        $userID = $this->auth->getUserId();

        // Delete mapping first for this $datasource_id_seq and type
        // Had to do this b/c the customQuery was barfing for some reason on a DELETE FROM mapping_name_to_id...
        $a_id_seq = '"'.$type.'"';
        $id_seq_name = 'datasource_id_seq = '.$datasource_id_seq.' AND type ';

        $this->generic->remove_noauth( 'mapping_name_to_id', $a_id_seq, $id_seq_name );

        // Add mapping
        foreach( $IDMapping as $key=>$val ){

            $data['customer_id_seq'] = $userID;
            $data['datasource_id_seq'] = $datasource_id_seq;
            $data['type'] = $type;
            $data['field_original_name'] = $val;
            $data['field_mapped_value'] = $key;
            $data['datetime_created'] = 'NOW()';
            $data['datetime_modified'] = 'NOW()';

            $this->generic->save( 'mapping_name_to_id', $data );
        }

    }
    public function addOneMappingNameToId( $type, $datasource_id_seq, $arbitrary_id, $value ){
        // Inserts one arbitrary id mapping to its real name into the DB
        // Type - user_id, item_id
        // $datasource_id_seq - id_seq of a file
        // $arbitrary_id - numeric number that is unquie to this set, the user of this function has to handle this unquieness
        // $value - the value associated with this $arbitrary_id

        $userID = $this->auth->getUserId();

        $data['customer_id_seq'] = $userID;
        $data['datasource_id_seq'] = $datasource_id_seq;
        $data['type'] = $type;
        $data['field_original_name'] = $value;
        $data['field_mapped_value'] = $arbitrary_id;
        $data['datetime_created'] = 'NOW()';
        $data['datetime_modified'] = 'NOW()';

        $result = $this->generic->save( 'mapping_name_to_id', $data );

        return json_encode( $result );
    }
    private function createRecFile( $recArray, $userFieldsMapping ){
        // Creates a string output of the recArray with new lines
        // INPUT: a rec array
        //      $user field mapping

        $text = "";

        foreach( $recArray as $aRec ){

            $text .= $aRec[$userFieldsMapping[0]['field_user_id']].",".$aRec[$userFieldsMapping[0]['field_item_id']].",4\n";

        }
        return $text;
    }
    public function deleteMapping( $request_vars ){
        // Deletes all the mapping in the mapping_name_to_id table for a given datasource_id_seq

        $authToken = $request_vars->getParam( 'authToken' );
        $userOwnsFile = json_decode( $this->DataSources->userOwnsFile( $request_vars ), true );
        $this->auth->setAuthToken( $authToken );

        $returnVal = '';

        if( $this->auth->isValid() && $userOwnsFile['results'] > 0 ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );

            $returnVal = $this->generic->remove_noauth( 'mapping_name_to_id', $datasource_id_seq, 'datasource_id_seq' ); 
        }

        return json_encode( $returnVal );
    }
}
