<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\SupportPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\SupportSteps;

class C07SupportCest
{
    /**
     * Function called before each test for setup
     */
    public function _before(SupportSteps $I)
    {
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(SupportSteps $I)
    {
    }

    /**
     * Function called when a test has failed
     */
    public function _failed(SupportSteps $I)
    {
        $this->_after($I);
    }

    public function verifySupportPage(SupportSteps $I)
    {
        // Go to the "Support" page
        $I->goToPage(ProfessionalSpamFilterPage::SUPPORT_BTN, SupportPage::TITLE);

        // Check "Support" page layout
        $I->checkSupportPageLayout();

        // Submit diagnostics form
        $I->submitDiagnosticForm();

        // Check diagnostics result
        $I->checkDiagnostics();
    }
}
