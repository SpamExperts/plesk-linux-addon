<?php

namespace Plesk;

use WebGuy;
use Step\Acceptance\CommonSteps;

class C00PsfInstallCest
{
    public function verifyPleaskPsfAddonIsInstalled(CommonSteps $I)
    {
        // Login as root
        $I->loginAsRoot();

        // Check if "Professional Spam Filter" is installed for root
        $I->checkPsfPresentForRoot();
    }
}
