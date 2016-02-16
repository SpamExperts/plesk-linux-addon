<?php

namespace Step\Acceptance;

use Pages\BulkprotectPage;
use Pages\ProfessionalSpamFilterPage;

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

        $I->seeElement(BulkprotectPage::EXECUTE_BULKPROTECT_BTN);
    }

    public function checkLastExecutionInfo()
    {
        $this->amGoingTo("\n\n --- Check last execution--- \n");
        $this->see('Bulk protect has been executed last at: ');
    }

    public function submitBulkprotectForm()
    {
        $this->click('Execute bulkprotect');
    }

    public function checkBulkprotectRunning()
    {
        $I = $this;
        $I->see("BULK PROTECTING, DO NOT RELOAD THIS PAGE!");
        $I->see("Results will be shown here when the process has finished");
        $I->see("It might take a while, especially if you have many domains or a slow connection.");
        $I->see("Please be patient while we're running the bulk protector");
        $I->wait(5);
    }

    public function checkBulkprotectRanSuccessfully()
    {
        $I = $this;
        $I->waitForText("Bulkprotect");
        $I->waitForElement(".//*[@id='bulkwarning']/div");
        $I->see("Bulkprotect has finished");
        $I->see("The bulkprotect process has finished its work. Please see the tables below for the results.");
    }

}
