<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Page\BrandingPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\BrandingSteps;
use Codeception\Util\Locator;

class C02BrandingCest
{
    // Default brandname
    protected $brandname = "Professional Spam Filter";

    /**
     * Function called before each test for setup
     */
    public function _before(BrandingSteps $I)
    {
        // Login as root
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(BrandingSteps $I)
    {
        // Logout from any account
        $I->logout();

        // Login as root
        $I->loginAsRoot();

        // Go to plugin "Branding" page
        $I->goToPage(ProfessionalSpamFilterPage::BRANDING_BTN, BrandingPage::TITLE);

        // Restore the brandname to default
        $I->submitBrandingSettingForm("Professional Spam Filter");

        // Check if changing brandname was successful
        $I->checkSettingsSavedSuccessfully();
    }

    /**
     * Function called when a test has failed
     */
    public function _failed(BrandingSteps $I)
    {
        $this->_after($I);
    }

    public function verifyBrandingPage(BrandingSteps $I)
    {
        // Go to "Branding" page
        $I->goToPage(ProfessionalSpamFilterPage::BRANDING_BTN, BrandingPage::TITLE);

        // Check "Branding" page layout
        $I->checkPageLayout();

        // Change the default brandname
        $this->brandname = "Branded Professional Spam Filter";
        $I->submitBrandingSettingForm($this->brandname);

        // Check if settings were saved
        $I->checkSettingsSavedSuccessfully();

        $I->reloadPage();

        // Check if brandname was changed for root
        $I->checkBrandingForRoot($this->brandname);

        // Enable a shared IP
        // $I->shareIp($resellerId);

        // Create new reseller and new customer account
        list($resellerUsername, $resellerPassword, $resellerId) = $I->createReseller();
        list($customerUsername, $customerPassword, ) = $I->createCustomer();

        // Logout from root account
        $I->logout();

        // Login with the reseller account
        $I->login($resellerUsername, $resellerPassword, false);

        // Check if brandname was changed for root
        $I->checkBrandingForReseller($this->brandname);

        // Logout from reseller account
        $I->logout();

        // Login with the customer account
        $I->loginAsClient($customerUsername, $customerPassword);

        // Check if brandname was changed for customer
        $I->checkBrandingForCustomer($this->brandname);
    }
}
