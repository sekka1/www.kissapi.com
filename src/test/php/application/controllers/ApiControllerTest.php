<?php

require_once APPLICATION_PATH . '/controllers/ApiController.php';

class ApiControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private $tokenid;
    private $datasource_id_seq;

    public function setUp()
    {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );

        $this->tokenid = 'c1a77f12caa5b03ee5654838f1741be0';

        // Set file to upload
        $_FILES['theFile']['tmp_name'] = APPLICATION_PATH . '/../../../test/php/Movie_Lens_10k_data.csv';
        $_FILES['theFile']['size'] = '8888';
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';

        parent::setUp();
    }
    public function tearDown()
    {

    }
/*
* This functionality has been moved to the API

    public function testUploadAFileAndDeleteAction(){

        $this->uploadAFile();
        $this->deleteAFile();
    }
*/
    public function uploadAFile(){
        //
        // Upload the file
        //
        $this->request->setMethod('POST')
            ->setPOST(array(
                    'class' => 'DataSources',
                    'method' => 'upload',
                    'authToken' => $this->tokenid,
                    'type' => 'unit_test',
                    'name' => 'unit_test_testUploadAFileAction',
                    'description' => 'a unit test upload file',
                    'version' => '1',
                    'isUnitTest' => '1'
                ));

        $this->dispatch('/api/v1');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('api');
        $this->assertAction('v1');

        // Get the body of the response                                                                                                                    
        $body = $response->getBody();                                                                                                                      
        $this->assertRegExp( '/^\{"api":\{"Authentication":"Success"\},"data":\d+\}/', $body, $body );

        // Parse the returned json to extract the datasource_id_seq
        $result = json_decode( $body, true );
        $this->datasource_id_seq = $result['data'];
    }
    public function deleteAFile(){
        //
        // Delete the File
        //
        $this->request->setMethod('GET')
            ->setPOST(array(
                    'class' => 'DataSources',
                    'method' => 'deleteAFile',
                    'authToken' => $this->tokenid,
                    'datasource_id_seq' => $this->datasource_id_seq,
                ));

        $this->dispatch('/api/v1');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('api');
        $this->assertAction('v1');

        // Get the body of the response                                                                                                                    
        $body = $response->getBody();                                                                                                                      
        $this->assertRegExp( '/^\{"api":\{"Authentication":"Success"\},"data":\d+\}/', $body, $body );
    }
/*
* Removing test
    public function testPrepRecommendationFileAction(){

        $this->uploadAFile();

        $this->request->setMethod('GET')
            ->setPOST(array(
                    'class' => 'PrepRecommendationFile',
                    'method' => 'doPrep',
                    'authToken' => $this->tokenid,
                    'datasource_id_seq' => $this->datasource_id_seq,
                    'field_user_id' => 'user',
                    'field_item_id' => 'item',
                    'field_preference' => 'pref',
                ));

        $this->dispatch('/api/v1');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('api');
        $this->assertAction('v1');

        // Get the body of the response
        $body = $response->getBody();

        $this->assertRegExp( '/\{"api":\{"Authentication":"Success"\},"data":\d+\} 
\{"api":\{"Authentication":"Success"\},"data":\{"field_map_id":"\d+","mapping":\{"outcome":"Success","TotalRows":\d+\}\}\}/', $body, $body );

        $this->deleteAFile();
    }
*/
/* 
*  Taking this test out.  We are no longer going through this server to get recommendations
*


    public function testGetRecommendationSimItemLogLikelihoodNoPrefAction(){

        //$request = $this->getRequest();

        $this->request->setMethod('GET')
            ->setPOST(array(
                    'class' => 'GetRecommendation',
                    'method' => 'simItemLogLikelihoodNoPref',
                    'authToken' => $this->tokenid,
                    'datasource_id_seq' => '2123',
                    'type' => 'item',
                    'item' => 'Die Hard 1988',
                ));

        $this->dispatch('/api/v1');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('api');
        $this->assertAction('v1');

        // Get the body of the response
        $body = $response->getBody();

        $this->assertRegExp( '/^\{"api":\{"Authentication":"Success"\},"data":\{"recommendation"/', $body, $body );
    }
*/
}
