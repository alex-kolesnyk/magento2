<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Model_Session_Abstract extends Mage_Core_Model_Session_Abstract_Varien
{
	public function init($namespace)
	{
		parent::init($namespace);
		$hostArr = explode(':', $_SERVER['HTTP_HOST']);
		$this->addHost($hostArr[0]);
		return $this;
	}
	
    public function isValidForHost($host)
    {
    	$hostArr = explode(':', $host);
    	$hosts = $this->getSessionHosts();
    	return (!empty($hosts[$host[0]]));
    }
    
    public function addHost($host)
    {
    	$hostArr = explode(':', $host);
    	$hosts = $this->getSessionHosts();
    	$hosts[$hostArr[0]] = true;
    	$this->setSessionHosts($hosts);
    	return $this;
    }
    
    public function getCookieDomain()
    {
    	$domain = Mage::getStoreConfig('web/cookie/cookie_domain');
    	if (empty($domain)) {
    		$domainArr = explode(':', $_SERVER['HTTP_HOST']);
    		$domain = $domainArr[0];
    	}
    	return $domain;
    }

    public function getCookiePath()
    {
    	$path = Mage::getStoreConfig('web/cookie/cookie_path');
    	if (empty($path)) {
    		$path = '/';
    	}
    	return $path;
    }
    
    public function getCookieLifetime()
    {
    	$lifetime = Mage::getStoreConfig('web/cookie/cookie_lifetime');
    	if (empty($lifetime)) {
    		$lifetime = 60*60*3;
    	}
    	return $lifetime;
    }
    

    /**
     * Retrieve messages from session
     *
     * @param   bool $clear
     * @return  Mage_Core_Model_Message_Collection
     */
    public function getMessages($clear=false)
    {
        if (!$this->getData('messages')) {
            $this->setMessages(Mage::getModel('core/message_collection'));
        }
        
        if ($clear) {
            $messages = clone $this->getData('messages');
            $this->getData('messages')->clear();
            return $messages;
        }
        return $this->getData('messages');
    }
    
    /**
     * Adding new message to message collection
     *
     * @param   Mage_Core_Model_Message_Abstract $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addMessage(Mage_Core_Model_Message_Abstract $message)
    {
        $this->getMessages()->add($message);
        return $this;
    }
    
    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addError($message)
    {
        $this->addMessage(Mage::getSingleton('core/message')->error($message));
        return $this;
    }
    
    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addWarning($message)
    {
        $this->addMessage(Mage::getSingleton('core/message')->warning($message));
        return $this;
    }
    
    /**
     * Adding new nitice message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addNotice($message)
    {
        $this->addMessage(Mage::getSingleton('core/message')->notice($message));
        return $this;
    }
    
    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addSuccess($message)
    {
        $this->addMessage(Mage::getSingleton('core/message')->success($message));
        return $this;
    }
    
    /**
     * Adding messages array to message collection
     *
     * @param   array $messages
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->addMessage($message);
            }
        }
        return $this;
    }
    
}