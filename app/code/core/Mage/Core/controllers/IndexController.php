<?php

class IndexController extends Mage_Core_Controller_Front_Action {

    function indexAction()
    {
        #$this->_forward('index', 'index', $this->getModuleInfo()->getConfig('controller')->default);
    }
    
    function __call($action, $args) 
    {
//        $request = $this->getRequest();
//        $params = $request->getParams();
    }


}