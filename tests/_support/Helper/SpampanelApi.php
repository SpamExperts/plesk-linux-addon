<?php

namespace Helper;

class SpampanelApi extends \Codeception\Module
{
    public function apiCheckDomainExists($domain)
    {
        codecept_debug("Checking if $domain exists in spampanel api");
        $response = $this->makeSpampanelApiRequest('domain/exists', ['domain' => $domain]);
        $this->assertEquals(200, $response['info']['http_code']);
        $data = json_decode($response['output'], true);
        $this->assertEquals(1, $data['present']);
    }

    public function apiCheckDomainNotExists($domain)
    {
        codecept_debug("Checking if $domain NOT exists in spampanel api");
        $response = $this->makeSpampanelApiRequest('domain/exists', ['domain' => $domain]);
        $this->assertEquals(200, $response['info']['http_code']);
        $data = json_decode($response['output'], true);
        $this->assertEquals(0, $data['present']);
    }

    public function apiGetDomainRoutes($domain)
    {
        codecept_debug("Getting $domain routes");
        $response = $this->makeSpampanelApiRequest('domain/getroute/format/json', ['domain' => $domain]);
        $this->assertEquals(200, $response['info']['http_code']);
        $response = json_decode($response['output'], true);

        if (! empty($response['messages']['error'])) {
            return [];
        }

        return $response['result'];
    }

    public function apiGetDomainAliases($domain)
    {
        codecept_debug("Getting $domain aliases");
        $response = $this->makeSpampanelApiRequest('domainalias/list/format/json', ['domain' => $domain]);
        $this->assertEquals(200, $response['info']['http_code']);
        $response = json_decode($response['output'], true);

        if (! empty($response['messages']['error'])) {
            return [];
        }

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

    public function requestUrl($url, $username = null, $password = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }

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
