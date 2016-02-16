<?php

namespace Step\Acceptance;

use Pages\UpdatePage;
use Pages\ProfessionalSpamFilterPage;

class UpdateSteps extends CommonSteps
{
    public function checkUpdatePageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Gheck update page layout --- \n");

        $I->see(UpdatePage::TITLE, "//h3");
        $I->see(UpdatePage::DESCRIPTION_A);
        $I->see(UpdatePage::DESCRIPTION_B);

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $I->see('Tier of addon to install');
        $I->seeElement(UpdatePage::TIER_DROP_DOWN);

        $I->see("Force a reinstall even if the system is up to date.");
        $I->seeElement(UpdatePage::FORCE_REINSTALL_INPUT);
        $I->seeElement(UpdatePage::CLICK_TO_UPGRADE_BTN);
    }

    public function submitUpgradeForm()
    {
        $this->click('Click to upgrade');
    }

    public function checkNoticeAfterUpgrade()
    {
        $this->see('There is no stable update available to install. You are already at the latest version.');
    }
}
