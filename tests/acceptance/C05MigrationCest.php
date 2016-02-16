<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Pages\MigrationPage;
use Pages\ProfessionalSpamFilterPage;
use Step\Acceptance\MigrationSteps;

class C05MigrationCest
{
    public function _before(MigrationSteps $I)
    {
        $I->login();
    }

    public function _after(MigrationSteps $I)
    {
    }

    public function verifyMigrationPage(MigrationSteps $I)
    {
        $I->goToPage(ProfessionalSpamFilterPage::MIGRATION_BTN, MigrationPage::TITLE);
        $I->checkMigrationPageLayout();
        $I->submitMigrationForm();
        $I->checkErrorAfterMigrate();

        // still need to add actual new account
    }
}
