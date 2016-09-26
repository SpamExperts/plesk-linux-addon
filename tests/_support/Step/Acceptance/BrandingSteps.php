<?php

namespace Step\Acceptance;

use Page\BrandingPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class BrandingSteps extends ConfigurationSteps
{
    /**
     * Function used to check "Branding" page layout
     */
    public function checkPageLayout()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check branding page layout --- \n");

        // Check if top links are displayed properly
        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        // Check if title and descriptions are displayed properly
        $this->see(BrandingPage::TITLE);
        $this->see(BrandingPage::DESCRIPTION);
        $this->see(BrandingPage::SUB_TITLE_A, "//h4");
        $this->see(BrandingPage::DESCRIPTION_A);

        // Check if "Branding Icon" is displayed properly
        $this->waitForElement(Locator::combine(BrandingPage::BRANDING_ICON_CSS, BrandingPage::BRANDING_ICON_XPATH));
        $this->see("Professional Spam Filter");

        $this->see(BrandingPage::SUB_TITLE_B, "//h4[contains(.,'Change branding')]");
        $this->see(BrandingPage::DESCRIPTION_B);

        // Check if change branding fields are displayed properly
        $this->seeElement(Locator::combine(BrandingPage::BRANDNAME_INPUT_CSS, BrandingPage::BRANDNAME_INPUT_XPATH));
        $this->seeElement(Locator::combine(BrandingPage::BRANDICON_SELECT_CSS, BrandingPage::BRANDICON_SELECT_XPATH));
        $this->seeElement(Locator::combine(BrandingPage::SAVE_BRANDING_BTN_CSS, BrandingPage::SAVE_BRANDING_BTN_XPATH));
    }

    /**
     * Function used to change brandname
     * @param  string $brandname desired brandname
     */
    public function submitBrandingSettingForm($brandname)
    {
        // Wait for "Brandname" field to show
        $this->waitForElement(Locator::combine(BrandingPage::BRANDNAME_INPUT_CSS, BrandingPage::BRANDNAME_INPUT_XPATH));

        // Fill brandname field with desired brandname
        $this->fillField(Locator::combine(BrandingPage::BRANDNAME_INPUT_CSS, BrandingPage::BRANDNAME_INPUT_XPATH), $brandname);

        // Click "Save Brand Settings" button
        $this->click(Locator::combine(BrandingPage::SAVE_BRANDING_BTN_CSS, BrandingPage::SAVE_BRANDING_BTN_XPATH));
    }

    /**
     * Function used to check if changing brand was successful
     */
    public function checkSettingsSavedSuccessfully()
    {
        $this->see("No new icon uploaded, using the current one.");
        $this->see("The branding settings have been saved.");
        $this->see("Brandname is set");
    }

    /**
     * Function used to check brandname for root
     * @param  string $brandname expected brandname
     */
    public function checkBrandingForRoot($brandname)
    {
        $this->amGoingTo("\n\n --- Check branding for root --- \n");
        $this->checkPsfPresentForRoot($brandname);
    }

    /**
     * Function used to check brandname for reseller
     * @param  string $brandname expected brandname
     */
    public function checkBrandingForReseller($brandname)
    {
        $this->amGoingTo("\n\n --- Check branding for reseller --- \n");
        $this->checkPsfPresentForReseller($brandname);
    }

    /**
     * Function used to check brandname for root
     * @param  string $brandname expected customer
     */
    public function checkBrandingForCustomer($brandname)
    {
        $this->amGoingTo("\n\n --- Check branding for customer --- \n");
        $this->checkPsfPresentForCustomer($brandname);
    }
}
