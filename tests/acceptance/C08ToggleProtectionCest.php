<?php

use Page\ConfigurationPage;
use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\ToggleProtectionSteps;
use Codeception\Util\Locator;

class C08ToggleProtectionCest
{
    public function _before(ToggleProtectionSteps $I)
    {
        $I->login();
    }

    public function _after(ToggleProtectionSteps $I)
    {
        $I->removeCreatedSubscriptions();
        $I->cleanupPlesk();
    }

    public function _failed(ToggleProtectionSteps $I)
    {
        $this->_after($I);
    }

    public function testToggleProtectionErrorAddedAsAliasNotDomain(ToggleProtectionSteps $I)
    {
        $setup = $I->setupErrorAddedAsAliasNotDomainScenario();
        $aliasDomainName = $setup['alias_domain_name'];

        // Go to Domain List page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search for $aliasDomainName
        $I->searchDomainList($aliasDomainName);

        // Toggle protection
        $I->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));
        $message = "The protection status of $aliasDomainName could not be changed to unprotected because alias domains and subdomains are treated as normal domains and \"$aliasDomainName\" is already added as an alias.";
        $I->waitForText($message, 60);
    }

    public function testHookErrorAddedAsAliasNotDomain(ToggleProtectionSteps $I)
    {

        $setup = $I->setupErrorAddedAsAliasNotDomainScenario();
        $alias = $setup['alias_domain_name'];
        codecept_debug("SETUP FINISHED");

        // Check if domain exists in Spampanel
        $I->apiCheckDomainExists($alias);

        $I->logout();

        // Login as client
        $I->login($setup['customer_username'], $setup['customer_password']);

        // Delete alias
        $I->removeAliasAsClient($alias);

        // Check if domain exists in Spampanel
        $I->apiCheckDomainExists($alias);
    }

    public function testToggleProtectionErrorAddedAsDomainNotAlias(ToggleProtectionSteps $I)
    {

        $setup = $I->setupErrorAddedAsDomainNotAliasScenario();
        $aliasDomainName = $setup['alias_domain_name'];

        // Go to Domain List page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search for aliasDomain
        $I->searchDomainList($aliasDomainName);

        // Check if alias was created correctly
        $I->see('alias', Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        $I->dontSeeLink('Check status', "//a[contains(.,'Check status')]");
    }

    public function testHookErrorAddedAsDomainNotAlias(ToggleProtectionSteps $I)
    {
        $setup = $I->setupErrorAddedAsDomainNotAliasScenario();
        $alias = $setup['alias_domain_name'];

        // Check if domain exists in Spampanel
        $I->apiCheckDomainExists($alias);
        $I->logout();

        // Login as client
        $I->login($setup['customer_username'], $setup['customer_password']);

        // Delete alias
        $I->removeAliasAsClient($alias);

        // Check if domain exists in Spampanel
        $I->apiCheckDomainExists($alias);
    }

    public function testToggleAsAliasAndUntoggleAlias(ToggleProtectionSteps $I)
    {
        $I->goToConfigurationPageAndSetOptions([
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => true,
        ]);

        // Create customer
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change customer plan
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);

        // Create alias
        $alias = $I->addAliasAsClient($domain);

        // Check that alias was created and exists in Spampanel
        $I->apiCheckDomainExists($alias);
        $I->assertIsAliasInSpampanel($alias, $domain);

        $I->logout();
        $I->loginAsRoot();

        // Go to Domain List page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Check alias wa created correctly
        $I->searchDomainList($alias);
        $I->see('alias', Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        $I->dontSeeLink('Check status', "//a[contains(.,'Check status')]");

        // Search $domain
        $I->amGoingTo("\n\n --- Search for {$domain} domain --- \n");
        $I->fillField(Locator::combine(DomainListPage::SEARCH_FIELD_XPATH, DomainListPage::SEARCH_FIELD_CSS), $domain);
        $I->click(Locator::combine(DomainListPage::SEARCH_BTN_XPATH, DomainListPage::SEARCH_BTN_CSS));
        $I->waitForText('Page 1 of 1. Total Items: 2');
        $I->see($domain, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));

        // Toggle protection
        $I->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));
        $I->waitForText("The protection status of $domain has been changed to unprotected", 60);

        // Check domain is not present in filter and Spampanel
        $I->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER);
        $I->apiCheckDomainNotExists($domain);
        $I->assertIsNotAliasInSpampanel($alias, $domain);
    }
}

