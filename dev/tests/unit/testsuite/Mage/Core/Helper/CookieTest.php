<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Helper_CookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Helper_Cookie
     */
    protected $_object = null;

    protected function setUp()
    {
        $this->_object = new Mage_Core_Helper_Cookie(
            array(
                'current_store' => $this->_getStoreStub(),
                'cookie_model' => $this->_getCookieStub(array(1 => 1)),
                'website' => $this->_getWebsiteStub(),
            )
        );
    }

    public function testIsUserNotAllowSaveCookie()
    {
        $this->assertFalse($this->_object->isUserNotAllowSaveCookie());
        $this->_object = new Mage_Core_Helper_Cookie(
            array(
                'current_store' => $this->_getStoreStub(),
                'cookie_model' => $this->_getCookieStub(array()),
                'website' => $this->_getWebsiteStub(),
            )
        );
        $this->assertTrue($this->_object->isUserNotAllowSaveCookie());
    }

    public function testGetAcceptedSaveCookiesWebsiteIds()
    {
        $this->assertEquals(
            $this->_object->getAcceptedSaveCookiesWebsiteIds(),
            json_encode(array(1 => 1))
        );
    }

    public function testGetCookieRestrictionLifetime()
    {
        $storeStub = $this->_getStoreStub();
        $storeStub->expects($this->once())
            ->method('getConfig')
            ->will($this->returnCallback('Mage_Core_Helper_CookieTest::getConfigMethodStub'))
            ->with($this->equalTo('web/cookie/cookie_restriction_lifetime'));
        $this->_object = new Mage_Core_Helper_Cookie(
            array(
                'current_store' => $storeStub,
                'cookie_model' => $this->_getCookieStub(array(1 => 1)),
                'website' => $this->_getWebsiteStub()
            )
        );
        $this->assertEquals($this->_object->getCookieRestrictionLifetime(), 60*60*24*365);
    }

    /**
     * Create store stub
     * @return Mage_Core_Model_Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMock('Mage_Core_Model_Store', array('getConfig'), array(), '', false);

        $store->expects($this->any())
            ->method('getConfig')
            ->will($this->returnCallback('Mage_Core_Helper_CookieTest::getConfigMethodStub'));

        return $store;
    }

    /**
     * Create cookie model stub
     * @param array $cookieString
     * @return Mage_Core_Model_Cookie
     */
    protected function _getCookieStub($cookieString = array())
    {
        $cookie = $this->getMock('Mage_Core_Model_Cookie', array('get'), array(), '', false);

        $cookie->expects($this->any())
            ->method('get')
            ->will($this->returnValue(json_encode($cookieString)));

        return $cookie;
    }

    /**
     * Create Website Stub
     * @return Mage_Core_Model_Website
     */
    protected function _getWebsiteStub()
    {
        $website = $this->getMock('Mage_Core_Model_Website', array('getId'), array(), '', false);

        $website->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $website;
    }

    /**
     * Mock get config method
     * @static
     * @param string $hashName
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getConfigMethodStub($hashName)
    {

        $defaultConfig = array(
            'web/cookie/cookie_restriction' => 1,
            'web/cookie/cookie_restriction_lifetime' => 60*60*24*365,
        );

        if (array_key_exists($hashName, $defaultConfig)) {
            return $defaultConfig[$hashName];
        }

        throw new InvalidArgumentException('Unknow id = ' . $hashName);
    }
}
