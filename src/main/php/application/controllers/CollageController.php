<?php

class CollageController extends Zend_Controller_Action
{
    private $facebook;
    private $HPCloud_collage_location = 'https://region-a.geo-1.objects.hpcloudsvc.com:443/v1/41738351831371/collage/';
    private $local_file_location = '/var/www/www.kissapi.com/src/main/php/public/collages_photos/';
    private $local_pic_url;

    public function init()
    {
        /* Initialize action controller here */
        require_once('Facebook/src/facebook.php');

        $config = array();
        $config['appId'] = '218727824866208';
        $config['secret'] = 'efd5eb4e6c765ffb857680132e0e7334';
        $config['fileUpload'] = false; // optional

        $this->facebook = new Facebook($config);

	$this->local_pic_url = 'http://'.$_SERVER['HTTP_HOST'] . '/collages_photos/';
    }
    public function preDispatch(){

        try{
            $me = $this->facebook->api('/me');

            if($me){
                // User is logged in already
            }
        }catch(FacebookApiException $e){
            // User is not logged in
            $this->_helper->redirector('index', 'index');
        }
    }
    public function __call($method, $args)
    {

    }
    public function indexAction()
    {

    }
    public function photosAction()
    {
	// Combine Various Albums into one array
        $this->view->AllPhotos = array();
        $this->view->AllPhotos['data'] = array();

	// Photo's user is tagged in
        $response = $this->facebook->api('/me/photos');
        $this->view->mePhotos = $response;
	$this->addPhotosToOutputArray($response);

	$allAlbums = $this->getAllFBPhotoAlbums();
//print_r($allAlbums);

	// Get all Album pictures and put it into the output array
	$allAlbumsIDs = $this->getAllAlbumIDs($allAlbums['data']);

	foreach($allAlbumsIDs as $anAlbum){
		$response = $this->facebook->api('/'.$anAlbum.'/photos');
		$this->addPhotosToOutputArray($response);
	}

/*
	// Get TimeLine Album
	$anAlbum = $this->getAlbumByName($allAlbums, 'Timeline Photos');	
	if( count($anAlbum) > 0 ){
		// Get this albums content
		$response = $this->facebook->api('/'.$anAlbum['id'].'/photos');
		$this->addPhotosToOutputArray($response);
	}

	// Get Mobile Uploads album
	$anAlbum = $this->getAlbumByName($allAlbums, 'Mobile Uploads');      
        if( count($anAlbum) > 0 ){
                // Get this albums content
                $response = $this->facebook->api('/'.$anAlbum['id'].'/photos');
		$this->addPhotosToOutputArray($response);
        }
*/
//print_r( $this->view->AllPhotos);

    }
    public function generateAction(){

        $selectedPics = $this->_request->getParam( 'pic' );

        if(count($selectedPics) > 0 ){

            // call some stuff from algorithms.io to create the collage
            include_once('AlgorithmsIO/classes/Utilities.php');
            $utilities = new Utilities();

            // Make call to get HP auth
            $url = 'http://v1.api.algorithms.io/jobs';
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

		    if( isset( $responseArray['collage'] ) ){
			if( isset( $responseArray['collage']['id_pic'] ) ){
	                    	$this->view->collagePicture = $this->local_pic_url.$responseArray['collage']['id_pic'];
				$this->view->didGenerate = true;

				// Save File locally
				$picture = file_get_contents($this->HPCloud_collage_location.$responseArray['collage']['id_pic']);
				$fp = fopen($this->local_file_location.$responseArray['collage']['id_pic'], 'w+');
				fwrite($fp, $picture);
				fclose($fp);
			}else{
				$this->view->didGenerate = false;
			}
		    }
                }

            }else{
                return '{"result":"error response was not a valid json"}';
            }
        }else{
            // Set a default pic
            $this->view->collagePicture = 'b2a5f67bfb84f961c487d06783885c6b92a24fa3';
        }
    }
    private function getAllFBPhotoAlbums(){

	$response = $this->facebook->api('/me/albums');

	return $response;
    }
    private function getAlbumByName($albumArray, $albumName){

	$foundAlbumArray = array();

	foreach( $albumArray['data'] as $anAlbum ){
		if( $anAlbum['name'] == $albumName )
			$foundAlbumArray = $anAlbum;
	}
	return $foundAlbumArray;
    }
    private function getAllAlbumIDs($albumArray){

	$albumIDs = array();
	
	foreach($albumArray as $anAlbum){
		array_push($albumIDs, $anAlbum['id']);
	}
	return $albumIDs;
    }
    private function addPhotosToOutputArray($fbPhotoData){
	
	foreach( $fbPhotoData['data'] as $aPic){
		array_push($this->view->AllPhotos['data'], $aPic);
	}
    }
}
