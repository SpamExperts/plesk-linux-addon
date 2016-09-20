<?php

namespace Plesk;

use PsfConfig;
use Page\DomainListPage;
use Page\ConfigurationPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\ConfigurationSteps;
use Codeception\Util\Locator;

class C01ConfigurationCest
{
    public function _before(ConfigurationSteps $I)
    {
        $I->login();
        $I->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);
    }

    public function _after(ConfigurationSteps $I)
    {
    }

    public function _failed(ConfigurationSteps $I)
    {
        $this->_after($I);
    }

    public function verifyConfigurationPage(ConfigurationSteps $I)
    {
        // Check addon configuration page layout
        $I->checkConfigurationPageLayout();

        // Check error messages if configuration page fields are wrong
        $I->checkUnsuccessfullConfigurations();

        // Set configuration fields option
        $I->setFieldApiUrl(PsfConfig::getApiUrl());
        $I->setFieldApiHostname(PsfConfig::getApiHostname());
        $I->setFieldApiUsernameIfEmpty(PsfConfig::getApiUsername());
        $I->setFieldApiPassword(PsfConfig::getApiPassword());
        $I->setFieldPrimaryMX(PsfConfig::getPrimaryMX());

        // Save the configuration
        $I->submitSettingForm();

        // Wait for success message
        $I->checkSubmissionIsSuccessful();
    }

    public function verifyAutomaticallyAddDomainToPsf(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
        ));
        $I->shareIp();

        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->checkDomainIsPresentInFilter($domain);
        $I->apiCheckDomainExists($domain);

        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $alias = $I->addAliasAsClient($domain);
        $I->apiCheckDomainExists($alias);
        $I->logout();
        $I->login();
        $I->removeSubscription($domain);
    }

    public function verifyNotAutomaticallyAddDomainToPsf(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
        ));
        $account = $I->addNewSubscription();
        $I->checkDomainIsNotPresentInFilter($account['domain']);
        $I->apiCheckDomainNotExists($account['domain']);
        $I->removeSubscription($account['domain']);
    }

    public function verifyNotAutomaticallyDeleteDomainToPsf(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => false,
        ));
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $I->apiCheckDomainExists($account['domain']);
        $I->removeSubscription($account['domain']);
        $I->apiCheckDomainExists($account['domain']);
    }

    public function verifyAutomaticallyDeleteDomainToPsf(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true,
        ));

        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->checkDomainIsPresentInFilter($domain);
        $I->apiCheckDomainExists($domain);

        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $alias = $I->addAliasAsClient($domain);
        $I->apiCheckDomainExists($alias);

        $I->logout();
        $I->loginAsRoot();
        $I->removeSubscription($domain);
        $I->apiCheckDomainNotExists($domain);
        $I->apiCheckDomainNotExists($alias);
    }

    public function verifyAutmaticallyDeleteSecondaryDomains(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true,
        ));

        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->checkDomainIsPresentInFilter($domain);
        $I->apiCheckDomainExists($domain);

        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $alias = $I->addAliasAsClient($domain);
        $I->apiCheckDomainExists($alias);

        $I->removeAliasAsClient($alias);
        $I->apiCheckDomainNotExists($alias);
    }

    public function verifyNotAutomaticallyChangeMXRecords(ConfigurationSteps $I)
    {
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(false);
        $I->setUseExistingMXRecordsOption(false);
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $I->openSubscription($account['domain']);
        $I->dontSee(PsfConfig::getPrimaryMX(), "//table[@class='list']");
    }

    public function verifyAutomaticallyChangeMXRecords(ConfigurationSteps $I)
    {
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption();
        $I->setUseExistingMXRecordsOption(false);
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $I->openSubscription($account['domain']);
        $I->see(PsfConfig::getPrimaryMX(), "//table[@class='list']");
    }

    public function verifyNotUseExistingMXRecords(ConfigurationSteps $I)
    {
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(false);
        $I->setUseExistingMXRecordsOption(false);
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $routes = $I->apiGetDomainRoutes($account['domain']);
        $I->assertContains($I->getEnvHostname().'::25', $routes);
    }

    public function verifyUseExistingMXRecords(ConfigurationSteps $I)
    {
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(false);
        $I->setUseExistingMXRecordsOption();
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $routes = $I->apiGetDomainRoutes($account['domain']);
        $I->assertContains("mail.".$account['domain'].'::25', $routes);
    }

    public function verifyNotConfigureTheEmailAddressForThisDomainOption(ConfigurationSteps $I)
    {
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setConfigureEmailAddressOption(false);
        $account = $I->addNewSubscription();
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($account['domain']);
        $I->toggleProtection($account['domain']);
        $I->loginOnSpampanel($account['domain']);
        $I->click('Domain settings');
        $I->dontSeeInField('#contact_email', 'devnull@spamlogin.com');
    }

    public function verifyConfigureTheEmailAddressForThisDomainOption(ConfigurationSteps $I)
    {
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setConfigureEmailAddressOption();
        $account = $I->addNewSubscription();
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($account['domain']);
        $I->toggleProtection($account['domain']);
        $I->loginOnSpampanel($account['domain']);
        $I->click('Domain settings');
        $I->seeInField('#contact_email', 'devnull@spamlogin.com');
    }

    public function verifyNotUseIPAsDestinationOption(ConfigurationSteps $I)
    {
        $I->setUseIPAsDestinationOption(false);
        $I->setUseExistingMXRecordsOption();
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $routes = $I->apiGetDomainRoutes($account['domain']);
        $I->assertContains("mail.".$account['domain'].'::25', $routes);
    }

    public function verifyUseIPAsDestinationOption(ConfigurationSteps $I)
    {
        $I->setUseIPAsDestinationOption();
        $account = $I->addNewSubscription();
        $I->toggleProtection($account['domain']);
        $ip = gethostbyname($I->getEnvHostname());
        $routes = $I->apiGetDomainRoutes($account['domain']);
        $I->assertContains($ip.'::25', $routes);
    }

    // Fails
    public function verifyAddonDomainsAsNormalDomain(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
        ));
        $I->shareIp();
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->logout();
        $I->login($customerUsername, $customerPassword, true);

        $addonDomainName = $I->addAddonDomainAsClient($domain);
        $I->logout();
        $I->login();
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($addonDomainName);
        $I->see($addonDomainName, Locator::combine(DomainListPage::DOMAIN_TABLE_CSS, DomainListPage::DOMAIN_TABLE_XPATH));
        $I->apiCheckDomainExists($addonDomainName);
    }

    public function verifyAddonDomainsAsAnAlias(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => true,
        ));
        $I->shareIp();
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->logout();
        $I->login($customerUsername, $customerPassword, true);

        $aliasDomain = $I->addAliasAsClient($domain);

        $I->logout();
        $I->login();
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->searchDomainList($aliasDomain);
        $I->see($aliasDomain, Locator::combine(DomainListPage::DOMAIN_TABLE_CSS, DomainListPage::DOMAIN_TABLE_XPATH));
        $I->see("alias", Locator::combine(DomainListPage::DOMAIN_TABLE_CSS, DomainListPage::DOMAIN_TABLE_XPATH));
    }

    public function verifyAddonDomainsAsAnAliasSubscription(ConfigurationSteps $I)
    {
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => true,
        ));
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->changeCustomerPlan($customerUsername);
        $I->wait(120);
        $I->apiCheckDomainExists($domain);
        $I->logout();
        $I->login($customerUsername, $customerPassword, true);
        $aliasDomain = $I->addAliasAsClient($domain);
        $I->apiCheckDomainExists($aliasDomain);
        $I->assertIsAliasInSpampanel($aliasDomain, $domain);
    }

    public function verifyRedirectBackToPleskUponLogout(ConfigurationSteps $I)
    {
        $I->setRedirectBackToPleskOption();
        $account = $I->addNewSubscription();
        $I->checkDomainList($account['domain'], true);
        $I->searchDomainList($account['domain']);
        $I->loginOnSpampanel($account['domain']);
        $I->logoutFromSpampanel();
        $I->seeInCurrentAbsoluteUrl($I->getEnvHostname());
    }
}
