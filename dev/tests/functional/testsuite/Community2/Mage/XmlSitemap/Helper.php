<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_XmlSitemap
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * XML Sitemap Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community2_Mage_XmlSitemap_Helper extends Mage_Selenium_TestCase
{
    /**
     * Generate URL for selected area
     *
     * @param string $uri
     * @return string
     */
    public function getFileUrl($uri)
    {
        $currentAreaBaseUrl = $this->_configHelper->getAreaBaseUrl('frontend');
        return  $currentAreaBaseUrl . $uri;
    }

    /**
     * Get file from admin area
     * Suitable for reports testing
     *
     * @param string $url
     * @return string
     */
    public function getFile($url)
    {
        $cookie = $this->getCookie();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $data = curl_exec($ch);
        $body=substr($data, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        curl_close($ch);
        return $body;
    }
}
