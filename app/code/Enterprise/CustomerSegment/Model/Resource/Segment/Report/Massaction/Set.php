<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Enterprise_CustomerSegment_Model_Resource_Segment_Report_Massaction_Set
    implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * @var Enterprise_ImportExport_Model_Scheduled_Operation_Data
     */
    protected $_segmentHelper;

    /**
     * @var Mage_Backend_Block_Widget_Grid_Column_Renderer_Options_Converter
     */
    protected $_dataConverter;

    /**
     * @param Enterprise_CustomerSegment_Helper_Data $helper
     * @param Mage_Backend_Block_Widget_Grid_Column_Renderer_Options_Converter $converter
     */
    public function __construct(
        Enterprise_CustomerSegment_Helper_Data $helper,
        Mage_Backend_Block_Widget_Grid_Column_Renderer_Options_Converter $converter)
    {
        $this->_segmentHelper = $helper;
        $this->_dataConverter = $converter;

    }

    /**
     * Return statuses array
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_dataConverter->toFlatArray($this->_segmentHelper->getOptionsArray());
    }
}
