<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\UpdatePage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\UpdateSteps;

class C06UpdateCest
{
    public function _before(UpdateSteps $I)
    {
        $I->login();
    }

    public function _after(UpdateSteps $I)
    {
    }

    public function verifyUpdatePage(UpdateSteps $I)
    {
        $I->goToPage(ProfessionalSpamFilterPage::UPDATE_BTN, UpdatePage::TITLE);
        $I->checkUpdatePageLayout();
        $I->submitUpgradeForm();
        $I->checkNoticeAfterUpgrade();

        // still need to perform proper upgrade
    }
}
