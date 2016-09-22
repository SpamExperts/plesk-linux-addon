<?php

namespace Step\Acceptance;

use Page\MigrationPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class MigrationSteps extends CommonSteps
{
    /**
     * Function used to check "Migration" page layout
     */
    public function checkMigrationPageLayout()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check migration page layout --- \n");

        // Check if top links are displayed properly
        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        // Check if title and descriptions are displayed properly
        $this->see(MigrationPage::TITLE, "//h3");
        $this->see(MigrationPage::DESCRIPTION_A);
        $this->see(MigrationPage::DESCRIPTION_B);

        // Check if the rest of the elements are displayed properly
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

    /**
     * Function used to submit "Migration" page form
     * @return [type] [description]
     */
    public function submitMigrationForm()
    {
        // Click on "Migrate" button
        $this->click(Locator::combine(MigrationPage::MIGRATE_BTN_XPATH, MigrationPage::MIGRATE_BTN_CSS));
    }

    /**
     * Function used to check migration operation error
     */
    public function checkErrorAfterMigrate()
    {
        $this->see('One or more settings are not correctly set.');
    }
}
