<?php

namespace Page;

class SpampanelPage
{
    const LOGOUT_LINK = '//a[@href="#confirmLogoutDialog"]';
    const LOGOUT_CONFIRM_LINK = '//a[@href="/index.php?action=logout"]';

    const CONTACT_EMAIL_FIELD_CSS = "#contact_email";
    const CONTACT_EMAIL_FIELD_XPATH = "//*[@id='contact_email']";
}
