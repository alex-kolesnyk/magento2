<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * DHL International configuration save handler
 *
 * @category   Mage
 * @package    Mage_Launcher
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Launcher_Model_Storelauncher_Shipping_Savehandlers_DhlSaveHandler
    extends Mage_Launcher_Model_Tile_ConfigBased_SaveHandlerAbstract
{
    /**
     * Retrieve the list of names of the related configuration sections
     *
     * @return array
     */
    public function getRelatedConfigSections()
    {
        return array('carriers');
    }

    /**
     * Prepare configuration data for saving
     *
     * @param array $data
     * @return array prepared data
     * @throws Mage_Launcher_Exception
     */
    public function prepareData(array $data)
    {
        $preparedData = array();
        if (empty($data['groups']['dhlint']['fields']['id']['value'])) {
            throw new Mage_Launcher_Exception('Access ID is required.');
        }
        if (empty($data['groups']['dhlint']['fields']['account']['value'])) {
            throw new Mage_Launcher_Exception('Account Number is required.');
        }
        if (empty($data['groups']['dhlint']['fields']['password']['value'])) {
            throw new Mage_Launcher_Exception('Password is required.');
        }

        $preparedData['carriers']['dhlint']['fields']['id']['value'] =
            trim($data['groups']['dhlint']['fields']['id']['value']);
        $preparedData['carriers']['dhlint']['fields']['account']['value'] =
            trim($data['groups']['dhlint']['fields']['account']['value']);
        $preparedData['carriers']['dhlint']['fields']['password']['value'] =
            trim($data['groups']['dhlint']['fields']['password']['value']);

        // Enable DHL international for checkout
        $preparedData['carriers']['dhlint']['fields']['active']['value'] = 1;

        return $preparedData;
    }
}
