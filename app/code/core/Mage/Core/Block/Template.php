<?php
/**
 * Base html block
 *
 * @package    Mage
 * @subpackage Core
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Core_Block_Template extends Mage_Core_Block_Abstract
{
    /**
     * Enter description here...
     *
     * @var string
     */
    protected $_viewDir = '';

    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_viewVars = array();

    /**
     * Set block template
     *
     * @param     string $templateName
     * @return    Mage_Core_Block_Template
     */
    public function setTemplate($templateName)
    {
        $this->setTemplateName($templateName);
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string|array $key
     * @param mixed $value
     * @return Mage_Core_Block_Template
     */
    public function assign($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->assign($k, $v);
            }
        }
        else {
            $this->_viewVars[$key] = $value;
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string $dir
     * @return Mage_Core_Block_Template
     */
    public function setScriptPath($dir)
    {
        $this->_viewDir = $dir;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string $fileName
     * @return string
     */
    public function fetchView($fileName)
    {
        extract ($this->_viewVars);
        ob_start();
        include $this->_viewDir.DS.$fileName;
        return ob_get_clean();
    }

    /**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        Varien_Profiler::start(__METHOD__);
        #$templatesDir = Mage::getSingleton('core/store')->getDir('template');
		#$this->setScriptPath($templatesDir.DS);

        $this->assign('baseUrl', Mage::getBaseUrl());
        $this->assign('baseSecureUrl', Mage::getBaseUrl(array('_secure'=>true)));
        $this->assign('baseSkinUrl', Mage::getBaseUrl(array('_type'=>'skin')));
        $this->assign('baseJsUrl', Mage::getBaseUrl(array('_type'=>'js')));
        #$this->assign('templatesDir', $templatesDir);
        $this->assign('currentUrl', Mage::registry('controller')->getRequest()->getRequestUri());
        $this->assign('currentBlock', $this);


        $this->setScriptPath(Mage::getBaseDir('design'));
        $params = array('_relative'=>true);
        if ($area = $this->getArea()) {
            $params['_area'] = $area;
        }
        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplateName(), $params);
        $html = $this->fetchView($templateName);
        Varien_Profiler::stop(__METHOD__);

        return $html;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * If returns false html is rendered empty and cache is not saved
     *
     * @return boolean
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * Before assign child block actions
     *
     * @param string $blockName
     */
    protected function _beforeChildToHtml($blockName, $blockObject)
    {
        // before assign child block actions
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function toHtml()
    {
        if ($html = $this->_loadCache()) {
            return $html;
        }

        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!$this->getTemplateName()) {
            return '';
        }

        $html = $this->renderView();
        $this->_saveCache($html);

        return $html;
    }

    /**
     * Enter description here...
     *
     * @param string $tplName
     * @param array $assign
     * @return string
     */
    public function tpl($tplName, array $assign=array())
    {
        $block = $this->getLayout()->createBlock('core/template');
        /* @var $block Mage_Core_Block_Template */
        foreach ($assign as $k=>$v) {
            $block->assign($k, $v);
        }
        return $block->setTemplate("$tplName.phtml")->toHtml();
    }

}