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
    public function _before(BulkProtectSteps $I)
    {
        $I->login();
    }

    public function _after(BulkProtectSteps $I)
    {
    }

    public function verifyBulkProtectPage(BulkProtectSteps $I)
    {
        $I->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH) => false,
        ));

        $I->removeAllDomains();
        $account = $I->addNewSubscription();
        $I->wait(120);

        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);
        $I->checkBulkProtectPageLayout();
        $I->checkLastExecutionInfo();
        $I->submitBulkprotectForm();
        $I->checkBulkprotectRunning();
        $I->checkBulkprotectRanSuccessfully();
        $I->see('Domain has been added', '#resultdomainstatus');

        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);
        $I->submitBulkprotectForm();
        $I->checkBulkprotectRanSuccessfully();
        $I->see('Skipped: Domain already exists', '#resultdomainstatus');

        $I->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_CSS, ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH) => true,
        ));

        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);
        $I->checkLastExecutionInfo();
        $I->submitBulkprotectForm();
        $I->checkBulkprotectRanSuccessfully();
        $I->see('Route & MX have been updated', '#resultdomainstatus');
    }
}
