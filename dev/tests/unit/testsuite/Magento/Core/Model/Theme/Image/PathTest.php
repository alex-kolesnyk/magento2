<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test of image path model
 */
namespace Magento\Core\Model\Theme\Image;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme\Image\Path|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\View\Url
     */
    protected $_viewUrlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\StoreManager
     */
    protected $_storeManagerMock;

    protected function setUp()
    {
        $this->_dirMock = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $this->_viewUrlMock = $this->getMock('Magento\View\Url', array(), array(), '', false);
        $this->_storeManagerMock = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);

        $this->_dirMock->expects($this->any())->method('getDir')->with(\Magento\App\Dir::MEDIA)
            ->will($this->returnValue('/media'));

        $this->_model = new Path(
            $this->_dirMock,
            $this->_viewUrlMock,
            $this->_storeManagerMock
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_dirMock = null;
        $this->_viewUrlMock = null;
        $this->_storeManagerMock = null;
    }

    /**
     * @covers \Magento\Core\Model\Theme\Image\Path::__construct
     * @covers \Magento\Core\Model\Theme\Image\Path::getPreviewImageDirectoryUrl
     */
    public function testPreviewImageDirectoryUrlGetter()
    {
        $store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/'));
        $this->_storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->assertEquals('http://localhost/theme/preview/', $this->_model->getPreviewImageDirectoryUrl());
    }

    /**
     * @covers \Magento\Core\Model\Theme\Image\PathMagento\Core\Model\Theme\Image\Path::getPreviewImageDefaultUrl
     */
    public function testDefaultPreviewImageUrlGetter()
    {
        $this->_viewUrlMock->expects($this->once())->method('getViewFileUrl')
            ->with(\Magento\Core\Model\Theme\Image\Path::DEFAULT_PREVIEW_IMAGE);
        $this->_model->getPreviewImageDefaultUrl();
    }

    /**
     * @covers \Magento\Core\Model\Theme\Image\Path::getImagePreviewDirectory
     */
    public function testImagePreviewDirectoryGetter()
    {
        $this->assertEquals(
            \Magento\Filesystem::fixSeparator('/media/theme/preview'),
            \Magento\Filesystem::fixSeparator($this->_model->getImagePreviewDirectory())
        );
    }

    /**
     * @covers \Magento\Core\Model\Theme\Image\Path::getTemporaryDirectory
     */
    public function testTemporaryDirectoryGetter()
    {
        $this->assertEquals(
            \Magento\Filesystem::fixSeparator('/media/theme/origin'),
            \Magento\Filesystem::fixSeparator($this->_model->getTemporaryDirectory())
        );
    }
}
