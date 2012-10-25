<?php

error_reporting( E_ALL | E_STRICT );
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
date_default_timezone_set('America/Los_Angeles');

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../main/php/application'));
$filecontents = file_get_contents("../../main/php/public/.htaccess", "r");
if ($_ENV["ZEND_APPLICATION_ENV"]) {
    define('APPLICATION_ENV', $_ENV["ZEND_APPLICATION_ENV"]);    
} else {
    define('APPLICATION_ENV', 'staging');
}
define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../../main/php/library'));
define('TESTS_PATH', realpath(dirname(__FILE__)));

$_SERVER['SERVER_NAME'] = 'http://pod1.staging.www.algorithms.io';

$includePaths = array(LIBRARY_PATH, get_include_path());
set_include_path(implode(PATH_SEPARATOR, $includePaths));

require_once "Zend/Loader.php";
Zend_Loader::registerAutoload();

// This is all the includes for running an Amazon SWF job.  Since the file and the class names are not the same the Zend autoloader is
// having a hard time finding the files
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/credentials.class.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/sdk.class.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/utilities.class.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/credential.class.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/services/swf.class.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/authentication/signer.abstract.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/authentication/signable.interface.php';
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/authentication/signature_v3json.class.php';                                                                              
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/lib/requestcore/requestcore.class.php';                                                                                  
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/request.class.php';                                                                                            
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/response.class.php';                                                                                           
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/json.class.php';                                                                                               
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/lib/dom/ArrayToDOMDocument.php';                                                                                         
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/simplexml.class.php';                                                                                          
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/array.class.php';                                                                                              
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/services/emr.class.php';                                                                                                 
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/services/s3.class.php';                                                                                                  
require_once APPLICATION_PATH . '/../library/AlgorithmsIO/classes/Amazon_SDK/sdk-1.5.3/utilities/mimetypes.class.php'; 

Zend_Session::$_unitTestEnabled = true;
Zend_Session::start();

