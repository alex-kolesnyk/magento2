<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Poll
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll model
 *
 * @file        Poll.php
 * @author      Alexander Stadnitski (hacki) alexander@varien.com
 */

class Mage_Poll_Model_Poll extends Mage_Core_Model_Abstract
{
    protected $_pollCookieDefaultName = 'poll';

    protected function _construct()
    {
        $this->_init('poll/poll');
    }

    public function resetVotesCount()
    {
        $this->getResource()->resetVotesCount($this);
        return $this;
    }

    public function setVoted($pollId=null)
    {
        $pollId = ( isset($pollId) ) ? $pollId : $this->getId();
        Mage::getSingleton('core/cookie')->set($this->_pollCookieDefaultName . $pollId, $pollId);
        return $this;
    }

    public function isVoted($pollId=null)
    {
        $pollId = ( isset($pollId) ) ? $pollId : $this->getId();
        $cookie = Mage::getSingleton('core/cookie')->get($this->_pollCookieDefaultName . $pollId);
        if( $cookie === false ) {
            return false;
        } else {
            return true;
        }
    }

    public function getRandomId()
    {
        return $this->getResource()->getRandomId($this);
    }

    public function getVotedPollsIds()
    {
        $idsArray = array();
        foreach( $_COOKIE as $cookieName => $cookieValue ) {
            $pattern = "/^" . $this->_pollCookieDefaultName . "([0-9]*?)$/";
            if( preg_match($pattern, $cookieName, $m) ) {
                if( $m[1] != Mage::getSingleton('core/session')->getJustVotedPoll() ) {
                    $idsArray[$m[1]] = $m[1];
                }
            }
        }
        return $idsArray;
    }
}