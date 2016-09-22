<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\UpdatePage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\UpdateSteps;

class C06UpdateCest
{
    /**
     * Function called before each test for setup
     */
    public function _before(UpdateSteps $I)
    {
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(UpdateSteps $I)
    {
    }

    /**
     * Function called when a test has failed
     */
    public function _failed(UpdateSteps $I)
    {
        $this->_after($I);
    }

    public function verifyUpdatePage(UpdateSteps $I)
    {
        // Go to the "Update" page
        $I->goToPage(ProfessionalSpamFilterPage::UPDATE_BTN, UpdatePage::TITLE);

        // Check "Update" test layout
        $I->checkUpdatePageLayout();

        // Submit upgrade form
        $I->submitUpgradeForm();

        // Check upgrade result
        $I->checkNoticeAfterUpgrade();

        //TODO still need to perform proper upgrade
    }
}
