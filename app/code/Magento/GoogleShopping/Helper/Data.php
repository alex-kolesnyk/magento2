<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Google Content Data Helper
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * Magento string lib
     *
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Helper\Context $context
     */
    public function __construct(
        \Magento\Stdlib\String $string,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Helper\Context $context
    ) {
        $this->string = $string;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get Google Content Product ID
     *
     * @param int $productId
     * @param int $storeId
     * @return string
     */
    public function buildContentProductId($productId, $storeId)
    {
        return $productId . '_' . $storeId;
    }

    /**
     * Remove characters and words not allowed by Google Content in title and content (description).
     *
     * (to avoid "Expected response code 200, got 400.
     * Reason: There is a problem with the character encoding of this attribute")
     *
     * @param string $string
     * @return string
     */
    public function cleanAtomAttribute($string)
    {
        return $this->string->substr(preg_replace('/[\pC¢€•—™°½]|shipping/ui', '', $string), 0, 3500);
    }

    /**
     * Normalize attribute's name.
     * The name has to be in lower case and the words are separated by symbol "_".
     * For instance: Meta Description = meta_description
     *
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        return strtolower(preg_replace('/[\s_]+/', '_', $name));
    }

    /**
     * Parse \Exception Response Body
     *
     * @param string $message \Exception message to parse
     * @param null|\Magento\Catalog\Model\Product $product
     * @return string
     */
    public function parseGdataExceptionMessage($message, $product = null)
    {
        $result = array();
        foreach (explode("\n", $message) as $row) {
            if (trim($row) == '') {
                continue;
            }

            if (strip_tags($row) == $row) {
                $row = preg_replace('/@ (.*)/', __("See '\\1'"), $row);
                if (!is_null($product)) {
                    $row .= ' ' . __("for product '%1' (in '%2' store)", $product->getName(), $this->_storeManager->getStore($product->getStoreId())->getName());
                }
                $result[] = $row;
                continue;
            }

            // parse not well-formatted xml
            preg_match_all('/(reason|field|type)=\"([^\"]+)\"/', $row, $matches);

            if (is_array($matches) && count($matches) == 3) {
                if (is_array($matches[1]) && count($matches[1]) > 0) {
                    $c = count($matches[1]);
                    for ($i = 0; $i < $c; $i++) {
                        if (isset($matches[2][$i])) {
                            $result[] = ucfirst($matches[1][$i]) . ': ' . $matches[2][$i];
                        }
                    }
                }
            }
        }
        return implode(". ", $result);
    }
}
