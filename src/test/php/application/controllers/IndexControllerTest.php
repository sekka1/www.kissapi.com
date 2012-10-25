<?php

require_once APPLICATION_PATH . '/controllers/IndexController.php';

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
        parent::setUp();
    }
    public function tearDown()
    {

    }
    public function testGetIndex2Action(){

        $request = $this->getRequest();

        $this->dispatch('/index/index2');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('index');
        $this->assertAction('index2');
        //$this->assertQuery('.layout');

        // Get the body of the response
        $body = $response->getBody();
        $this->assertTrue( preg_match( '/<title>Algorithms<\/title>/', $body ) >= 1 ? true : false );

    }
}
