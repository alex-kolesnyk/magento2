<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Archive
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
* Helper class that simplifies files stream reading and writing
*/
namespace Magento\Archive\Helper;

class File
{
    /**
     * Full path to directory where file located
     *
     * @var string
     */
    protected $_fileLocation;

    /**
     * File name
     *
     * @var string
     */
    protected $_fileName;

    /**
     * Full path (directory + filename) to file
     *
     * @var string
     */
    protected $_filePath;

    /**
     * File permissions that will be set if file opened in write mode
     *
     * @var int
     */
    protected $_chmod;

    /**
     * File handler
     *
     * @var pointer
     */
    protected $_fileHandler;

    /**
     * Set file path via constructor
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $pathInfo = pathinfo($filePath);

        $this->_filePath = $filePath;
        $this->_fileLocation = isset($pathInfo['dirname'])  ? $pathInfo['dirname'] : '';
        $this->_fileName     = isset($pathInfo['basename']) ? $pathInfo['basename'] : '';
    }

    /**
     * Close file if it's not closed before object destruction
     */
    public function __destruct()
    {
        if ($this->_fileHandler) {
            $this->_close();
        }
    }

    /**
     * Open file
     *
     * @param string $mode
     * @param int $chmod
     * @throws \Magento\Exception
     */
    public function open($mode = 'w+', $chmod = 0666)
    {
        if ($this->_isWritableMode($mode)) {
            if (!is_writable($this->_fileLocation)) {
                throw new \Magento\Exception('Permission denied to write to ' . $this->_fileLocation);
            }

            if (is_file($this->_filePath) && !is_writable($this->_filePath)) {
                throw new \Magento\Exception("Can't open file " . $this->_fileName . " for writing. Permission denied.");
            }
        }

        if ($this->_isReadableMode($mode) && (!is_file($this->_filePath) || !is_readable($this->_filePath))) {
            if (!is_file($this->_filePath)) {
                throw new \Magento\Exception('File ' . $this->_filePath . ' does not exist');
            }

            if (!is_readable($this->_filePath)) {
                throw new \Magento\Exception('Permission denied to read file ' . $this->_filePath);
            }
        }

        $this->_open($mode);

        $this->_chmod = $chmod;
    }

    /**
     * Write data to file
     *
     * @param string $data
     */
    public function write($data)
    {
        $this->_checkFileOpened();
        $this->_write($data);
    }

    /**
     * Read data from file
     *
     * @param int $length
     * @return string|boolean
     */
    public function read($length = 4096)
    {
        $data = false;
        $this->_checkFileOpened();
        if ($length > 0) {
            $data = $this->_read($length);
        }

        return $data;
    }

    /**
     * Check whether end of file reached
     *
     * @return boolean
     */
    public function eof()
    {
        $this->_checkFileOpened();
        return $this->_eof();
    }

    /**
     * Close file
     */
    public function close()
    {
        $this->_checkFileOpened();
        $this->_close();
        $this->_fileHandler = false;
        @chmod($this->_filePath, $this->_chmod);
    }

    /**
     * Implementation of file opening
     *
     * @param string $mode
     * @throws \Magento\Exception
     */
    protected function _open($mode)
    {
        $this->_fileHandler = @fopen($this->_filePath, $mode);

        if (false === $this->_fileHandler) {
            throw new \Magento\Exception('Failed to open file ' . $this->_filePath);
        }
    }

    /**
     * Implementation of writing data to file
     *
     * @param string $data
     * @throws \Magento\Exception
     */
    protected function _write($data)
    {
        $result = @fwrite($this->_fileHandler, $data);

        if (false === $result) {
            throw new \Magento\Exception('Failed to write data to ' . $this->_filePath);
        }
    }

    /**
     * Implementation of file reading
     *
     * @param int $length
     * @throws \Magento\Exception
     */
    protected function _read($length)
    {
        $result = fread($this->_fileHandler, $length);

        if (false === $result) {
            throw new \Magento\Exception('Failed to read data from ' . $this->_filePath);
        }

        return $result;
    }

    /**
     * Implementation of EOF indicator
     *
     * @return boolean
     */
    protected function _eof()
    {
        return feof($this->_fileHandler);
    }

    /**
     * Implementation of file closing
     */
    protected function _close()
    {
        fclose($this->_fileHandler);
    }

    /**
     * Check whether requested mode is writable mode
     *
     * @param string $mode
     */
    protected function _isWritableMode($mode)
    {
        return preg_match('/(^[waxc])|(\+$)/', $mode);
    }

    /**
    * Check whether requested mode is readable mode
    *
    * @param string $mode
    */
    protected function _isReadableMode($mode) {
        return !$this->_isWritableMode($mode);
    }

    /**
     * Check whether file is opened
     *
     * @throws \Magento\Exception
     */
    protected function _checkFileOpened()
    {
        if (!$this->_fileHandler) {
            throw new \Magento\Exception('File not opened');
        }
    }
}
