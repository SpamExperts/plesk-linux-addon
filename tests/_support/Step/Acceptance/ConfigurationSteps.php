<?php

namespace Step\Acceptance;

use Pages\DomainListPage;
use Pages\ConfigurationPage;
use Pages\ProfessionalSpamFilterPage;

class ConfigurationSteps extends DomainListSteps
{
    public function checkConfigurationPageLayout()
    {
        $I = $this;
        $I->amGoingTo("\n\n --- Check configuration page layout --- \n");
        $I->see(ConfigurationPage::TITLE);
        $I->see(ConfigurationPage::DESCRIPTION_A);
        $I->see(ConfigurationPage::DESCRIPTION_B);

        $I->seeElement(ProfessionalSpamFilterPage::CONFIGURATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::DOMAIN_LIST_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::BRANDING_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::MIGRATION_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::UPDATE_LINK);
        $I->seeElement(ProfessionalSpamFilterPage::SUPPORT_LINK);

        $I->see('AntiSpam API URL');
        $I->seeElement(ConfigurationPage::ANTISPAM_API_URL);
        $I->see('API hostname');
        $I->seeElement(ConfigurationPage::API_HOSTNAME);
        $I->see('API username');
        $I->seeElement(ConfigurationPage::API_USERNAME);
        $I->see('API password');
        $I->seeElement(ConfigurationPage::API_PASSWORD);
        $I->see('Primary MX');
        $I->seeElement(ConfigurationPage::MX_PRIMARY);
        $I->see('Secondary MX');
        $I->seeElement(ConfigurationPage::MX_SECONDARY);
        $I->see('Tertiary MX');
        $I->seeElement(ConfigurationPage::MX_TERTIARY);
        $I->see('Quaternary MX');
        $I->seeElement(ConfigurationPage::MX_QUATERNARY);
        $I->see('Language');
        $I->seeElement(ConfigurationPage::LANGUAGE_DROP_DOWN);

        $I->see('Enable SSL for API requests to the spamfilter and Plesk');
        $I->seeElement(ConfigurationPage::ENABLE_SSL_FOR_API_OPT);
        $I->see('Enable automatic updates');
        $I->seeElement(ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT);
        $I->see('Automatically add domains to the SpamFilter');
        $I->seeElement(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT);
        $I->see('Automatically delete domains from the SpamFilter');
        $I->seeElement(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT);
        $I->see('Automatically change the MX records for domains');
        $I->seeElement(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT);
        $I->see('Configure the email address for this domain');
        $I->seeElement(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT);
        $I->see('Process aliases and sub-domains');
        $I->seeElement(ConfigurationPage::PROCESS_ADDON_PLESK_OPT);
        $I->see('Add aliases and sub-domains as an alias instead of a normal domain.');
        $I->seeElement(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT);
        $I->see('Use existing MX records as routes in the spamfilter.');
        $I->seeElement(ConfigurationPage::USE_EXISTING_MX_OPT);
        $I->see('Do not protect remote domains');
        $I->seeElement(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT);
        $I->see('Redirect back to Plesk upon logout');
        $I->seeElement(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT);
        $I->see('Add the domain to the spamfilter during login if it does not exist');
        $I->seeElement(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT);
        $I->see('Force changing route & MX records, even if the domain exist');
        $I->seeElement(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT);
        $I->see('Use IP as destination route instead of domain');
        $I->seeElement(ConfigurationPage::USE_IP_AS_DESTINATION_OPT);
        $I->seeElement(ConfigurationPage::SAVE_SETTINGS_BTN);
    }

