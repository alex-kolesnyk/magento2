<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Backend Auth model
 *
 * @category    Mage
 * @package     Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Auth
{
    /**
     * @var Mage_Backend_Model_Auth_StorageInterface
     */
    protected $_authStorage = null;

    /**
     * @var Mage_Backend_Model_Auth_Credential_StorageInterface
     */
    protected $_credentialStorage = null;

    /**
     * Set auth storage if it is instance of Mage_Backend_Model_Auth_StorageInterface
     *
     * @param Mage_Backend_Model_Auth_StorageInterface $storage
     * @return Mage_Backend_Model_Auth
     * @throw Mage_Backend_Model_Auth_Exception if $storage is not correct
     */
    public function setAuthStorage($storage)
    {
        if (!($storage instanceof Mage_Backend_Model_Auth_StorageInterface)) {
            self::throwException('Authentication storage is incorrect.');
        }
        $this->_authStorage = $storage;
        return $this;
    }

    /**
     * Return auth storage.
     * If auth storage was not defined outside - returns default object of auth storage
     *
     * @return Mage_Backend_Model_Auth_StorageInterface
     */
    public function getAuthStorage()
    {
        if (is_null($this->_authStorage)) {
            $this->_authStorage = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        }
        return $this->_authStorage;
    }

    /**
     * Return current (successfully authenticated) user,
     * an instance of Mage_Backend_Model_Auth_Credential_StorageInterface
     *
     * @return Mage_Backend_Model_Auth_Credential_StorageInterface
     */
    public function getUser()
    {
        return $this->getAuthStorage()->getUser();
    }

    /**
     * Initialize credential storage from configuration
     *
     * @return void
     * @throw Mage_Backend_Model_Auth_Exception if credential storage absent or has not correct configuration
     */
    protected function _initCredentialStorage()
    {
        $areaConfig = Mage::getConfig()->getAreaConfig(Mage::helper('Mage_Backend_Helper_Data')->getAreaCode());
        $storage = Mage::getModel($areaConfig['auth']['credential_storage']);

        if ($storage instanceof Mage_Backend_Model_Auth_Credential_StorageInterface) {
            $this->_credentialStorage = $storage;
            return;
        }

        self::throwException(
            Mage::helper('Mage_Backend_Helper_Data')->__('There are no authentication credential storage.')
        );
    }

    /**
     * Return credential storage object
     *
     * @return null | Mage_Backend_Model_Auth_Credential_StorageInterface
     */
    public function getCredentialStorage()
    {
        if (is_null($this->_credentialStorage)) {
            $this->_initCredentialStorage();
        }
        return $this->_credentialStorage;
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws Mage_Backend_Model_Auth_Exception if login process was unsuccessful
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid User Name or Password.'));
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->login($username, $password);
            if ($this->getCredentialStorage()->getId()) {

                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();

                Mage::dispatchEvent('backend_auth_user_login_success', array('user' => $this->getCredentialStorage()));
            }

            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid User Name or Password.'));
            }

        } catch (Mage_Backend_Model_Auth_Plugin_Exception $e) {
            Mage::dispatchEvent('backend_auth_user_login_failed', array('user_name' => $username, 'exception' => $e));
            throw $e;
        } catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('backend_auth_user_login_failed', array('user_name' => $username, 'exception' => $e));
            self::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid User Name or Password.'));
        }
    }

    /**
     * Perform logout process
     *
     * @return void
     */
    public function logout()
    {
        $this->getAuthStorage()->processLogout();
        Mage::dispatchEvent('admin_session_user_logout');
    }

    /**
     * Check if current user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getAuthStorage()->isLoggedIn();
    }

    /**
     * Throws specific Backend Authentication Exception
     *
     * @static
     * @param string $msg
     * @param string $code
     * @throws Mage_Backend_Model_Auth_Exception
     */
    public static function throwException($msg = null, $code = null)
    {
        if (is_null($msg)) {
            $msg = Mage::helper('Mage_Backend_Helper_Data')->__('Authentication error occurred.');
        }
        throw new Mage_Backend_Model_Auth_Exception($msg, $code);
    }
}
