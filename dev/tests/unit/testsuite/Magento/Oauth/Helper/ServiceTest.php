<?php
/**
 * Test WebAPI authentication helper.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Oauth\Helper;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Core\Helper\Data */
    protected $_coreHelper;

    /** @var \Magento\Core\Helper\Context */
    protected $_coreContextMock;

    /** @var \Magento\Core\Model\Store\Config */
    protected $_storeConfigMock;

    /** @var \Magento\Oauth\Helper\Service */
    protected $_oauthHelper;

    protected function setUp()
    {
        $this->_coreContextMock = $this->getMockBuilder('Magento\Core\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeConfigMock = $this->getMockBuilder('Magento\Core\Model\Store\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_encryptorMock = $this->getMockBuilder('Magento\Core\Model\Encryption')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_coreHelper = new \Magento\Core\Helper\Data(
            $this->_coreContextMock,
            $this->getMockBuilder('Magento\Core\Model\Event\Manager')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Magento\Core\Helper\Http')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Magento\Core\Model\Config')->disableOriginalConstructor()->getMock(),
            $this->_storeConfigMock,
            $this->getMockBuilder('Magento\Core\Model\StoreManager')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Magento\Core\Model\Locale')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Magento\Core\Model\Date')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Magento\Core\Model\App\State')->disableOriginalConstructor()->getMock(),
            $this->_encryptorMock
        );

        $this->_oauthHelper = new \Magento\Oauth\Helper\Service(
            $this->_coreHelper,
            $this->_coreContextMock,
            $this->_storeConfigMock
        );
    }

    protected function tearDown()
    {
        unset($this->_coreHelper);
        unset($this->_coreContextMock);
        unset($this->_storeConfigMock);
        unset($this->_oauthHelper);
    }

    public function testGenerateToken()
    {
        $token = $this->_oauthHelper->generateToken();
        $this->assertTrue(is_string($token) && strlen($token) === \Magento\Oauth\Model\Token::LENGTH_TOKEN);
    }

    public function testGenerateTokenSecret()
    {
        $token = $this->_oauthHelper->generateTokenSecret();
        $this->assertTrue(is_string($token) && strlen($token) === \Magento\Oauth\Model\Token::LENGTH_SECRET);
    }

    public function testGenerateVerifier()
    {
        $token = $this->_oauthHelper->generateVerifier();
        $this->assertTrue(is_string($token) && strlen($token) === \Magento\Oauth\Model\Token::LENGTH_VERIFIER);
    }

    public function testGenerateConsumerKey()
    {
        $token = $this->_oauthHelper->generateConsumerKey();
        $this->assertTrue(is_string($token) && strlen($token) === \Magento\Oauth\Model\Consumer::KEY_LENGTH);
    }

    public function testGenerateConsumerSecret()
    {
        $token = $this->_oauthHelper->generateConsumerSecret();
        $this->assertTrue(is_string($token) && strlen($token) === \Magento\Oauth\Model\Consumer::SECRET_LENGTH);
    }

    public function testIsCleanupProbabilityZero()
    {
        $this->_storeConfigMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue(0));
        $this->assertFalse($this->_oauthHelper->isCleanupProbability());
    }

    public function testIsCleanupProbabilityRandomOne()
    {
        $this->_storeConfigMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue(1));
        $this->assertTrue($this->_oauthHelper->isCleanupProbability());
    }

    public function testGetCleanupExpirationPeriodZero()
    {
        $this->_storeConfigMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue(0));
        $this->assertEquals(
            \Magento\Oauth\Helper\Service::CLEANUP_EXPIRATION_PERIOD_DEFAULT,
            $this->_oauthHelper->getCleanupExpirationPeriod()
        );
    }

    public function testGetCleanupExpirationPeriodNonZero()
    {
        $this->_storeConfigMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue(10));
        $this->assertEquals(10, $this->_oauthHelper->getCleanupExpirationPeriod());
    }

    public function testGetConsumerExpirationPeriodZero()
    {
        $this->_storeConfigMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue(0));
        $this->assertEquals(
            \Magento\Oauth\Helper\Service::CONSUMER_EXPIRATION_PERIOD_DEFAULT,
            $this->_oauthHelper->getConsumerExpirationPeriod()
        );
    }

    public function testGetConsumerExpirationPeriodNonZero()
    {
        $this->_storeConfigMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue(10));
        $this->assertEquals(10, $this->_oauthHelper->getConsumerExpirationPeriod());
    }
}
