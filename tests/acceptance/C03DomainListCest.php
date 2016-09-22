<?php

namespace Plesk;

use WebGuy;
use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\CommonSteps;
use Step\Acceptance\DomainListSteps;

class C03DomainListCest
{
    protected $doamin;

    /**
     * Function called before each test for setup
     */
    public function _before(DomainListSteps $I)
    {
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(DomainListSteps $I)
    {
    }

    /**
     * Function called when a test has failed
     */
    public function _failed(DomainListSteps $I)
    {
        $this->_after($I);
    }

    public function verifyDomainListAsRoot(DomainListSteps $I)
    {
        // Go to "Domain List" page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // check "Domain List" page layout
        $I->checkListDomainsPageLayout();

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Check if subscription domain is present in list
        $I->checkDomainList($account['domain'], true);

        // Check toggle protection for the subscription domain
        $I->checkToogleProtection($account['domain']);

        // Check login functionality for the subscription domain
        $I->checkLoginFunctionality($account['domain']);
    }

    public function verifyDomainListAsReseller(DomainListSteps $I)
    {
        // Create a new reseller account
        list($resellerUsername, $resellerPassword, $resellerId) = $I->createReseller();

        // Enable a shared IP
        $I->shareIp($resellerId);

        // Logout from root account
        $I->logout();

        // Login with the reseller account
        $I->login($resellerUsername, $resellerPassword);

        // Check if PSF in installed for the reseller and there is no domain in "Domain List"
        $I->checkPsfPresentForReseller();

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Check if domain is present in "Domain List"
        $I->checkDomainList($account['domain']);

        // Check toggle protection for the reseller account
        $I->checkToogleProtection($account['domain']);

        // Check login functionality for the reseller domain
        $I->checkLoginFunctionality($account['domain']);
    }

    public function verifyDomainListAsCustomer(DomainListSteps $I)
    {
        // Create a new reseller account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Enable a shared IP
        $I->shareIp();

        // Logout from root account
        $I->logout();

        // Login with the client account
        $I->loginAsClient($customerUsername, $customerPassword);

        // Check if customer domain exist in "Domain List"
        $I->checkPsfPresentForCustomer();

        // Check login functionality for the customer domain
        $I->checkLoginFunctionality($domain, false);
    }
}
