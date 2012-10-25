<?php
/*
* This class performs actions on S3 datastore
*/

require_once dirname(__FILE__) . '/Amazon_SDK/S3.php';

class S3Usage{

        private $s3;
        private $filePathLocation;
        private $bucket;

	public function __construct( ){

            // Grab the application.ini params
            $config = new Zend_Config_Ini(
                    APPLICATION_PATH . '/configs/application.ini',
                    APPLICATION_ENV
                    );

            $this->filePathLocation = $config->app->params->amazon->s3TempLocation;
            $this->bucket = $config->app->params->amazon->bucket;

            $useSSL = false;

            // Instantiate the class
            $this->s3 = new S3($config->app->params->amazon->awsAccessKey, $config->app->params->amazon->awsSecretKey, $useSSL);
        }
        public function test(){

		// List your buckets:
		echo "S3::listBuckets(): ".print_r($this->s3->listBuckets(), 1)."\n";
	}
	public function upload( $uploadFile ){
		// Input: Full system path to the file

		$uploadedFileName = '';

                if ( $this->s3->putObjectFile($uploadFile, $this->bucket, baseName($uploadFile), S3::ACL_PRIVATE)) {
                    // ACL_PUBLIC_READ

			$uploadedFileName = baseName($uploadFile);
		}

		return $uploadedFileName; 
	}
        public function uploadText( $fileName, $text ){
            // Input: $fileName - a file name
            //          $text - a text string of what you want put onto S3

            // Create file and place onto the file system 
            $tmp_filename = '/tmp/'.md5( mt_rand( 1000, mt_getrandmax() ) . time() );
            $fp = fopen($tmp_filename, 'w');    
            fwrite( $fp, $text );
            fclose( $fp );
        
            // Upload to S3
            $uploadedFileName = '';

            if ( $this->s3->putObjectFile($tmp_filename, $this->bucket, $fileName, S3::ACL_PRIVATE)) {
                // ACL_PUBLIC_READ

                $uploadedFileName = $fileName;
            }

            unlink( $tmp_filename );

            return $uploadedFileName;

        }
	public function getObjectInfo( $uploadFile ){ 
	// Input: Just the file name

		return $this->s3->getObjectInfo( $this->bucket, baseName($uploadFile));
	}
        public function getBucket( $bucketName ){

            $returnVal = array();

            if (($contents = $this->s3->getBucket($bucketName)) !== false) {
                $returnVal = $contents;
            }
            return $returnVal;
        }
        public function getObjectToFileSystem( $uri ){
            // Pull a file from S3 and put it onto the file system

            $fileNamePath = '';

            // Choose a name for the file and check if it exist first
            $tempFileNamePath = $this->filePathLocation.$uri;
            $fileExist = true;
            $i = 0;
            while( $fileExist ){

                if( file_exists( $tempFileNamePath ) )
                    $tempFileNamePath = $this->filePathLocation.$uri.'_'.$i;
                else
                    $fileExist = false;

                $i++;
            }

            // To save it to a file (unbuffered write stream):
            if (($object = S3::getObject( $this->bucket, $uri, $tempFileNamePath)) !== false) {
                //print_r($object);
                //var_dump($object);
                $fileNamePath = $this->filePathLocation.$uri;
            }
            return $fileNamePath;
        }
        public function getTextObjectandReturnContents( $uri ){
            // Gets a text object and returns the contents inside this file

            $returnVal = '';

            // To save it to a file (unbuffered write stream):
            //if (($object = S3::getObject( bucket, $uri, "/tmp/".$uri)) !== false) {
            //    print_r($object);
            //    var_dump($object);
            //}

            $file = '/tmp/'.$uri;

            // To write it to a resource (unbuffered write stream):
            $fp = fopen( $file, "wb" );
            if (($object = $this->s3->getObject($this->bucket, $uri, $fp)) !== false) {
                //print_r($object);
                //var_dump($object);
                
                // open back up this file and read the contents and return it
                $handle = fopen($file, "rb");
                $contents = fread($handle, filesize($file));
                fclose( $handle );

                // Delte this file
                unlink( $file );

                $returnVal = $contents;
            }
            return $returnVal;
        }
        public function deleteObject( $uri ){

            $this->s3->deleteObject( $this->bucket, $uri );            
        }
}

?>
