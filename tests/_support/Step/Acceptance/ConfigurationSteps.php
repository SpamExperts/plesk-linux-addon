<?php

namespace Step\Acceptance;

use Page\DomainListPage;
use Page\ConfigurationPage;
use Codeception\Util\Locator;
use Page\ProfessionalSpamFilterPage;

class ConfigurationSteps extends DomainListSteps
{
    /**
     * Function used to check configuration page layout
     */
    public function checkConfigurationPageLayout()
    {
        // Display info message
        $this->amGoingTo("\n\n --- Check configuration page layout --- \n");

        // Check if title and descriptions are displayd properly
        $this->see(ConfigurationPage::TITLE);
        $this->see(ConfigurationPage::DESCRIPTION_A);
        $this->see(ConfigurationPage::DESCRIPTION_B);

        // Check if top links are displayed properly
        $this->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $this->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        // Check if fields and their description are displayed properly
        $this->see('AntiSpam API URL');
        $this->seeElement(Locator::combine(ConfigurationPage::ANTISPAM_API_URL_CSS, ConfigurationPage::ANTISPAM_API_URL_XPATH));
        $this->see('API hostname');
        $this->seeElement(Locator::combine(ConfigurationPage::API_HOSTNAME_CSS, ConfigurationPage::API_HOSTNAME_XPATH));
        $this->see('API username');
        $this->seeElement(Locator::combine(ConfigurationPage::API_USERNAME_CSS, ConfigurationPage::API_USERNAME_XPATH));
        $this->see('API password');
        $this->seeElement(Locator::combine(ConfigurationPage::API_PASSWORD_CSS, ConfigurationPage::API_PASSWORD_XPATH));
        $this->see('Primary MX');
        $this->seeElement(Locator::combine(ConfigurationPage::MX_PRIMARY_CSS, ConfigurationPage::MX_PRIMARY_XPATH));
        $this->see('Secondary MX');
        $this->seeElement(Locator::combine(ConfigurationPage::MX_SECONDARY_CSS, ConfigurationPage::MX_SECONDARY_XPATH));
        $this->see('Tertiary MX');
        $this->seeElement(Locator::combine(ConfigurationPage::MX_TERTIARY_CSS, ConfigurationPage::MX_TERTIARY_XPATH));
        $this->see('Quaternary MX');
        $this->seeElement(Locator::combine(ConfigurationPage::MX_QUATERNARY_CSS, ConfigurationPage::MX_QUATERNARY_XPATH));
        $this->see('Language');
        $this->seeElement(Locator::combine(ConfigurationPage::LANGUAGE_DROP_DOWN_CSS, ConfigurationPage::LANGUAGE_DROP_DOWN_XPATH));

        // Check if options and their description are displayed properly
        $this->see('Enable SSL for API requests to the spamfilter and Plesk');
        $this->seeElement(Locator::combine(ConfigurationPage::ENABLE_SSL_FOR_API_OPT_CSS, ConfigurationPage::ENABLE_SSL_FOR_API_OPT_XPATH));
        $this->see('Enable automatic updates');
        $this->seeElement(Locator::combine(ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT_CSS, ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT_XPATH));
        $this->see('Automatically add domains to the SpamFilter');
        $this->seeElement(Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH));
        $this->see('Automatically delete domains from the SpamFilter');
        $this->seeElement(Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH));
        $this->see('Automatically change the MX records for domains');
        $this->seeElement(Locator::combine(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_CSS, ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_XPATH));
        $this->see('Configure the email address for this domain');
        $this->seeElement(Locator::combine(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_CSS, ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_XPATH));
        $this->see('Process aliases and sub-domains');
        $this->seeElement(Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH));
        $this->see('Add aliases and sub-domains as an alias instead of a normal domain.');
        $this->seeElement(Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH));
        $this->see('Use existing MX records as routes in the spamfilter.');
        $this->seeElement(Locator::combine(ConfigurationPage::USE_EXISTING_MX_OPT_CSS, ConfigurationPage::USE_EXISTING_MX_OPT_XPATH));
        $this->see('Do not protect remote domains');
        $this->seeElement(Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH));
        $this->see('Redirect back to Plesk upon logout');
        $this->seeElement(Locator::combine(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_CSS, ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_XPATH));
        $this->see('Add the domain to the spamfilter during login if it does not exist');
        $this->seeElement(Locator::combine(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_CSS, ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_XPATH));
        $this->see('Force changing route & MX records, even if the domain exist');
        $this->seeElement(Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH));
        $this->see('Use IP as destination route instead of domain');
        $this->seeElement(Locator::combine(ConfigurationPage::USE_IP_AS_DESTINATION_OPT_CSS, ConfigurationPage::USE_IP_AS_DESTINATION_OPT_XPATH));
        $this->seeElement(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
    }

    /**
     * Function used to check error messaged for usuccessfull configuration options
     */
    public function checkUnsuccessfullConfigurations()
    {
        // Set "AntiSpam API URL" filed to empty
        $this->setFieldApiUrl('');

        // Set "API hostname" field to empty
        $this->setFieldApiHostname('');

        // Set "Primary MX" field to empty
        $this->setFieldPrimaryMX('');

        // Submit configuration options form
        $this->submitSettingForm();

        // Check error messages
        $this->checkSubmissionIsUnsuccessful();
        $this->see("Value is required and can't be empty\n'' is not a valid URL.", ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
        $this->see("Value is required and can't be empty\n'' is not a valid hostname.", ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
        $this->see("The API is unreachable", ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
        $this->see("Value is required and can't be empty\n", ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
    }

    /**
     * Function used to fill API URL field
     * @param string $url api url
     */
    public function setFieldApiUrl($url)
    {
        $this->waitForElement(Locator::combine(ConfigurationPage::ANTISPAM_API_URL_CSS, ConfigurationPage::ANTISPAM_API_URL_XPATH), 10);
        $this->fillField(Locator::combine(ConfigurationPage::ANTISPAM_API_URL_CSS, ConfigurationPage::ANTISPAM_API_URL_XPATH), $url);
    }

    /**
     * Function used to fill API hostname field
     * @param string $hostname api hostname
     */
    public function setFieldApiHostname($hostname)
    {
        $this->waitForElement(Locator::combine(ConfigurationPage::API_HOSTNAME_CSS, ConfigurationPage::API_HOSTNAME_XPATH), 10);
        $this->fillField(Locator::combine(ConfigurationPage::API_HOSTNAME_CSS, ConfigurationPage::API_HOSTNAME_XPATH), $hostname);
    }

    /**
     * Function used to fill API username field if empty
     * @param string $username api username
     */
    public function setFieldApiUsernameIfEmpty($username)
    {
        $this->waitForElement(Locator::combine(ConfigurationPage::API_USERNAME_CSS, ConfigurationPage::API_USERNAME_XPATH), 10);
        $value = $this->grabValueFrom(Locator::combine(ConfigurationPage::API_USERNAME_CSS, ConfigurationPage::API_USERNAME_XPATH));
        if (!$value)
            $this->fillField(Locator::combine(ConfigurationPage::API_USERNAME_CSS, ConfigurationPage::API_USERNAME_XPATH), $username);
    }

    /**
     * Function used to fill API password field
     * @param string $password api password
     */
    public function setFieldApiPassword($password)
    {
        $this->waitForElement(Locator::combine(ConfigurationPage::API_PASSWORD_CSS, ConfigurationPage::API_PASSWORD_XPATH), 10);
        $this->fillField(Locator::combine(ConfigurationPage::API_PASSWORD_CSS, ConfigurationPage::API_PASSWORD_XPATH), $password);
    }

    /**
     * Function used to fill Primary MX field
     * @param string $mx primary mx
     */
    public function setFieldPrimaryMX($mx)
    {
        $this->waitForElement(Locator::combine(ConfigurationPage::MX_PRIMARY_CSS, ConfigurationPage::MX_PRIMARY_XPATH), 10);
        $this->fillField(Locator::combine(ConfigurationPage::MX_PRIMARY_CSS, ConfigurationPage::MX_PRIMARY_XPATH), $mx);
    }

    /**
     * Function used to submit configuration form
     */
    public function submitSettingForm()
    {
        $this->waitForElement(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH), 10);
        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
    }

    /**
     * Function used to check if submission of configuration form was successful
     */
    public function checkSubmissionIsSuccessful()
    {
        $this->see('The settings have been saved.', ConfigurationPage::SUCCESS_MESSAGE_CONTAINER);
    }

    /**
     * Function used to check if submission of configuration form was not succesful
     */
    public function checkSubmissionIsUnsuccessful()
    {
        $this->see('One or more settings are not correctly set.', ConfigurationPage::ERROR_MESSAGE_CONTAINER);
    }

    /**
     * Function used to check "Enable SSL for API requests to the spamfilter and Plesk" option
     */
    public function setEnableSSLforAPIOption()
    {
        $this->checkOption(Locator::combine(ConfigurationPage::ENABLE_SSL_FOR_API_OPT_CSS, ConfigurationPage::ENABLE_SSL_FOR_API_OPT_XPATH));
        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to check  "Enable automatic updates" option
     */
    public function setEnableAutomaticUpdatesOption()
    {
        $this->checkOption(Locator::combine(ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT_CSS, ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT_XPATH));
        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Automaticaly add domains to the SpamFilter" option
     * @param boolean $add enable/disable
     */
    public function setAutomaticallyAddDomainsToSpamfilterOption($add = true)
    {
        if ($add)
            $this->checkOption(Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Automaticaly delete domains from the SpamFilter" option
     * @param boolean $delete enable/disable
     */
    public function setAutomaticallyDeleteDomainsFromSpamfilterOption($delete = true)
    {
        if ($delete)
            $this->checkOption(Locator::combine(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS, ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH));
        else
            $this->uncheckOption(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT);

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Automaticaly change the MX records for domanis" option
     * @param boolean $change enable/disable
     */
    public function setAutomaticallyChangeMXRecordsOption($change = true)
    {
        if ($change)
            $this->checkOption(Locator::combine(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_CSS, ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_CSS, ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Configure the email address for this domain" option
     * @param boolean $configure enable/disable
     */
    public function setConfigureEmailAddressOption($configure = true)
    {
        if ($configure)
            $this->checkOption(Locator::combine(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_CSS, ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_CSS, ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Process aliases and subdomains" option
     * @param boolean $set enable/disable
     */
    public function setProcessAddOnAndParkedDomainsOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::PROCESS_ADDON_PLESK_OPT_CSS, ConfigurationPage::PROCESS_ADDON_PLESK_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Add aliases and subdomains as an alias instead of a normal domain" option
     * @param boolean $set enable/disable
     */
    public function setAddOnAsAnAliasOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS, ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Use existing MX records as routes in the SpamFilter" option
     * @param boolean $use enable/disable
     */
    public function setUseExistingMXRecordsOption($use = true)
    {
        if ($use)
            $this->checkOption(Locator::combine(ConfigurationPage::USE_EXISTING_MX_OPT_CSS, ConfigurationPage::USE_EXISTING_MX_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::USE_EXISTING_MX_OPT_CSS, ConfigurationPage::USE_EXISTING_MX_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Do not protect remote domains" option
     * @param boolean $set enable/disable
     */
    public function setDoNotProtectRemoteDomainsOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS, ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Redirect back to Plesk upon logout" option
     * @param boolean $set enable/disable
     */
    public function setRedirectBackToPleskOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_CSS, ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_CSS, ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Add the domain in the spamfilter during login if it does not exist" option
     * @param boolean $set enable/disable
     */
    public function setAddDomainToSpamfilterDuringLoginOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_CSS, ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_CSS, ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Force changing route & MX records, even if the domain exist" option
     * @param boolean $set enable/disable
     */
    public function setForceChangeRouteAndMXOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_CSS, ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }

    /**
     * Function used to toggle "Use IP as destination route instead of domain" option
     * @param boolean $set enable/disable
     */
    public function setUseIPAsDestinationOption($set = true)
    {
        if ($set)
            $this->checkOption(Locator::combine(ConfigurationPage::USE_IP_AS_DESTINATION_OPT_CSS, ConfigurationPage::USE_IP_AS_DESTINATION_OPT_XPATH));
        else
            $this->uncheckOption(Locator::combine(ConfigurationPage::USE_IP_AS_DESTINATION_OPT_CSS, ConfigurationPage::USE_IP_AS_DESTINATION_OPT_XPATH));

        $this->click(Locator::combine(ConfigurationPage::SAVE_SETTINGS_BTN_CSS, ConfigurationPage::SAVE_SETTINGS_BTN_XPATH));
        $this->checkSubmissionIsSuccessful();
    }
}
