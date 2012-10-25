<?php

require_once APPLICATION_PATH . '/controllers/LoginController.php';

class LoginControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private $userLogin;
    private $userPassword;

    public function setUp()
    {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
            
        $this->userLogin = 'testing5@algorithms.io';
        $this->userPassword = "icudj38c!3dd''";

        parent::setUp();
    }
    public function tearDown()
    {

    }
    public function testLoginWithAUserAction(){

        $this->request->setMethod('POST')
            ->setPOST(array(
                    'username' => $this->userLogin,
                    'password' => $this->userPassword
                ));

        $this->dispatch('/login/process');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('login');
        $this->assertAction('process');

        // Get the body of the response
        $body = $response->getBody();
        // This will return a blank page

        $this->assertRegExp( '//', $body, $body );
    }
}
