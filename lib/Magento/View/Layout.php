<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View;

use Magento\View\Layout\Processor;
use Magento\View\Layout\Handle;

interface Layout
{
    /**
     * Retrieve the layout processor
     *
     * @return Processor
     */
    public function getUpdate();

    /**
     * Layout xml generation
     *
     * @return Layout
     * @todo MERGE with generateElements()
     */
    public function generateXml();

    /**
     * Create structure of elements from the loaded XML configuration
     *
     * @todo MERGE with generateXml()
     */
    public function generateElements();

    /**
     * Find an element in layout, render it and return string with its output
     *
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true);

    /**
     * Add an element to output
     *
     * @param string $name
     * @return \Magento\View\Layout
     */
    public function addOutputElement($name);

    /**
     * Get all blocks marked for output
     *
     * @return string
     */
    public function getOutput();

    /**
     * @param $name
     * @param array $element
     * @return Layout
     */
    public function addElement($name, array & $element);

    /**
     * @param string $name
     * @return array
     */
    public function & getElement($name);

    /**
     * Check if element exists in layout structure
     *
     * @param string $name
     * @return bool
     * @todo DELETE (only two-three calls from outside)
     */
    public function hasElement($name);

    /**
     * Remove block from registry
     *
     * @param string $name
     * @return Layout
     * @todo DELETE (only two-three calls from outside)
     */
    public function unsetElement($name);

    /**
     * Retrieve all blocks from registry as array
     *
     * @return array
     */
    public function getAllBlocks();

    /**
     * Get block object by name
     *
     * @param string $name
     * @return \Magento\Core\Block\AbstractBlock|bool
     */
    public function getBlock($name);

    /**
     * Get child block if exists
     *
     * @param string $parentName
     * @param string $alias
     * @return null
     */
    public function getChildBlock($parentName, $alias);

    /**
     * Set child element into layout structure
     *
     * @param string $parentName
     * @param string $elementName
     * @param string $alias
     * @return Layout
     */
    public function setChild($parentName, $elementName, $alias);

    /**
     * Reorder a child of a specified element
     *
     * If $offsetOrSibling is null, it will put the element to the end
     * If $offsetOrSibling is numeric (integer) value, it will put the element after/before specified position
     * Otherwise -- after/before specified sibling
     *
     * @param string $parentName
     * @param string $childName
     * @param string|int|null $offsetOrSibling
     * @param bool $after
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true);

    /**
     * Remove child element from parent
     *
     * @param string $parentName
     * @param string $alias
     * @return Layout
     */
    public function unsetChild($parentName, $alias);

    /**
     * Get list of child names
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName);

    /**
     * Get list of child blocks
     *
     * Returns associative array of <alias> => <block instance>
     *
     * @param string $parentName
     * @return array
     */
    public function getChildBlocks($parentName);

    /**
     * Get child name by alias
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|string
     */
    public function getChildName($parentName, $alias);

    /**
     * Add element to parent group
     *
     * @param string $blockName
     * @param string $parentGroupName
     * @return bool
     */
    public function addToParentGroup($blockName, $parentGroupName);

    /**
     * Get element names for specified group
     *
     * @param string $blockName
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($blockName, $groupName);

    /**
     * Gets parent name of an element with specified name
     *
     * @param string $childName
     * @return bool|string
     */
    public function getParentName($childName);

    /**
     * Block Factory
     *
     * @param  string $type
     * @param  string $name
     * @param  array $attributes
     * @return \Magento\Core\Block\AbstractBlock
     * @todo DELETE (use viewFactory)
     */
    public function createBlock($type, $name = '', array $attributes = array());

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|\Magento\Core\Block\AbstractBlock $block
     * @param string $name
     * @param string $parent
     * @param string $alias
     * @return \Magento\Core\Block\AbstractBlock
     * @todo DELETE (use viewFactory and addElement instead)
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '');

    /**
     * Insert container into layout structure
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @param string $parent
     * @param string $alias
     * @todo DELETE (use viewFactory and addElement instead)
     */
    public function addContainer($name, $label, array $options = array(), $parent = '', $alias = '');

    /**
     * Rename element in layout and layout structure
     *
     * @param string $oldName
     * @param string $newName
     * @return bool
     * @todo DELETE (used mostly in setNameInLayout)
     */
    public function renameElement($oldName, $newName);

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return bool|string
     * @todo DELETE
     */
    public function getElementAlias($name);

    /**
     * Remove an element from output
     *
     * @param string $name
     * @return \Magento\View\Layout
     * @todo DELETE
     */
    public function removeOutputElement($name);

    /**
     * Retrieve messages block
     *
     * @return \Magento\Core\Block\Messages
     * @todo DELETE use whatever instead
     */
    public function getMessagesBlock();

    /**
     * Get block singleton
     *
     * @param string $type
     * @throws \Magento\Core\Exception
     * @return \Magento\Core\Helper\AbstractHelper
     * @todo DELETE use object manager or view factory
     */
    public function getBlockSingleton($type);

    /**
     * Retrieve block factory
     *
     * @return \Magento\Core\Model\BlockFactory
     * @todo DELETE
     */
    public function getBlockFactory();

    /**
     * Retrieve layout area
     *
     * @return string
     * @todo DELETE
     */
    public function getArea();

    /**
     * Set layout area
     *
     * @param $area
     * @return Layout
     * @todo DELETE
     */
    public function setArea($area);

    /**
     * Declaring layout direct output flag
     *
     * @param   bool $flag
     * @return  Layout
     * @todo DELETE
     */
    public function setDirectOutput($flag);

    /**
     * Retrieve direct output flag
     *
     * @return bool
     * @todo DELETE
     */
    public function isDirectOutput();

    /**
     * Get property value of an element
     *
     * @param string $name
     * @param string $attribute
     * @return mixed
     * @todo DELETE
     */
    public function getElementProperty($name, $attribute);

    /**
     * Whether specified element is a block
     *
     * @param string $name
     * @return bool
     * @todo DELETE
     */
    public function isBlock($name);

    /**
     * Checks if element with specified name is container
     *
     * @param string $name
     * @return bool
     * @todo DELETE
     */
    public function isContainer($name);

    /**
     * Whether the specified element may be manipulated externally
     *
     * @param string $name
     * @return bool
     * @todo DELETE
     */
    public function isManipulationAllowed($name);

    /**
     * Save block in blocks registry
     *
     * @param string $name
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return Layout
     * @todo DELETE
     */
    public function setBlock($name, $block);
}
