<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Formatter\PrettyPrinter;

class ConditionalLineBreak extends LineBreak
{
    /**
     * This flag indicates if the first in a group of conditionals should be used.
     * @var bool
     */
    protected $firstInGroupRequired = true;

    /**
     * This member holds a 2 dimensional array of breaks to insert. First dimension is the level.
     * Second dimension is the index of the occurrence of the break instance.
     * @var array
     */
    protected $breaks;

    /**
     * This methods constructs a new conditional line break.
     * @param array $breaks Values used to insert conditional line breaks.
     */
    public function __construct(array $breaks)
    {
        $this->breaks = $breaks;
    }

    /**
     * This method returns the current value of the break.
     * @return string
     */
    public function __toString()
    {
        throw new \Exception("This should never be called.");
    }

    /**
     * This method returns the value for the break based on the passed in information.
     * @param int $level Indicator for the level for which the break is being resolved.
     * @param int $index Zero based index of this break occurrence in the line.
     * @param int $total Total number of this break occurrences in the line.
     */
    public function getValue($level, $index, $total)
    {
        // cap the level at the greatest one in the first dimension
        $maxLevel = max(array_keys($this->breaks));
        if ($level > $maxLevel) {
            $level = $maxLevel;
        }
        // cap the index to the greatest one in the second dimension
        $maxIndex = max(array_keys($this->breaks[$level]));
        if ($index > $maxIndex) {
            $index = $maxIndex;
        }
        // return the specified break;
        return $this->breaks[$level][$index];
    }

    /**
     * This method returns if the next line should be indented.
     */
    public function isNextLineIndented()
    {
        throw new \Exception("This should never be called.");
    }
}
