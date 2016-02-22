<?php

namespace Plesk;

use PsfConfig;
use WebGuy;
use Pages\BrandingPage;
use Pages\ProfessionalSpamFilterPage;
use Step\Acceptance\BrandingSteps;

class C02BrandingCest
{
    protected $brandname = "Professional Spam Filter";

    public function _before(BrandingSteps $I)
    {
        $I->login();
    }

    public function _after(BrandingSteps $I)
    {
        $I->logout();
        $I->login();
        $I->goToPage(ProfessionalSpamFilterPage::BRANDING_BTN, BrandingPage::TITLE);
        $I->submitBrandingSettingForm("Professional Spam Filter");
        $I->checkSettingsSavedSuccessfully();
    }

    public function verifyBrandingPage(BrandingSteps $I)
    {
        $I->goToPage(ProfessionalSpamFilterPage::BRANDING_BTN, BrandingPage::TITLE);
        $I->checkPageLayout();
        $this->brandname = "Branded Professional Spam Filter";
        $I->submitBrandingSettingForm($this->brandname);
        $I->checkSettingsSavedSuccessfully();

        $I->checkBrandingForRoot($this->brandname);

        list($resellerUsername, $resellerPassword, $resellerId) = $I->createReseller();
        list($customerUsername, $customerPassword, ) = $I->createCustomer();
        $I->shareIp($resellerId);

        $I->logout();
        $I->login($resellerUsername, $resellerPassword);
        $I->checkBrandingForReseller($this->brandname);

        $I->logout();
        $I->login($customerUsername, $customerPassword, true);
        $I->checkBrandingForCustomer($this->brandname);
    }
}
