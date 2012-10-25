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

        // call some stuff from algorithms.io to create the collage
        include_once('AlgorithmsIO/classes/Utilities.php');
        $utilities = new Utilities();

        // Make call to get HP auth
        $url = 'http://pod1.staging.v1.api.algorithms.io/jobs';
        $headers = array('authToken: c1a77f12caa5b03ee5654838f1741be0');
        $data['job']['algorithm']['id'] = "33";
        $data['job']['algorithm']['params']['hp_username'] = 'garland';
        $data['job']['algorithm']['params']['hp_password'] = 'teachMe!';
        $data['job']['algorithm']['params']['hp_tenantID'] = '41738351831371';
        $data['job']['algorithm']['params']['params'] = array();
        $data['job']['outputType'] = 'json';
        $data['job']['method'] = 'sync';
        $data['job']['datasources'] = array();
        $post_params['job_params'] = json_encode( $data );

        $response = $utilities->curlPost($url, $post_params, $headers);
        $responseArray = json_decode($response, true);
//echo $response;
        if(json_last_error() == 0){

            // Make call to generate collage 
            unset( $data['job']['algorithm']['params'] );
            unset( $post_params );

            $data['job']['algorithm']['id'] = "34";
            $data['job']['algorithm']['params']['hp_authToken'] = $responseArray['hp_authToken'];
            $data['job']['algorithm']['params']['hp_template_url'] = 'https://region-a.geo-1.objects.hpcloudsvc.com:443/v1/41738351831371/collage-templates/template-heart.png';
            $data['job']['algorithm']['params']['params'] = $selectedPics;
            $post_params['job_params'] = json_encode( $data );

            $response = $utilities->curlPost($url, $post_params, $headers);
            $responseArray = json_decode($response, true);

            if(json_last_error() == 0){
//print_r($responseArray);
                // Set the colloage png to the view
                $this->view->collagePicture = $responseArray['collage']['id_pic'];
            }

        }else{
            return '{"result":"error response was not a valid json"}';
        }

    }
}
