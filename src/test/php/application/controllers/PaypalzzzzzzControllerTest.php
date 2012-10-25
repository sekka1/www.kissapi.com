<?php

require_once APPLICATION_PATH . '/controllers/PaypalzzzzzzController.php';

class PaypalzzzzzzControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private $internalPaypalAuthKey;

    public function setUp()
    {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
            
        $this->internalPaypalAuthKey = 'ddkeick98347fkwpemcje02jeeucmeDuw3932mw';

        parent::setUp();
    }
    public function tearDown()
    {

    }
/*
* This funcitonality has been moved to the API instead

    public function testInsertFreeCreditAction(){

        $this->request->setMethod('GET')
            ->setPOST(array(
                    'key' => $this->internalPaypalAuthKey,
                    'user_id' => '38',      // THis is the user with auth token: c1a77f12caa5b03ee5654838f1741be0
                ));

        $this->dispatch('/Paypalzzzzzz/get');

        // Get response from the dispatched path
        $response = $this->getResponse();

        $this->assertController('Paypalzzzzzz');
        $this->assertAction('get');

        // Get the body of the response
        $body = $response->getBody();
        $this->assertRegExp( '/^Not implemented 2/', $body, $body );

    }
*/
}
