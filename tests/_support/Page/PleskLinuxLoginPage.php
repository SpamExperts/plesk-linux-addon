<?php

namespace Page;

class PleskLinuxLoginPage
{
    const USERNAME_FIELD_XPATH = "//input[@id='loginSection-username']";
    const USERNAME_FIELD_CSS = "#loginSection-username";

    const PASSWORD_FIELD_XPATH = "//input[@id='loginSection-password']";
    const PASSWORD_FIELD_CSS = "#loginSection-password";

    const LOGIN_BTN_XPATH = "//button[@type='submit']";
    const LOGIN_BTN_CSS = "#btn-send>button";
}
