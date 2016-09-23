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
    }

    public function _failed(ToggleProtectionSteps $I)
    {
        $this->_after($I);
    }

    public function testToggleProtectionErrorAddedAsAliasNotDomain(ToggleProtectionSteps $I)
    {
        $setup = $I->setupErrorAddedAsAliasNotDomainScenario();
        $aliasDomainName = $setup['alias_domain_name'];

        // Test
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($aliasDomainName);
        $I->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));
        $message = "The protection status of $aliasDomainName could not be changed to unprotected because alias domains and subdomains are treated as normal domains and \"$aliasDomainName\" is already added as an alias.";
        $I->waitForText($message, 60);
    }

    public function testHookErrorAddedAsAliasNotDomain(ToggleProtectionSteps $I)
    {

        $setup = $I->setupErrorAddedAsAliasNotDomainScenario();
        $alias = $setup['alias_domain_name'];
        codecept_debug("SETUP FINISHED");

        // Test
        $I->apiCheckDomainExists($alias);
        $I->logout();
        $I->login($setup['customer_username'], $setup['customer_password']);
        $I->removeAliasAsClient($alias);
        $I->apiCheckDomainExists($alias);
    }

    public function testToggleProtectionErrorAddedAsDomainNotAlias(ToggleProtectionSteps $I)
    {

        $setup = $I->setupErrorAddedAsDomainNotAliasScenario();
        $aliasDomainName = $setup['alias_domain_name'];

        // Test
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($aliasDomainName);
        $I->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);
        $I->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));
        $message = "The protection status of $aliasDomainName could not be changed to unprotected because alias domains and subdomains are treated as aliases and \"$aliasDomainName\" is already added as a normal domain.";
        $I->waitForText($message, 60);
    }

    public function testHookErrorAddedAsDomainNotAlias(ToggleProtectionSteps $I)
    {
        $setup = $I->setupErrorAddedAsDomainNotAliasScenario();
        $alias = $setup['alias_domain_name'];

        // Test
        $I->apiCheckDomainExists($alias);
        $I->logout();
        $I->login($setup['customer_username'], $setup['customer_password']);
        $I->removeAliasAsClient($alias);
        $I->apiCheckDomainExists($alias);
    }

    public function testToggleAsAliasAndUntoggleAlias(ToggleProtectionSteps $I)
    {
        $I->goToConfigurationPageAndSetOptions([
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => true,
        ]);

        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $alias = $I->addAliasAsClient($domain);
        $I->apiCheckDomainExists($alias);
        $I->assertIsAliasInSpampanel($alias, $domain);

        $I->logout();
        $I->loginAsRoot();
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($alias);
        $I->pauseExecution();
        $I->click(Locator::combine(DomainListPage::TOGGLE_PROTECTION_LINK_XPATH, DomainListPage::TOGGLE_PROTECTION_LINK_CSS));
        $I->waitForText("The protection status of $alias has been changed to unprotected", 60);
        $I->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER);
        $I->apiCheckDomainNotExists($alias);
        $I->assertIsNotAliasInSpampanel($alias, $domain);
    }
}

