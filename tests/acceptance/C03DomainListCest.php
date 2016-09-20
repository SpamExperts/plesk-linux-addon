<?php

namespace Plesk;

use WebGuy;
use Page\DomainListPage;
use Page\ProfessionalSpamFilterPage;
use Step\Acceptance\CommonSteps;
use Step\Acceptance\DomainListSteps;

class C03DomainListCest
{
    protected $doamin;

    public function _before(DomainListSteps $I)
    {
        $I->login();
    }

    public function _after(DomainListSteps $I)
    {
    }

    public function verifyDomainListAsRoot(DomainListSteps $I)
    {
        $I->goToPage(ProfessionalSpamFilterPage::DOMAIN_LIST_BTN, DomainListPage::TITLE);
        $I->checkListDomainsPageLayout();
        $account = $I->addNewSubscription();
        $I->checkDomainList($account['domain'], true);
        $I->checkToogleProtection($account['domain']);
        $I->checkLoginFunctionality($account['domain']);
    }

    public function verifyDomainListAsReseller(DomainListSteps $I)
    {
        list($resellerUsername, $resellerPassword, $resellerId) = $I->createReseller();
        $I->shareIp($resellerId);
        $I->logout();
        $I->login($resellerUsername, $resellerPassword);
        $I->checkPsfPresentForReseller();
        $account = $I->addNewSubscription();
        $I->checkDomainList($account['domain']);
        $I->checkToogleProtection($account['domain']);
        $I->checkLoginFunctionality($account['domain']);
    }

    public function verifyDomainListAsCustomer(DomainListSteps $I)
    {
        list($customerUsername, $customerPassword, $domain) = $I->createCustomer();
        $I->shareIp();
        $I->logout();
        $I->loginAsClient($customerUsername, $customerPassword);
        $I->checkPsfPresentForCustomer();
        $I->checkLoginFunctionality($domain, false);
    }
}
