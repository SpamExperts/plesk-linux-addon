<?php

namespace Pages;

class PleskLinuxClientPage
{
    const CLIENT_SUBSCRIPTIONS              = "//a[contains(.,'Subscriptions')]";
    const CLIENT_ALL_ENTRIES_BUTTON         = "//a[contains(.,'All')]";
    const CLIENT_SELECT_ALL_SUBSCRIPTIONS   = "//input[contains(@name,'listGlobalCheckbox')]";
    const CLIENT_ADD_NEW_SUBSCRIPTION       = "//span[contains(.,'Add New Subscription')]";
    const CLIENT_REMOVE_SUBSCRIPTION_BUTTON = "//span[contains(.,'Remove')]";
}