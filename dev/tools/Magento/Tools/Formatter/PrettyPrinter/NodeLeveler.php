<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */

namespace Magento\Tools\Formatter\PrettyPrinter;

use Magento\Tools\Formatter\Tree\TreeNode;

class NodeLeveler extends LevelNodeVisitor
{
    const MAX_LINE_LENGTH = 80;

    /**
     * This member holds what is being used as a prefix to the line (i.e. 4 spaces).
     */
    const PREFIX = '    ';

    /**
     * This method is called when first visiting a node.
     * @param TreeNode $treeNode
     */
    public function nodeEntry(TreeNode $treeNode)
    {
        parent::nodeEntry($treeNode);
        // get the data from the node
        $lineData = $treeNode->getData();
        $this->processLine($lineData, 0, $treeNode);
    }

    /**
     * This method processes the current line data for the passed in break level.
     * @param Line $line
     * @param int $level
     * @param TreeNode $treeNode
     */
    protected function processLine($line, $level, TreeNode $treeNode)
    {
        // split the lines at the current level to check for length
        $currentLines = $line->splitLine($level);
        // determine if all is good
        $valid = true;
        foreach ($currentLines as $currentLine) {
            $lineText = $currentLine[Line::ATTRIBUTE_LINE];
            if (self::MAX_LINE_LENGTH < strlen($lineText) + $this->level * strlen(self::PREFIX)) {
                $valid = false;
                break;
            }
        }
        // if valid, then add any extra lines
        if ($valid) {
            // only need to change things if resolved line spans multiple lines
            if (count($currentLines) > 1) {
                $this->splitNode($line, $currentLines, $treeNode);
            } else {
                $line->setTokens($currentLines[0]);
            }
        } else {
            // try a higher level split
            $this->processLine($line, $level + 1, $treeNode); // TODO protect against infinite loop
        }
    }

    /**
     * This method takes the current lines and splits them around the current node.
     * @param Line $line
     * @param array $currentLines
     * @param TreeNode $treeNode
     */
    protected function splitNode(Line $line, array $currentLines, TreeNode $treeNode)
    {
        // save off any child nodes of the current node
        $originalChildren = $treeNode->getChildren();
        // split the lines based on resolved lines
        $lastLineBreak = null;
        $lastNode = $treeNode;
        foreach ($currentLines as $index => $currentLine) {
            $lineBreak = $currentLine[Line::ATTRIBUTE_TERMINATOR];
            // replace the existing data if on the first index
            if ($index == 0) {
                $line->setTokens($currentLine);
            } else {
                // determine the indentation based on the type of terminator on the previous line
                if ($lastLineBreak->isNextLineIndented()) {
                    $treeNode->addChild(new TreeNode(new Line($currentLine)));
                } else {
                    $lastNode = $treeNode->addSibling(new TreeNode(new Line($currentLine)));
                }
            }
            // save off the current line break
            $lastLineBreak = $lineBreak;
        }
        // copy the original children if there is a new last node based on the line split
        if (null !== $originalChildren && $lastNode !== $treeNode) {
            foreach ($originalChildren as $originalChild) {
                $lastNode->addChild($originalChild);
            }
        }
    }
}
