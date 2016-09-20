<?php
namespace Step\Acceptance;

use Page\ConfigurationPage;
use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\CommonSteps;
use Codeception\Util\Locator;

class ToggleProtectionSteps extends CommonSteps
{
	public function setupErrorAddedAsAliasNotDomainScenario()
    {
        // setup
        $this->goToConfigurationPageAndSetOptions([
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::Combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
        ]);

        list($customerUsername, $customerPassword, $domain) = $this->createCustomer();
        $this->changeCustomerPlan($customerUsername);
//        $this->wait(120);

        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($domain);
        $this->click(DomainListPage::TOGGLE_PROTECTION_LINK);
        $this->waitForText("The protection status of $domain has been changed to protected", 60);
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);

        $this->logout();
        $this->loginAsClient($customerUsername, $customerPassword);
        $aliasDomainName = $this->addAliasAsClient($domain);

        $this->logout();
        $this->loginAsRoot();
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($aliasDomainName);
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER);
        $this->addDomainAlias($aliasDomainName, $domain);
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);

        $this->goToConfigurationPageAndSetOptions([
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
        $this->goToConfigurationPageAndSetOptions([
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH)=> true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => false,
        ]);

        list($customerUsername, $customerPassword, $domain) = $this->createCustomer();
        $this->changeCustomerPlan($customerUsername);
        //$this->wait(120);

        $this->logout();
        $this->loginAsClient($customerUsername, $customerPassword);
        $aliasDomainName = $this->addAliasAsClient($domain);

        $this->apiCheckDomainExists($aliasDomainName);

        $this->logout();
        $this->loginAsRoot();
        $this->goToConfigurationPageAndSetOptions([
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
