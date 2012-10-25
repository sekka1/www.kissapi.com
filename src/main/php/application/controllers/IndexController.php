<?php

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    public function preDispatch(){

    }
    public function __call($method, $args)
    {

    }
    public function indexAction()
    {

    }
    public function channelAction()
    {
        $this->_helper->layout()->disableLayout();
        // Channel file for FB.  It addresses some cross domain issues
    }
}
