<?php

class CollageController extends Zend_Controller_Action
{
    private $facebook;

    public function init()
    {
        /* Initialize action controller here */
        require_once('Facebook/src/facebook.php');

        $config = array();
        $config['appId'] = '218727824866208';
        $config['secret'] = 'efd5eb4e6c765ffb857680132e0e7334';
        $config['fileUpload'] = false; // optional

        $this->facebook = new Facebook($config);
    }
    public function preDispatch(){

        // If user dont have a session forward them to the home page
        $user_id = $this->facebook->getUser();
    
        if($user_id == 0)
            $this->_helper->redirector('index', 'index');
    }
    public function __call($method, $args)
    {

    }
    public function indexAction()
    {

    }
    public function photosAction()
    {
//        $access_token = $facebook->getAccessToken();
        //$facebook->setAccessToken($access_token);
//        echo $access_token.'<br/>';

//        $user_id = $facebook->getUser();
//        echo $user_id.'<br/>';

        $response = $this->facebook->api('/me/photos');        
//     print_r($response);
        $this->view->mePhotos = $response;
    }
    public function generateAction(){

        $selectedPics = $this->_request->getParam( 'pic' );
print_r($selectedPics);

        // call some stuff from algorithms.io to create the collage
    }
}
