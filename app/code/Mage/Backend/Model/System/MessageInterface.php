<?php
/**
 * Backend system message
 *
 * {license_notice}
 * 
 * @copyright {copyright}
 * @license   {license_link}
 */
interface Mage_Backend_Model_System_MessageInterface
{
    const SEVERITY_CRITICAL = 1;
    const SEVERITY_MAJOR = 2;

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity();

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed();

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText();

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink();

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity();
}
