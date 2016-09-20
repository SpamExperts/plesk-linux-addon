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
            $canSeeElement = $this->canSeeElement("//button[contains(text(), 'OK, back to Plesk')]");
            if ($canSeeElement) {
                $this->click("//button[contains(text(), 'OK, back to Plesk')]");
            }
            $this->see("Websites & Domains");
            $this->seeElement("//span[contains(.,'Professional Spam Filter')]");
        } else {
            $this->switchToTopFrame();
            $this->waitForElement("//img[contains(@name,'logo')]");
            $this->see('Log out', "//a[contains(.,'Log out')]");
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
        $this->amOnPage('/login_up.php3');
    }

    public function loginOnSpampanel($domain)
    {
        $href = $this->grabAttributeFrom('//a[contains(text(), "Login")]', 'href');
        $this->amOnUrl($href);
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

    public function goToPage($page, $title)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Go to {$title} page --- \n");
        $I->switchToLeftFrame();
        $I->waitForElement("//td[contains(.,'Links to Additional Services')]");
        $I->wait(1);
        $I->click(ProfessionalSpamFilterPage::PROF_SPAM_FILTER_BTN);
        $I->switchToWorkFrame();
        $I->waitForText(ProfessionalSpamFilterPage::TITLE, 10);
        $I->see(ProfessionalSpamFilterPage::TITLE);
        $I->waitForText(ConfigurationPage::TITLE);
        $I->click($page);
        $I->waitForText($title);
    }

    public function checkDomainIsPresentInFilter($domain)
    {
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($domain);
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER);
    }

    public function checkDomainIsNotPresentInFilter($domain)
    {
        $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $this->searchDomainList($domain);
        $this->checkProtectionStatusIs(DomainListPage::STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER);
    }

    public function checkProtectionStatusIs($status)
    {
        $this->click('Check status');
        $this->waitForText($status, 60);
    }

    public function searchDomainList($domain)
    {
        $this->amGoingTo("\n\n --- Search for {$domain} domain --- \n");
        $this->fillField(Locator::combine(DomainListPage::SEARCH_FIELD_XPATH, DomainListPage::SEARCH_FIELD_CSS), $domain);
        $this->click(Locator::combine(DomainListPage::SEARCH_BTN_XPATH, DomainListPage::SEARCH_BTN_CSS));
        $this->waitForText('Page 1 of 1. Total Items: 1');
        $this->see($domain, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
    }

    public function checkDomainList($domainName, $isRoot = false)
    {
        $this->amGoingTo("\n\n --- Check Domain list is present --- \n");
        if ($isRoot) {
            $this->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
            $this->see($domainName, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        }
        else {
            $this->switchToLeftFrame();
            $this->click(ProfessionalSpamFilterPage::PROF_SPAM_FILTER_BTN);
            $this->switchToWorkFrame();
            $this->amGoingTo("Check domain '{$domainName}' is present on the list");
            $this->see($domainName, Locator::combine(DomainListPage::DOMAIN_TABLE_XPATH, DomainListPage::DOMAIN_TABLE_CSS));
        }
    }

    public function shareIp($resellerId = null)
    {
        $this->amGoingTo("\n\n --- Enable a shared IP --- \n");
        $this->switchToLeftFrame();
        $this->click(ToolsAndSettingsPage::TOOLS_N_SETTINGS_LINK_XPATH);
        $this->switchToWorkFrame();
        $this->click(Locator::combine(ToolsAndSettingsPage::IP_ADDRESSES_BTN_XPATH, ToolsAndSettingsPage::IP_ADDRESSES_BTN_CSS));
        $this->click(ToolsAndSettingsPage::EDIT_IP_ADDRESS_LINK_XPATH);
        $this->checkOption(Locator::combine(ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_XPATH, ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_CSS));
        $this->click(Locator::combine(ToolsAndSettingsPage::OK_BTN_XPATH, ToolsAndSettingsPage::OK_BTN_CSS));
        $this->waitForElementNotVisible(Locator::combine(ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_XPATH, ToolsAndSettingsPage::SHARED_OPTION_CHECKBOX_CSS), 30);
        $this->waitForElement(Locator::combine(ToolsAndSettingsPage::IP_ADDRESSES_MSJ_XPATH, ToolsAndSettingsPage::IP_ADDRESSES_MSJ_CSS), 30);
        $this->see("The properties of the IP address");
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
     * Function used to check if Professional Spam Filter is installed
     * @param  string $brandname Brand name
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

        // See if the brandname text is displayed correctly
        $this->see($brandname);
    }

    public function checkPsfPresentForReseller($brandname = 'Professional Spam Filter')
    {
        $this->amGoingTo("\n\n --- Check PSF is present at reseller level --- \n");
        $this->switchToLeftFrame();
        $this->waitForElement("//td[contains(.,'Links to Additional Services')]");
        $this->switchToWorkFrame();
        $this->waitForText($brandname);
        $this->see($brandname);
        $this->see('Home');
        $this->click(ProfessionalSpamFilterPage::PROSPAMFILTER_BTN);
        $this->waitForElement("//h3[contains(.,'List Domains')]");
        $this->see("There are no domains on this server.", "//div[@class='alert alert-info']");
    }

    public function checkPsfPresentForCustomer($brandname = 'Professional Spam Filter')
    {
        $this->amGoingTo("\n\n --- Check PSF is present at customer level --- \n");
        $this->click("//span[contains(.,'{$brandname}')]");
        $this->see($brandname);
        $this->switchToIFrame("pageIframe");
        $this->waitForElement("//h3[contains(.,'List Domains')]");
        $this->see($this->domain, "//tbody/tr[1]");
        $this->dontSeeElement("//tbody/tr[2]");
    }

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

        // Click "Add New Reseller" button
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::ADD_NEW_RESELLER_BTN_CSS, PleskLinuxClientPage::ADD_NEW_RESELLER_BTN_XPATH), 10);
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_NEW_RESELLER_BTN_CSS, PleskLinuxClientPage::ADD_NEW_RESELLER_BTN_XPATH));

        # Contact Information fields

        // Fill "Contact name" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::CONTACT_NAME_FIELD_CSS, PleskLinuxClientPage::CONTACT_NAME_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::CONTACT_NAME_FIELD_CSS, PleskLinuxClientPage::CONTACT_NAME_FIELD_XPATH), $this->resellerUsername);

        // Fill "Email address" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_CSS, PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_CSS, PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_XPATH), "test@example.com");

        // Fill "Phone number" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PHONE_NUMBER_FIELD_CSS, PleskLinuxClientPage::PHONE_NUMBER_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PHONE_NUMBER_FIELD_CSS, PleskLinuxClientPage::PHONE_NUMBER_FIELD_XPATH), "0123456789");

        # Access to Plesk fields

        // Fill "Username" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PLESK_USERNAME_FIELD_CSS, PleskLinuxClientPage::PLESK_USERNAME_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_USERNAME_FIELD_CSS, PleskLinuxClientPage::PLESK_USERNAME_FIELD_XPATH), $this->resellerUsername);

        // Fill "Password" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PLESK_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_PASSWORD_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_PASSWORD_FIELD_XPATH), $this->resellerPassword);

        // Fill "Repeat password" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_XPATH), 10);
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

        // Click "Add New Customer" button
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::ADD_NEW_CUSTOMER_BTN_CSS, PleskLinuxClientPage::ADD_NEW_CUSTOMER_BTN_XPATH), 10);
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_NEW_CUSTOMER_BTN_CSS, PleskLinuxClientPage::ADD_NEW_CUSTOMER_BTN_XPATH));

        # Contact Information fields

        // Fill "Contact name" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::CONTACT_NAME_FIELD_CSS, PleskLinuxClientPage::CONTACT_NAME_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::CONTACT_NAME_FIELD_CSS, PleskLinuxClientPage::CONTACT_NAME_FIELD_XPATH), $this->customerUsername);

        // Fill "Email address" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_CSS, PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_CSS, PleskLinuxClientPage::EMAIL_ADDRESS_FIELD_XPATH), "test@example.com");

        // Fill "Phone number" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PHONE_NUMBER_FIELD_CSS, PleskLinuxClientPage::PHONE_NUMBER_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PHONE_NUMBER_FIELD_CSS, PleskLinuxClientPage::PHONE_NUMBER_FIELD_XPATH), "0123456789");

        # Access to Plesk fields

        // Fill "Username" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PLESK_USERNAME_FIELD_CSS, PleskLinuxClientPage::PLESK_USERNAME_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_USERNAME_FIELD_CSS, PleskLinuxClientPage::PLESK_USERNAME_FIELD_XPATH), $this->customerUsername);

        // Fill "Password" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PLESK_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_PASSWORD_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_PASSWORD_FIELD_XPATH), $this->customerPassword);

        // Fill "Repeat password" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::PLESK_REPEAT_PASSWORD_FIELD_XPATH), $this->customerPassword);

        # Subscription

        // Fill "Domain name" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_DOMAIN_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_DOMAIN_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_DOMAIN_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_DOMAIN_FIELD_XPATH), $this->domain);

        // Fill "Username" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_USERNAME_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_USERNAME_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_USERNAME_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_USERNAME_FIELD_XPATH), $this->customerUsername);

        // Fill "Password" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_PASSWORD_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_PASSWORD_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_PASSWORD_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_PASSWORD_FIELD_XPATH), $this->customerPassword);

        // Fill "Repeat password" field
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH), 10);
        $this->fillField(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS, PleskLinuxClientPage::SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH), $this->customerPassword);


        // Select "Service plan" option to "Default domain"
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_CSS, PleskLinuxClientPage::SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_XPATH), 10);
        $this->selectOption(Locator::combine(PleskLinuxClientPage::SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_CSS, PleskLinuxClientPage::SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_XPATH), "Default Domain");

        // Click "OK" button
        $this->click("//button[@name='send']");
        $this->waitForElementNotVisible("//button[@name='send']", 100);

        // Wait for success message to appear
        $this->waitForElement("//div[@class='msg-content']", 10);
        $this->see("Customer {$this->customerUsername} was created.", "//div[@class='msg-box msg-info']");

        // Return array with username, password and domain
        return [$this->customerUsername, $this->customerPassword, $this->domain];
    }

    public function changeCustomerPlan($customerUsername)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Change the subscription plan for '$this->customerUsername' --- \n");
        $I->switchToLeftFrame();
        $I->click("//a[contains(.,'Customers')]");
        $I->switchToWorkFrame();
        $I->click("//a[contains(.,'$this->customerUsername')]");
        $I->waitForElementVisible("//a[contains(.,'Subscription')]");
        $I->checkOption("//input[contains(@name,'listGlobalCheckbox')]");
        $I->click("//span[contains(.,'Change Plan')]");
        $I->waitForElementVisible("//select[contains(@name,'planSection[servicePlan]')]");
        $I->selectOption("//select[contains(@id,'planSection-servicePlan')]", "Unlimited");
        $I->click("//button[contains(.,'OK')]");
        $I->waitForElementVisible("//a[contains(.,'Subscription')]");
    }

    public function addNewSubscription(array $params = array())
    {
        if (empty($params['domain'])) {
            $params['domain'] = $this->generateRandomDomainName();
        }
        if (empty($params['username'])) {
            $params['username'] = $this->generateRandomUserName();
        }
        if (empty($params['password'])) {
            $params['password'] = uniqid();
        }
        $this->amGoingTo("\n\n --- Add a new subscription for '{$params['domain']}'--- \n");
        $this->switchToLeftFrame();
        $this->click("//a[contains(.,'Subscriptions')]");
        $this->switchToWorkFrame();

        $this->click(Locator::combine(PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_XPATH, PleskLinuxClientPage::CLIENT_ADD_NEW_SUBSCRIPTION_CSS));

        $this->waitForElement(Locator::combine(PleskLinuxClientPage::CLIENT_SUBSCRIPTIONS_XPATH, PleskLinuxClientPage::CLIENT_SUBSCRIPTIONS_CSS), 10);

        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_DOMAIN_FIELD_XPATH, PleskLinuxClientPage::ADD_SUBSCRIPTION_DOMAIN_FIELD_CSS), $params['domain']);
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_USERNAME_FIELD_XPATH, PleskLinuxClientPage::ADD_SUBSCRIPTION_USERNAME_FIELD_CSS), $params['username']);
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_PASSWORD_FIELD_XPATH, PleskLinuxClientPage::ADD_SUBSCRIPTION_PASSWORD_FIELD_CSS), $params['password']);
        $this->fillField(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH, PleskLinuxClientPage::ADD_SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS), $params['password']);
        $this->click(Locator::combine(PleskLinuxClientPage::ADD_SUBSCRIPTION_OK_BTN_XPATH, PleskLinuxClientPage::ADD_SUBSCRIPTION_OK_BTN_CSS));

        $this->waitForElementNotVisible(PleskLinuxClientPage::ADD_SUBSCRIPTION_DOMAIN_NAME_CONTAINER_XPATH, 100);
        $this->waitForElementVisible(PleskLinuxClientPage::ADD_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, 100);
        $this->see("Subscription {$params['domain']} was created.");

        $account = array(
            'domain' => $params['domain'],
            'username' => $params['username'],
            'password' => $params['password']
        );

        self::$accounts[] = $account;

        return $account;
    }

    public function removeCreatedSubscriptions()
    {
        foreach(self::$accounts as $account) {
            $this->removeSubscription($account['domain']);
        }
    }

    public function removeSubscription($domainName)
    {
        $this->amGoingTo("\n\n --- Remove subscription for '{$domainName}'--- \n");
        $this->switchToLeftFrame();
        $this->wait(1);
        $this->click("//a[contains(.,'Subscriptions')]");
        $this->switchToWorkFrame();
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::SUBSCRIPTION_TABLE_CSS, PleskLinuxClientPage::SUBSCRIPTION_TABLE_XPATH), 30);
        $this->waitForElement("//div[@class='b-indent status-ok']/a[contains(text(), '{$domainName}')]");
        $value = $this->grabAttributeFrom("//div[@class='b-indent status-ok']/a[contains(text(), '{$domainName}')]", 'href');
        $subscriptionNo = array_pop(explode('/', $value));
        $this->checkOption("//input[@value='{$subscriptionNo}']");
        $this->click("//span[contains(.,'Remove')]");


        $this->waitForElement(Locator::combine(PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH, PleskLinuxClientPage::REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS), 30);

        $this->waitForElementVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        $this->click(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS));

        $this->waitForElementNotVisible(Locator::combine(PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH, PleskLinuxClientPage::REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS), 30);

        $this->waitForElementVisible("//div[@class='msg-content']", 100);
        $this->see("Selected subscriptions were removed.");
    }

    public function openSubscription($domainName)
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Open subscription for '{$domainName}'--- \n");
        $I->switchToLeftFrame();
        $I->click("//a[contains(.,'Subscriptions')]");
        $I->switchToWorkFrame();
        $I->fillField("//input[@id='subscriptions-list-search-text-domainName']", $domainName);
        $I->click("//*[@class='search-field']//em");
        $I->click(" //a[@class='s-btn sb-login']");
        $I->waitForText("Websites & Domains");
        $I->click("//span[@class='caption-control-wrap']");
        $I->wait(2);
        $I->click("//span[contains(.,'DNS Settings')]");
        $I->waitForElementVisible("//table[@class='list']");
    }

    public function generateRandomDomainName()
    {
        $domain = uniqid("domain") . ".example.com";
        $this->comment("I generated random domain: $domain");

        return $domain;
    }

    public function generateRandomUserName()
    {
        $username = uniqid("pleskuser");
        $this->comment("I generated random username: $username");

        return $username;
    }

    public function getEnvHostname()
    {
        $url = getenv($this->getEnvParameter('url'));
        $parsed = parse_url($url);

        if (empty($parsed['host'])) {
            throw new \Exception("Couldnt parse url");
        }

        return $parsed['host'];
    }

    public function switchToLeftFrame()
    {
        $I = $this;
        $I->switchToWindow();
        $I->waitForElement('#leftFrame');
        $I->switchToIFrame('leftFrame');
    }

    public function switchToWorkFrame()
    {
        $I = $this;
        $I->switchToWindow();
        $I->waitForElement('#workFrame');
        $I->switchToIFrame('workFrame');
    }

    public function switchToTopFrame()
    {
        $I = $this;
        $I->switchToWindow();
        $I->waitForElement('#topFrame');
        $I->switchToIFrame('topFrame');
    }

    public function goToConfigurationPageAndSetOptions(array $options)
    {
        $this->goToPage(ProfessionalSpamFilterPage::CONFIGURATION_BTN, ConfigurationPage::TITLE);
        $this->setConfigurationOptions($options);
    }

    public function addAliasAsClient($domain, $alias = null)
    {
        if (!$alias) {
            $alias = 'alias' . $domain;
        }
        $I = $this;
        $I->click("//a[@id='buttonAddDomainAlias']");
        $I->waitForText('Add a Domain Alias');
        $I->fillField("//input[@id='name']", $alias);
        $I->click("//button[@name='send']");
        $I->waitForText("The domain alias $alias was created.", 30);

        return $alias;
    }

    public function removeAliasAsClient($alias)
    {
        $I = $this;
        $I->click($alias);
        $I->click("Remove Domain Alias");
        $I->waitForText("Removing this website will also delete all related files, directories, and web applications from the server.");
        $I->click("Yes");
        $I->waitForText("The alias was removed", 30);
    }

    /**
     * Function used to login as client
     * @param  string $customerUsername client username
     * @param  string $customerPassword client password
     */
    public function loginAsClient($customerUsername, $customerPassword)
    {
        $this->login($customerUsername, $customerPassword, true);
        $this->waitForElement("//button[contains(.,'OK, back to Plesk')]", 30);
        $this->click("//button[contains(.,'OK, back to Plesk')]");
        $this->waitForElementNotVisible("//button[contains(.,'OK, back to Plesk')]", 30);
    }

    /**
     * Function used to login as root
     */
    public function loginAsRoot()
    {
        $this->login();
    }

    public function addAddonDomainAsClient($domain, $addonDomainName = null)
    {
        if (! $addonDomainName) {
            $addonDomainName = 'addon' . $domain;
        }

        $I = $this;
        $I->click('Add New Domain');
        $I->waitForText('Adding New Domain Name');
        $I->fillField("//input[@id='domainName-name']", $addonDomainName);
        $I->click("//button[@name='send']");
        $I->waitForText("The domain $addonDomainName was successfully created.", 30);

        return $addonDomainName;
    }
}
