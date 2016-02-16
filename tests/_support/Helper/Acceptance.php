<?php
namespace Helper;

use Codeception\Module\WebDriver;

class Acceptance extends \Codeception\Module
{
    public function checkAllBoxes($selector)
    {
        $webDriver = $this->getWebDriver();

        $elements = $webDriver->_findElements($selector);
        foreach ($elements as $element) {
            $this->getWebDriver()->checkOption($element);
        }
    }

    public function getElementsCount($selector)
    {
        return count($this->getWebDriver()->_findElements($selector));
    }

    public function seeInCurrentAbsoluteUrl($text)
    {
        $this->assertContains($text, $this->getWebDriver()->webDriver->getCurrentURL());
    }

    /**
     * @return WebDriver
     */
    private function getWebDriver()
    {
        return $this->getModule('WebDriver');
    }

    public function setWebDriverUrl($scenario)
    {
        /** @var WebDriver $webDriver */
        $webDriver = $this->getWebDriver();

        $env = $scenario->current('env');
        $file = realpath(__DIR__.'/../../acceptance.suite.yml');
        $parameters = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));

        if (isset($parameters['env'][$env]['url'])) {
            $webDriver->_setConfig([
                'url' => getenv($parameters['env'][$env]['url'])
            ]);
        }
        codecept_debug($webDriver->_getConfig()['url']);

    }
}
