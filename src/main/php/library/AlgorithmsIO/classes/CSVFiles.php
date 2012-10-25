<?php
// Manip CSV Files

class CSVFiles
{
    private $auth;
    private $authToken;
    private $generic;
    private $datasource_id_seq;
    private $debug;
    private $s3;
    private $mapping;
    private $filePathName;
    private $fileHandle;
    private $headers;
    private $currentFilePosition;
    private $isFirstRow;
    private $fileWritePathName;
    private $tempWriteDir;
    private $bucket;
    private $filePathLocation;

    public function __construct(){

        $this->debug = false;

        $this->tempWriteDir = '/tmp/CSVFiles_Dir/';

        // Grab the application.ini params
        $config = new Zend_Config_Ini(
                APPLICATION_PATH . '/configs/application.ini',
                APPLICATION_ENV
                );

        $this->filePathLocation = $config->app->params->amazon->s3TempLocation;
        $this->bucket = $config->app->params->amazon->bucket;

        // Init Auth Class
        require_once('AlgorithmsIO/classes/Auth.php');
        $this->auth = new Auth();

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();
        
        //require_once('AlgorithmsIO/classes/S3Usage.php');
        require_once('AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/sdk.class.php');
        $this->s3 = new AmazonS3();

        require_once('AlgorithmsIO/classes/DataSources.php');
        $this->datasources = new DataSources();

        require_once('AlgorithmsIO/classes/Mapping.php');
        $this->mapping = new Mapping();

        // Setting for the S3.php uses strtotime
        date_default_timezone_set('America/New_York');

        $this->filePathName = '';
        $this->fileWritePathName = '/tmp/CSVFiles_'.md5( mt_rand( 1000, mt_getrandmax() ) . time() );// FIle writing outpue
        $this->headers = array();
        $this->isFirstRow = true;
    }
    public function setDebugTrue(){

        $this->debug = true;
    }
    public function setAuthToken( $authToken ){
        $this->authToken = $authToken;
    }
    public function setDatasourceFile( $datasource_id_seq ){
        $this->datasource_id_seq = $datasource_id_seq;
    }
    public function getFile(){
        // Get the file and place it on the local file system

        $request_vars = new Zend_Controller_Request_Http(); 
        $data['authToken'] = $this->authToken;
        $data['datasource_id_seq'] = $this->datasource_id_seq;
        $request_vars->setParams( $data );

        // File attributes including the S3 name
        $fileAttributes = json_decode( $this->datasources->getAFile( $request_vars ), true );
//        print_r( $fileAttributes );

        // What the user mapped the files for Mahout
        $userFieldMappings = json_decode( $this->mapping->getUserFields( $request_vars ), true ); 
//        print_r( $userFieldMappings );

        // Get the File
        sleep(1);
        $s3Params['fileDownload'] = $this->filePathLocation.'CSVFiles_'.md5( mt_rand( 1000, mt_getrandmax() ) . time() );
        $this->filePathName = $s3Params['fileDownload'];
        $response = $this->s3->get_object( $this->bucket, $fileAttributes[0]['filesystem_name'], $s3Params );

        if( $this->filePathName != '' )
            $this->fileHandle = fopen( $this->filePathName, "rb+" );
    }
    public function cleanUp(){
        // Cleans up this class's stuff

        $this->deleteFile();
        fclose( $this->fileHandle );
    }
    public function deleteFile(){
        // Deletes the file that this class has pulled down from S3

        if( $this->filePathName != '' && file_exists( $this->filePathName ) ){
            unlink( $this->filePathName );
        }

        if( file_exists( $this->fileWritePathName ) )
            unlink( $this->fileWritePathName );
    }
    public function setHeader(){

        //
        // Probably can make this more efficient
        //
        $isFirstRow = true;
    
        while( ( $data = fgetcsv( $this->fileHandle, 1000, "," ) ) !== FALSE ){

            if( $isFirstRow ){
                if( count( $this->headers ) == 0 ){
                    // First time seeing the header.  Save it
                    $this->saveHeaders( $data );
                }
            }
            break;
        }
    }
    public function searchForItemAndReturnRow( $request_vars ){
        // API web enabled search for item function and returns that row
        // $datasource_id_seq, authToken
        // $column = which column you want to search for
        // $item = regular expression of the item
        // $range = All, FirstOccurance

        $returnVar = array();
        $returnVar['matches'] = array();

        $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );

