<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backup
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Class to work system backup that excludes media folder
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Nomedia extends Mage_Backup_Media
{
    /**
     * Overlap getType
     *
     * @return string
     * @see Mage_Backup_Interface::getType()
     */
    public function getType()
    {
        return 'nomedia';
    }

    /**
     * Add media folder to ignore list
     *
     * @return Mage_Backup_Media
     */
    protected function _prepareIgnoreList()
    {
        $this->getSnapshotManager()->addIgnorePaths(array(
            $this->getSnapshotManager()->getRootDir() . DS . 'media',
            $this->getSnapshotManager()->getRootDir() . DS . 'pub' . DS . 'media',
        ));
        return $this;
    }
}