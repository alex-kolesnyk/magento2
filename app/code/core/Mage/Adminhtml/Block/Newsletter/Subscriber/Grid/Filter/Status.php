<?php
/**
 * Adminhtml newsletter subscribers grid website filter
 *
 * @package    Mage
 * @subpackage Adminhtml
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 * @license    http://www.opensource.org/licenses/osl-3.0.php
 * @author	   Ivan Chepurnyi <mitch@varien.com>
 */

class Mage_Adminhtml_Block_Newsletter_Subscriber_Grid_Filter_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select 
{
	protected static $_statuses = array(
		null												  => null,
		Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => 'Not activated',
		Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => 'Subcribed',
		Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => 'Unsubcribed'
	);
	
	protected function _getOptions() 
	{
		$result = array();
		foreach (self::$_statuses as $code=>$label) {
			$result[] = array('value'=>$code, 'label'=>__($label));
		}
		
		return $result;
	}
	
	
	public function getCondition()
	{
		if(is_null($this->getValue())) {
			return null;
		}
		
		return array('eq'=>$this->getValue());
	}
	
	
}// Class Mage_Adminhtml_Block_Newsletter_Queue_Grid_Filter_Status END