<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Backend_Model_Menu extends ArrayIterator
{
    /**
     * @var int
     */
    protected $_sortIndex = 0;

    public function __construct($array = array())
    {
        parent::__construct($array);
    }

    public function next()
    {
        parent::next();
        if ($this->valid() && ($this->current()->isDisabled() || !$this->current()->isAllowed())) {
            $this->next();
        }
    }

    public function rewind()
    {
        parent::rewind();
        if ($this->valid() && (current($this)->isDisabled() || !(current($this)->isAllowed()))) {
            $this->next();
        }
    }

    public function addChild(Mage_Backend_Model_Menu_Item $item, $index = null)
    {
        $index = !is_null($index) ? $index : ($item->hasSortIndex() ? $item->getSortIndex() : $this->_sortIndex++);
        if (!isset($this[$index])) {
            $this->offsetSet($index, $item);
            $item->setParent($this);
        } else {
            $this->addChild($item, $index + 1);
        }
    }

    public function isLast(Mage_Backend_Model_Menu_Item $item)
    {
        return $this->offsetGet($this->count() - 1) == $item;
    }
}
