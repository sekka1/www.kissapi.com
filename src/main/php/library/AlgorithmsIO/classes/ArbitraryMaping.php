<?php
// Creates the arbitrary mapping that Mahout needs based on a CSV datasource

class ArbitraryMaping
{
    private $auth;
    private $generic;
    private $s3;
    private $debug;
    private $datasources;
    private $mapping;
    private $csvFiles;
    private $arbitrary_id_item;
    private $arbitrary_id_user;
    private $authToken;
    private $mapping_array_user;
    private $mapping_array_item;

    public function __construct(){

        $this->debug = false;

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();

        require_once('AlgorithmsIO/classes/S3Usage.php');
        $this->s3 = new S3Usage();
    
        require_once('AlgorithmsIO/classes/DataSources.php');
        $this->datasources = new DataSources();

        require_once('AlgorithmsIO/classes/Mapping.php');
        $this->mapping = new Mapping();

        require_once('AlgorithmsIO/classes/CSVFiles.php');
        $this->csvFiles = new CSVFiles();

        // Setting up initial arbitrary ids that will be used
        $this->arbitrary_id_item = 1;
        $this->arbitrary_id_user = 1;

        // Setting up the array to hold the arbitrary IDs
        $this->mapping_array_user = array();
        $this->mapping_array_item = array();
    }
    public function setDebugTrue(){

        $this->debug = true;
    }
    public function createXToIdMapping( $request_vars ){
        // Creates the mapping datasource files that Mahout needs to run recommendations

        $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
        $userOwnsFile = json_decode( $this->datasources->userOwnsFile( $request_vars ), true );
        $this->authToken = $request_vars->getParam( 'authToken' );

        $returnArray['outcome'] = 'Failed';

        if( is_numeric( $datasource_id_seq ) && $userOwnsFile['results'] > 0 ){

                // Set the files datasource_id_seq
                $this->csvFiles->setDatasourceFile( $datasource_id_seq );
                $this->csvFiles->setAuthToken( $this->authToken );

                // Get the mapping that the user has provided for the user and item fields
                $userFieldMappings = json_decode( $this->mapping->getUserFields( $request_vars ), true );

                // Get the file so it is on the local file system
                $this->csvFiles->getFile();
                
                // Get the header positions of the field_user_id and field_item_id
                $field_user_id_position = $this->csvFiles->getItemHeadPosition( $userFieldMappings[0]['field_user_id'] );
                $field_item_id_position = $this->csvFiles->getItemHeadPosition( $userFieldMappings[0]['field_item_id'] );
                $field_perference_id_position = $this->csvFiles->getItemHeadPosition( $userFieldMappings[0]['field_preference'] );

                if( $field_user_id_position != -1 && $field_item_id_position != -1 ){

                    // Create Output Rec File
                    $recCsvFile = new CSVFiles();
                    $recCsvFile->openFileForWrite();

                    // Remove all entries for this datasource_id_seq in the Temp_XToIdMappingTable
                    $this->removeThisTemp_XToIdMappingTable( $datasource_id_seq );

                    $isEndOfFile = false;
                    $i = 1;
                    while( ! $isEndOfFile ){

                        $data = $this->csvFiles->returnOne();

                        // Set end of file var
                        $isEndOfFile = $data['isLastRow'];

                        if( ! $isEndOfFile && isset( $data['data'] ) ){
                            // Check one more time b/c we have to actually get the row before we know if it is the end

                            //
                            // Check if the User mapping is in the DB, if not then put it in there MySQL
                            //
                            $results_user = $this->getTemp_XToIdMappingTable( $datasource_id_seq, 'user_id', $data['data'][$field_user_id_position] );
                            $Temp_XToIdMapping_id_seq_user = '-1';                        

                            if( count( $results_user ) == 0 ){
                                // Not in DB yet, insert into the DB and get the id_seq number for this item
                                $Temp_XToIdMapping_id_seq_user = $this->putTemp_XToIdMappingTable( $datasource_id_seq, 'user_id', $data['data'][$field_user_id_position] );
                            }else{
                                // Was in MySQL DB, use this value
                                $Temp_XToIdMapping_id_seq_user = $results_user[0]['field_mapped_value'];
                            }

                            //
                            // Check if the Item mapping is in the DB, if not then put it in there MySQL
                            //
                            $results_item = $this->getTemp_XToIdMappingTable( $datasource_id_seq, 'item_id', $data['data'][$field_item_id_position] );
                            $Temp_XToIdMapping_id_seq_item = '-1';

                            if( count( $results_item ) == 0 ){
                                // Not in DB yet, insert into the DB and get the id_seq number for this item
                                $Temp_XToIdMapping_id_seq_item = $this->putTemp_XToIdMappingTable( $datasource_id_seq, 'item_id', $data['data'][$field_item_id_position] );
                            }else{
                                // Was in MySQL DB, use this value
                                $Temp_XToIdMapping_id_seq_item = $results_item[0]['field_mapped_value'];
                            }

                            //
                            // Get the Perference Field if any
                            //
                            $perference_field_value = -1;

                            if( $field_perference_id_position != -1 ){
                                // User has set a perference field, get it out of the current line

                                $perference_field_value = $data['data'][$field_perference_id_position];
                            }

                            //
                            // Write Current row to the rec file
                            //
                            $csvString = $Temp_XToIdMapping_id_seq_user.','.$Temp_XToIdMapping_id_seq_item.','.$perference_field_value."\n";
                            $recCsvFile->writeContent( $csvString );

                            /////////////////////
                            $i++;

                            //if( $i == 500 )
                            //    break;
                            //echo 'echo '. $i .' >> /mnt/out.txt <br/>';
                            //system ( 'echo "'. $i .'" - '.date( 'g:i:s' ).' >> /mnt/out.txt' );

                        }
                        //echo $i.': '.date('Y-m-d i:s:u').'<br/>';
                    }

                    // Put the mapping into the DB
                    $this->mapping->addMappingNameToId( 'user_id', $datasource_id_seq, $this->mapping_array_user );
                    $this->mapping->addMappingNameToId( 'item_id', $datasource_id_seq, $this->mapping_array_item );

                    // Upload Rec File to S3
                    $newTempFileName = $recCsvFile->getTempWriteDir().'rec_'.$datasource_id_seq;
                    $didCopy = copy( $recCsvFile->getFileWritePathName(), $newTempFileName );
                    if( $didCopy )
                        $this->s3->upload( $newTempFileName );

                    // Remove Temp File
                    unlink( $newTempFileName );

                    // Close out $recCsvFile
                    $recCsvFile->cleanUp();

                    $returnArray['TotalRows'] = $i;
                    $returnArray['outcome'] = 'Success';
                }else{
                    $returnArray['outcome'] = 'Failed';
                    $returnArray['message'] = 'No headers found';
                }

            // Clean up the CSVFile
            $this->csvFiles->cleanUp(); 
        }

        return json_encode( $returnArray );
    }
    public function getTemp_XToIdMappingTable( $datasource_id_seq, $type, $value ){
        // Gets an item from this temp DB

        // Change blank values to 'none'
        if( $value == '' )
            $value = 'none';
/*
        // Create request vars to pass into the mapping function
        $request_vars = new Zend_Controller_Request_Http(); 
        $data['authToken'] = $this->authToken;
        $data['datasource_id_seq'] = $datasource_id_seq;
        $data['field_original_name'] = $value;
        $request_vars->setParams( $data );

        $results = $this->mapping->getArbitraryFieldMappedValueByOriginalName( $request_vars );
*/
        $returnVal = array();
        
        if( $type == 'user_id' ){

            $key = array_search( $value, $this->mapping_array_user );

            if( $key != false )
                $returnVal[0]['field_mapped_value'] = $key;
        }
        if( $type == 'item_id' ){                                                                                                                               

            $key = array_search( $value, $this->mapping_array_item );

            if( $key != false )
                $returnVal[0]['field_mapped_value'] = $key;
        }

        return $returnVal;
    }
    public function putTemp_XToIdMappingTable($datasource_id_seq, $type, $value ){
        // Puts an item into this temp DB

        $arbitrary_id = -1;

        // Change blank values to 'none'
        if( $value == '' )
            $value = 'none';

/*        if( $type == 'user_id' )
            $arbitrary_id = $this->arbitrary_id_user;
        elseif( $type == 'item_id' )
            $arbitrary_id = $this->arbitrary_id_item;
*/
        //$this->mapping->addOneMappingNameToId( $type, $datasource_id_seq, $arbitrary_id, $value ); 

        // Put value into the mapping array
        if( $type == 'user_id' )
            $this->mapping_array_user[$this->arbitrary_id_user] = $value;
        elseif( $type == 'item_id' )
            $this->mapping_array_item[$this->arbitrary_id_item] = $value;

        // Increment the ID used
        if( $type == 'user_id' )
            $this->arbitrary_id_user++;
        elseif( $type == 'item_id' )
            $this->arbitrary_id_item++;

        return $arbitrary_id;
    }
    public function removeThisTemp_XToIdMappingTable( $datasource_id_seq ){
        // Removes all entries for this datasource_id_seq in the mapping table

        // Create request vars to pass into the mapping function
        $request_vars = new Zend_Controller_Request_Http();
        $data['authToken'] = $this->authToken;
        $data['datasource_id_seq'] = $datasource_id_seq;
        $request_vars->setParams( $data );

        $results = $this->mapping->deleteMapping( $request_vars );

        return $results;
    }
}
