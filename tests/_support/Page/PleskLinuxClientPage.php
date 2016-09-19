<?php

namespace Page;

class PleskLinuxClientPage
{
    const CLIENT_SUBSCRIPTIONS              = "//a[contains(.,'Subscriptions')]";
    const CLIENT_ALL_ENTRIES_BUTTON         = "//a[contains(.,'All')]";
    const CLIENT_SELECT_ALL_SUBSCRIPTIONS   = "//input[contains(@name,'listGlobalCheckbox')]";
    const CLIENT_ADD_NEW_SUBSCRIPTION       = "//span[contains(.,'Add New Subscription')]";
    const CLIENT_REMOVE_SUBSCRIPTION_BUTTON = "//span[contains(.,'Remove')]";

    const LEFT_FRAME_CSS = "#leftFrame";
    const LEFT_FRAME_XPATH = "//*[@id='leftFrame']";
    const LEFT_FRAME_NAME = "leftFrame";

    const WORK_FRAME_CSS = "#workFrame";
    const WORK_FRAME_XPATH = "//*[@id='workFrame']";
    const WORK_FRAME_NAME = "workFrame";

    const TOP_FRAME_CSS = "#topFrame";
    const TOP_FRAME_XPATH = "//*[@id='topFrame']";
    const TOP_FRAME_NAME = "topFrame";
}
