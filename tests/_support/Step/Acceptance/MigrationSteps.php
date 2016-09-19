<?php

namespace Step\Acceptance;

use Page\MigrationPage;
use Page\ProfessionalSpamFilterPage;

class MigrationSteps extends CommonSteps
{
    public function checkMigrationPageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check migration page layout --- \n");
        $I->see(MigrationPage::TITLE, "//h3");
        $I->see(MigrationPage::DESCRIPTION_A);
        $I->see(MigrationPage::DESCRIPTION_B);

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $I->see('Current username');
        $I->seeElement(MigrationPage::CURRENT_USERNAME);
        $I->see('New username');
        $I->seeElement(MigrationPage::NEW_USERNAME);
        $I->see('New password');
        $I->seeElement(MigrationPage::NEW_PASSWORD);
        $I->waitForText('I am sure I want to migrate all protected domains on this server to this new user.');
        $I->seeElement(MigrationPage::CONFIRM_INPUT);
        $I->seeElement(MigrationPage::MIGRATE_BTN);
    }

    public function submitMigrationForm()
    {
        $this->click('Migrate');
    }

    public function checkErrorAfterMigrate()
    {
        $this->see('One or more settings are not correctly set.');
    }
}
