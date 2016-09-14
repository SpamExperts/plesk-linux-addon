<?php
use Codeception\Scenario;
use Symfony\Component\Yaml\Yaml;

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
class WebGuy extends \Codeception\Actor
{
    use _generated\WebGuyActions;

    private $parameters = [];

    public function __construct(Scenario $scenario)
    {
        parent::__construct($scenario);
        $this->parseYaml();
    }

    public function getEnvParameter($name, $default = null)
    {

        return $this->parameters['env'][$name];
    }

    private function parseYaml()
    {
        $file = realpath(__DIR__ . '/../acceptance.suite.yml');
        $this->parameters = Yaml::parse(file_get_contents($file));
    }
}
