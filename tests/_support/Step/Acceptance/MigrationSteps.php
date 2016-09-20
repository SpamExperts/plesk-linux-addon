<?php

namespace Step\Acceptance;

use Page\MigrationPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class MigrationSteps extends CommonSteps
{
    public function checkMigrationPageLayout()
    {
        $this->amGoingTo("\n\n --- Check migration page layout --- \n");
        $this->see(MigrationPage::TITLE, "//h3");
        $this->see(MigrationPage::DESCRIPTION_A);
        $this->see(MigrationPage::DESCRIPTION_B);

        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $this->see('Current username');
        $this->seeElement(Locator::combine(MigrationPage::CURRENT_USERNAME_FIELD_XPATH, MigrationPage::CURRENT_USERNAME_FIELD_CSS));
        $this->see('New username');
        $this->seeElement(Locator::combine(MigrationPage::NEW_USERNAME_FIELD_XPATH, MigrationPage::NEW_USERNAME_FIELD_CSS));
        $this->see('New password');
        $this->seeElement(Locator::combine(MigrationPage::NEW_PASSWORD_FIELD_XPATH, MigrationPage::NEW_PASSWORD_FIELD_CSS));
        $this->waitForText('I am sure I want to migrate all protected domains on this server to this new user.');
        $this->seeElement(Locator::combine(MigrationPage::CONFIRM_INPUT_XPATH, MigrationPage::CONFIRM_INPUT_CSS));
        $this->seeElement(Locator::combine(MigrationPage::MIGRATE_BTN_XPATH, MigrationPage::MIGRATE_BTN_CSS));
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
