<?php

namespace Helper;

use RuntimeException;

class SpampanelApi extends \Codeception\Module
{
    /**
     * Function used to check if a domain is alias for other domain from Spampanel
     * @param  string $alias  alias domain to check
     * @param  string $domain domain
     */
    public function assertIsAliasInSpampanel($alias, $domain)
    {
        // Get Spampanel alias list for the domain
        $aliases = $this->apiGetDomainAliases($domain);

        // See if the alias is in alias list from spampanel
        $this->assertContains($alias, $aliases);
    }

    /**
     * Function used to check if a domain is not alias for other domain from Spampanel
     * @param  string $alias  alias domain to check
     * @param  string $domain domain
     */
    public function assertIsNotAliasInSpampanel($alias, $domain)
    {
        // Get Spampanel alias list for the domain
        $aliases = $this->apiGetDomainAliases($domain);

        // See if the alias is in alias list from spampanel
        $this->assertNotContains($alias, $aliases);
    }

    /**
     * Function used to check if domain exist in Spampanel
     * @param  string  $domain desired domain
     */
    public function apiCheckDomainExists($domain)
    {
        // Display debug info
        codecept_debug("Checking if $domain exists in spampanel api");

        // Make a Spampanel API request
        $response = $this->makeSpampanelApiRequest('domain/exists', ['domain' => $domain]);

        // Check request return values
        $this->assertEquals(200, $response['info']['http_code']);
        $data = json_decode($response['output'], true);
        $this->assertEquals(1, $data['present']);
    }

     /**
     * Function used to check if domain don't exist in Spampanel
     * @param  string  $domain desired domain
     */
    public function apiCheckDomainNotExists($domain)
    {
        // Dislay debug info
        codecept_debug("Checking if $domain NOT exists in spampanel api");

        // Make a Spampanel API request
        $response = $this->makeSpampanelApiRequest('domain/exists', ['domain' => $domain]);

        // Check request return values
        $this->assertEquals(200, $response['info']['http_code']);
        $data = json_decode($response['output'], true);
        $this->assertEquals(0, $data['present']);
    }

    /**
     * Function used to get routes for a certain domain in Spampanel
     * @param  string $domain domain
     * @return routes list
     */
    public function apiGetDomainRoutes($domain)
    {
        codecept_debug("Getting $domain routes");
        $response = $this->makeSpampanelApiRequest('domain/getroute/format/json', ['domain' => $domain]);
        $this->assertEquals(200, $response['info']['http_code']);
        $response = json_decode($response['output'], true);

        if (! empty($response['messages']['error']))
            return [];

        return $response['result'];
    }

    public function apiGetDomainAliases($domain)
    {
        codecept_debug("Getting $domain aliases");
        $response = $this->makeSpampanelApiRequest('domainalias/list/format/json', ['domain' => $domain]);
        $this->assertEquals(200, $response['info']['http_code']);
        $response = json_decode($response['output'], true);

        if (! empty($response['messages']['error']))
            return [];

        return $response['result'];
    }

    public function makeSpampanelApiRequest($url, array $params = array())
    {
        $url = \PsfConfig::getApiUrl().'/api/'.$url;

        foreach ($params as $name => $value) {
            if (is_array($value)) {
                $value = array_map(function($val){return '"'.$val.'"';}, $value);
                $value = '['.implode(',', $value).']';
            }
            $url .= '/'.$name.'/'.rawurlencode($value);
        }

        $response = $this->requestUrl($url, \PsfConfig::getApiUsername(), \PsfConfig::getApiPassword());

        codecept_debug("Making api request: ".$url);

        return $response;
    }

    public function addDomainAlias($alias, $domain)
    {
        $response = $this->makeSpampanelApiRequest('domainalias/add/format/json', ['domain' => $domain, 'alias' => $alias]);
        $this->checkResponseStatus($response);
        $response = json_decode($response['output'], true);

        if (!empty($response['messages']['error']))
            throw new RuntimeException("Api error: ".var_export($response, true));

    }

    private function checkResponseStatus($response)
    {
        if (200 != $response['info']['http_code'])
            throw new RuntimeException("Invalid api status code ".$response['info']['http_code']);
    }

    public function requestUrl($url, $username = null, $password = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($username && $password)
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);

        $output =
            curl_exec($ch);

        $response = [
            'output' => $output,
            'info' => curl_getinfo($ch)
        ];

        curl_close($ch);

        return $response;
    }
}
