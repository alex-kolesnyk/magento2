<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_FullPageCache
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Url processing helper
 */
class Magento_FullPageCache_Helper_Url
{
    /**
     * @var Magento_Core_Helper_Url
     */
    protected $_urlHelper;

    /**
     * @var Magento_Core_Model_Session
     */
    protected $_coreSession;

    /**
     * @param Magento_Core_Helper_Url $urlHelper
     * @param Magento_Core_Model_Session $coreSession
     */
    public function __construct(
        Magento_Core_Helper_Url $urlHelper,
        Magento_Core_Model_Session $coreSession
    ) {
        $this->_urlHelper = $urlHelper;
        $this->_coreSession = $coreSession;
    }

    /**
     * Retrieve unique marker value
     *
     * @return string
     */
    protected static function _getSidMarker()
    {
        return '{{' . chr(1) . chr(2) . chr(3) . '_SID_MARKER_' . chr(3) . chr(2) . chr(1) . '}}';
    }

    /**
     * Replace all occurrences of session_id with unique marker
     *
     * @param  string $content
     * @return bool
     */
    public function replaceSid(&$content)
    {
        if (!$content) {
            return false;
        }
        $replacementCount = 0;
        $content = str_replace(
            $this->_coreSession->getSessionIdQueryParam() . '=' . $this->_coreSession->getSessionId(),
            $this->_coreSession->getSessionIdQueryParam() . '=' . self::_getSidMarker(),
            $content, $replacementCount);
        return ($replacementCount > 0);
    }

    /**
     * Restore session_id from marker value
     *
     * @param string $content
     * @param string $sidValue
     * @return bool
     */
    public static function restoreSid(&$content, $sidValue)
    {
        if (!$content) {
            return false;
        }
        $replacementCount = 0;
        $content = str_replace(self::_getSidMarker(), $sidValue, $content, $replacementCount);
        return ($replacementCount > 0);
    }

    /**
     * Calculate UENC parameter value and replace it
     *
     * @param string $content
     * @return string
     */
    public function replaceUenc($content)
    {
        $search = '/\/(' . Magento_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED . ')\/[^\/]*\//';
        $replace = '/$1/' . $this->_urlHelper->getEncodedUrl() . '/';
        $content = preg_replace($search, $replace, $content);
        return $content;
    }
}
