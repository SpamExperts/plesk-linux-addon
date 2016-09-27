<?php

namespace Step\Acceptance;

use Page\ConfigurationPage;
use Page\DomainListPage;
use Page\PleskLinuxClientPage;
use Page\PleskLinuxLoginPage;
use Page\ProfessionalSpamFilterPage;
use Page\SpampanelPage;
use Page\ToolsAndSettingsPage;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverKeys;
use Codeception\Lib\Interfaces\Web;
use Codeception\Util\Locator;

class CommonSteps extends \WebGuy
{
    protected $domainName;
    protected $resellerUsername;
    protected $resellerPassword;
    protected $customerUsername;
    protected $customerPassword;

    private static $accounts = array();

    /**
     * Function used to login into plesk control pannel
     * @param  string  $username   username for login
     * @param  string  $password   passwor for login
     * @param  boolean $isCustomer is user customer or not
     */
    public function login($username = "", $password = "", $isCustomer = false)
    {
        // If username is empty use the default one
        if (empty($username))
            $username = getenv($this->getEnvParameter('username'));

        // If password is empty use the default one
        if (empty($password))
            $password = getenv($this->getEnvParameter('password'));

        $this->amGoingTo("\n\n --- Login as '{$username}' --- \n");
        $this->amOnUrl(getenv($this->getEnvParameter('url')));

        // Wait for page elements
        $this->waitForElement(Locator::combine(PleskLinuxLoginPage::USERNAME_FIELD_XPATH, PleskLinuxLoginPage::USERNAME_FIELD_CSS), 10);
        $this->waitForElement(Locator::combine(PleskLinuxLoginPage::PASSWORD_FIELD_XPATH, PleskLinuxLoginPage::PASSWORD_FIELD_CSS), 10);
        $this->waitForElement(Locator::combine(PleskLinuxLoginPage::LOGIN_BTN_XPATH, PleskLinuxLoginPage::LOGIN_BTN_CSS), 10);

        // Fill username and password
        $this->fillField(Locator::combine(PleskLinuxLoginPage::USERNAME_FIELD_XPATH, PleskLinuxLoginPage::USERNAME_FIELD_CSS), $username);
        $this->fillField(Locator::combine(PleskLinuxLoginPage::PASSWORD_FIELD_XPATH, PleskLinuxLoginPage::PASSWORD_FIELD_CSS), $password);

        // Click login button
        $this->click(Locator::combine(PleskLinuxLoginPage::LOGIN_BTN_XPATH, PleskLinuxLoginPage::LOGIN_BTN_CSS));

        if ($isCustomer) {
            $this->wait(2);
            $this->waitForElement("//button[contains(.,'OK, back to Plesk')]", 30);
            $this->click("//button[contains(.,'OK, back to Plesk')]");
            $this->see("Websites & Domains");
        }
    }

