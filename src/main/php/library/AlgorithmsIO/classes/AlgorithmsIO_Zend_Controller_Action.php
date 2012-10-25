<?php
namespace AlgorithmsIO;
/**
 * Description of AlgorithmsIO_Zend_Rest_Controller
 *
 * @author mark
 */
abstract class AlgorithmsIO_Zend_Controller_Action extends \Zend_Controller_Action {

    const INVALID_AUTH_TOKEN=-1;
    public $debug = false; //true;
    public $username;
    public $user_id_seq;
    
    public function init()
    {
        $this->debug("DEBUG201208031240: In AlgorithmsIO_Zend_Controller_Action->init()");
        
        $this->getResponse()
                ->setHeader("Access-Control-Allow-Origin", "*"); //FIXME - Probably don't need this CORS on the webserver
        //$this->_helper->viewRenderer->setNoRender(true);
    }

    public function preDispatch(){
        $this->debug("DEBUG201208031241: In AlgorithmsIO_Zend_Controller_Action->preDispatch()");
        // Authentication Piece
        $this->auth = \Zend_Auth::getInstance();
    
        \Zend_Loader::loadClass('Users');
        $user = new \Users();

        if(!$this->auth->hasIdentity()){
                 $this->debug("DEBUG201207220849: Authentication failed");
                 $this->_redirect( '/login?f=' . $this->_request->getRequestUri() );
		 return;
        } else {
                // User is valid and logged in
                $this->debug("DEBUG201207220850: Authentication Successful");
                $this->username = $this->auth->getIdentity();
                $this->debug("DEBUG201207220851: After auth->getIdentity()");
                $this->user_id_seq = $user->getUserIdByEmailAddress( $this->username );
		$this->debug("DEBUG201206051454: IN preDispatch()");

		
        }

        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->conf = $bootstrap->getOption('app');
        //$this->fileUploadUrl = $this->conf['params']['url']['fileUploadUrl'];
        
	\Zend_Loader::loadClass('AuthToken');
        $this->authToken = new \AuthToken();
	$this->debug("DEBUG201207031352: user_id_seq=".$this->user_id_seq);
        $this->usersAuthTokens = $this->authToken->retrieveUsersAuthTokens( $this->user_id_seq );
        $this->_tokenid = $this->usersAuthTokens[0]['token'];

	require_once("AlgorithmsIO/SDK/PHP/AlgorithmsIO.class.php");
	require_once("AlgorithmsIO/SDK/PHP/Security.class.php");
        $AIO_Config = \AlgorithmsIO\Config::getInstance();
        $AIO_Config->set($this->conf['params']['AlgorithmsIO']['SDK']);
        //$this->debug("GGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGG".print_r($this->conf['params']['AlgorithmsIO']['SDK']),true);
        
	$this->authObj = new \AlgorithmsIO\Authentication(array(
		"authToken"		=>$this->usersAuthTokens[0]['token'],
));

	$this->security = new \AlgorithmsIO\Security(array(
		"authObj"		=>$this->authObj,
	));
        $this->setViewDefaults();
     }

    public function setViewDefaults() {
        $this->view->params = $this->_request->getParams();
        $this->view->API_URL = $this->conf['params']['AlgorithmsIO']['API']['url'];
        $this->view->user = $this->user();
        $this->view->token = $this->_tokenid;
        $this->view->username = $this->username;
        $this->view->usersAuthTokens = $this->usersAuthTokens;
	$this->view->authentication = $this->authObj;
	$this->view->security = $this->security;
	$path = realpath(APPLICATION_PATH.'/../library/AlgorithmsIO/');
	$this->view->localization = simplexml_load_file($path."/localization/en.xml");        
    } 
     
    public function authToken($newToken=null) {
            $this->debug("DEBUG201208161000: ***********************************************");
        if(isset($newToken)) {
            // TODO: MRR20120717 Should make this check to see if an AuthToken class was passed in, or an ID (make it get the class)
            $this->debug("DEBUG201207171937: Setting authToken by passed in argument");
            $this->_authToken = $newToken;
        } elseif (isset($this->_authToken)) {
            return $this->_authToken;
        } else {
            // Grab the Auth Token from the header
            $tokenid = $this->_tokenid ?: $this->_request->getHeader('authToken');
            if(!$tokenid) {
                $tokenid = $this->_getParam("authToken");
            }
            // Get an AuthToken Entity
            require_once('AlgorithmsIO/Entity/AuthTokens.php');
            $entityManager = $this->entityManager();
            $t = $entityManager->getRepository('AlgorithmsIO\Entity\AuthTokens')->findOneBy(array('token'=>$tokenid));    
            //$this->debug(print_r($t,true));
            //$this->debug("*********************************************** ID=".$t->get_id()."  ID=".$t->get_id());
            $this->_authToken = $t;
        }
        
        if(isset($this->_authToken)) {
            return $this->_authToken;
        } else { 
            return INVALID_AUTH_TOKEN; 
        }
    }
    
    public function user($newUser=null) {

        if(isset($newUser)) {
            $this->_user = $newUser;
        }

        if(!isset($this->_user)) {            
            $authToken = $this->authToken();
            if(get_class($authToken) == "AlgorithmsIO\\Entity\\AuthTokens") {
                $user = $authToken->get_user();
                if(isset($user)) {
                    $this->_user = $user;
                    return $this->_user;
                } else {
                    unset($this->_user);
                    $this->warning("WARNING201207161423: Could not find user with authToken=".$authToken->get_id());
                }
            } else {
                $this->error("ERROR201207161420: Cannot find the user as I have no authToken");
            }
        }

        return $this->_user;
    }
    
    public function entityManager($newEntityManager=null) {
        if (isset($newEntityManager)) {
            $this->entityManager = $newEntityManager;
        }
        
        if (isset($this->entityManger)) {
            return $this->entityManager;
        }
        
        $doctrine = \Zend_Registry::get("doctrine");
        return ($this->entityManager = $doctrine->getEntityManager());
    }

    public function __call($method, $args)
    {
	$this->error("ERROR201206051447: $method is not defined in ".get_Class($this));
        if ('Action' == substr($method, -6)) {
            // If the action method was not found, render the error
            // template
            return $this->render('error');

	    // Forward to another page
            //return $this->_forward('index');
        }

        // all other methods throw an exception
        throw new Exception('Invalid method "'
                            . $method
                            . '" called',
                                500);

    }
    
    public function _debug_to_string($argarray = array()) {
            //$argarray = func_get_args();
            if(count($argarray)>0) {
                return vsprintf(array_shift($argarray), $argarray);
            } else {
                error_log("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!".count($argarray));
                return $argarray[0];
            }  
    }    
    public function debug($msg) {
            if($this->debug) {
                    error_log($this->_debug_to_string(func_get_args()));
            }
    }
    public function warning($msg) {
        error_log($this->_debug_to_string(func_get_args()));
    }
    public function error($msg) {
            $msg .= " *** Backtrace: "; //.print_r(debug_backtrace(),true);
            //ob_start();
            debug_print_backtrace();
            error_log(ob_get_clean());
            error_log($msg);
            if($this->debug) {
                    echo $msg;
            }
    }
    
    
}

?>
