<?php

namespace Plesk;

use WebGuy;
use Step\Acceptance\CommonSteps;

class C00PsfInstallCest
{
    public function verifyPleaskPsfAddonIsInstalled(CommonSteps $I)
    {
        $I->login();
        $I->checkPsfPresentForRoot();
    }
}
