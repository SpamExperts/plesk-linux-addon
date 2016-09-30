<?php

namespace Plesk;

use WebGuy;
use Page\DomainListPage;
use Page\ConfigurationPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\CommonSteps;
use Step\Acceptance\DomainListSteps;
use Codeception\Util\Locator;

class C03DomainListCest
{
    protected $doamin;

    /**
     * Function called before each test for setup
     */
    public function _before(DomainListSteps $I)
    {
        // Login as root
        $I->loginAsRoot();
    }

    /**
     * Function called after each test for cleanup
     */
    public function _after(DomainListSteps $I)
    {
        $I->cleanupPlesk();
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

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Go to "Domain List" page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // check "Domain List" page layout
        $I->checkListDomainsPageLayout();

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

    public function verifyLoginAsReseller(DomainListSteps $I)
    {
        // Go to addon "Configuration" page
        $I->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);

        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => false
        ));

        // Create a new reseller account
        list($resellerUsername, $resellerPassword, $resellerId) = $I->createReseller();

        // Enable a shared IP
        $I->shareIp($resellerId);

        // Logout from root account
        $I->logout();

        // Login with the reseller account
        $I->login($resellerUsername, $resellerPassword);

        // Create customer
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Logout from reseller account
        $I->logout();

        // Login with the customer account
        $I->loginAsClient($customerUsername, $customerPassword);

        // Create alias
        $alias = $I->addAliasAsClient($domain);

        // Check that aliasdomain exists in Spampanel
        $I->apiCheckDomainExists($alias);

        // Logout from client account
        $I->logout();

        // Login with reseller account
        $I->login($resellerUsername, $resellerPassword);

        // Check if alias domain is present in list
        $I->checkDomainList($alias);

         // Check login functionality for the reseller domain
        $I->checkLoginFunctionality($alias);
    }
}