    /**
     * Function used to set configuration options
     * @param array $options options locators
     */
    public function setConfigurationOptions(array $options)
    {
        // Merge received array with the default configuration array
        $options = array_merge($this->getDefaultConfigurationOptions(), $options);

        // Toggle each option from array
        foreach ($options as $option => $check)
            if ($check)
                $this->checkOption($option);
            else
                $this->uncheckOption($option);

        // Save the configuration
        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));

        // See if the settings have been saved
        $this->see('The settings have been saved.');
    }

    /**
     * Function used to return default configuration options
     * @return array array with config options locators
     */
    private function getDefaultConfigurationOptions()
    {
        // Return array with default plugin config options
        return array(
            Locator::combine(ConfigurationPage::ENABLE_SSL_FOR_API_OPT_CSS, ConfigurationPage::ENABLE_SSL_FOR_API_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT_CSS,ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_CSS, ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_CSS, ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::USE_EXISTING_MX_OPT_CSS, ConfigurationPage::USE_EXISTING_MX_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_CSS, ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_CSS, ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_XPATH) => true,
            Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH) => false,
            Locator::combine(ConfigurationPage::USE_IP_AS_DESTINATION_OPT_CSS, ConfigurationPage::USE_IP_AS_DESTINATION_OPT_XPATH) => false,
        );
    }

    /**
     * Function used to logout
     */
    public function logout()
    {
        // Go to the login page .php
        $this->amOnPage('/login_up.php3');
    }

    /**
     * Function used to login in Spampanel as a domain from "Domain List"
     * @param  string $domain desired domain
     */
    public function loginOnSpampanel($domain)
    {
        // Grab Spampanel login link from "href" attribute
        $href = $this->grabAttributeFrom('//a[contains(text(), "Login")]', 'href');

        // Go to Spampanel login link
        $this->amOnUrl($href);

        // Wait for page to load
        $this->waitForText("Welcome to the $domain control panel", 60);
        $this->see("Logged in as: $domain");
        $this->see("Domain User");
    }

    public function logoutFromSpampanel()
    {
        $this->waitForElementVisible(SpampanelPage::LOGOUT_LINK);
        $this->click(SpampanelPage::LOGOUT_LINK);
        $this->waitForElementVisible(SpampanelPage::LOGOUT_CONFIRM_LINK);
        $this->click(SpampanelPage::LOGOUT_CONFIRM_LINK);
    }

    /**
     * Function used to navigate through addon Pages
     * @param  string $page  page name
     * @param  string $title page title
     */
    public function goToPage($page, $title)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Go to {$title} page --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Wait for "Links to Additional Services"
        $this->waitForElement("//td[contains(.,'Links to Additional Services')]");
        $this->wait(1);

        // Click "Professional Spam Filter" option
        $this->click(ProfessionalSpamFilterPage::PROF_SPAM_FILTER_BTN);

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for addon main page to load
        $this->waitForText(ProfessionalSpamFilterPage::TITLE, 10);
        $this->see(ProfessionalSpamFilterPage::TITLE);
        $this->waitForText(ConfigurationPage::TITLE);

        // Click on desired page
        $this->click($page);

        // Wait for page to load
        $this->waitForText($title);
    }

    /**
     * Function used to check if domain is present in filter
     * @param  string $domain domain to check
     */
    public function checkDomainIsPresentInFilter($domain)
    {
        // Go to domain list page
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search desired domain in the list
        $this->searchDomainList($domain);

        // Check if domain status is "Protected"
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);
    }

    /**
     * Function used to check if domain is not present in filter
     * @param  string $domain domain to check
     */
    public function checkDomainIsNotPresentInFilter($domain)
    {
        // Go to domain list page
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

        // Search desired domain in the list
        $this->searchDomainList($domain);

        // Check if domain status is "Unprotected"
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER);
    }

    /**
     * Function used to check if protection status for a domain
     * @param  string $status expected status
     */
    public function checkProtectionStatusIs($status)
    {
        // Click "Check status" button for the domain
        $this->click('Check status');

        // Check if the status is the expected one
        $this->waitForText($status, 120);
    }

    /**
     * Function used to search if domain is present in Domain List
     * @param  string $domain domain to search for
     */
    public function searchDomainList($domain)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Search for {$domain} domain --- \n");

        // Fill search field with the domain name
        $this->fillField(Locator::combine(DomainListPage::SEARCH_FIELD_XPATH, DomainListPage::SEARCH_FIELD_CSS), $domain);

        // Click the search button
        $this->click(Locator::combine(DomainListPage::SEARCH_BTN_XPATH, DomainListPage::SEARCH_BTN_CSS));

        // Wait for search to finish and find just 1 item
        $this->waitForText('Page 1 of 1. Total Items: 1');

        // See if domain is present in domain list table
        $this->see($domain, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
    }

    /**
     * Function used to check if domain is in domain list
     * @param  string  $domainName domain to check
     * @param  boolean $isRoot     enter as root or as reseller or customer
     */
    public function checkDomainList($domainName, $isRoot = false)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check Domain list is present --- \n");

        // If logged as root
        if ($isRoot) {
            // Go to "Domain List" page
            $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);

            // Check if domain is present in list
            $this->see($domainName, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        }
        // If logged as reseller or customer
        else {
            // Switch to left frame
            $this->switchToLeftFrame();

            // Click the "Professional Spam Filter" addon button
            $this->click(ProfessionalSpamFilterPage::PROF_SPAM_FILTER_BTN);

            // Switch to main frame
            $this->switchToWorkFrame();

            // Display info message
            $this->amGoingTo("Check domain '{$domainName}' is present on the list");

            // Check if domain is present in list
            $this->see($domainName, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        }
    }

    /**
     * Function used to enable a shared IP
     * @param  string $resellerId reseller account id
     */
    public function shareIp($resellerId = null)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Enable a shared IP --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Go to home page
        $this->click(ToolsAndSettingsPage::HOME_BTN_XPATH);

        // Switch to top frame
        $this->switchToTopFrame();

        // Type "IP Addresses" in the search bar
        $this->fillField(Locator::combine(PleskLinuxClientPage::CLIENT_SEARCH_BAR_CSS, PleskLinuxClientPage::CLIENT_SEARCH_BAR_XPATH), "IP Addresses");

        // Press "Enter" key
        $this->pressKey(Locator::combine(PleskLinuxClientPage::CLIENT_SEARCH_BAR_CSS, PleskLinuxClientPage::CLIENT_SEARCH_BAR_XPATH),WebDriverKeys::ENTER);

        // Switch to main frame
        $this->switchToWorkFrame();

        $this->waitForText("View, add, remove IP addresses, and assign IP addresses to resellers.", 30);

        // Click on the last IPv4 address in list
        $this->click(ToolsAndSettingsPage::EDIT_IP_ADDRESS_LINK_XPATH);

        // Check "IP adress is distributed as Shared" option for that IP
        $this->checkOption(Locator::combine(ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_XPATH, ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_CSS));

        // Click the "OK" button
        $this->click(Locator::combine(ToolsAndSettingsPage::OK_BTN_XPATH, ToolsAndSettingsPage::OK_BTN_CSS));

        // Wait for settings to save
        $this->waitForElementNotVisible(Locator::combine(ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_XPATH, ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_CSS), 30);

        // Wait for success message
        $this->waitForElement(Locator::combine(ToolsAndSettingsPage::IP_ADDRESSES_MSJ_XPATH, ToolsAndSettingsPage::IP_ADDRESSES_MSJ_CSS), 30);
        $this->waitForText("The properties of the IP address", 30);

        // If reseller Id provided, repeat the above steps but for the reseller id not for the last IPv4 address
        if ($resellerId) {
            $this->click(ToolsAndSettingsPage::IP_RESELLER_OPTION_XPATH);
            $this->waitForText("Resellers who use Shared IP address");
            $this->click(Locator::combine(ToolsAndSettingsPage::RESELLER_ASSIGN_BTN_XPATH, ToolsAndSettingsPage::RESELLER_ASSIGN_BTN_CSS));
            $this->waitForText("Add IP address to reseller's pool");
            $this->checkOption("//input[@id='del_{$resellerId}']");
            $this->click(Locator::combine(ToolsAndSettingsPage::ADD_IP_TO_RESELLER_OK_BTN_XPATH, ToolsAndSettingsPage::ADD_IP_TO_RESELLER_OK_BTN_CSS));
            $this->waitForText("Resellers who use Shared IP address");
            $this->seeElement("//table/tbody/tr/td//a[contains(@href, '/admin/reseller/overview/id/{$resellerId}')]");
        }
    }

    /**
     * Function used to check if Professional Spam Filter is installed for root
     * @param  string $brandname expected brandname
     */
    public function checkPsfPresentForRoot($brandname = 'Professional Spam Filter')
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check PSF is present at root level --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Wait for Links to Aditional Services category to show
        $this->waitForElement("//td[contains(.,'Links to Additional Services')]");

        // Click the brandname
        $this->click("//a[contains(.,'{$brandname}')]");

        // Switch back to mainframe
        $this->switchToWorkFrame();

        // Wait for brandname text to appear
        $this->waitForText($brandname);
    }

    /**
     * Function used to check if Professional Spam Filter is installed for reseller
     * @param  string $brandname expected brandname
     */
    public function checkPsfPresentForReseller($brandname = 'Professional Spam Filter')
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check PSF is present at reseller level --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Wait fo "Links to Aditional Services" category to appear
        $this->waitForElement("//td[contains(.,'Links to Additional Services')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for expected brandname text to appear
        $this->waitForText($brandname);
        $this->see($brandname);
        $this->see('Home');

        // Click the new brandname
        $this->click("//a[contains(.,'{$brandname}')]");

        // Check if no domain is present in list
        $this->waitForText("There are no domains on this server.", 30);
    }

    /**
     * Function used to check if Professional Spam Filter is installed for customer
     * @param  string $brandname expected brandname
     */
    public function checkPsfPresentForCustomer($brandname = 'Professional Spam Filter')
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check PSF is present at customer level --- \n");

        // Wait for expected brandname text to appear
        $this->waitForText($brandname);

        // Fill the new brand name in the top search bar
        $this->fillField(Locator::combine(PleskLinuxClientPage::CLIENT_SEARCH_BAR_CSS, PleskLinuxClientPage::CLIENT_SEARCH_BAR_CSS), $brandname);

        // Press "Enter" key
        $this->pressKey(Locator::combine(PleskLinuxClientPage::CLIENT_SEARCH_BAR_CSS, PleskLinuxClientPage::CLIENT_SEARCH_BAR_CSS),WebDriverKeys::ENTER);

        // Wait for "pageIframe" to load
        $this->waitForElement("#pageIframe", 100);

        // Switch to "pageIframe"
        $this->switchToIFrame("pageIframe");

        // Wait for page to load
        $this->waitForElement("//h3[contains(.,'List Domains')]", 30);

        // Expect to see just a single domain in "Domain List"
        $this->see($this->domain, Locator::combine(PleskLinuxClientPage::CLIENT_DOMAIN_TABLE_CSS, PleskLinuxClientPage::CLIENT_DOMAIN_TABLE_XPATH));
        // $this->dontSeeElement("//tbody/tr[2]");
    }

    /**
     * Function used to create a reseller account
     * @return array array containing reseller username, password and id
     */
    public function createReseller()
    {
        // Generate unique reseller username
        $this->resellerUsername = uniqid("reseller");

        // Generate unique password
        $this->resellerPassword = uniqid("xX");

        // Display info message
        $this->amGoingTo("\n\n --- Create a new reseller '{$this->resellerUsername}' --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click on "Resellers" button
        $this->click("//a[contains(.,'Resellers')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for Resellers page to load
        $this->waitForText("This is where you manage accounts of resellers", 100);

        // Click "Add New Reseller" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_NEW_RESELLER_BTN_CSS, PleskLinuxClientPage::ADD_NEW_RESELLER_BTN_XPATH));

        # Contact Information fields

        // Fill "Contact name" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::CONTACT_NAME_FIELD_CSS, PleskLinuxClientPage::CONTACT_NAME_FIELD_XPATH), $this->resellerUsername);

        // Fill "Email address" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_CSS, PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_XPATH), "test@example.com");

        // Fill "Phone number" field (even if is not mandatory is needed later)
        $this->fillField(Locator::combine(PleskLinuxClientPage::PHONE_NUMBER_FIELD_CSS, PleskLinuxClientPage::PHONE_NUMBER_FIELD_XPATH), "0123456789");

        # Access to Plesk fields

        // Fill "Username" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_USERNAME_FIELD_CSS, PleskLinuxClientPage::PLESK_USERNAME_FIELD_XPATH), $this->resellerUsername);

        // Fill "Password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_PASSWORD_FIELD_XPATH), $this->resellerPassword);

        // Fill "Repeat password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_XPATH), $this->resellerPassword);

        // Click "OK" button
        $this->click("//button[@name='send']");
        $this->waitForElementNotVisible("//button[@name='send']", 100);

        // Wait for success message to appear
        $this->waitForElement("//div[@class='msg-box msg-info']", 10);
        $this->see("Reseller {$this->resellerUsername} was created.", "//div[@class='msg-box msg-info']");

        // Grab resellerId from page
        $href = $this->grabAttributeFrom("//table[@id='resellers-list-table']/tbody/tr/td//a[text()='{$this->resellerUsername}']", 'href');
        $resellerId = array_pop(explode('/', $href));

        // Return array with username, password and resellerId
        return [$this->resellerUsername, $this->resellerPassword, $resellerId];
    }

    /**
     * Function used to create a customer account
     * @return array array containing username, password and domain
     */
    public function createCustomer()
    {
        // Generate an unique customer username
        $this->customerUsername = uniqid("customer");

        // Generate a unique password
        $this->customerPassword = uniqid("xX");

        // Generate a unique domain domain(unique).example.com
        $this->domain = uniqid("domain") . ".example.com";

        // Display info message
        $this->amGoingTo("\n\n --- Create a new customer '{$this->customerUsername}' --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click on "Customers" button
        $this->click("//a[contains(.,'Customers')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for Customers page to load
        $this->waitForText("This is where you manage accounts of your hosting service customers", 100);

        // Click "Add New Customer" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_NEW_CUSTOMER_BTN_CSS, PleskLinuxClientPage::ADD_NEW_CUSTOMER_BTN_XPATH));

        # Contact Information fields

        // Fill "Contact name" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::CONTACT_NAME_FIELD_CSS, PleskLinuxClientPage::CONTACT_NAME_FIELD_XPATH), $this->customerUsername);

        // Fill "Email address" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_CSS, PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_XPATH), "test@example.com");

        // Fill "Phone number" field (even if is not mandatory is needed later)
        $this->fillField(Locator::combine(PleskLinuxClientPage::PHONE_NUMBER_FIELD_CSS, PleskLinuxClientPage::PHONE_NUMBER_FIELD_XPATH), "0123456789");

        # Access to Plesk fields

        // Fill "Username" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_USERNAME_FIELD_CSS, PleskLinuxClientPage::PLESK_USERNAME_FIELD_XPATH), $this->customerUsername);

        // Fill "Password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_PASSWORD_FIELD_XPATH), $this->customerPassword);

        // Fill "Repeat password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_XPATH), $this->customerPassword);

        # Subscription

        // Fill "Domain name" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_DOMAIN_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_DOMAIN_FIELD_XPATH), $this->domain);

        // Fill "Username" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_USERNAME_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_USERNAME_FIELD_XPATH), $this->customerUsername);

        // Fill "Password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_PASSWORD_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_PASSWORD_FIELD_XPATH), $this->customerPassword);

        // Fill "Repeat password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH), $this->customerPassword);

        // Select "Service plan" option to "Default domain"
        $this->selectOption(Locator::combine(PleskLinuxClientPage::SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_CSS, PleskLinuxClientPage::SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_XPATH), "Default Domain");

        // Click "OK" button
        $this->click(Locator::combine(PleskLinuxClientPage::CREATE_NEW_CUSTOMER_OK_BTN_CSS, PleskLinuxClientPage::CREATE_NEW_CUSTOMER_OK_BTN_XPATH));

        // Wait for success message to appear
        $this->waitForText("Customer {$this->customerUsername} was created.", 200);

        // Return array with username, password and domain
        return [$this->customerUsername, $this->customerPassword, $this->domain];
    }

    /**
     * Function used to change Customer Plan to Unlimited for a customer account
     * @param  [type] $customerUsername customer username
     */
    public function changeCustomerPlan($customerUsername)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Change the subscription plan for '$this->customerUsername' --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click "Customers" button
        $this->click("//a[contains(.,'Customers')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for table to appear
        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::CUSTOMER_LIST_TABLE_XPATH, PleskLinuxClientPage::CUSTOMER_LIST_TABLE_CSS), 30);

        // Click the desired customer username
        $this->click("//a[contains(.,'$this->customerUsername')]");

        // Wait for "Subscriptions" category to load
        $this->waitForElementVisible("//a[contains(.,'Subscription')]");

        // Check all subscriptions
        $this->checkOption("//input[contains(@name,'listGlobalCheckbox')]");

        // Click "Change Plan" button
        $this->click(Locator::combine(PleskLinuxClientPage::CHANGE_PLAN_BTN_CSS, PleskLinuxClientPage::CHANGE_PLAN_BTN_XPATH));

        // Wait for "New service plan" drop down to appear
        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::NEW_SERVICE_PLAN_DROP_DOWN_CSS, PleskLinuxClientPage::NEW_SERVICE_PLAN_DROP_DOWN_XPATH));

        // Select Unlimited as the "New service plan"
        $this->selectOption(Locator::combine(PleskLinuxClientPage::NEW_SERVICE_PLAN_DROP_DOWN_CSS, PleskLinuxClientPage::NEW_SERVICE_PLAN_DROP_DOWN_XPATH), "Unlimited");

        // Click OK button
        $this->click(Locator::combine(PleskLinuxClientPage::CHANGE_PLAN_ON_BTN_CSS, PleskLinuxClientPage::CHANGE_PLAN_ON_BTN_XPATH));

        // Wait for success message
        $this->waitForText("Selected subscriptions were successfully re-associated with service plans.", 100);
    }

    /**
     * Function used to add new subscription for a certain account
     * @param array $params subscription info
     */
    public function addNewSubscription(array $params = array())
    {
        // If domain name not present in $params, generate a random one
        if (empty($params['domain']))
            $params['domain'] = $this->generateRandomDomainName();

        // If username not present in $params, generate a random one
        if (empty($params['username']))
            $params['username'] = $this->generateRandomUserName();

        // If password not present in $params, generate a random one
        if (empty($params['password']))
            $params['password'] = uniqid();

        // Display info message
        $this->amGoingTo("\n\n --- Add a new subscription for '{$params['domain']}'--- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click "Subscriptions" button
        $this->click("//a[contains(.,'Subscriptions')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for Subscriptions page to load
        $this->waitForText("Customers obtain hosting services from you by subscribing to a hosting plan", 100);

        // Click "Add New Subscription" button
        $this->click(Locator::combine(PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_CSS, PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_XPATH));

        // Fill "Domain name" field with the domain
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_DOMAIN_FIELD_CSS, PleskLinuxClientPage::ADD_SUBSCRIPTION_DOMAIN_FIELD_XPATH), $params['domain']);

        // Fill "Username" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_USERNAME_FIELD_CSS, PleskLinuxClientPage::ADD_SUBSCRIPTION_USERNAME_FIELD_XPATH), $params['username']);

        // Fill "Password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_PASSWORD_FIELD_CSS, PleskLinuxClientPage::ADD_SUBSCRIPTION_PASSWORD_FIELD_XPATH), $params['password']);

        // Fill "Repeat password" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::ADD_SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH), $params['password']);

        // Click the "OK" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_OK_BTN_CSS, PleskLinuxClientPage::ADD_SUBSCRIPTION_OK_BTN_XPATH));

        // $this->waitForElementNotVisible(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_OK_BTN_CSS, PleskLinuxClientPage::ADD_SUBSCRIPTION_OK_BTN_XPATH), 100);

        // Wait for success message to appears
        $this->waitForText("Subscription {$params['domain']} was created.", 100);

        // Create an array with the subscription info
        $account = array(
            'domain' => $params['domain'],
            'username' => $params['username'],
            'password' => $params['password']
        );

        self::$accounts[] = $account;

        return $account;
    }

    /**
     * Function used to remove all created subscriptions
     */
    public function removeCreatedSubscriptions()
    {
        foreach(self::$accounts as $account)
            $this->removeSubscription($account['domain']);
    }

    /**
     * Function used to remove created subscription for a certain domain name
     * @param  string $domainName desired domain
     */
    public function removeSubscription($domainName)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Remove subscription for '{$domainName}'--- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click "Subscriptions" button
        $this->wait(1);
        $this->click("//a[contains(.,'Subscriptions')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Wait for subscription list
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_TABLE_CSS, PleskLinuxClientPage::SUBSCRIPTION_TABLE_XPATH), 30);

        // Wait for subscription domain name
        $this->waitForElement("//div[@class='b-indent status-ok']/a[contains(text(), '{$domainName}')]");

        // Grab href of the subscription domain name
        $value = $this->grabAttributeFrom("//div[@class='b-indent status-ok']/a[contains(text(), '{$domainName}')]", 'href');
        $subscriptionNo = array_pop(explode('/', $value));

        // Check subscription domain name
        $this->checkOption("//input[@value='{$subscriptionNo}']");

        // Click remove button
        $this->click("//span[contains(.,'Remove')]");

        // Wait for remove confirmation modal to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 30);

        // Wait for remove button to appear
        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        // Wait for text inside button to change to "Yes"
        $this->waitForText("Yes");

        // Click the "Yes" button
        $this->click(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH));

        // Wait for button to disappear
        $this->waitForElementNotVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        // Wait for success message
        $this->waitForElementVisible("//div[@class='msg-content']", 30);
        $this->waitForText("Selected subscriptions were removed.", 30);
    }

    /**
     * Function used to open "DNS Settings" option for a subscription
     * @param  string $domainName domain name
     */
    public function openSubscription($domainName)
    {
        // Display info message
        $this->amGoingTo("\n\n --- Open subscription for '{$domainName}'--- \n");

        // Switch to left fraame
        $this->switchToLeftFrame();

        // Click "Subscription" option
        $this->click("//a[contains(.,'Subscriptions')]");

        // Switch to main frame
        $this->switchToWorkFrame();

        // Fill search bar field with the domain name
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTIONS_SEARCH_BAR_CSS , PleskLinuxClientPage::SUBSCRIPTIONS_SEARCH_BAR_XPATH), $domainName);

        // Click "Search" button
        $this->click(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_SUBMIT_SEARCH_CSS, PleskLinuxClientPage::SUBSCRIPTION_SUBMIT_SEARCH_XPATH));

        // Wait for search to finish
        $this->wait(2);

        // Click "Manage Hosting" button
        $this->click("Manage Hosting");

        // Wait for page to load
        $this->waitForText("Websites & Domains", 30);

        // Click "Show More" link
        $this->click("//span[contains(.,'Show More')]");

        // Wait for options to show
        $this->wait(2);

        // Click "DNS Settings" option
        $this->click("//span[contains(.,'DNS Settings')]");

        // Wait for table to appear
        $this->waitForElementVisible("//table[@class='list']");
    }

    /**
     * Function used to generate random domain name
     * @return string domain
     */
    public function generateRandomDomainName()
    {
        $domain = uniqid("domain") . ".example.com";
        $this->comment("I generated random domain: $domain");

        return $domain;
    }

    /**
     * Function used to generate random username
     * @return string username
     */
    public function generateRandomUserName()
    {
        $username = uniqid("pleskuser");
        $this->comment("I generated random username: $username");

        return $username;
    }

    /**
     * Function used to get addon hostname
     * @return string addon hostname
     */
    public function getEnvHostname()
    {
        $url = getenv($this->getEnvParameter('url'));
        $parsed = parse_url($url);

        if (empty($parsed['host']))
            throw new \Exception("Couldnt parse url");

        return $parsed['host'];
    }

    /**
     * Function used to switch to left frame of plesk control panel
     */
    public function switchToLeftFrame()
    {
        // Switch to main window
        $this->switchToWindow();

        // Wait for left frame to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::LEFT_FRAME_CSS, PleskLinuxClientPage::LEFT_FRAME_XPATH));

        // Switch to main frame
        $this->switchToIFrame(PleskLinuxClientPage::LEFT_FRAME_NAME);
    }

    /**
     * Function used to switch to main frame of plesk control panel
     */
    public function switchToWorkFrame()
    {
        // Switch to main window
        $this->switchToWindow();

        // Wait for main frame to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::WORK_FRAME_CSS, PleskLinuxClientPage::WORK_FRAME_XPATH));

        // Switch to main frame
        $this->switchToIFrame(PleskLinuxClientPage::WORK_FRAME_NAME);
    }

    /**
     * Function used to switch to top frame of plesk control panel
     */
    public function switchToTopFrame()
    {
        // Switch to main window
        $this->switchToWindow();

        // Wait for main frame to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::TOP_FRAME_CSS, PleskLinuxClientPage::TOP_FRAME_XPATH));

        // Switch to main frame
        $this->switchToIFrame(PleskLinuxClientPage::TOP_FRAME_NAME);
    }

    public function goToConfigurationPageAndSetOptions(array $options)
    {
        $this->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);
        $this->setConfigurationOptions($options);
    }

    /**
     * Function used to add an alias domain for a customer account
     * @param string $domain customer domain
     * @param string $alias  alias name (optional)
     */
    public function addAliasAsClient($domain, $alias = null)
    {
        // If no alias provided, generate one based on domain
        if (!$alias)
            $alias = 'alias' . $domain;


        // Wait for "Add New Domain Alias" button to show
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::ADD_NEW_DOMAIN_ALIAS_BTN_CSS, PleskLinuxClientPage::ADD_NEW_DOMAIN_ALIAS_BTN_CSS), 10);

        // Click "Add New Domain Alias" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_NEW_DOMAIN_ALIAS_BTN_CSS, PleskLinuxClientPage::ADD_NEW_DOMAIN_ALIAS_BTN_XPATH));

        // Wait for page to load
        $this->waitForText('Add a Domain Alias', 60);

        // Filll "Domain alias name" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::DOMAIN_ALIAS_NAME_FIELD_CSS, PleskLinuxClientPage::DOMAIN_ALIAS_NAME_FIELD_XPATH), $alias);

        // Wait for "OK" button to show
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::ADD_ALIAS_DOMAIN_OK_BTN_CSS, PleskLinuxClientPage::ADD_ALIAS_DOMAIN_OK_BTN_XPATH), 10);

        // Click "OK" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_ALIAS_DOMAIN_OK_BTN_CSS, PleskLinuxClientPage::ADD_ALIAS_DOMAIN_OK_BTN_XPATH));

        // Wait for success message
        $this->waitForText("The domain alias $alias was created.", 100);

        return $alias;
    }

    public function removeAliasAsClient($alias)
    {
        $this->click($alias);
        $this->click("Remove Domain Alias");
        $this->waitForText("Removing this website will also delete all related files, directories, and web applications from the server.");
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 30);

        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        $this->waitForText("Yes");

        $this->click(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH));

        $this->dontSee($alias);
    }

    /**
     * Function used to login as client
     * @param  string $customerUsername client username
     * @param  string $customerPassword client password
     */
    public function loginAsClient($customerUsername, $customerPassword)
    {
        $this->login($customerUsername, $customerPassword, true);
    }

    /**
     * Function used to login as root
     */
    public function loginAsRoot()
    {
        $this->login();
    }

    /**
     * Function used to add an addon domain for a customer account
     * @param string $domain customer domain
     * @param string $addonDomainName  addon domain name (optional)
     */
    public function addAddonDomainAsClient($domain, $addonDomainName = null)
    {
        // If no addon domain provided, generate one based on domain
        if (!$addonDomainName)
            $addonDomainName = 'addon' . $domain;

        // Wait for "Add domain" button to show
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::ADD_NEW_DOMAIN_BTN_CSS, PleskLinuxClientPage::ADD_NEW_DOMAIN_BTN_XPATH), 30);

        // Click "Add Domain" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_NEW_DOMAIN_BTN_CSS, PleskLinuxClientPage::ADD_NEW_DOMAIN_BTN_XPATH));

        // Wait for page to load
        $this->waitForText('Adding New Domain Name', 30);

        // Fill "Domain name" field
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADDON_DOMAIN_NAME_FIELD_CSS, PleskLinuxClientPage::ADDON_DOMAIN_NAME_FIELD_XPATH), $addonDomainName);

        // Wait for "OK" button to show
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::ADD_DOMAIN_OK_BTN_CSS, PleskLinuxClientPage::ADD_DOMAIN_OK_BTN_XPATH));

        // Click the "OK" button
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_DOMAIN_OK_BTN_CSS, PleskLinuxClientPage::ADD_DOMAIN_OK_BTN_XPATH));

        // Wait for success message
        $this->waitForText("The domain $addonDomainName was successfully created.", 100);

        return $addonDomainName;
    }

    /**
     * Function used to remove all created customers,resellers, domains or subscriptions from Plesk and Spampanel
     */
    public function cleanupPlesk()
    {
        // Logout from any account
        $this->logout();

        // Login as root
        $this->loginAsRoot();

        // Go to the configuration page
        $this->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);

        // Make sure removed domains are removed from Spampanel
        $this->setConfigurationOptions(array(
            Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH) => true
        ));

        // Remove all created customer accounts
        $this->removeAllCustomers();

        // Remove all created reseller accounts
        $this->removeAllResellers();

        // Remove all created subscriptions
        $this->removeAllSubscriptions();
    }

    /**
     * Function used to remove all customers from Plesk
     */
    public function removeAllCustomers()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Remove all customer accounts --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click on "Customers" option
        $this->click("//a[contains(.,'Customers')]");

        // Switch to left main frame
        $this->switchToWorkFrame();

        // Check if there are any Customers in list
        if (!$this->getElementsCount("//td[contains(@class,'select')]"))
            return;

        // Select all customers
        $this->click("//input[@name='listGlobalCheckbox']");

        // Click the remove button
        $this->click("#buttonRemoveUser");

        // Wait for modal to show
        $this->waitForText("Do you want to remove the selected customer accounts?", 30);

        // Wait for "Yes" button to show
        $this->waitForText("Yes", 30);

        // Click on "Yes" button
        $this->click("//button[contains(.,'Yes')]");

        // Wait for removal to finish
        $this->waitForText("Selected customers were deleted", 300);
    }

    /**
     * Function used to remove all resellers from Plesk
     */
    public function removeAllResellers()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Remove all reseller accounts --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click on "Customers" option
        $this->click("//a[contains(.,'Resellers')]");

        // Switch to left main frame
        $this->switchToWorkFrame();

        // Check if there are any Customers in list
        if (!$this->getElementsCount("//td[contains(@class,'select')]"))
            return;

        // Select all customers
        $this->click("//input[@name='listGlobalCheckbox']");

        // Click the remove button
        $this->click("#buttonRemoveUser");

        // Wait for modal to show
        $this->waitForText("Do you really want to remove the selected resellers and all their service plans, customers and subscriptions?", 30);

        // Wait for "Yes" button to show
        $this->waitForText("Yes", 30);

        // Click on "Yes" button
        $this->click("//button[contains(.,'Yes')]");

        // Wait for removal to finish
        $this->waitForText("The selected resellers were removed", 300);
    }

    /**
     * Function used to remove all subscriptions from Plesk
     */
    public function removeAllSubscriptions()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Remove all subscriptions --- \n");

        // Switch to left frame
        $this->switchToLeftFrame();

        // Click on "Customers" option
        $this->click("//a[contains(.,'Subscriptions')]");

        // Switch to left main frame
        $this->switchToWorkFrame();

        // Check if there are any Customers in list
        if (!$this->getElementsCount("//td[contains(@class,'select')]"))
            return;

        // Select all customers
        $this->click("//input[@name='listGlobalCheckbox']");

        // Click the remove button
        $this->click("#buttonRemoveUser");

        // Wait for modal to show
        $this->waitForText("Do you really want to remove selected subscriptions?", 30);

        // Wait for "Yes" button to show
        $this->waitForText("Yes", 30);

        // Click on "Yes" button
        $this->click("//button[contains(.,'Yes')]");

        // Wait for removal to finish
        $this->waitForText("Selected subscriptions were removed", 300);
    }
}
