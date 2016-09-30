<?php

namespace Page;

class DomainListPage
{
    const TITLE = "List Domains";
    const DESCRIPTION = "This page shows you a list of all domains owned by you";

    const STATUS_DOMAIN_IS_PRESENT_IN_THE_FILTER = 'This domain is present in the filter.';
    const STATUS_DOMAIN_IS_NOT_PRESENT_IN_THE_FILTER = 'This domain is not present in the filter.';

    const SEARCH_FIELD_XPATH = "//input[@id='searchInput']";
    const SEARCH_FIELD_CSS   = "#searchInput";

    const SEARCH_BTN_XPATH = "//button[@id='searchSubmit']";
    const SEARCH_BTN_CSS   = "#searchSubmit";

    const RESET_BTN_XPATH = "//button[@id='searchReset']";
    const RESET_BTN_CSS   = "#searchReset";

    const CHECK_STATUS_FOR_ALL_DOMAIN_XPATH = "//button[@id='checkAllDomains']";
    const CHECK_STATUS_FOR_ALL_DOMAIN_CSS   = "#checkAllDomains";

    const TOGGLE_PROTECTION_FOR_SELECTED_XPATH = "//button[@id='toggleSelected']";
    const TOGGLE_PROTECTION_FOR_SELECTED_CSS   = "#toggleSelected";

    const ITEMS_PER_PAGE_INPUT_XPATH = "//input[@id='itemsPerPage']";
    const ITEMS_PER_PAGE_INPUT_CSS   = "#itemsPerPage";

    const CHANGE_BTN_XPATH = "//button[@id='changeItems']";
    const CHANGE_BTN_CSS   = "#changeItems";

    const DOMAIN_TABLE_XPATH = "//table[@id='domainoverview']";
    const DOMAIN_TABLE_CSS   = "#domainoverview";

    const TYPE_COLUMN_FROM_FIRST_ROW_XPATH = "//*[@id=\"domainoverview\"]/tbody/tr[1]/td[3]";

    const CHECK_STATUS_LINK_XPATH = "span.pstatus a";

    const TOGGLE_PROTECTION_LINK_XPATH = "//*[@id='domainoverview']/tbody/tr/td[5]/a[1]";
    const TOGGLE_PROTECTION_LINK_CSS   = "#domainoverview > tbody > tr > td:nth-child(5) > a.toggle";

    const LOGIN_LINK_XPATH = "//a[contains(.,'Login')]";
}
