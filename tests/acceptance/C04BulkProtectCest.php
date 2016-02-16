<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Pages\BulkprotectPage;
use Pages\ProfessionalSpamFilterPage;
use Step\Acceptance\BulkProtectSteps;

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
        $I->goToPage(ProfessionalSpamFilterPage::BULKPROTECT_BTN, BulkprotectPage::TITLE);
        $I->checkBulkProtectPageLayout();
        $I->checkLastExecutionInfo();
        $I->submitBulkprotectForm();
        $I->checkBulkprotectRunning();
        $I->checkBulkprotectRanSuccessfully();

        //need to modify tests after a domain is added and then run bulk again
    }
}
