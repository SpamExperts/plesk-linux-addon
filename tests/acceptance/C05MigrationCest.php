<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\MigrationPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\MigrationSteps;

class C05MigrationCest
{
    /**
     * Function called before each test for setup
     */
    public function _before(MigrationSteps $I)
    {
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(MigrationSteps $I)
    {
    }

    /**
     * Function called when a test has failed
     */
    public function _failed(MigrationSteps $I)
    {
        $this->_after($I);
    }


    public function verifyMigrationPage(MigrationSteps $I)
    {
        // Go to the "Migration page"
        $I->goToPage(ProfessionalSpamFilterPage::MIGRATION_BTN, MigrationPage::TITLE);

        // Check migration page layout
        $I->checkMigrationPageLayout();

        // Submit migration form
        $I->submitMigrationForm();

        // Check migration result
        $I->checkErrorAfterMigrate();

        //TODO still need to add actual new account
    }
}
