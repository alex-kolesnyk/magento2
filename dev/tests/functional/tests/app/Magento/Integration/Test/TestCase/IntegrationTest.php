<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Integration\Test\TestCase;

use Mtf\Factory\Factory;
use Magento\Integration\Test\Repository\Integration as IntegrationRepository;
use Magento\Integration\Test\Fixture\Integration as IntegrationFixture;

/**
 * Integration functionality verification
 */
class IntegrationTest extends \Mtf\TestCase\Functional
{
    /**
     * Login into backend area before tests.
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Create new Integration with valid data
     *
     * @param string $integrationDataSet
     *
     * @dataProvider integrationDataProvider
     *
     * @ZephyrId MAGETWO-16694
     */
    public function testCreateIntegration($integrationDataSet)
    {
        //Data
        $integrationFixture = Factory::getFixtureFactory()->getMagentoIntegrationIntegration();
        $integrationFixture->switchData($integrationDataSet);
        //Steps
        $newIntegrationPage = Factory::getPageFactory()->getAdminIntegrationNew();
        $newIntegrationPage->open();
        $newIntegrationPage->getIntegrationFormBlock()->fill($integrationFixture)->save($integrationFixture);
        //Verification
        $this->_checkSaveSuccessMessage();
        $this->_ensureMatchingIntegrationExists($integrationFixture);
    }

    /**
     * Integration data
     *
     * @return array
     */
    public function integrationDataProvider()
    {
        return array(
            array(IntegrationRepository::INTEGRATION_MANUAL),
            array(IntegrationRepository::INTEGRATION_OAUTH)
        );
    }

    /**
     * Edit Integration
     *
     * @param IntegrationFixture $integrationFixture injectable
     *
     * @ZephyrId MAGETWO-16759
     */
    public function testEditIntegration(IntegrationFixture $integrationFixture)
    {
        //Precondition
        $integrationFixture->switchData(IntegrationRepository::INTEGRATION_OAUTH);
        $integrationFixture->persist();
        //Steps
        $editIntegrationPage = Factory::getPageFactory()->getAdminIntegrationEdit();
        $this->_openByName($integrationFixture->getName());
        $editForm = $editIntegrationPage->getIntegrationFormBlock();
        $integrationFixture->switchData(IntegrationRepository::INTEGRATION_MANUAL);
        $editForm->update($integrationFixture)->save($integrationFixture);
        //Verification
        $this->_checkSaveSuccessMessage();
        $this->_ensureMatchingIntegrationExists($integrationFixture);
    }

    /**
     * Search Integration in the Integration's grid
     *
     * @param IntegrationFixture $integrationFixture injectable
     *
     * @ZephyrId MAGETWO-16721
     */
    public function testSearchIntegration(IntegrationFixture $integrationFixture)
    {
        //Preconditions
        $integrationFixture->switchData(IntegrationRepository::INTEGRATION_OAUTH);
        $integrationFixture->persist();
        //Steps
        Factory::getPageFactory()->getAdminIntegrationEdit();
        $this->_openByName($integrationFixture->getName());
    }

    /**
     * Reset data in the New Integration form
     *
     * @param IntegrationFixture $integrationFixture injectable
     *
     * @ZephyrId MAGETWO-16822
     */
    public function testResetData(IntegrationFixture $integrationFixture)
    {
        //Data
        $integrationFixture->switchData(IntegrationRepository::INTEGRATION_OAUTH);
        $originalFixture = clone $integrationFixture;
        //Preconditions
        $integrationFixture->persist();
        //Steps
        $editIntegrationPage = Factory::getPageFactory()->getAdminIntegrationEdit();
        $this->_openByName($integrationFixture->getName());
        $editForm = $editIntegrationPage->getIntegrationFormBlock();
        $integrationFixture->switchData(IntegrationRepository::INTEGRATION_MANUAL);
        $editForm->update($integrationFixture)->reset($integrationFixture);
        //Verification
        $editForm = $editIntegrationPage->getIntegrationFormBlock();
        $editForm->reinitRootElement();
        $editForm->verify($originalFixture);
    }

    /**
     * Navigate to the Integration page from Edit Integration page
     *
     * @param IntegrationFixture $integrationFixture injectable
     *
     * @ZephyrId MAGETWO-16823
     */
    public function testNavigation(IntegrationFixture $integrationFixture)
    {
        //Preconditions
        $integrationFixture->persist();
        //Steps
        $editIntegrationPage = Factory::getPageFactory()->getAdminIntegrationEdit();
        $this->_openByName($integrationFixture->getName());
        $editIntegrationPage->getIntegrationFormBlock()->back();
        //Verification
        $this->assertTrue(
            Factory::getPageFactory()->getAdminIntegration()->getGridBlock()->isVisible(),
            'Integration grid is not visible'
        );
    }

    /**
     * Check success message after integration save.
     */
    protected function _checkSaveSuccessMessage()
    {
        $this->assertTrue(
            Factory::getPageFactory()->getAdminIntegration()->getMessageBlock()->waitForSuccessMessage(),
            'Integration save success message was not found.'
        );
    }

    /**
     * Check if integration exists
     *
     * @param IntegrationFixture $integrationFixture
     */
    protected function _ensureMatchingIntegrationExists(IntegrationFixture $integrationFixture)
    {
        $editIntegrationPage = Factory::getPageFactory()->getAdminIntegrationEdit();
        $this->_openByName($integrationFixture->getName());
        $editIntegrationPage->getIntegrationFormBlock()->verify($integrationFixture);
    }

    /**
     * Open existing integration page by integration name.
     *
     * @param string $integrationName
     */
    protected function _openByName($integrationName)
    {
        $integrationGridPage = Factory::getPageFactory()->getAdminIntegration();
        $integrationGridPage->open();
        $integrationGridPage->getGridBlock()->searchAndOpen(array('name' => $integrationName));
    }
}
