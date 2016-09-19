<?php

namespace Page;

class PleskLinuxClientPage
{
    const CLIENT_SUBSCRIPTIONS_XPATH = "//a[contains(.,'Subscriptions')]";
    const CLIENT_SUBSCRIPTIONS_CSS   = "#pathbar-item-gen-id-148275>a>span";

    const CLIENT_ALL_ENTRIES_BUTTON_XPATH = "//span[contains(.,'All')]";
    const CLIENT_ALL_ENTRIES_BUTTON_CSS   = ".paging-view>span";

    const CLIENT_SELECT_ALL_SUBSCRIPTIONS_XPATH = "//input[@name='listGlobalCheckbox']";
    const CLIENT_SELECT_ALL_SUBSCRIPTIONS_CSS   = ".checkbox";

    const CLIENT_ADD_NEW_SUBSCRIPTION_XPATH = "//span[contains(.,'Add New Subscription')]";
    const CLIENT_ADD_NEW_SUBSCRIPTION_CSS   = "#buttonAddNewOwnSubscription>i>i>i>span";

    const CLIENT_REMOVE_SUBSCRIPTION_BUTTON_XPATH = "//span[contains(.,'Remove')]";
    const CLIENT_REMOVE_SUBSCRIPTION_BUTTON_CSS   = "#buttonRemoveSubscription>i>i>i>span";

    const REMOVE_SELECTED_SUBSCRIPTION_BTN_XPATH = "//button[contains(.,'Yes')]";
    const REMOVE_SELECTED_SUBSCRIPTION_BTN_CSS   = ".btn>button";

    const SUBSCRIPTION_LIST_TABLE_XPATH = "//table[@id='subscriptions-list-table']";
    const SUBSCRIPTION_LIST_TABLE_CSS   = "#subscriptions-list-table";

    const REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_XPATH = "//div[@class='confirmation-msg mw-delete']";
    const REMOVE_SUBSCRIPTION_CONFIRMATION_MSG_CSS   = ".confirmation-msg.mw-delete";

    const ADD_SUBSCRIPTION_DOMAIN_FIELD_XPATH = "//input[@id='subscription-domainInfo-domainName']";
    const ADD_SUBSCRIPTION_DOMAIN_FIELD_CSS   = "#subscription-domainInfo-domainName";

    const ADD_SUBSCRIPTION_USERNAME_FIELD_XPATH = "//input[@id='subscription-domainInfo-userName']";
    const ADD_SUBSCRIPTION_USERNAME_FIELD_CSS   = "#subscription-domainInfo-userName";

    const ADD_SUBSCRIPTION_PASSWORD_FIELD_XPATH = "//input[@id='subscription-domainInfo-password']";
    const ADD_SUBSCRIPTION_PASSWORD_FIELD_CSS   = "#subscription-domainInfo-password";

    const ADD_SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH = "//input[@id='subscription-domainInfo-passwordConfirmation']";
    const ADD_SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS   = "#subscription-domainInfo-passwordConfirmation";

    const ADD_SUBSCRIPTION_OK_BTN_XPATH = "//button[@name='send']";
    const ADD_SUBSCRIPTION_OK_BTN_CSS   = "#btn-send>button";

    const ADD_SUBSCRIPTION_DOMAIN_NAME_CONTAINER_XPATH = "//div[contains(.,'Domain name *')]";

    const ADD_SUBSCRIPTION_CONFIRMATION_MSG_XPATH = "//div[@class='msg-content']";

    const LEFT_FRAME_CSS = "#leftFrame";
    const LEFT_FRAME_XPATH = "//*[@id='leftFrame']";
    const LEFT_FRAME_NAME = "leftFrame";

    const WORK_FRAME_CSS = "#workFrame";
    const WORK_FRAME_XPATH = "//*[@id='workFrame']";
    const WORK_FRAME_NAME = "workFrame";

    const TOP_FRAME_CSS = "#topFrame";
    const TOP_FRAME_XPATH = "//*[@id='topFrame']";
    const TOP_FRAME_NAME = "topFrame";

    // Customers page

    const ADD_NEW_CUSTOMER_BTN_CSS = "#buttonAddNewCustomer";
    const ADD_NEW_CUSTOMER_BTN_XPATH = "//*[@id='buttonAddNewCustomer']";

    const CONTACT_NAME_FIELD_CSS = "#contactInfoSection-contactInfo-contactName";
    const CONTACT_NAME_FIELD_XPATH = "//input[@id='contactInfoSection-contactInfo-contactName']";

    const EMAIL_ADDRESS_FIELD_CSS = "#contactInfoSection-contactInfo-email";
    const EMAIL_ADDRESS_FIELD_XPATH = "//input[@id='contactInfoSection-contactInfo-email']";


    const PLESK_USERNAME_FIELD_CSS = "#accessToPanelSection-loginInfo-userName";
    const PLESK_USERNAME_FIELD_XPATH = "//input[@id='accessToPanelSection-loginInfo-userName']";

    const PLESK_PASSWORD_FIELD_CSS = "#accessToPanelSection-loginInfo-password";
    const PLESK_PASSWORD_FIELD_XPATH = "//input[@id='accessToPanelSection-loginInfo-password']";

    const PLESK_REPEAT_PASSWORD_FIELD_CSS = "#accessToPanelSection-loginInfo-passwordConfirmation";
    const PLESK_REPEAT_PASSWORD_FIELD_XPATH = "//input[@id='accessToPanelSection-loginInfo-passwordConfirmation']";

    const SUBSCRIPTION_DOMAIN_FIELD_CSS = "#subscription-domainInfo-domainName";
    const SUBSCRIPTION_DOMAIN_FIELD_XPATH = "//input[@id='subscription-domainInfo-domainName']";

    const SUBSCRIPTION_USERNAME_FIELD_CSS = "#subscription-domainInfo-userName";
    const SUBSCRIPTION_USERNAME_FIELD_XPATH = "//input[@id='subscription-domainInfo-userName']";

    const SUBSCRIPTION_PASSWORD_FIELD_CSS = "#subscription-domainInfo-passwordConfirmation";
    const SUBSCRIPTION_PASSWORD_FIELD_XPATH = "//input[@id='subscription-domainInfo-passwordConfirmation']";

    const SUBSCRIPTION_REPEAT_PASSWORD_FIELD_CSS = "#subscription-subscriptionInfo-servicePlan";
    const SUBSCRIPTION_REPEAT_PASSWORD_FIELD_XPATH = "//select[@id='subscription-subscriptionInfo-servicePlan']";

    const SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_CSS = "#subscription-subscriptionInfo-servicePlan";
    const SUBCRIPTION_SERVICE_PLAN_DROP_DOWN_XPATH = "//select[@id='subscription-subscriptionInfo-servicePlan']";


    // Const resellers page

    const ADD_NEW_RESELLER_BTN_CSS = "#buttonAddNewReseller";
    const ADD_NEW_RESELLER_BTN_XPATH = "//*[@id='buttonAddNewReseller']";

}
