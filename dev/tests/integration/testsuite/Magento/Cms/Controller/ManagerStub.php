<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Router
     */
    protected $_model;

    protected function setUp()
    {
        $this->markTestIncomplete('MAGETWO-3393');
        $this->_model = new \Magento\Cms\Controller\Router(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Core\Controller\Varien\Action\Factory'),
            new \Magento\Core\Model\Event\ManagerStub(
                $this->getMockForAbstractClass('Magento\Core\Model\Event\InvokerInterface', array(), '', false),
                $this->getMock('Magento\Core\Model\Event\Config', array(), array(), '', false),
                $this->getMock('Magento\EventFactory', array(), array(), '', false),
                $this->getMock('Magento\Event\ObserverFactory', array(), array(), '', false)
            )
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMatch()
    {
        $this->markTestIncomplete('MAGETWO-3393');
        $request = \Mage::getObjectManager()->create('Magento\Core\Controller\Request\Http');
        //Open Node
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Controller\Response\Http')
            ->headersSentThrowsException = \Mage::$headersSentThrowsException;
        $request->setPathInfo('parent_node');
        $controller = $this->_model->match($request);
        $this->assertInstanceOf('Magento\Core\Controller\Varien\Action\Redirect', $controller);
    }
}

