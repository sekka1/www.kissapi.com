<?php

require_once APPLICATION_PATH . '/controllers/SignupController.php';

class SignupControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private $userToSignUp;
    private $userToSignUpPassword;

    public function setUp()
    {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
            
        $this->userToSignUp = 'testing11@algorithms.io';
        $this->userToSignUpPassword = 'hellothereyou12';

        parent::setUp();
    }
    public function tearDown()
    {

    }
    public function testIndexAction(){

        $this->request->setMethod('GET');

        $this->dispatch('/signup/index');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('signup');
        $this->assertAction('index');

        // Get the body of the response
        $body = $response->getBody();
        $this->assertRegExp( '/Sign up for Algorithms.io/', $body, $body );

    }
    public function testSigningUpAction(){

        $this->request->setMethod('POST')
            ->setPOST(array(
                    'betacode' => 'bunny',
                    'email' => $this->userToSignUp,
                    'password' => $this->userToSignUpPassword
                ));

        $this->dispatch('/signup/index');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('signup');
        $this->assertAction('index');

        // Get the body of the response
        $body = $response->getBody();
        $this->assertRegExp( '/.*Check your email for sign up verification.*/', $body, $body );
    }
    public function testCheckVerificationPageAction(){

        $this->request->setMethod('GET');

        $this->dispatch('/signup/verify/email/unit_test_guy@algorithms.io/id/82c9e5a69cfae9a22d1e7f26dc732177');

        // Get response from the dispatched path
        $response = $this->getResponse();

        // Get the body of the response
        $body = $response->getBody();
        $this->assertRegExp( '/Verfication Successful/', $body, $body );        


    }
}
