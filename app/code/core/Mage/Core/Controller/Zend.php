<?php

/**
 * Zend Controller
 *
 * @author Andrey Korolyov <andrey@varien.com>
 *
 */
class Mage_Core_Controller_Zend {
    /**
     * Enter description here...
     *
     * @var Zend_Controller_Front
     */
    private $_front;


    /**
     * Enter description here...
     *
     * @var Zend_Controller_Dispatcher_Standard
     */
    private $_dispatcher;
    
    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    private $_request;

    /**
     * Enter description here...
     *
     * @var Zend_Controller_Router_Rewrite
     */
    private $_router;
    
    private $_defaultModule;

    /**
     * Controller constructor
     *
     */
    public function __construct() 
    {
        $this->_front  = Zend_Controller_Front::getInstance();
        $this->_front->throwExceptions(true);
        $this->_front->setParam('useDefaultControllerAlways', true);
        $this->_front->registerPlugin(new Varien_Controller_Plugin_NotFound());
        $this->_view = new Mage_Core_View_Zend();
        $this->_request = new Mage_Core_Controller_Zend_Request();
        $this->_dispatcher = new Varien_Controller_Dispatcher_Standard();
        $this->_front->setDispatcher($this->_dispatcher);
    }
    
    public function loadModule($modInfo)
    {
        if (is_string($modInfo)) {
            $modInfo = Mage::getModuleInfo($modInfo);
        }
        if (!$modInfo instanceof Mage_Core_Module_Info) {
            Mage::exception('Argument suppose to be module name or module info object');
        }
        if (!$modInfo->isFront()) {
            return false;
        }
        
        $name = $modInfo->getName();
        $this->_front->addControllerDirectory($modInfo->getRoot('controllers'), strtolower($name));
        
        
        if (isset($modInfo->getConfig('controller')->default) && true==$modInfo->getConfig('controller')->default) {
            $this->_defaultModule = strtolower($name);
        }
        
        if (strcasecmp($modInfo->getFrontName(), $name)!==0) {
            $routeMatch = $modInfo->getFrontName().'/:controller/:action/*';
            $route = new Zend_Controller_Router_Route($routeMatch, array('module'=>strtolower($name), 'controller'=>'index', 'action'=>'index'));
            $this->_front->getRouter()->addRoute($name, $route);
        }
        
//        if (($class = $modInfo->getSetupClass()) && is_callable(array($class, 'loadFront'))) {
//            $class->loadFront();
//        }
    }
    
    public function getRequest()
    {
        return $this->_request;
    }
    
    public function getFront()
    {
        return $this->_front;
    }

    /**
     * Test
     *
     * @param     none
     * @return	  none
     * @author	  Soroka Dmitriy <dmitriy@varien.com>
     */

    public function test()
    {
/*    	echo( "<PRE>" );
    	print_r( $this->_front->getControllerDirectory() );
    	echo( "</PRE></BR>" );
*/    }

    /**
     * Run controller
     *
     */
    public function run() 
    {
        $default = Mage::getModuleInfo('Mage_Core')->getRoot('controllers');
        $this->_front->addControllerDirectory($default, 'default');

        $this->_dispatcher->setControllerDirectory($this->_front->getControllerDirectory());
        
        if (!empty($this->_defaultModule)) {
            $this->_dispatcher->setDefaultModuleName($this->_defaultModule);
        }
        
        Mage_Core_Event::dispatchEvent('front_initLayout');
        
        $this->_front->dispatch($this->_request);
    }
}