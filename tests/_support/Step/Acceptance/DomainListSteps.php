<?php

namespace Step\Acceptance;

use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class DomainListSteps extends CommonSteps
{
    public function checkListDomainsPageLayout()
    {
        $this->amGoingTo("\n\n --- Check list domains page layout --- \n");
        $this->see(DomainListPage::TITLE);
        $this->see(DomainListPage::DESCRIPTION);

        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $this->seeElement(Locator::combine(DomainListPage::SEARCH_FIELD_XPATH, DomainListPage::SEARCH_FIELD_CSS));
        $this->seeElement(Locator::combine(DomainListPage::SEARCH_BTN_XPATH, DomainListPage::SEARCH_BTN_CSS));
        $this->seeElement(Locator::combine(DomainListPage::RESET_BTN_XPATH, DomainListPage::RESET_BTN_CSS));
        $this->seeElement(Locator::combine(DomainListPage::CHECK_STATUS_FOR_ALL_DOMAIN_XPATH, DomainListPage::CHECK_STATUS_FOR_ALL_DOMAIN_CSS));
        $this->seeElement(Locator::combine(DomainListPage::TOGGLE_PROTECTION_FOR_SELECTED_XPATH, DomainListPage::TOGGLE_PROTECTION_FOR_SELECTED_CSS));
        $this->seeElement(Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        $this->seeElement(Locator::combine(DomainListPage::ITEMS_PER_PAGE_INPUT_XPATH, DomainListPage::ITEMS_PER_PAGE_INPUT_CSS));
        $this->seeElement(Locator::combine(DomainListPage::CHANGE_BTN_XPATH, DomainListPage::CHANGE_BTN_CSS));
    }

    public function checkToogleProtection($domain)
    {
        $this->amGoingTo("\n\n --- Check Toogle protection functionality --- \n");
        $this->searchDomainList($domain);
        $this->click(DomainListPage::CHECK_STATUS_LINK_XPATH);

        $this->waitForText(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER, 30);
        $this->see(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));

        $this->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));
        $this->waitForText("The protection status of {$domain} has been changed to protected", 30);

        $this->searchDomainList($domain);
        $this->click(DomainListPage::CHECK_STATUS_LINK_XPATH);
        $this->waitForText(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER, 30);
    }

    public function checkLoginFunctionality($domain, $isRoot = true)
    {
        $this->amGoingTo("\n\n --- Check login functionality --- \n");
        if ($isRoot) {
            $this->searchDomainList($domain);
        }
        $this->loginOnSpampanel($domain);
    }

    public function toggleProtection($domain)
    {
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($domain);
        $this->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_CSS, DomainListPage::TOGGLE_PROTECTION_LINK_XPATH));
        $this->waitForText("The protection status of {$domain} has been changed to protected", 30);
    }
}
