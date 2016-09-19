<?php

namespace Step\Acceptance;

use Pages\BulkprotectPage;
use Pages\PleskLinuxClientPage;
use Pages\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class BulkProtectSteps extends CommonSteps
{
    public function checkBulkProtectPageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check bulk protect page layout--- \n");
        $I->see(BulkprotectPage::TITLE);
        $I->see(BulkprotectPage::DESCRIPTION_A);
        $I->see(BulkprotectPage::DESCRIPTION_B);

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $I->seeElement(Locator::combine(BulkprotectPage::EXECUTE_BULKPROTECT_BTN_XPATH, BulkprotectPage::EXECUTE_BULKPROTECT_BTN_CSS));
    }

    public function checkLastExecutionInfo()
    {
        $this->amGoingTo("\n\n --- Check last execution--- \n");
        $this->see('Bulk protect has been executed last at: ');
    }

    public function submitBulkprotectForm()
    {
        $this->click(Locator::combine(BulkprotectPage::EXECUTE_BULKPROTECT_BTN_XPATH, BulkprotectPage::EXECUTE_BULKPROTECT_BTN_CSS));
    }

    public function checkBulkprotectRunning()
    {
        $I = $this;
        $I->see("BULK PROTECTING, DO NOT RELOAD THIS PAGE!");
        $I->see("Results will be shown here when the process has finished");
        $I->see("It might take a while, especially if you have many domains or a slow connection.");
        $I->see("Please be patient while we're running the bulk protector");
    }

    public function checkBulkprotectRanSuccessfully()
    {
        $I = $this;
        $I->waitForText("Bulkprotect", 200);
        $I->waitForElement(Locator::combine(BulkprotectPage::EXECUTE_BULKPROTECT_WARNING_XPATH, BulkprotectPage::EXECUTE_BULKPROTECT_WARNING_CSS), 200); 
        $I->waitForText("Bulkprotect has finished", 200);
        $I->see("The bulkprotect process has finished its work. Please see the tables below for the results.");
    }

    public function removeAllDomains()
    {
        $I = $this;
        $I->switchToLeftFrame();
        $I->click(Locator::combine(PleskLinuxClientPage::CLIENT_SUBSCRIPTIONS_XPATH, PleskLinuxClientPage::CLIENT_SUBSCRIPTIONS_CSS));
        $I->switchToWorkFrame();

        if (!$I->getElementsCount("//td[contains(@class,'select')]")) {
            return;
        }

        $I->waitForElementVisible(Locator::combine(PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_XPATH, PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_CSS), 10);
        $I->click(Locator::combine(PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_XPATH, PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_CSS));

        $I->waitForElementVisible(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_LIST_TABLE_XPATH, PleskLinuxClientPage::SUBSCRIPTION_LIST_TABLE_CSS), 10);
        $I->waitForElementVisible(PleskLinuxClientPage::CLIENT_SELECT_ALL_SUBSCRIPTIONS_XPATH, 10);
        $I->click(Locator::combine(PleskLinuxClientPage::CLIENT_SELECT_ALL_SUBSCRIPTIONS_XPATH, PleskLinuxClientPage::CLIENT_SELECT_ALL_SUBSCRIPTIONS_CSS));

        $I->waitForElementVisible(Locator::combine(PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_XPATH, PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_CSS), 10);
        $I->click(Locator::combine(PleskLinuxClientPage::CLIENT_REMOVE_SUBSCRIPTION_BUTTON_XPATH, PleskLinuxClientPage::CLIENT_REMOVE_SUBSCRIPTION_BUTTON_CSS));
        $I->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        $I->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 10);
        $I->wait(1);
        $I->click(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS));
        $I->waitForElementNotVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 10);

        $I->waitForText("Information: Selected subscriptions were removed.", 200);
    }
}
