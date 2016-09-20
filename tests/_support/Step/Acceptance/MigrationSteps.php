<?php

namespace Step\Acceptance;

use Page\MigrationPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

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
        $I->seeElement(Locator::combine(MigrationPage::CURRENT_USERNAME_FIELD_XPATH, MigrationPage::CURRENT_USERNAME_FIELD_CSS));
        $I->see('New username');
        $I->seeElement(Locator::combine(MigrationPage::NEW_USERNAME_FIELD_XPATH, MigrationPage::NEW_USERNAME_FIELD_CSS));
        $I->see('New password');
        $I->seeElement(Locator::combine(MigrationPage::NEW_PASSWORD_FIELD_XPATH, MigrationPage::NEW_PASSWORD_FIELD_CSS));
        $I->waitForText('I am sure I want to migrate all protected domains on this server to this new user.');
        $I->seeElement(Locator::combine(MigrationPage::CONFIRM_INPUT_XPATH, MigrationPage::CONFIRM_INPUT_CSS));
        $I->seeElement(Locator::combine(MigrationPage::MIGRATE_BTN_XPATH, MigrationPage::MIGRATE_BTN_CSS));
    }

    public function submitMigrationForm()
    {
        $this->click(Locator::combine(MigrationPage::MIGRATE_BTN_XPATH, MigrationPage::MIGRATE_BTN_CSS));
    }

    public function checkErrorAfterMigrate()
    {
        $this->see('One or more settings are not correctly set.');
    }
}
