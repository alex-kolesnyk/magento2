<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Test;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Name of the sample cookie to be used in tests
     */
    const SAMPLE_COOKIE_NAME = 'sample_cookie';

    /**
     * @var \Magento\TestFramework\Cookie
     */
    protected $_model;

    protected function setUp()
    {
        $helperMock = $this->getMockBuilder('Magento\Backend\Helper\DataProxy')
            ->disableOriginalConstructor()
            ->getMock();
        $coreStoreConfig = $this->getMockBuilder('Magento\Core\Model\Store\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new \Magento\TestFramework\Cookie(
            $coreStoreConfig,
            new \Magento\TestFramework\Request($helperMock),
            new \Magento\TestFramework\Response(
                $this->getMock('Magento\Core\Model\Event\Manager', array(), array(), '', false)
            )
        );
    }

    public function testSet()
    {
        $cookieValue = 'some_cookie_value';
        $this->assertFalse($this->_model->get(self::SAMPLE_COOKIE_NAME));
        $this->_model->set(self::SAMPLE_COOKIE_NAME, $cookieValue);
        $this->assertEquals($cookieValue, $this->_model->get(self::SAMPLE_COOKIE_NAME));
        $this->assertEquals($cookieValue, $_COOKIE[self::SAMPLE_COOKIE_NAME]);
    }

    public function testDelete()
    {
        $this->_model->set(self::SAMPLE_COOKIE_NAME, 'some_value');
        $this->_model->delete(self::SAMPLE_COOKIE_NAME);
        $this->assertFalse($this->_model->get(self::SAMPLE_COOKIE_NAME));
        $this->assertArrayNotHasKey(self::SAMPLE_COOKIE_NAME, $_COOKIE);
    }
}