        if( is_numeric( $datasource_id_seq ) ){
        
            $userOwnsFile = json_decode( $this->datasources->userOwnsFile( $request_vars ), true );

            $this->authToken = $request_vars->getParam( 'authToken' );
            $column = $request_vars->getParam( 'column' );

            if( $userOwnsFile['results'] > 0 ){
                // User owns this file, can proceed

                    // Setup this file
                    $this->setDatasourceFile( $datasource_id_seq );
                    $this->setAuthToken( $this->authToken );
                    // Get the file so it is on the local file system
                    $this->getFile();

                    // Get the header positions of the column this user wants to search in
                    $search_column_id = $this->getItemHeadPosition( $column );

                    $returnVar['headers'] = $this->headers;

                    $search_item = $request_vars->getParam( 'item' );

                    if( $search_column_id >= 0 && $search_item != '' ){
                        // Is a valid column.  Look for the item

                        $isEndOfFile = false;

                        // Loop through file
                        while( ! $isEndOfFile ){

                            $data = $this->returnOne();

                            // Set end of file var
                            $isEndOfFile = $data['isLastRow'];
                            $i=0;

                            if( ! $isEndOfFile && isset( $data['data'] ) ){
                                // Look for the item that the user wants
            
                                if( isset( $data['data'][$search_column_id] ) ){

                                    $match_result = preg_match( '/'.$search_item.'/', $data['data'][$search_column_id] );

                                    if( $match_result > 0 ){
                                        // Match found
            
                                        array_push( $returnVar['matches'], $data['data'] );

                                        // Stop looking
                                        break;
                                    }
                                }
                                if( $i == 10 )                                                                      
                                    break;                                                                          
                                $i++;  
                            }

                        }
                    }else{
                        $returnVar['outcome'] = 'Failed';
                        $returnVar['reason'] = 'Column not found';
                    }

                $this->cleanUp();
            }else{
                $returnVar['outcome'] = 'Failed';
                $returnVar['reason'] = 'Unauthorized';
            }
        }
        return json_encode( $returnVar );
    }
    public function saveHeaders( $headerArray ){
        // Saves the header

        $this->headers = $headerArray;
    }
    public function getHeaders(){
        // Returns the header array

        return $this->headers;
    }
    public function getItemHeadPosition( $headerName ){
        // Returns the array position of where a given header is

        $headerPostion = -1;

        // See if something already gotten the headers, if not go save it
        if( count( $this->headers ) == 0 ){
            while( ( $data = fgetcsv( $this->fileHandle, 1000, "," ) ) !== FALSE ){
                $this->saveHeaders( $data );
                break;
            }
        }

        foreach( $this->headers as $key=>$val ){
            if( preg_match( '/'.$headerName.'/', $val ) ){
                $headerPostion = $key;
            }
        }
        return $headerPostion;
    }
    public function returnOne(){

        $resultsArray = array();

        //$isFirstRow = true;
        $didGoInLoop = false;
        $resultsArray['isLastRow'] = 0;

//        while( ( $data = fgetcsv( $this->fileHandle, 1000, "," ) ) !== FALSE ){
         while ( ! feof( $this->fileHandle ) ){

            $line = fgetcsv( $this->fileHandle, 1000, "," );

            $didGoInLoop = true;

            if( $this->isFirstRow ){
                if( count( $this->headers ) == 0 ){
                    // First time seeing the header.  Save it
                    $this->saveHeaders( $line );                                                                          
                }                                                                       
                $this->isFirstRow = false;
            }else{

                // Put data in the return array
                $resultsArray['data'] = $line;
            }
            // Save curent position
            $this->currentFilePosition = ftell( $this->fileHandle );

            // End the loop since we only want one line
            break;
        }

        $resultsArray['currentFilePosition'] = $this->currentFilePosition;

        if( ! $didGoInLoop )
            $resultsArray['isLastRow'] = 1;

        return $resultsArray;
    }
    public function openFileForWrite(){
        // Open a file for writing

        $this->fileHandle = fopen( $this->fileWritePathName, 'w+' );

        // Create Temp write dir for the user of this class to use
        if( ! file_exists( $this->tempWriteDir ) )
            mkdir( $this->tempWriteDir, 0777 );
    }
    public function getFileWritePathName(){
        // Return the full path to the file that the user had insert into

        return $this->fileWritePathName;
    }
    public function getTempWriteDir(){
        // A safe place for the user of this class to write files out to that wont clash with other files

        return $this->tempWriteDir;
    }
    public function writeContent( $content ){
        // Write content to file
        // INPUT: text string

        fwrite( $this->fileHandle, $content );
    }
    public function convertArrayToCSV( $dataArray ){
        // Converts an array to a CVS line
        // INPUT: 1D array

        $csv = '';

        foreach( $dataArray as $anItem ){

            $csv .= $anItem.',';
        }

        // Remove trailing comma
        $csv = preg_replace( '/,$/', '', $csv );

        // insert line return
        $csv .= "\n";

        return $csv;
    }
}

