<?php

namespace Step\Acceptance;

use Page\UpdatePage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class UpdateSteps extends CommonSteps
{
    /**
     * Function used to check "Upgrade" page layout
     */
    public function checkUpdatePageLayout()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Gheck update page layout --- \n");

        // Check if top links are displayed properly
        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        // Check if title and descriptions are displayed properly
        $this->see(UpdatePage::TITLE, "//h3");
        $this->see(UpdatePage::DESCRIPTION_A);
        $this->see(UpdatePage::DESCRIPTION_B);


        // Check if the rest of the elements are displayed properly
        $this->see('Tier of addon to install');
        $this->seeElement(Locator::combine(UpdatePage::TIER_DROP_DOWN_XPATH, UpdatePage::TIER_DROP_DOWN_CSS));
        $this->see("Force a reinstall even if the system is up to date.");
        $this->seeElement(Locator::combine(UpdatePage::FORCE_REINSTALL_INPUT_XPATH, UpdatePage::FORCE_REINSTALL_INPUT_CSS));
        $this->seeElement(Locator::combine(UpdatePage::CLICK_TO_UPGRADE_BTN_XPATH, UpdatePage::CLICK_TO_UPGRADE_BTN_XPATH));
    }

    /**
     * Function used to submit upgrade form
     */
    public function submitUpgradeForm()
    {
        $this->click(Locator::combine(UpdatePage::CLICK_TO_UPGRADE_BTN_XPATH, UpdatePage::CLICK_TO_UPGRADE_BTN_XPATH));
    }

    /**
     * Function used to check the upgrade operation error
     */
    public function checkNoticeAfterUpgrade()
    {
        $this->see('There is no stable update available to install. You are already at the latest version.');
    }
}
