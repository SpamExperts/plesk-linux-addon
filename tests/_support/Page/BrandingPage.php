<?php

namespace Page;

class BrandingPage
{
    const TITLE = "Branding";
    const DESCRIPTION = "On this page you can change how the addon looks for the customers.";

    const ORIGINAL_BRANDNAME = 'Professional Spam Filter';

    const SUB_TITLE_A   = "Current branding";
    const DESCRIPTION_A = "Your branding is currently set to:";
    const SUB_TITLE_B   = "Change branding";
    const DESCRIPTION_B = "If you want to change the branding shown above, you can do this in the form below.";

    const BRANDING_ICON_CSS = "#some_icon";
    const BRANDING_ICON_XPATH = "//img[@id='some_icon']";

    const BRANDNAME_INPUT_CSS = "#brandname";
    const BRANDNAME_INPUT_XPATH = "//input[@id='brandname']";

    const BRANDICON_SELECT_CSS = "#brandicon";
    const BRANDICON_SELECT_XPATH = "//input[@id='brandicon']";

    const SAVE_BRANDING_BTN_CSS = "#submit";
    const SAVE_BRANDING_BTN_XPATH = "//input[@id='submit']";
}
