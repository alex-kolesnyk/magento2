<?php

class Mage_Sales_Model_Email_Template extends Mage_Core_Model_Email_Template 
{
	public function getInclude($template, array $variables)
	{
		$filename = Mage::getDesign()->getTemplateFilename($template);
		if (!$filename) {
			return '';
		}
		extract($variables);
		ob_start();
		include $filename;
		return ob_get_clean();
	}
}