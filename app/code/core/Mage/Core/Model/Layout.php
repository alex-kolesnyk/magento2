<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Layout model
 *
 * @category   Mage
 * @package    Mage_Core
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Mage_Core_Model_Layout extends Varien_Simplexml_Config
{
    /**
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG   = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID    = 'htmlId';

    /**
     * Layout Update module
     *
     * @var Mage_Core_Model_Layout_Update
     */
    protected $_update;

    /**
     * Blocks registry
     *
     * @var array
     */
    protected $_blocks = array();

    /**
     * Cache of elements to output during rendering
     *
     * @var array
     */
    protected $_output = array();

    /**
     * Layout area (f.e. admin, frontend)
     *
     * @var string
     */
    protected $_area;

    /**
     * Helper blocks cache for this layout
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Flag to have blocks' output go directly to browser as oppose to return result
     *
     * @var boolean
     */
    protected $_directOutput = false;

    /**
     * Available options for containers in layout
     *
     * @var array
     */
    protected $_containerOptions = array(
        self::CONTAINER_OPT_HTML_CLASS,
        self::CONTAINER_OPT_HTML_ID,
        self::CONTAINER_OPT_HTML_TAG,
    );

    /**
     * Cache of generated elements' HTML
     *
     * @var array
     */
    protected $_elementsHtmlCache = array();

    /**
     * Layout structure model
     *
     * @var Mage_Core_Model_Layout_Structure
     */
    protected $_structure;

    /**
     * Increment for anonymous block names
     *
     * @var int
     */
    protected $_nameIncrement = 0;

    /**
     * Class constructor
     *
     * @param array $data
     */
    public function __construct($data=array())
    {
        $this->_structure = Mage::getModel('Mage_Core_Model_Layout_Structure');
        $this->_elementClass = Mage::getConfig()->getModelClassName('Mage_Core_Model_Layout_Element');
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        $this->_update = Mage::getModel('Mage_Core_Model_Layout_Update');
        parent::__construct($data);
    }

    /**
     * Layout update instance
     *
     * @return Mage_Core_Model_Layout_Update
     */
    public function getUpdate()
    {
        return $this->_update;
    }

    /**
     * Set layout area
     *
     * @param   string $area
     * @return  Mage_Core_Model_Layout
     */
    public function setArea($area)
    {
        $this->_area = $area;
        return $this;
    }

    /**
     * Retrieve layout area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_area;
    }

    /**
     * Declaring layout direct output flag
     *
     * @param   bool $flag
     * @return  Mage_Core_Model_Layout
     */
    public function setDirectOutput($flag)
    {
        $this->_directOutput = $flag;
        return $this;
    }

    /**
     * Retrieve direct output flag
     *
     * @return bool
     */
    public function isDirectOutput()
    {
        return $this->_directOutput;
    }

    /**
     * Layout xml generation
     *
     * @return Mage_Core_Model_Layout
     */
    public function generateXml()
    {
        $xml = $this->getUpdate()->asSimplexml();
        $removeInstructions = (array)$xml->xpath("//remove[@name]");
        foreach ($removeInstructions as $infoNode) {
            $attributes = $infoNode->attributes();
            $blockName = (string)$attributes->name;
            $ignoreNodes = $xml->xpath("//block[@name='" . $blockName . "']");
            if (!is_array($ignoreNodes)) {
                continue;
            }
            $ignoreReferences = $xml->xpath("//reference[@name='" . $blockName . "']");
            if (is_array($ignoreReferences)) {
                $ignoreNodes = array_merge($ignoreNodes, $ignoreReferences);
            }

            foreach ($ignoreNodes as $block) {
                if ($block->getAttribute('ignore') !== null || (($acl = (string)$attributes->acl)
                    && Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed($acl))) {
                    continue;
                }
                if (!isset($block->attributes()->ignore)) {
                    $block->addAttribute('ignore', true);
                }
            }
        }
        $this->setXml($xml);
        return $this;
    }

    /**
     * Create layout blocks hierarchy from layout xml configuration
     *
     * @param Mage_Core_Model_Layout_Element|null $parent
     */
    public function generateBlocks($parent=null)
    {
        if (empty($parent)) {
            $parent = $this->getNode();
        }
        /** @var Mage_Core_Model_Layout_Element $node  */
        foreach ($parent as $node) {
            $attributes = $node->attributes();
            if ((bool)$attributes->ignore) {
                continue;
            }
            switch ($node->getName()) {
                case 'container':
                case 'block':
                    $this->_generateElement($node, $parent);
                    $this->generateBlocks($node);
                    break;

                case 'reference':
                    $this->generateBlocks($node);
                    break;

                case 'action':
                    $this->_generateAction($node, $parent);
                    break;
            }
        }
    }

    /**
     * Creates block/container object based on xml node data
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param Mage_Core_Model_Layout_Element $parent
     * @return Mage_Core_Model_Layout
     * @throws Magento_Exception
     */
    protected function _generateElement($node, $parent)
    {
        $elementType = $node->getName();
        $name = $node->getAttribute('name');

        $_profilerKey = strtoupper($elementType) . ':' . $name;
        Magento_Profiler::start($_profilerKey);

        $parentName = $node->getAttribute('parent');
        if (is_null($parentName)) {
            $parentName = $parent->getElementName();
        }

        $alias = $node->getAttribute('as');
        if (!$alias) {
            $alias = $name;
        }

        $sibling = $node->getSibling();
        $after = true;
        if (isset($node['before'])) {
            $after = false;
        }

        $options = $this->_getValidNodeOptions($node);
        $elementName = $this->_structure
            ->insertElement($parentName, $name, $elementType, $alias, $after, $sibling, $options);

        $this->_filterBlocksList($elementName);

        if ($this->_structure->isBlock($elementName)) {
            $block = $this->_generateBlock($node);
            $updatedName = $block->getNameInLayout();
            if (empty($name)) {
                if (empty($alias)) {
                    $this->_structure->setElementAttribute($elementName, 'alias', $updatedName);
                }
                $this->_structure->setElementAttribute($elementName, 'name', $updatedName);
            }
        }

        if (!empty($node['output'])) {
            $this->addOutputElement($elementName);
        }

        Magento_Profiler::stop($_profilerKey);

        return $this;
    }

    /**
     * Insert block into layout structure
     *
     * @param $parentName
     * @param $name
     * @param $alias
     * @param bool $after
     * @param string $sibling
     * @return bool|string
     */
    public function insertBlock($parentName, $name, $alias = '', $after = true, $sibling = '')
    {
        return $this->_structure->insertBlock($parentName, $name, $alias, $after, $sibling);
    }

    /**
     * Insert container into layout structure
     *
     * @param $parentName
     * @param $name
     * @param string $alias
     * @param bool $after
     * @param string $sibling
     * @return bool|string
     */
    public function insertContainer($parentName, $name, $alias = '', $after = true, $sibling = '')
    {
        return $this->_structure->insertContainer($parentName, $name, $alias, $after, $sibling);
    }

    /**
     * Remove child element from parent
     *
     * @param $parentName
     * @param $alias
     * @return Mage_Core_Model_Layout
     */
    public function unsetChild($parentName, $alias)
    {
        $this->_structure->unsetChild($parentName, $alias);
        return $this;
    }

    /**
     * Get child block if exists
     *
     * @param $parentName
     * @param $alias
     * @return bool|Mage_Core_Block_Abstract
     */
    public function getChildBlock($parentName, $alias)
    {
        $name = $this->_structure->getChildName($parentName, $alias);
        if ($this->_structure->isBlock($name)) {
            return $this->getBlock($name);
        }
        return false;
    }

    /**
     * Extract valid options from a node
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return array
     */
    protected function _getValidNodeOptions(Mage_Core_Model_Layout_Element $node)
    {
        $options = array();
        foreach ($this->_containerOptions as $optName) {
            if ($value = $node->getAttribute($optName)) {
                $options[$optName] = $value;
            }
        }

        return $options;
    }

    /**
     * Creates block object based on xml node data and add it to the layout
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return Mage_Core_Block_Abstract
     */
    protected function _generateBlock(Mage_Core_Model_Layout_Element $node)
    {
        if (!empty($node['class'])) {
            $className = (string)$node['class'];
        } else {
            $className = (string)$node['type'];
        }
        $elementName = $node->getAttribute('name');

        $block = $this->_createBlock($className, $elementName);
        if (!$block) {
            return $this;
        }
        if (!empty($node['template'])) {
            $block->setTemplate((string)$node['template']);
        }

        return $block;
    }

    /**
     * Set child element into layout structure
     *
     * @param $parentName
     * @param $elementName
     * @param $alias
     * @return Mage_Core_Model_Layout
     */
    public function setChild($parentName, $elementName, $alias)
    {
        $block = $this->getBlock($elementName);
        if ($block) {
            $elementName = $block->getNameInLayout();
            if (empty($alias)) {
                $alias = $elementName;
            }
        }

        $this->_structure->setChild($parentName, $elementName, $alias);

        return $this;
    }

    /**
     * Get list of child names
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName)
    {
        return $this->_structure->getChildNames($parentName);
    }

    /**
     * Get child name by alias
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|string
     */
    public function getChildName($parentName, $alias)
    {
        return $this->_structure->getChildName($parentName, $alias);
    }

    /**
     * Find an element in layout and render it
     *
     * Returns element's output as string or false if element is not found
     *
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true)
    {
        if ($useCache && isset($this->_elementsHtmlCache[$name])) {
            return $this->_elementsHtmlCache[$name];
        }

        if ($this->_structure->isBlock($name)) {
            return $this->_renderBlock($name);
        }
        $html = $this->_renderContainer($name);

        $this->_elementsHtmlCache[$name] = $html;

        return $html;
    }

    public function addToParentGroup($name, $parentName, $parentGroupName)
    {
        return $this->_structure->addToParentGroup($name, $parentName, $parentGroupName);
    }

    /**
     * Get element names for specified group
     *
     * @param string $name
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($name, $groupName)
    {
        return $this->_structure->getGroupChildNames($name, $groupName);
    }

    /**
     * Gets HTML of block element
     *
     * @param string $name
     * @return string
     * @throws Magento_Exception
     */
    protected function _renderBlock($name)
    {
        $block = $this->getBlock($name);
        return $block ? $block->toHtml() : '';
    }

    /**
     * Gets HTML of container element
     *
     * @param string $name
     * @return string
     */
    protected function _renderContainer($name)
    {
        $html = '';
        $children = $this->_structure->getChildNames($name);
        foreach ($children as $child) {
            $html .= $this->renderElement($child);
        }
        if ($html == '' || !$this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_TAG)) {
            return $html;
        }

        $htmlId = $this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_ID);
        if ($htmlId) {
            $htmlId = ' id="' . $htmlId . '"';
        }

        $htmlClass = $this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_CLASS);
        if ($htmlClass) {
            $htmlClass = ' class="'. $htmlClass . '"';
        }

        $htmlTag = $this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_TAG);

        $html = sprintf('<%1$s%2$s%3$s>%4$s</%1$s>', $htmlTag, $htmlId, $htmlClass, $html);

        return $html;
    }

    /**
     * Run action defined in layout update
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param Mage_Core_Model_Layout_Element $parent
     * @return Mage_Core_Model_Layout
     * @throws Magento_Exception
     */
    protected function _generateAction($node, $parent)
    {
        $configPath = $node->getAttribute('ifconfig');
        if ($configPath && !Mage::getStoreConfigFlag($configPath)) {
            return $this;
        }

        if (Mage_Core_Model_Layout_Structure::ELEMENT_TYPE_CONTAINER === $parent->getName()) {
            throw new Magento_Exception('Action can not be placed inside container');
        }

        $method = $node->getAttribute('method');
        $parentName = $node->getAttribute('block');
        if (empty($parentName)) {
            $parentName = $parent->getElementName();
        }

        $_profilerKey = 'BLOCK_ACTION:' . $parentName . '>' . $method;
        Magento_Profiler::start($_profilerKey);

        $block = $this->getBlock($parentName);
        if (!empty($block)) {

            $args = $this->_extractArgs($node);

            $this->_translateLayoutNode($node, $args);
            call_user_func_array(array($block, $method), $args);
        }

        Magento_Profiler::stop($_profilerKey);

        return $this;
    }

    /**
     * Update args according to its type
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return array
     */
    protected function _extractArgs($node)
    {
        $args = (array)$node->children();
        unset($args['@attributes']);

        foreach ($args as $key => $arg) {
            if (($arg instanceof Mage_Core_Model_Layout_Element)) {
                if (isset($arg['helper'])) {
                    $args[$key] = $this->_getArgsByHelper($arg);
                } else {
                    /**
                     * if there is no helper we hope that this is assoc array
                     */
                    $arr = $this->_getArgsFromAssoc($arg);
                    if (!empty($arr)) {
                        $args[$key] = $arr;
                    }
                }
            }
        }

        if (isset($node['json'])) {
            $json = explode(' ', (string)$node['json']);
            foreach ($json as $arg) {
                $args[$arg] = Mage::helper('Mage_Core_Helper_Data')->jsonDecode($args[$arg]);
            }
        }

        return $args;
    }

    /**
     * Get args by helper
     *
     * @param Mage_Core_Model_Layout_Element $arg
     * @return mixed
     */
    protected function _getArgsByHelper(Mage_Core_Model_Layout_Element $arg)
    {
        $helper = (string)$arg['helper'];
        if (strpos($helper, '::') === false) {
            $helperName = explode('/', $helper);
            $helperMethod = array_pop($helperName);
            $helperName = implode('/', $helperName);
        } else {
            list($helperName, $helperMethod) = explode('::', $helper);
        }
        $arg = $arg->asArray();
        unset($arg['@']);
        return call_user_func_array(array(Mage::helper($helperName), $helperMethod), $arg);
    }

    /**
     * Get args from associative array
     *
     * @param array $array
     * @return array
     */
    protected function _getArgsFromAssoc($array)
    {
        $arr = array();
        foreach ($array as $key => $value) {
            $arr[(string)$key] = $value->asArray();
        }
        return $arr;
    }

    /**
     * Check if element exists in layout structure
     *
     * @param $name
     * @return bool
     */
    public function hasElement($name)
    {
        return $this->_structure->hasElement($name);
    }

    public function isContainer($name)
    {
        return $this->_structure->isContainer($name);
    }

    /**
     * Translate layout node
     *
     * @param Varien_Simplexml_Element $node
     * @param array $args
     **/
    protected function _translateLayoutNode($node, &$args)
    {
        if (isset($node['translate'])) {
            $items = explode(' ', (string)$node['translate']);
            foreach ($items as $arg) {
                if (isset($node['module'])) {
                    $args[$arg] = Mage::helper($node['module'])->__($args[$arg]);
                } else {
                    $args[$arg] = Mage::helper('Mage_Core_Helper_Data')->__($args[$arg]);
                }
            }
        }
    }

    /**
     * Save block in blocks registry
     *
     * @param string $name
     * @param Mage_Core_Block_abstract $block
     * @return Mage_Core_Model_Layout
     */
    public function setBlock($name, $block)
    {
        $this->_blocks[$name] = $block;
        return $this;
    }

    /**
     * Remove block from registry
     *
     * @param $name
     * @return Mage_Core_Model_Layout
     */
    public function unsetElement($name)
    {
        if (isset($this->_blocks[$name])) {
            $this->_blocks[$name] = null;
            unset($this->_blocks[$name]);
        }
        $this->_structure->unsetElement($name);

        return $this;
    }

    /**
     * Block Factory
     *
     * @param     string $type
     * @param     string $origName
     * @param     array $attributes
     * @return    Mage_Core_Block_Abstract
     */
    public function createBlock($type, $origName='', array $attributes = array())
    {
        $name = ('.' === $origName{0}) ? '' : $origName;
        $name = $this->_structure->insertBlock('', $name);
        $block = $this->_createBlock($type, $name, $attributes);
        $this->_updateAnonymousBlock($block, $origName, $name);
        return $block;
    }

    /**
     * Creates block and add to layout
     *
     * @param $type
     * @param string $name
     * @param array $attributes
     * @return mixed
     */
    protected function _createBlock($type, $name='', array $attributes = array())
    {
        $block = $this->_getBlockInstance($type, $attributes);

        $block->setType($type);
        $block->setNameInLayout($name);
        $block->addData($attributes);
        $block->setLayout($this);

        $this->_blocks[$name] = $block;
        Mage::dispatchEvent('core_layout_block_create_after', array('block'=>$block));
        return $this->_blocks[$name];
    }

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @param string $origName
     * @param string $parent
     * @param string $alias
     * @param bool $after
     * @param string $sibling
     * @return Mage_Core_Block_Abstract
     */
    public function addBlock($block, $origName = '', $parent = '', $alias = '', $after = true, $sibling = '')
    {
        $name = ('.' === $origName{0}) ? '' : $origName;
        if (is_string($block)) {
            $name = $this->_structure->insertBlock($parent, $name, $alias, $after, $sibling);
        } elseif (empty($name)) {
            $name = $block->getNameInLayout();
        }
        $block = $this->_createBlock($block, $name);
        $this->_updateAnonymousBlock($block, $origName, $name);
        $block->setLayout($this);
        return $block;
    }

    /**
     * Mark block as anonymous depending on its name
     *
     * @param Mage_Core_Block_Abstract $block
     * @param $origName
     * @param $name
     * @return mixed
     */
    protected function _updateAnonymousBlock(Mage_Core_Block_Abstract $block, $origName, $name)
    {
        if (!preg_match('/^' . Mage_Core_Model_Layout_Structure::TMP_NAME_PREFIX . '/', $name)) {
            return;
        }
        $block->setIsAnonymous(true);
        if (!empty($origName)) {
            $block->setAnonSuffix(substr($origName, 1));
        }
    }

    /**
     * Append block to the structure
     *
     * Check if block exists in layout
     *
     * @param string $parentName
     * @param string|Mage_Core_Block_Abstract $block
     * @return bool
     * @throws Magento_Exception
     */
    public function appendBlock($parentName, $block)
    {
        if (is_string($block)) {
            $block = $this->getBlock($block);
        }
        if (!($block instanceof Mage_Core_Block_Abstract)) {
            return false;
        }
        $this->insertBlock($parentName, $block->getNameInLayout());

        return true;
    }

    /**
     * Rename element in layout and layout structure
     *
     * @param string $oldName
     * @param string $newName
     * @return bool;
     */
    public function renameElement($oldName, $newName)
    {
        $renamed = false;
        if (isset($this->_blocks[$oldName])) {
            $block = $this->_blocks[$oldName];
            $this->_blocks[$oldName] = null;
            unset($this->_blocks[$oldName]);
            $this->_blocks[$newName] = $block;
            $renamed = true;
        }
        return $this->_structure->setElementAttribute($oldName, 'name', $newName) || $renamed;
    }

    /**
     * Create block object instance based on block type
     *
     * @param string $block
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    protected function _getBlockInstance($block, array $attributes=array())
    {
        if ($block && is_string($block)) {
            $block = Mage::getConfig()->getBlockClassName($block);
            if (Magento_Autoload::getInstance()->classExists($block)) {
                $block = new $block($attributes);
            }
        }
        if (!$block instanceof Mage_Core_Block_Abstract) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Invalid block type: %s', $block));
        }
        return $block;
    }


    /**
     * Retrieve all blocks from registry as array
     *
     * @return array
     */
    public function getAllBlocks()
    {
        return $this->_blocks;
    }

    /**
     * Get block object by name
     *
     * @param string $name
     * @return Mage_Core_Block_Abstract
     */
    public function getBlock($name)
    {
        if (isset($this->_blocks[$name])) {
            return $this->_blocks[$name];
        } else {
            return false;
        }
    }

    public function getParentName($childName)
    {
        return $this->_structure->getParentName($childName);
    }

    /**
     * Get element alias by name
     *
     * @param $name
     * @return string
     */
    public function getElementAlias($name)
    {
        return $this->_structure->getElementAlias($name);
    }

    /**
     * Add an element to output
     *
     * @param string $name
     * @return Mage_Core_Model_Layout
     */
    public function addOutputElement($name)
    {
        $this->_output[$name] = $name;
        return $this;
    }

    /**
     * Remove an element from output
     *
     * @param $name
     */
    public function removeOutputElement($name)
    {
        if (false !== ($key = array_search($name, $this->_output))) {
            unset($this->_output[$key]);
        }
    }

    /**
     * Get all blocks marked for output
     *
     * @return string
     */
    public function getOutput()
    {
        $out = '';
        foreach ($this->_output as $name) {
            $out .= $this->renderElement($name);
        }

        return $out;
    }

    /**
     * Retrieve messages block
     *
     * @return Mage_Core_Block_Messages
     */
    public function getMessagesBlock()
    {
        $block = $this->getBlock('messages');
        if ($block) {
            return $block;
        }
        return $this->createBlock('Mage_Core_Block_Messages', 'messages');
    }

    /**
     * Enter description here...
     *
     * @param string $type
     * @return Mage_Core_Helper_Abstract
     */
    public function getBlockSingleton($type)
    {
        if (!isset($this->_helpers[$type])) {
            $className = Mage::getConfig()->getBlockClassName($type);
            if (!$className) {
                Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Invalid block type: %s', $type));
            }

            $helper = new $className();
            if ($helper) {
                if ($helper instanceof Mage_Core_Block_Abstract) {
                    $helper->setLayout($this);
                }
                $this->_helpers[$type] = $helper;
            }
        }
        return $this->_helpers[$type];
    }

    /**
     * Retrieve helper object
     *
     * @param   string $name
     * @return  Mage_Core_Helper_Abstract
     */
    public function helper($name)
    {
        $helper = Mage::helper($name);
        if (!$helper) {
            return false;
        }
        return $helper->setLayout($this);
    }

    /**
     * Lookup module name for translation from current specified layout node
     *
     * Priorities:
     * 1) "module" attribute in the element
     * 2) "module" attribute in any ancestor element
     * 3) layout handle name - first 1 or 2 parts (namespace is determined automatically)
     *
     * @param Varien_Simplexml_Element $node
     * @return string
     */
    public static function findTranslationModuleName(Varien_Simplexml_Element $node)
    {
        // Commented out code uses not yet implemented functionality.
        $result = (string) $node->getAttribute('module');
        if ($result) {
            //return Mage::getConfig()->getModuleConfig($result) ? $result : 'core';
            return $result;
        }
        foreach (array_reverse($node->xpath('ancestor::*[@module]')) as $element) {
            $result = (string) $element->getAttribute('module');
            if ($result) {
                //return Mage::getConfig()->getModuleConfig($result) ? $result : 'core';
                return $result;
            }
        }
        foreach ($node->xpath('ancestor-or-self::*[last()-1]') as $handle) {
            $name = Mage::getConfig()->determineOmittedNamespace($handle->getName(), true);
            if ($name) {
                //return Mage::getConfig()->getModuleConfig($name) ? $name : 'core';
                return $name;
            }
        }
        return 'Mage_Core';
    }

    /**
     * Gets Layout Structure model
     *
     * @return Mage_Core_Model_Layout_Structure
     */
    public function getStructure()
    {
        return $this->_structure;
    }

    /**
     * Remove block from the list if added container with the same name
     *
     * @param $name
     * @return bool
     */
    protected function _filterBlocksList($name)
    {
        if (isset($this->_blocks[$name]) && !$this->_structure->isBlock($name)) {
            unset($this->_blocks[$name]);
            return true;
        }
        return false;
    }

}
