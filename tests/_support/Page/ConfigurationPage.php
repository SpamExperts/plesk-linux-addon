<?php

namespace Page;

class ConfigurationPage
{
    const TITLE = "Configuration";
    const DESCRIPTION_A = "On this page you can configure the admin settings of the addon.";
    const DESCRIPTION_B = "You can hover over the options to see more detailed information about what they do.";

    // Configuration page fields

    const ANTISPAM_API_URL_CSS = "#spampanel_url";
    const ANTISPAM_API_URL_XPATH = "//*[@id='spampanel_url']";

    const API_HOSTNAME_CSS = "#apihost";
    const API_HOSTNAME_XPATH = "//*[@id='apihost']";

    const API_USERNAME_CSS = "#apiuser";
    const API_USERNAME_XPATH = "//*[@id='apiuser']";

    const API_PASSWORD_CSS = "#apipass";
    const API_PASSWORD_XPATH = "//*[@id='apipass']";

    const MX_PRIMARY_CSS = "#mx1";
    const MX_PRIMARY_XPATH = "//*[@id='mx1']";

    const MX_SECONDARY_CSS = "#mx2";
    const MX_SECONDARY_XPATH = "//*[@id='mx2']";

    const MX_TERTIARY_CSS = "#mx3";
    const MX_TERTIARY_XPATH = "//*[@id='mx3']";

    const MX_QUATERNARY_CSS = "#mx4";
    const MX_QUATERNARY_XPATH = "//*[@id='mx4']";

    const LANGUAGE_DROP_DOWN_CSS = "#language";
    const LANGUAGE_DROP_DOWN_XPATH = "//*[@id='language']";

    // Configuration page options

    const ENABLE_SSL_FOR_API_OPT_CSS = "#ssl_enabled";
    const ENABLE_SSL_FOR_API_OPT_XPATH = "//*[@id='ssl_enabled']";

    const ENABLE_AUTOMATIC_UPDATES_OPT_CSS = "#auto_update";
    const ENABLE_AUTOMATIC_UPDATES_OPT_XPATH = "//*[@id='auto_update']";

    const AUTOMATICALLY_ADD_DOMAINS_OPT_CSS = "#auto_add_domain";
    const AUTOMATICALLY_ADD_DOMAINS_OPT_XPATH = "//*[@id='auto_add_domain']";

    const AUTOMATICALLY_DELETE_DOMAINS_OPT_CSS = "#auto_del_domain";
    const AUTOMATICALLY_DELETE_DOMAINS_OPT_XPATH = "//*[@id='auto_del_domain']";

    const AUTOMATICALLY_CHANGE_MX_OPT_CSS = "#provision_dns";
    const AUTOMATICALLY_CHANGE_MX_OPT_XPATH = "//*[@id='provision_dns']";

    const CONFIGURE_EMAIL_ADDRESS_OPT_CSS = "#set_contact";
    const CONFIGURE_EMAIL_ADDRESS_OPT_XPATH = "//*[@id='set_contact']";

    const PROCESS_ADDON_PLESK_OPT_CSS = "#handle_extra_domains";
    const PROCESS_ADDON_PLESK_OPT_XPATH = "//*[@id='handle_extra_domains']";

    const ADD_ADDON_AS_ALIAS_PLESK_OPT_CSS = "#add_extra_alias";
    const ADD_ADDON_AS_ALIAS_PLESK_OPT_XPATH = "//*[@id='add_extra_alias']";

    const USE_EXISTING_MX_OPT_CSS = "#use_existing_mx";
    const USE_EXISTING_MX_OPT_XPATH = "//*[@id='use_existing_mx']";

    const DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_CSS = "#handle_only_localdomains";
    const DO_NOT_PROTECT_REMOTE_DOMAINS_OPT_XPATH = "//*[@id='handle_only_localdomains']";

    const REDIRECT_BACK_TO_PLESK_OPT_CSS = "#redirectback";
    const REDIRECT_BACK_TO_PLESK_OPT_XPATH = "//*[@id='redirectback']";

    const ADD_DOMAIN_DURING_LOGIN_OPT_CSS = "#add_domain_loginfail";
    const ADD_DOMAIN_DURING_LOGIN_OPT_XPATH = "//*[@id='#add_domain_loginfail']";

    const FORCE_CHANGE_MX_ROUTE_OPT_CSS = "#bulk_force_change";
    const FORCE_CHANGE_MX_ROUTE_OPT_XPATH = "//*[@id='bulk_force_change']";

    const USE_IP_AS_DESTINATION_OPT_CSS = "#use_ip_address_as_destination_routes";
    const USE_IP_AS_DESTINATION_OPT_XPATH = "//*[@id='use_ip_address_as_destination_routes']";

    // Configuration page save button

    const SAVE_SETTINGS_BTN_CSS = "#submit";
    const SAVE_SETTINGS_BTN_XPATH = "//input[@id='submit']";


    const ERROR_MESSAGE_CONTAINER       = "//div[@class='alert alert-error alert-danger']";
    const SUCCESS_MESSAGE_CONTAINER     = "//div[@class='alert alert-success']";
    const OPT_ERROR_MESSAGE_CONTAINER   = "//div[@class='error control-group']";
}
