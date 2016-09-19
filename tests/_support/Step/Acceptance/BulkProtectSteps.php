<?php

namespace Step\Acceptance;

use Codeception\Util\Locator;
use Page\BulkprotectPage;
use Page\PleskLinuxClientPage;
use Page\ProfessionalSpamFilterPage;

class BulkProtectSteps extends CommonSteps
{
    public function checkBulkProtectPageLayout()
    {
        $this->amGoingTo("\n\n --- Check bulk protect page layout--- \n");
        $this->see(BulkprotectPage::TITLE);
        $this->see(BulkprotectPage::DESCRIPTION_A);
        $this->see(BulkprotectPage::DESCRIPTION_B);

        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $this->seeElement(Locator::combine(BulkprotectPage::EXECUTE_BULKPROTECT_BTN_XPATH, BulkprotectPage::EXECUTE_BULKPROTECT_BTN_CSS));
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
        $this->see("BULK PROTECTING, DO NOT RELOAD THIS PAGE!");
        $this->see("Results will be shown here when the process has finished");
        $this->see("It might take a while, especially if you have many domains or a slow connection.");
        $this->see("Please be patient while we're running the bulk protector");
    }

    public function checkBulkprotectRanSuccessfully()
    {
        $this->waitForText("Bulkprotect", 200);
        $this->waitForElement(Locator::combine(BulkprotectPage::EXECUTE_BULKPROTECT_WARNING_XPATH, BulkprotectPage::EXECUTE_BULKPROTECT_WARNING_CSS), 200); 
        $this->waitForText("Bulkprotect has finished", 200);
        $this->see("The bulkprotect process has finished its work. Please see the tables below for the results.");
    }

    public function removeAllDomains()
    {
        $this->switchToLeftFrame();
        $this->click(Locator::combine(PleskLinuxClientPage::CLIENT_SUBSCRIPTIONS_XPATH, PleskLinuxClientPage::CLIENT_SUBSCRIPTIONS_CSS));
        $this->switchToWorkFrame();

        if (!$this->getElementsCount("//td[contains(@class,'select')]")) {
            return;
        }

        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_XPATH, PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_CSS), 10);
        $this->click(Locator::combine(PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_XPATH, PleskLinuxClientPage::CLIENT_ALL_ENTRIES_BUTTON_CSS));

        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_LIST_TABLE_XPATH, PleskLinuxClientPage::SUBSCRIPTION_LIST_TABLE_CSS), 10);
        $this->waitForElementVisible(PleskLinuxClientPage::CLIENT_SELECT_ALL_SUBSCRIPTIONS_XPATH, 10);
        $this->click(Locator::combine(PleskLinuxClientPage::CLIENT_SELECT_ALL_SUBSCRIPTIONS_XPATH, PleskLinuxClientPage::CLIENT_SELECT_ALL_SUBSCRIPTIONS_CSS));

        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_XPATH, PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_CSS), 10);
        $this->click(Locator::combine(PleskLinuxClientPage::CLIENT_REMOVE_SUBSCRIPTION_BUTTON_XPATH, PleskLinuxClientPage::CLIENT_REMOVE_SUBSCRIPTION_BUTTON_CSS));
        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 10);
        $this->wait(1);
        $this->click(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS));
        $this->waitForElementNotVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 10);

        $this->waitForText("Information: Selected subscriptions were removed.", 200);
    }
}