    public function checkUnsuccessfullConfigurations()
    {
        $I = $this;

        $I->setFieldApiUrl('');
        $I->setFieldApiHostname('');
        $I->setFieldPrimaryMX('');
        $I->submitSettingForm();
        $I->checkSubmissionIsUnsuccessful();
        $I->see("Value is required and can't be empty\n'' is not a valid URL.",
         ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
        $I->see("Value is required and can't be empty\n'' is not a valid hostname.",
         ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
        $I->see("The API is unreachable",
         ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
        $I->see("Value is required and can't be empty\n",
         ConfigurationPage::OPT_ERROR_MESSAGE_CONTAINER);
    }

    public function setFieldApiUrl($string)
    {
        $this->fillField(ConfigurationPage::ANTISPAM_API_URL, $string);
    }

    public function setFieldApiHostname($string)
    {
        $this->fillField(ConfigurationPage::API_HOSTNAME, $string);
    }

    public function setFieldApiUsernameIfEmpty($string)
    {
        $I = $this;
        $value = $I->grabValueFrom(ConfigurationPage::API_USERNAME);
        if (! $value) {
            $I->fillField(ConfigurationPage::API_USERNAME, $string);
        }
    }

    public function setFieldApiPassword($string)
    {
        $this->fillField(ConfigurationPage::API_PASSWORD, $string);
    }

    public function setFieldPrimaryMX($string)
    {
        $this->fillField(ConfigurationPage::MX_PRIMARY, $string);
    }

    public function submitSettingForm()
    {
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
    }

    public function checkSubmissionIsSuccessful()
    {
        $this->see('The settings have been saved.',
            ConfigurationPage::SUCCESS_MESSAGE_CONTAINER);
    }

    public function checkSubmissionIsUnsuccessful()
    {
        $this->see('One or more settings are not correctly set.',
            ConfigurationPage::ERROR_MESSAGE_CONTAINER);
    }

    public function setEnableSSLforAPIOption()
    {
        $this->checkOption(ConfigurationPage::ENABLE_SSL_FOR_API_OPT);
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setEnableAutomaticUpdatesOption()
    {
        $this->checkOption(ConfigurationPage::ENABLE_AUTOMATIC_UPDATES_OPT);
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setAutomaticallyAddDomainsToSpamfilterOption($add = true)
    {
        if ($add) {
            $this->checkOption(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::AUTOMATICALLY_ADD_DOMAINS_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setAutomaticallyDeleteDomainsFromSpamfilterOption($delete = true)
    {
        if ($delete) {
            $this->checkOption(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::AUTOMATICALLY_DELETE_DOMAINS_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setAutomaticallyChangeMXRecordsOption($change = true)
    {
        if ($change) {
            $this->checkOption(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::AUTOMATICALLY_CHANGE_MX_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setConfigureEmailAddressOption($configure = true)
    {
        if ($configure) {
            $this->checkOption(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::CONFIGURE_EMAIL_ADDRESS_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setProcessAddOnAndParkedDomainsOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::PROCESS_ADDON_PLESK_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::PROCESS_ADDON_PLESK_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setAddOnAsAnAliasOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::ADD_ADDON_AS_ALIAS_PLESK_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setUseExistingMXRecordsOption($use = true)
    {
        if ($use) {
            $this->checkOption(ConfigurationPage::USE_EXISTING_MX_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::USE_EXISTING_MX_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setDoNotProtectRemoteDomainsOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::DO_NOT_PROTECT_REMOTE_DOMAINS_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setRedirectBackToPleskOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::REDIRECT_BACK_TO_PLESK_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setAddDomainToSpamfilterDuringLoginOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::ADD_DOMAIN_DURING_LOGIN_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setForceChangeRouteAndMXOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::FORCE_CHANGE_MX_ROUTE_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }

    public function setUseIPAsDestinationOption($set = true)
    {
        if ($set) {
            $this->checkOption(ConfigurationPage::USE_IP_AS_DESTINATION_OPT);
        } else {
            $this->uncheckOption(ConfigurationPage::USE_IP_AS_DESTINATION_OPT);
        }
        $this->click(ConfigurationPage::SAVE_SETTINGS_BTN);
        $this->checkSubmissionIsSuccessful();
    }
}
