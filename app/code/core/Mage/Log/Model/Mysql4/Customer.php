<?php
/**
 * Customer log resource
 *
 * @package     Mage
 * @subpackage  Log
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Log_Model_Mysql4_Customer
{
    /**
     * Visitor data table name
     *
     * @var string
     */
    protected $_visitorTable;

    /**
     * Visitor info data table
     *
     * @var string
     */
    protected $_visitorInfoTable;

    /**
     * Customer data table
     *
     * @var string
     */
    protected $_customerTable;

    /**
     * Url info data table
     *
     * @var string
     */
    protected $_urlInfoTable;

    /**
     * Log URL data table name.
     *
     * @var string
     */
    protected $_urlTable;

    /**
     * Log quote data table name.
     *
     * @var string
     */
    protected $_quoteTable;

    /**
     * Database read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * Database write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');

        $this->_visitorTable    = $resource->getTableName('log/visitor');
        $this->_visitorInfoTable= $resource->getTableName('log/visitor_info');
        $this->_urlTable        = $resource->getTableName('log/url_table');
        $this->_urlInfoTable    = $resource->getTableName('log/url_info_table');
        $this->_customerTable   = $resource->getTableName('log/customer');
        $this->_quoteTable      = $resource->getTableName('log/quote_table');

        $this->_read = $resource->getConnection('log_read');
        $this->_write = $resource->getConnection('log_write');
    }
    
    public function load($object, $customerId)
    {
        $select = $this->_read->select();
        $select->from($this->_customerTable, array('login_at', 'logout_at'))
            ->joinInner($this->_visitorTable, $this->_visitorTable.'.visitor_id='.$this->_customerTable.'.visitor_id', array('last_visit_at'))
            ->joinInner($this->_visitorInfoTable, $this->_visitorTable.'.visitor_id='.$this->_visitorInfoTable.'.visitor_id', array('http_referer', 'remote_addr'))
            ->joinInner($this->_urlInfoTable, $this->_urlInfoTable.'.url_id='.$this->_visitorTable.'.last_url_id', array('url'))
            ->where($this->_read->quoteInto($this->_customerTable.'.customer_id=?', $customerId))
            ->order($this->_customerTable.'.login_at desc')
            ->limit(1);
        $object->setData($this->_read->fetchRow($select));
        return $object;
    }
}