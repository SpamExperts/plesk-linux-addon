<?php

namespace Step\Acceptance;

use Page\SupportPage;
use Page\ProfessionalSpamFilterPage;

class SupportSteps extends CommonSteps
{
    public function checkSupportPageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check support page layout --- \n");

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $I->see(SupportPage::TITLE);
        $I->see(SupportPage::DESCRIPTION);
        $I->see(SupportPage::TEXT_A);
        $I->see(SupportPage::TEXT_B);
        $I->see(SupportPage::TEXT_C);
        $I->see(SupportPage::TEXT_D);
        $I->see(SupportPage::TEXT_E);

        $I->seeElement(SupportPage::RUN_DIAGNOSTICS_BTN);
    }

    public function submitDiagnosticForm()
    {
        $this->click('Run diagnostics');
    }

    public function checkDiagnostics()
    {
        $I = $this;
        $I->see("PHP version:", "//strong[contains(.,'PHP version:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("PHP extensions:", "//strong[contains(.,'PHP extensions:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Configuration permissions:", "//strong[contains(.,'Configuration permissions:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Panel version:", "//strong[contains(.,'Panel version:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Addon version:", "//strong[contains(.,'Addon version:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Hashes:", "//strong[contains(.,'Hashes:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Hooks:", "//strong[contains(.,'Hooks:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Symlinks:", "//strong[contains(.,'Symlinks:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Controlpanel API:", "//strong[contains(.,'Controlpanel API:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Spamfilter API:", "//strong[contains(.,'Spamfilter API:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->see("Symlink to PHP5 binary:", "//strong[contains(.,'Symlink to PHP5 binary:')]");
        $I->seeElement("//span[contains(.,'OK!')]");
        $I->dontSeeElement("//span[contains(.,'WARNING')]");
        $I->dontSeeElement("//span[contains(.,'CRITICAL')]");
    }
}
