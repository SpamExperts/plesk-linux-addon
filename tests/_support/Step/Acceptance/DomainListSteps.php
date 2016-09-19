<?php

namespace Step\Acceptance;

use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;

class DomainListSteps extends CommonSteps
{
    public function checkListDomainsPageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check list domains page layout --- \n");
        $I->see(DomainListPage::TITLE);
        $I->see(DomainListPage::DESCRIPTION);

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $I->seeElement(DomainListPage::SEARCH_FIELD);
        $I->seeElement(DomainListPage::SEARCH_BTN);
        $I->seeElement(DomainListPage::RESET_BTN);
        $I->seeElement(DomainListPage::CHECK_STATUS_FOR_ALL_DOMAIN);
        $I->seeElement(DomainListPage::TOGGLE_PROTECTION_FOR_SELECTED);
        $I->seeElement(DomainListPage::DOMAIN_TABLE);
        $I->seeElement(DomainListPage::ITEMS_PER_PAGE_INPUT);
        $I->seeElement(DomainListPage::CHANGE_BTN);

    }

    public function checkToogleProtection($domain)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check Toogle protection functionality --- \n");
        $I->searchDomainList($domain);
        $I->click(DomainListPage::CHECK_STATUS_LINK);
        $I->waitForText("This domain is not present in the filter.", 30);
        $I->see("This domain is not present in the filter.",
            DomainListPage::DOMAIN_TABLE);
        $I->click(DomainListPage::TOGGLE_PROTECTION_LINK);
        $I->waitForText("The protection status of {$domain} has been changed to protected", 30);
        $I->searchDomainList($domain);
        $I->click(DomainListPage::CHECK_STATUS_LINK);
        $I->waitForText("This domain is present in the filter.", 30);
    }

    public function checkLoginFunctionality($domain, $isRoot = true)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check login functionality --- \n");
        if ($isRoot) {
            $I->searchDomainList($domain);
        }
        $I->loginOnSpampanel($domain);
    }

    public function toggleProtection($domain)
    {
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($domain);
        $this->click(DomainListPage::TOGGLE_PROTECTION_LINK);
        $this->waitForText("The protection status of {$domain} has been changed to protected", 30);
    }
}
