<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Theme
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

    /**
     * Theme management tests for Backend
     *
     * @package     selenium
     * @subpackage  tests
     * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */
class Enterprise_Mage_Theme_ThemeTest extends Core_Mage_Theme_ThemeTest
{
    /**
     * DataProvider for autogenerated values
     *
     * @return array
     */
    public function prepopulatedValuesDataProvider()
    {
        return array(
            array('theme_version', '0.0.0.1'),
            array('theme_title', 'Copy of Magento Fixed Width'),
        );
    }

    /**
     * DataProvider for needed quantity of CSS links
     *
     * @return array
     */
    public function allThemeCssLinks()
    {
        return array(
            array('framework_files', '5'),
            array('library_files', '2'),
            array('theme_files', '7'),
        );
    }

    /**
     * DataProvider of CSS files' content
     *
     * @return array
     */
    public function allThemeCss()
    {
        return array(
            array('Enterprise_Banner__widgets.css', 'enterprise_banner_widgets'),
            array('Enterprise_CatalogEvent__widgets.css', 'enterprise_catalog_event_widgets'),
            array('Enterprise_Cms__widgets.css', 'enterprise_cms_widgets'),
            array('Magento_Catalog--widgets.css', 'magento_catalog_widget'),
            array('Mage_Oauth--css-oauth-simple.css', 'mage_oauth_css_oauth_simple'),
            array('jquery_jqzoom_css_jquery.jqzoom.css', 'jquery_jqzoom_css'),
            array('mage-calendar.css', 'mage_calendar'),
            array('Enterprise_css_print.css', 'css_print'),
            array('Enterprise_css_styles-ie.css', 'css_style_ie'),
            array('Enterprise_css_styles.css', 'css_style'),
            array('Enterprise_Default_Cms__widgets.css', 'mage_cms_widgets'),
            array('Enterprise_Default_Page__css_tabs.css', 'mage_page_css_tabs'),
            array('Enterprise_Default_Reports__widgets.css', 'mage_reports_widgets'),
            array('Enterprise_Default_Widget__widgets.css', 'mage_widget_widgets'),
        );
    }
}

