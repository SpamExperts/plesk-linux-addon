<?php

namespace Step\Acceptance;

use Pages\DomainListPage;
use Pages\ProfessionalSpamFilterPage;

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
        $I->seeElement(DomainListPage::TOOGLE_PROTECTION_FOR_SELECTED);
        $I->seeElement(DomainListPage::DOMAIN_TABLE);
        $I->seeElement(DomainListPage::ITEMS_PER_PAGE_INPUT);
        $I->seeElement(DomainListPage::CHANGE_BTN);

    }

    public function searchDomainList($domain)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Search for {$domain} domain --- \n");
        $I->fillField(DomainListPage::SEARCH_FIELD, $domain);
        $I->click(DomainListPage::SEARCH_BTN);
        $I->waitForText('Page 1 of 1. Total Items: 1');
        $I->see($domain, DomainListPage::DOMAIN_TABLE);
    }

    public function checkDomainList($domainName, $isRoot = false)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check Domain list is present --- \n");
        if ($isRoot) {
            $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
            $I->see($domainName, DomainListPage::DOMAIN_TABLE);
        }
        else {
            $I->switchToLeftFrame();
            $I->click("//a[contains(.,'Professional Spam Filter')]");
            $I->switchToWorkFrame();
            $I->amGoingTo("Check domin '{$domainName}' is present on the list");
            $I->see($domainName, DomainListPage::DOMAIN_TABLE);
        }
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
        $I->click(DomainListPage::TOOGLE_PROTECTION_LINK);
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

    public function toogleProtection($domain)
    {
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($domain);
        $this->click(DomainListPage::TOOGLE_PROTECTION_LINK);
        $this->waitForText("The protection status of {$domain} has been changed to protected", 30);
    }
}
