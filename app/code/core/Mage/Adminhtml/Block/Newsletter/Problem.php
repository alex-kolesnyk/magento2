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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter problem block template.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author	   Ivan Chepurnyi <mitch@varien.com>
 */

class Mage_Adminhtml_Block_Newsletter_Problem extends Mage_Core_Block_Template 
{
	public function __construct() 
	{
		parent::__construct();
		$this->setTemplate('newsletter/problem/list.phtml');
		$collection = Mage::getResourceSingleton('newsletter/problem_collection')
			->addSubscriberInfo()
			->addQueueInfo();
			
		
	}
	
	protected function _initChildren()
	{
		$this->setChild('grid', 
			$this->getLayout()->createBlock('adminhtml/newsletter_problem_grid','newsletter.problem.grid')
		);
		
		$this->setChild('deleteButton', 
			$this->getLayout()->createBlock('adminhtml/widget_button','del.button')
				->setData(
					array(
						'label' => __('Delete Selected Problems'),
						'onclick' => 'problemController.deleteSelected();'
					)
				)
		);
		
		$this->setChild('unsubscribeButton', 
			$this->getLayout()->createBlock('adminhtml/widget_button','unsubscribe.button')
				->setData(
					array(
						'label' => __('Unsubscibe Selected'),
						'onclick' => 'problemController.unsubscribe();'
					)
				)
		);
	}
	
	public function getUnsubscribeButtonHtml() 
	{
		return $this->getChildHtml('unsubscribeButton');
	}
	
	public function getDeleteButtonHtml() 
	{
		return $this->getChildHtml('deleteButton');
	}
	
	public function getShowButtons() 
	{
		return  Mage::getResourceSingleton('newsletter/problem_collection')->getSize() > 0;
	}
	
}// Class Mage_Adminhtml_Block_Newsletter_Problem END
