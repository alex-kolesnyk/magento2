<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Storage model test
 */
class Mage_Theme_Model_Wysiwyg_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var null|string
     */
    protected $_storageRoot;

    /**
     * @var null|Magento_Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var null|Mage_Theme_Helper_Storage
     */
    protected $_helperStorage;

    /**
     * @var null|Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var null|Mage_Theme_Model_Wysiwyg_Storage
     */
    protected $_storageModel;

    public function setUp()
    {
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_helperStorage = $this->getMock('Mage_Theme_Helper_Storage', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);

        $this->_storageModel = new Mage_Theme_Model_Wysiwyg_Storage(
            $this->_filesystem,
            $this->_helperStorage,
            $this->_objectManager
        );

        $this->_storageRoot = DIRECTORY_SEPARATOR . 'root';
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::createFolder
     */
    public function testCreateFolder()
    {
        $newDirectoryName = 'dir1';
        $fullNewPath = $this->_storageRoot . DIRECTORY_SEPARATOR . $newDirectoryName;

        $this->_filesystem->expects($this->once())
            ->method('isWritable')
            ->with($this->_storageRoot)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($fullNewPath)
            ->will($this->returnValue(false));

        $this->_filesystem->expects($this->once())
            ->method('ensureDirectoryExists')
            ->with($fullNewPath);


        $this->_helperStorage->expects($this->once())
            ->method('getShortFilename')
            ->with($newDirectoryName)
            ->will($this->returnValue($newDirectoryName));

        $this->_helperStorage->expects($this->once())
            ->method('convertPathToId')
            ->with($fullNewPath)
            ->will($this->returnValue($newDirectoryName));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $expectedResult = array(
            'name'       => $newDirectoryName,
            'short_name' => $newDirectoryName,
            'path'       => DIRECTORY_SEPARATOR . $newDirectoryName,
            'id'         => $newDirectoryName
        );

        $this->assertEquals(
            $expectedResult,
            $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot)
        );
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getDirsCollection
     */
    public function testGetDirsCollection()
    {
        $dirs = array(
            $this->_storageRoot . DIRECTORY_SEPARATOR . 'dir1',
            $this->_storageRoot . DIRECTORY_SEPARATOR . 'dir2'
        );

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($this->_storageRoot)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($this->_storageRoot, '*')
            ->will($this->returnValue($dirs));

        $this->_filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));

        $this->assertEquals($dirs, $this->_storageModel->getDirsCollection($this->_storageRoot));
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getFilesCollection
     */
    public function testGetFilesCollection()
    {
        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($this->_storageRoot));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(Mage_Theme_Model_Wysiwyg_Storage::TYPE_FONT));

        $this->_helperStorage->expects($this->any())
            ->method('urlEncode')
            ->will($this->returnArgument(0));


        $paths = array(
            $this->_storageRoot . DIRECTORY_SEPARATOR . 'font1.ttf',
            $this->_storageRoot . DIRECTORY_SEPARATOR . 'font2.ttf'
        );

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($this->_storageRoot, '*')
            ->will($this->returnValue($paths));

        $this->_filesystem->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(2, $result);
        $this->assertEquals('font1.ttf', $result[0]['text']);
        $this->assertEquals('font2.ttf', $result[1]['text']);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::getTreeArray
     */
    public function testTreeArray()
    {
        $currentPath = $this->_storageRoot . DIRECTORY_SEPARATOR . 'dir';
        $dirs = array(
            $currentPath . DIRECTORY_SEPARATOR . 'dir_one',
            $currentPath . DIRECTORY_SEPARATOR . 'dir_two'
        );

        $expectedResult = array(
            array(
                'text' => pathinfo($dirs[0], PATHINFO_BASENAME),
                'id'   => $dirs[0],
                'cls'  => 'folder'
            ),
            array(
                'text' => pathinfo($dirs[1], PATHINFO_BASENAME),
                'id'   => $dirs[1],
                'cls'  => 'folder'
        ));

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($currentPath)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->once())
            ->method('searchKeys')
            ->with($currentPath, '*')
            ->will($this->returnValue($dirs));

        $this->_filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));


        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($currentPath));

        $this->_helperStorage->expects($this->any())
            ->method('getShortFilename')
            ->will($this->returnArgument(0));

        $this->_helperStorage->expects($this->any())
            ->method('convertPathToId')
            ->will($this->returnArgument(0));

        $result = $this->_storageModel->getTreeArray();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers Mage_Theme_Model_Wysiwyg_Storage::deleteFile
     */
    public function testDeleteFile()
    {
        $image = 'image.jpg';
        $storagePath = $this->_storageRoot;
        $imagePath = $storagePath . DIRECTORY_SEPARATOR . $image;
        $thumbnailDir = $this->_storageRoot . DIRECTORY_SEPARATOR
            . Mage_Theme_Model_Wysiwyg_Storage::THUMBNAIL_DIRECTORY;

        $session = $this->getMock('Mage_Backend_Model_Session', array('getStoragePath'), array(), '', false);
        $session->expects($this->atLeastOnce())
            ->method('getStoragePath')
            ->will($this->returnValue($storagePath));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($session));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('urlDecode')
            ->with($image)
            ->will($this->returnArgument(0));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getThumbnailDirectory')
            ->with($imagePath)
            ->will($this->returnValue($thumbnailDir));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));


        $filesystem = $this->_filesystem;
        $filesystem::staticExpects($this->once())
            ->method('getAbsolutePath')
            ->with($imagePath)
            ->will($this->returnValue($imagePath));

        $filesystem::staticExpects($this->any())
            ->method('isPathInDirectory')
            ->with($imagePath, $storagePath)
            ->will($this->returnValue(true));

        $filesystem::staticExpects($this->any())
            ->method('isPathInDirectory')
            ->with($imagePath, $this->_storageRoot)
            ->will($this->returnValue(true));

        $this->_filesystem->expects($this->at(0))
            ->method('delete')
            ->with($imagePath);

        $this->_filesystem->expects($this->at(1))
            ->method('delete')
            ->with($thumbnailDir . DIRECTORY_SEPARATOR . $image);

        $this->assertInstanceOf('Mage_Theme_Model_Wysiwyg_Storage', $this->_storageModel->deleteFile($image));
    }
}
