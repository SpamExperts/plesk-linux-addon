<?php
use Page\PleskLinuxClientPage;
use Codeception\Util\Locator;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Function used to switch to left frame of plesk control panel
     */
    public function switchToLeftFrame()
    {
        // Switch to main window
        $this->switchToWindow();

        // Wait for left frame to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::LEFT_FRAME_CSS, PleskLinuxClientPage::LEFT_FRAME_XPATH));

        // Switch to main frame
        $this->switchToIFrame(PleskLinuxClientPage::LEFT_FRAME_NAME);
    }

    /**
     * Function used to switch to main frame of plesk control panel
     */
    public function switchToWorkFrame()
    {
        // Switch to main window
        $this->switchToWindow();

        // Wait for main frame to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::WORK_FRAME_CSS, PleskLinuxClientPage::WORK_FRAME_XPATH));

        // Switch to main frame
        $this->switchToIFrame(PleskLinuxClientPage::WORK_FRAME_NAME);
    }

    /**
     * Function used to switch to top frame of plesk control panel
     */
    public function switchToTopFrame()
    {
        // Switch to main window
        $this->switchToWindow();

        // Wait for main frame to appear
        $this->waitForElement(Locator::combine(PleskLinuxClientPage::TOP_FRAME_CSS, PleskLinuxClientPage::TOP_FRAME_XPATH));

        // Switch to main frame
        $this->switchToIFrame(PleskLinuxClientPage::TOP_FRAME_NAME);
    }
}
