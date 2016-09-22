<?php

namespace Step\Acceptance;

use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Codeception\Util\Locator;

class DomainListSteps extends CommonSteps
{
    /**
     * Function used to check domain list page layout
     */
    public function checkListDomainsPageLayout()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check list domains page layout --- \n");
        $this->see(DomainListPage::TITLE);
        $this->see(DomainListPage::DESCRIPTION);

        // Check if top links are displayed properly
        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        // Check if the rest of elements are displayed properly
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
        // Display info message
        $this->amGoingTo("\n\n --- Check Toogle protection functionality --- \n");

        // Search domain in "Domain List"
        $this->searchDomainList($domain);

        // Click on "Check Status" button
        $this->click(DomainListPage::CHECK_STATUS_LINK_XPATH);

        // Check if domain is not present in filter
        $this->waitForText(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER, 30);
        $this->see(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));

        // Click on "Toggle Protection"
        $this->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));

        // Wait for protection status to change
        $this->waitForText("The protection status of {$domain} has been changed to protected", 30);

        // Search again the domain in list
        $this->searchDomainList($domain);

        // Click on "Check Status" button
        $this->click(DomainListPage::CHECK_STATUS_LINK_XPATH);

        // Check if the domain is present in filter
        $this->waitForText(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER, 30);
    }


    /**
     * Function used to check login functionality
     * @param  string  $domain domain to login with
     * @param  boolean $isRoot -
     */
    public function checkLoginFunctionality($domain, $isRoot = true)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check login functionality --- \n");

        // If isRoot is true, search the domain in DomainList
        if ($isRoot)
            $this->searchDomainList($domain);

        // Try to login in spampanel using the domain
        $this->loginOnSpampanel($domain);
    }

    /**
     * Function used to toggle protection for a certain domain
     * @param  string $domain desired domain
     */
    public function toggleProtection($domain)
    {
        // Go to "Domain List" page
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search desired domain in list
        $this->searchDomainList($domain);

        // Click "Toggle Protection" button for the domain
        $this->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_CSS, DomainListPage::TOGGLE_PROTECTION_LINK_XPATH));

        // Wait for success message
        $this->waitForText("The protection status of {$domain} has been changed to protected", 30);
    }
}
