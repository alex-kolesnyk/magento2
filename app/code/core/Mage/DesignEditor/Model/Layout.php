<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Model for manipulating layout for purpose of design editor
 */
class Mage_DesignEditor_Model_Layout
{
    /**
     * List of block types considered as "safe"
     *
     * "Safe" means that they will work with any template (if applicable)
     *
     * @var array
     */
    protected static $_blockWhiteList = array(
        'Mage_Core_Block_Template',
        'Mage_Core_Block_Text_List',
        'Mage_Page_Block_',
        'Mage_DesignEditor_Block_',
    );

    /**
     * List of block types considered as "not safe"
     *
     * @var array
     */
    protected static $_blockBlackList = array(
        'Mage_Page_Block_Html_Pager',
    );

    /**
     * List of layout containers that potentially have "safe" blocks
     *
     * @var array
     */
    protected static $_containerWhiteList = array(
        'root', 'head', 'after_body_start', 'header', 'footer', 'before_body_end',
        'top.links', 'top.menu',
    );

    /**
     * Replace all potentially dangerous blocks in layout into stubs
     *
     * It is important to sanitize the references first, because they refer to blocks to check whether they are safe.
     * But if the blocks were sanitized before references, then they ALL will be considered safe.
     *
     * @param Varien_Simplexml_Element $node
     */
    public static function sanitizeLayout(Varien_Simplexml_Element $node)
    {
        self::_sanitizeLayout($node, 'reference'); // it is important to sanitize references first
        self::_sanitizeLayout($node, 'block');
    }

    /**
     * Sanitize nodes which names match the specified one
     *
     * Recursively goes through all underlying nodes
     *
     * @param Varien_Simplexml_Element $node
     * @param string $nodeName
     */
    protected static function _sanitizeLayout(Varien_Simplexml_Element $node, $nodeName)
    {
        if ($node->getName() == $nodeName) {
            switch ($nodeName) {
                case 'block':
                    self::_sanitizeBlock($node);
                    break;
                case 'reference':
                    self::_sanitizeReference($node);
                    break;
            }
        }
        foreach ($node->children() as $child) {
            self::_sanitizeLayout($child, $nodeName);
        }
    }

    /**
     * Replace "unsafe" types of blocks into Mage_Core_Block_Template and cut all their actions
     *
     * A "stub" template will be assigned for the blocks
     *
     * @param Varien_Simplexml_Element $node
     */
    protected static function _sanitizeBlock(Varien_Simplexml_Element $node)
    {
        $type = $node->getAttribute('type');
        if (!$type) {
            return; // we encountered a node with name "block", however it doesn't actually define any block...
        }
        if (self::_isParentSafe($node) || self::_isTypeSafe($type)) {
            return;
        }
        self::_overrideAttribute($node, 'template', 'Mage_DesignEditor::stub.phtml');
        self::_overrideAttribute($node, 'type', 'Mage_Core_Block_Template');
        self::_deleteNodes($node, 'action');
    }

    /**
     * Whether parent node of specified node can be considered a safe container
     *
     * @param Varien_Simplexml_Element $node
     * @return bool
     */
    protected static function _isParentSafe(Varien_Simplexml_Element $node)
    {
        $parentAttributes = $node->getParent()->attributes();
        if (isset($parentAttributes['name'])) {
            if (in_array($parentAttributes['name'], self::$_containerWhiteList)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the specified type of block can be safely used in layout without required context
     *
     * @param string $type
     * @return bool
     */
    protected static function _isTypeSafe($type)
    {
        if (in_array($type, self::$_blockBlackList)) {
            return false;
        }
        foreach (self::$_blockWhiteList as $safeType) {
            if ('_' !== substr($safeType, -1, 1)) {
                if ($type === $safeType) {
                    return true;
                }
            } elseif (0 === strpos($type, $safeType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add or update specified attribute of a node with specified value
     *
     * @param Varien_Simplexml_Element $node
     * @param string $name
     * @param string $value
     */
    protected static function _overrideAttribute(Varien_Simplexml_Element $node, $name, $value)
    {
        $attributes = $node->attributes();
        if (isset($attributes[$name])) {
            $attributes[$name] = $value;
        } else {
            $attributes->addAttribute($name, $value);
        }
    }

    /**
     * Delete child nodes by specified name
     *
     * @param Varien_Simplexml_Element $node
     * @param string $name
     */
    protected static function _deleteNodes(Varien_Simplexml_Element $node, $name)
    {
        $count = count($node->{$name});
        for ($i = $count; $i >= 0; $i--) {
            unset($node->{$name}[$i]);
        }
    }

    /**
     * Cleanup reference node according to the block it refers to
     *
     * Look for the block by reference name and if the block is "unsafe", cleanup the reference node from actions
     *
     * @param Varien_Simplexml_Element $node
     */
    protected static function _sanitizeReference(Varien_Simplexml_Element $node)
    {
        $attributes = $node->attributes();
        $name = $attributes['name'];
        $result = $node->xpath("//block[@name='{$name}']") ?: array();
        foreach ($result as $block) {
            $isTypeSafe = self::_isTypeSafe($block->getAttribute('type'));
            if (!($isTypeSafe || $isTypeSafe && self::_isParentSafe($block))) {
                self::_deleteNodes($node, 'action');
            }
            break;
        }
    }
}
