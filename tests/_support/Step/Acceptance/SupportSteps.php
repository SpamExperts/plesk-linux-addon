<?php

namespace Step\Acceptance;

use Page\SupportPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class SupportSteps extends CommonSteps
{
    public function checkSupportPageLayout()
    {
        $this->amGoingTo("\n\n --- Check support page layout --- \n");

        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $this->see(SupportPage::TITLE);
        $this->see(SupportPage::DESCRIPTION);
        $this->see(SupportPage::TEXT_A);
        $this->see(SupportPage::TEXT_B);
        $this->see(SupportPage::TEXT_C);
        $this->see(SupportPage::TEXT_D);
        $this->see(SupportPage::TEXT_E);

        $this->seeElement(Locator::combine(SupportPage::RUN_DIAGNOSTICS_BTN_XPATH, SupportPage::RUN_DIAGNOSTICS_BTN_CSS));
    }

    public function submitDiagnosticForm()
    {
        $this->click(Locator::combine(SupportPage::RUN_DIAGNOSTICS_BTN_XPATH, SupportPage::RUN_DIAGNOSTICS_BTN_CSS));
    }

    public function checkDiagnostics()
    {
        $this->see("PHP version:", "//strong[contains(.,'PHP version:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("PHP extensions:", "//strong[contains(.,'PHP extensions:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Configuration permissions:", "//strong[contains(.,'Configuration permissions:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Panel version:", "//strong[contains(.,'Panel version:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Addon version:", "//strong[contains(.,'Addon version:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Hashes:", "//strong[contains(.,'Hashes:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Hooks:", "//strong[contains(.,'Hooks:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Symlinks:", "//strong[contains(.,'Symlinks:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Controlpanel API:", "//strong[contains(.,'Controlpanel API:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Spamfilter API:", "//strong[contains(.,'Spamfilter API:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->see("Symlink to PHP5 binary:", "//strong[contains(.,'Symlink to PHP5 binary:')]");
        $this->seeElement("//span[contains(.,'OK!')]");
        $this->dontSeeElement("//span[contains(.,'WARNING')]");
        $this->dontSeeElement("//span[contains(.,'CRITICAL')]");
    }
}
