<?php
namespace Step\Acceptance;

use Page\ConfigurationPage;
use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\CommonSteps;
use Codeception\Util\Locator;

class ToggleProtectionSteps extends CommonSteps
{
	public function setupErrorAddedAsAliasNotDomainScenario(CommonSteps $I)
    {
        // setup
        $I->goToConfigurationPageAndSetOptions([
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::Combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
        ]);

        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
//        $I->wait(120);

        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($domain);
        $I->click(DomainListPage::TOGGLE_PROTECTION_LINK);
        $I->waitForText("The protection status of $domain has been changed to protected", 60);
        $I->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);

        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $aliasDomainName = $I->addAliasAsClient($domain);

        $I->logout();
        $I->loginAsRoot();
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($aliasDomainName);
        $I->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER);
        $I->addDomainAlias($aliasDomainName, $domain);
        $I->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);

        $I->goToConfigurationPageAndSetOptions([
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => false,
        ]);

        return [
            'alias_domain_name' => $aliasDomainName,
            'customer_username' => $customerUsername,
            'customer_password' => $customerPassword,
            'domain' => $domain
        ];
    }

    public function setupErrorAddedAsDomainNotAliasScenario(CommonSteps $I)
    {
        $I->goToConfigurationPageAndSetOptions([
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH)=> true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => false,
        ]);

        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        //$I->wait(120);

        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $aliasDomainName = $I->addAliasAsClient($domain);

        $I->apiCheckDomainExists($aliasDomainName);

        $I->logout();
        $I->loginAsRoot();
        $I->goToConfigurationPageAndSetOptions([
            ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT => true,
        ]);

        return [
            'alias_domain_name' => $aliasDomainName,
            'customer_username' => $customerUsername,
            'customer_password' => $customerPassword,
            'domain' => $domain
        ];
    }
}