<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Formatter\PrettyPrinter\Operator;


use Magento\Tools\Formatter\PrettyPrinter\Line;
use Magento\Tools\Formatter\Tree\TreeNode;

abstract class AbstractAssignmentOperator extends AbstractInfixOperator
{
    public function left()
    {
        return $this->node->var;
    }
    public function right()
    {
        return $this->node->expr;
    }
    /**
     * We override this from the base class as Assignment operators should not have the conditional line break
     * like the other infix operators.
     */
    protected function addOperatorToLine(TreeNode $treeNode)
    {
        /** @var Line $line */
        $line = $treeNode->getData()->line;
        $line->add(' ')
            ->add($this->operator())
            ->add(' ');
    }
    /**
     * Most Assignment operators have an associativity of 1
     *
     * @return int
     */
    public function associativity()
    {
        return 1;
    }
    /**
     * Most Assignment operators have an associativity of 15
     *
     * @return int
     */
    public function precedence()
    {
        return 15;
    }
}
