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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Block_Profiler extends Mage_Core_Block_Abstract
{
    public function toHtml()
    {
		if (!$this->_beforeToHtml()) {
			return '';
		}

    	if (!Mage::getStoreConfig('dev/debug/profiler')) {
    		return '';
    	}
    	
        $timers = Varien_Profiler::getTimers();

        #$out = '<div style="position:fixed;bottom:5px;right:5px;opacity:.1;background:white" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.1">';
        #$out = '<div style="opacity:.1" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.1">';
        $out = "<a href=\"javascript:void(0)\" onclick=\"$('profiler_section').style.display=$('profiler_section').style.display==''?'none':''\">[profiler]</a>";
        $out .= '<div id="profiler_section" style="background:white; display:none">';
        $out .= '<pre>Memory usage: real: '.memory_get_usage(true).', emalloc: '.memory_get_usage().'</pre>';
        $out .= '<table border=1 cellspacing=0 cellpadding=2 style="width:auto">';
        $out .= '<tr><th>Code Profiler</th><th>Time</th><th>Cnt</th><th>RealMem</th><th>Emalloc</th></tr>';
        foreach ($timers as $name=>$timer) {
            $sum = Varien_Profiler::fetch($name,'sum');
            if ($sum<.0005) {
                continue;
            }
            $out .= '<tr><td>'.$name.'</td><td>'
            	.number_format(Varien_Profiler::fetch($name,'sum'),4).'</td><td>'
            	.Varien_Profiler::fetch($name,'count').'</td><td>'
            	.number_format(Varien_Profiler::fetch($name,'realmem')).'</td><td>'
            	.number_format(Varien_Profiler::fetch($name,'emalloc')).'</td></tr>'
            	.'</td></tr>';
        }
        $out .= '</table>';
        $out .= '<pre>';
        $out .= print_r(Varien_Profiler::getSqlProfiler(Mage::getSingleton('core/resource')->getConnection('core_write')), 1);
        $out .= '</pre>';
        $out .= '</div>';
        return $out;
    }
}