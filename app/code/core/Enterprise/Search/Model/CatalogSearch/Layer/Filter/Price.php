<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layer price filter
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_CatalogSearch_Layer_Filter_Price
    extends Enterprise_Search_Model_Catalog_Layer_Filter_Price
{
    /**
     * Retrieve resource model
     *
     * @return object
     */
    protected function _getResource()
    {
        $engineClassName         = get_class(Mage::helper('catalogsearch')->getEngine());
        $fulltextEngineClassName = get_class(Mage::getResourceSingleton('catalogsearch/fulltext_engine'));

        if ($engineClassName == $fulltextEngineClassName) {
            return parent::_getResource();
        }

        return Mage::getResourceSingleton('enterprise_search/catalogsearch_facets_price');
    }
}
