<?php

namespace Step\Acceptance;

use Page\BrandingPage;
use Page\ProfessionalSpamFilterPage;

class BrandingSteps extends CommonSteps
{
    public function checkPageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check branding page layout --- \n");
        $I->see(BrandingPage::TITLE);
        $I->see(BrandingPage::DESCRIPTION);
        $I->see(BrandingPage::SUB_TITLE_A, "//h4");
        $I->see(BrandingPage::DESCRIPTION_A);
        $I->waitForElement(BrandingPage::BRANDING_ICON);
        $I->see("Professional Spam Filter");

        $I->see(BrandingPage::SUB_TITLE_B, "//h4[contains(.,'Change branding')]");
        $I->see(BrandingPage::DESCRIPTION_B);
        $I->seeElement(BrandingPage::BRANDNAME_INPUT);
        $I->seeElement(BrandingPage::BRANDICON_SELECT);
        $I->seeElement(BrandingPage::SAVE_BRANDING_BTN);

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);
    }

    public function submitBrandingSettingForm($brandname)
    {
        $I = $this;
        $I->waitForElement(BrandingPage::BRANDNAME_INPUT);
        $I->fillField(BrandingPage::BRANDNAME_INPUT, $brandname);
        $I->click('Save Branding Settings');
    }

    public function checkSettingsSavedSuccessfully()
    {
        $I = $this;
        $I->see("No new icon uploaded, using the current one.");
        $I->see("The branding settings have been saved.");
        $I->see("Brandname is set");
    }

    public function checkBrandingForRoot($brandname)
    {
        $I = new CommonSteps($this->getScenario());
        $I->amGoingTo("\n\n --- Check branding for root --- \n");
        $I->checkPsfPresentForRoot($brandname);
    }

    public function checkBrandingForReseller($brandname)
    {
        $I = new CommonSteps($this->getScenario());
        $I->amGoingTo("\n\n --- Check branding for reseller --- \n");
        $I->checkPsfPresentForReseller($brandname);
    }

    public function checkBrandingForCustomer($brandname)
    {
        $I = new CommonSteps($this->getScenario());
        $I->amGoingTo("\n\n --- Check branding for customer --- \n");
        $I->checkPsfPresentForCustomer($brandname);
    }
}
