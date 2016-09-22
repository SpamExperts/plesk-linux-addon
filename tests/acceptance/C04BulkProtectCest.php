<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\BulkprotectPage;
use Page\ConfigurationPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\CommonSteps;
use Step\Acceptance\BulkProtectSteps;
use Codeception\Util\Locator;

class C04BulkProtectCest
{
    /**
     * Function called before each test for setup
     */
    public function _before(BulkProtectSteps $I)
    {
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(BulkProtectSteps $I)
    {
    }

    /**
     * Function called when a test has failed
     */
    public function _failed(BulkProtectSteps $I)
    {
        $this->_after($I);
    }

    public function verifyBulkProtectPage(BulkProtectSteps $I)
    {
        // Go to the "Configuration" page
        $I->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);

        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH) => false,
        ));

        // Remove all created domains
        $I->removeAllDomains();

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Go to "Bulkprotect" page
        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);

        // Check the "Bulkprotect" page layout
        $I->checkBulkProtectPageLayout();

        // Check last execution date of bulkprotect
        $I->checkLastExecutionInfo();

        // Run the Bulkprotect task
        $I->submitBulkprotectForm();

        // Check if bulkprotect task is running
        $I->checkBulkprotectRunning();

        // Check if bulkprotect task successfuly finished
        $I->checkBulkprotectRanSuccessfully();


        // Check if subscription domain has been aded
        $I->see('Domain has been added', '#resultdomainstatus');

        // Go to "Bulkprotect" page
        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);

        // Run the Bulkprotect task
        $I->submitBulkprotectForm();

        // Check if the bulkprotect task was succesful
        $I->checkBulkprotectRanSuccessfully();

        // Check if domain was skipped because it was allready protected
        $I->see('Skipped: Domain already exists', '#resultdomainstatus');

        // Go to the "Configuration" page
        $I->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);

        // Set configuration options needed
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_CSS, ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH) => true,
        ));

        // Go to bulkprotect page
        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);

        // Cheeck last execution date of bulkprotect
        $I->checkLastExecutionInfo();

        // Run Bulkprotect task
        $I->submitBulkprotectForm();

        // Check if bulkprotect task successfuly finished
        $I->checkBulkprotectRanSuccessfully();

        // Check if Route & MX have been updated
        $I->see('Route & MX have been updated', '#resultdomainstatus');
    }
}
