<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\SupportPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\SupportSteps;

class C07SupportCest
{
    public function _before(SupportSteps $I)
    {
        $I->login();
    }

    public function _after(SupportSteps $I)
    {
    }

    public function verifySupportPage(SupportSteps $I)
    {
        $I->goToPage(ProfessionalSpamFilterPage::SUPPORT_BTN, SupportPage::TITLE);
        $I->checkSupportPageLayout();
        $I->submitDiagnosticForm();
        $I->checkDiagnostics();
    }
}
