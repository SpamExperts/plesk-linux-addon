<?php

namespace Page;

class ToolsAndSettingsPage
{
	const HOME_BTN_XPATH = "//a[contains(.,'Home')]";

	const IP_ADDRESSES_BTN_XPATH = "//*[@id='buttonIpAddresses']";
	const IP_ADDRESSES_BTN_CSS   = "#buttonIpAddresses";

	const EDIT_IP_ADDRESS_LINK_XPATH = "//a[@href='/admin/ip-address/edit/id/1']";

	const SHARED_OPTION_CHECKBOX_XPATH = "//*[@id='generalSection-distributionType-shared']";
	const SHARED_OPTION_CHECKBOX_CSS   = "#generalSection-distributionType-shared";

	const OK_BTN_XPATH = "//button[@name='send']";
	const OK_BTN_CSS   = "#btn-send>button";

	const IP_ADDRESSES_MSJ_XPATH = "//div[@class='msg-content']";
	const IP_ADDRESSES_MSJ_CSS   = ".msg-content";

	const IP_RESELLER_OPTION_XPATH = "//a[@href='/plesk/server/ip-address@1/client@']";

	const RESELLER_ASSIGN_BTN_XPATH = "//span[@id='spanid-add-client']";
	const RESELLER_ASSIGN_BTN_CSS   = "#spanid-add-client";

	const ADD_IP_TO_RESELLER_OK_BTN_XPATH = "//button[@id='buttonid-ok']";
	const ADD_IP_TO_RESELLER_OK_BTN_CSS   = "#buttonid-ok";
}
