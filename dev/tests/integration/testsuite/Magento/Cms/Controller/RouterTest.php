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

namespace Magento\Cms\Controller;

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
            new \Magento\Event\ManagerInterfaceStub(
                $this->getMockForAbstractClass('Magento\Event\InvokerInterface'),
                $this->getMock('Magento\Event\Config', array(), array(), '', false),
                $this->getMock('Magento\EventFactory', array(), array(), '', false),
                $this->getMock('Magento\Event\ObserverFactory', array(), array(), '', false)
            ),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\UrlInterface'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Core\Model\Config\Primary'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Cms\Model\PageFactory'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Core\Model\StoreManagerInterface'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Core\Model\StoreManagerInterface')
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMatch()
    {
        $this->markTestIncomplete('MAGETWO-3393');
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\App\RequestInterface');
        //Open Node
        $request->setPathInfo('parent_node');
        $controller = $this->_model->match($request);
        $this->assertInstanceOf('Magento\Core\Controller\Varien\Action\Redirect', $controller);
    }
}

/**
 * Event manager stub
 */
namespace Magento\Event;

class ManagerStub extends Manager
{
    /**
     * Stub dispatch event
     *
     * @param string $eventName
     * @param array $params
     * @return \Magento\Core\Model\App|null
     */
    public function dispatch($eventName, array $params = array())
    {
        switch ($eventName) {
            case 'cms_controller_router_match_before' :
                $params['condition']->setRedirectUrl('http://www.example.com/');
                break;
        }

        return null;
    }
}
