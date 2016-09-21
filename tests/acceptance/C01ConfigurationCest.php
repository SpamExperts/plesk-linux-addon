<?php

namespace Plesk;

use PsfConfig;
use Page\DomainListPage;
use Page\SpampanelPage;
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
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
        ));

        // Enable a shared IP
        $I->shareIp();

        // Create a new customer account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change the customer plan to unlimited for the created account
        $I->changeCustomerPlan($customerUsername);

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Check if customer domain is present in filter
        $I->checkDomainIsPresentInFilter($domain);

        // Check if customer domain exist in Spampanel
        $I->apiCheckDomainExists($domain);

        // Logout from root
        $I->logout();

        // Login with the customer account
        $I->loginAsClient($customerUsername, $customerPassword);

        // Add an alias domain for the customer account
        $alias = $I->addAliasAsClient($domain);

        // Check if alias domain exist in Spampanel
        $I->apiCheckDomainExists($alias);
    }

    public function verifyNotAutomaticallyAddDomainToPsf(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
        ));

        // Create e new subscription
        $account = $I->addNewSubscription();

        // Check if subscription domain is not present in filter
        $I->checkDomainIsNotPresentInFilter($account['domain']);

        // Check if subscription domain dont exist
        $I->apiCheckDomainNotExists($account['domain']);
    }

    public function verifyNotAutomaticallyDeleteDomainToPsf(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => false,
        ));

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for subscription domain
        $I->toggleProtection($account['domain']);

        // Check if subscription domain exist
        $I->apiCheckDomainExists($account['domain']);

        // Remove subscription
        $I->removeSubscription($account['domain']);

        // Check if subscription domain exist even if subscription was removed
        $I->apiCheckDomainExists($account['domain']);
    }

    public function verifyAutomaticallyDeleteDomainToPsf(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true,
        ));

        // Create a new customer account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change the customer plan to unlimited for the created account
        $I->changeCustomerPlan($customerUsername);

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Check if customer domain is present in filter
        $I->checkDomainIsPresentInFilter($domain);

        // Check if customer domain exist in Spampanel
        $I->apiCheckDomainExists($domain);

        // Logout from root account
        $I->logout();

        // Login with the customer account
        $I->loginAsClient($customerUsername, $customerPassword);

        // Add an alias domain for the customer account
        $alias = $I->addAliasAsClient($domain);

        // Check if alias domain exist in Spampanel
        $I->apiCheckDomainExists($alias);

        // Logout from customer account
        $I->logout();

        // Login as root
        $I->loginAsRoot();

        // Remove subscriptions for the customer account
        $I->removeSubscription($domain);

        // Check if customer domain don't exist in Spampanel
        $I->apiCheckDomainNotExists($domain);

        // Check if alias domain don't exist in Spampanel
        $I->apiCheckDomainNotExists($alias);
    }

    public function verifyAutmaticallyDeleteSecondaryDomains(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::Combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
            Locator::Combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true,
        ));

        // Create a new customer account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change the customer plan to unlimited for the created account
        $I->changeCustomerPlan($customerUsername);

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Check if customer domain is present in filter
        $I->checkDomainIsPresentInFilter($domain);

        // Check if customer domain exist in Spampanel
        $I->apiCheckDomainExists($domain);

        // Logout from root account
        $I->logout();

        // Login with the customer account
        $I->loginAsClient($customerUsername, $customerPassword);

         // Add an alias domain for the customer account
        $alias = $I->addAliasAsClient($domain);

        // Check if alias domain exist in Spampanel
        $I->apiCheckDomainExists($alias);

        // Remove the alias for the customer account
        $I->removeAliasAsClient($alias);

        // Check if alias domain don't exist in Spampanel
        $I->apiCheckDomainNotExists($alias);
    }

    public function verifyNotAutomaticallyChangeMXRecords(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(false);
        $I->setUseExistingMXRecordsOption(false);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for the created subscription domain
        $I->toggleProtection($account['domain']);

        // Open "DNS Settings" for the created subscription
        $I->openSubscription($account['domain']);

        // Check if "Primary MX" is not present in the  "DNS Setings" table
        $I->dontSee(PsfConfig::getPrimaryMX(), "//table[@class='list']");
    }

    public function verifyAutomaticallyChangeMXRecords(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(true);
        $I->setUseExistingMXRecordsOption(false);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for the created subscription domain
        $I->toggleProtection($account['domain']);

        // Open "DNS Settings" for the created subscription
        $I->openSubscription($account['domain']);

        // Check if "Primary MX" is present in the  "DNS Setings" table
        $I->see(PsfConfig::getPrimaryMX(), "//table[@class='list']");
    }

    public function verifyNotUseExistingMXRecords(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(false);
        $I->setUseExistingMXRecordsOption(false);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for the created subscription domain
        $I->toggleProtection($account['domain']);

        // Get subscription domain routes from Spampanel
        $routes = $I->apiGetDomainRoutes($account['domain']);

        // Check if hostname is present in routes list
        $I->assertContains($I->getEnvHostname().'::25', $routes);
    }

    public function verifyUseExistingMXRecords(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setAutomaticallyChangeMXRecordsOption(false);
        $I->setUseExistingMXRecordsOption(true);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for the created subscription domain
        $I->toggleProtection($account['domain']);

        // Get subscription domain routes from Spampanel
        $routes = $I->apiGetDomainRoutes($account['domain']);

        // Check if subscription domain is present in routes list
        $I->assertContains("mail.".$account['domain'].'::25', $routes);
    }

    public function verifyNotConfigureTheEmailAddressForThisDomainOption(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setConfigureEmailAddressOption(false);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Go to the "Domain List" page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search subscription domain in list
        $I->searchDomainList($account['domain']);

        // Toggle protection for that domain
        $I->toggleProtection($account['domain']);

        // Login in Spampanel
        $I->loginOnSpampanel($account['domain']);

        // Go to Spampanel "Domain settings" option
        $I->click('Domain settings');

        // Check if "devnull@spamlogin" is not present in contact email field
        $I->dontSeeInField(Locator::combine(SpampanelPage::CONTACT_EMAIL_FIELD_CSS, SpampanelPage::CONTACT_EMAIL_FIELD_XPATH), 'devnull@spamlogin.com');
    }

    public function verifyConfigureTheEmailAddressForThisDomainOption(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setAutomaticallyAddDomainsToSpamfilterOption(false);
        $I->setConfigureEmailAddressOption(true);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Go to the "Domain List" page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search subscription domain in list
        $I->searchDomainList($account['domain']);

        // Toggle protection for that domain
        $I->toggleProtection($account['domain']);

        // Login in Spampanel
        $I->loginOnSpampanel($account['domain']);

        // Go to Spampanel "Domain settings" option
        $I->click('Domain settings');

        // Check if "devnull@spamlogin" is present in contact email field
        $I->seeInField(Locator::combine(SpampanelPage::CONTACT_EMAIL_FIELD_CSS, SpampanelPage::CONTACT_EMAIL_FIELD_XPATH), 'devnull@spamlogin.com');
    }

    public function verifyNotUseIPAsDestinationOption(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setUseIPAsDestinationOption(false);
        $I->setUseExistingMXRecordsOption(true);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for subscription domain
        $I->toggleProtection($account['domain']);

        // Get subscription domain routes from Spampanel
        $routes = $I->apiGetDomainRoutes($account['domain']);

        // Check if subscription domain is present in routes list
        $I->assertContains("mail.".$account['domain'].'::25', $routes);
    }

    public function verifyUseIPAsDestinationOption(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setUseIPAsDestinationOption(true);

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Toggle protection for subscription domain
        $I->toggleProtection($account['domain']);

        // Get ip for the current addon hostname
        $ip = gethostbyname($I->getEnvHostname());

        // Get subscription domain routes from Spampanel
        $routes = $I->apiGetDomainRoutes($account['domain']);

        // Check if addon hostname ip is present in routes list
        $I->assertContains($ip.'::25', $routes);
    }

    // Fails
    public function verifyAddonDomainsAsNormalDomain(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
        ));

        // Enable a shared IP
        $I->shareIp();

        // Create a new customer account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change the customer plan to unlimited for the created account
        $I->changeCustomerPlan($customerUsername);

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Logout from root account
        $I->logout();

        // Login with the customer account
        $I->login($customerUsername, $customerPassword, true);

        // Add an addon domain name for the customer account
        $addonDomainName = $I->addAddonDomainAsClient($domain);

        // Logout from customer account
        $I->logout();

        // Login as root
        $I->login();

        // Go to "Domain List" page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search the addon domain
        $I->searchDomainList($addonDomainName);

        // Check if the addon domain is present in "Domain List" table
        $I->see($addonDomainName, Locator::combine(DomainListPage::DOMAIN_TABLE_CSS, DomainListPage::DOMAIN_TABLE_XPATH));

        // Check if addon domain domain exist in Spampanel
        $I->apiCheckDomainExists($addonDomainName);
    }

    public function verifyAddonDomainsAsAnAlias(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => true,
        ));

        // Enable a shared IP
        $I->shareIp();

        // Create a new customer account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change the customer plan to unlimited for the created account
        $I->changeCustomerPlan($customerUsername);

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Logout from root account
        $I->logout();

        // Login with the customer account
        $I->loginAsClient($customerUsername, $customerPassword, true);

        // Add an alias domain for the customer account
        $aliasDomain = $I->addAliasAsClient($domain);

        // Logout from customer account
        $I->logout();

        // Login as root
        $I->login();

        // Go to "Domain List" page
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search alias domain in list
        $I->searchDomainList($aliasDomain);

        // Look for alias domain in "Domain List" table
        $I->see($aliasDomain, Locator::combine(DomainListPage::DOMAIN_TABLE_CSS, DomainListPage::DOMAIN_TABLE_XPATH));

        // See if alias domain "Type" is "alias"
        $I->see("alias", Locator::combine(DomainListPage::DOMAIN_TABLE_CSS, DomainListPage::DOMAIN_TABLE_XPATH));
    }

    public function verifyAddonDomainsAsAnAliasSubscription(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => true,
        ));

        // Enable a shared IP
        $I->shareIp();

        // Create a new customer account
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();

        // Change the customer plan to unlimited for the created account
        $I->changeCustomerPlan($customerUsername);

        // Wait in order to domain be present in filter
        $I->wait(120);

        // Check if customer domain exist in Spampanel
        $I->apiCheckDomainExists($domain);

        // Logout from root account
        $I->logout();

        // Login with the client account
        $I->login($customerUsername, $customerPassword, true);

        // Add an alias domain for the customer account
        $aliasDomain = $I->addAliasAsClient($domain);

        // Check if alias domain exist in Spampanel
        $I->apiCheckDomainExists($aliasDomain);

        // Check if alias domain is also an alias for the domain in Spampanel
        $I->assertIsAliasInSpampanel($aliasDomain, $domain);
    }

    public function verifyRedirectBackToPleskUponLogout(ConfigurationSteps $I)
    {
        // Set configuration options needed for the test
        $I->setRedirectBackToPleskOption();

        // Create a new subscription
        $account = $I->addNewSubscription();

        // Search subscription domain in "Domain List"
        $I->searchDomainList($account['domain']);

        // Login in Spampanel using the subscription domain
        $I->loginOnSpampanel($account['domain']);

        // Logout from Spampanel
        $I->logoutFromSpampanel();

        // Check if redirect to plesk works
        $I->seeInCurrentAbsoluteUrl($I->getEnvHostname());
    }
}
