<?php

class Plesk_Driver_Domain extends Plesk_Driver
{
    /**
     * Panel support manager instance
     *
     * @access private
     * @var SpamFilter_PanelSupport_Plesk
     */
    private $_panelSupport;

    /**
     * Panel support manager instance setter method
     *
     * @param SpamFilter_PanelSupport_Plesk $panelSupport
     *
     * @return void
     */
    public function setPanelSupport(SpamFilter_PanelSupport_Plesk $panelSupport)
    {
        $this->_panelSupport = $panelSupport;
    }

    private function _retrieveDomains($filter = '', $unfiltered = false, $topLevelOnly = false)
    {
        if (null === $this->_panelSupport) {
            $this->_panelSupport = new SpamFilter_PanelSupport_Plesk;
        }

        return Plesk_Driver_Domain_Extractor_Factory::createInstance(
            $this->_panelSupport->getVersion(),
            new Plesk_Driver
        )->getSortedDomains($filter, $unfiltered, $topLevelOnly);
    }

    public function getAllDomains()
    {
        return $this->_retrieveDomains();
    }

    public function getDomainbyId($domain_id)
    {
        return $this->_retrieveDomains(
            array('id' => $domain_id),
            false,
            true
        );
    }

    public function getOwnerIdByDomain($domain)
    {
        $api = new Plesk_Driver;

        /**
         * First of all we should check for site->webspace relation to avoid errors like
         * "Webspace does not exist" while trying to find a site's contact email
         */
        $siteXml = $api->doRequest(
            new Plesk_Api_Request_Body('site', 'get', array('name' => $domain), array('gen_info')), '', true
        );

        $dom = new DomDocument("1.0", "UTF-8");
        if (!@$dom->loadXML($siteXml)) {
            return false;
        }

        /**
         * Now we should find the domain's webspace to detect related client's data
         */
        $webspaceGuid = self::_getScalarByXpath($dom, '//gen_info/webspace-guid');
        $subscriptionFilter = (!empty($webspaceGuid) ? array('guid' => $webspaceGuid) : array('name' => $domain));
        $subscriptionXml = $api->doRequest(
            new Plesk_Api_Request_Body('webspace', 'get', $subscriptionFilter, array('gen_info')), '', true
        );
        
        $dom = new DomDocument("1.0", "UTF-8");
        if (!@$dom->loadXML($subscriptionXml)) {
            return false;
        }

        $ownerId    = self::_getScalarByXpath($dom, '//gen_info/owner-id');
        $ownerLogin = self::_getScalarByXpath($dom, '//gen_info/owner-login');

        // try get owner ID, if ID's empty we try another way
        if(!empty($ownerLogin) && class_exists('pm_Client') && 'cli' != PHP_SAPI){
            $cData = pm_Client::getByLogin($ownerLogin);
            $clientID = $cData->getId();
            if(!empty($clientID)){
                return $clientID;
            }
        }
        /**
         * And finally try to find the required client
         */
        if (!empty($ownerId) || !empty($ownerLogin)) {
            $filter = (!empty($ownerId) ? array('id' => $ownerId) : array('login' => $ownerLogin));
            $customerXml = $api->doRequest(
                new Plesk_Api_Request_Body('customer', 'get', $filter, array('gen_info')), '', true
            );
            $dom = new DomDocument("1.0", "UTF-8");
            if (!@$dom->loadXML($customerXml)) {
                return false;
            } else {
                if( (int)self::_getScalarByXpath($dom, '//result/errcode') == 1013 ){
                    $resellerXml = $api->doRequest(
                        new Plesk_Api_Request_Body('reseller', 'get', $filter, array('gen-info')), '', true
                    );
                    if (@$dom->loadXML($resellerXml)) {
                        return self::_getScalarByXpath($dom, '//result/id');
                    }
                }
                return self::_getScalarByXpath($dom, '//result/id');
            }
        }
        return false;
    }

    public function getDomainByDomain($domain, $unfiltered = false)
    {
        return $this->_retrieveDomains(
            array(
                 $this->_getDomainNameFilter() => $domain,
            ), $unfiltered
        );
    }

    public function getDomainsbyUser($user)
    {
        return $this->_retrieveDomains(
            array(
                 'owner-login' => $user
            )
        );
    }

    public function getDomainsbyDomain($domain)
    {
        return $this->_retrieveDomains(
            array(
                 $this->_getDomainNameFilter() => $domain,
            )
        );
    }

    public function getDomainsbyClient($client)
    {
        return $this->_retrieveDomains(
            array('owner-id' => $client)
        );
    }

    public function getDomainsbyOwnerId($user)
    {
        return $this->_retrieveDomains(
            array('owner-login' => $user)
        );
    }

    public function getDomainsbyOwner($client)
    {
        return $this->_retrieveDomains(
            array('owner-id' => $client)
        );
    }

    /**
     * Method for reseller's domains retrieval
     *
     * @access public
     *
     * @param string $resellerUsername
     *
     * @return array
     */
    public function getResellerDomains($resellerUsername)
    {
        $api        = new Plesk_Driver;
        $clientsXml = $api->doRequest(
            array(
                 'customer' => array(
                     'get' => array(
                         'filter'  => array(
                             'owner-login' => $resellerUsername,
                         ),
                         'dataset' => array(
                             'gen_info' => ''
                         ),
                     ),
                 ),
            ), Plesk_Driver_Domain_Extractor_V10::PROTOCOL_VERSION, true
        );

        $dom = new DomDocument("1.0", "UTF-8");
        if (!@$dom->loadXML($clientsXml)) {
            return array();
        }

        unset($clientsXml);

        $resellerClients = array();

        $xpath      = new DOMXPath($dom);
        $xpathQuery = "//gen_info[./login]";

        foreach ($xpath->query($xpathQuery) as $node) {

            /** @var $node DOMElement */
            $nodes = $node->getElementsByTagName('login');
            if ($nodes->length) {
                $resellerClients[] = (string)$nodes->item(0)->nodeValue;
            }
        }

        return $this->_retrieveDomains(
            array('owner-login' => array_merge(array($resellerUsername), $resellerClients))
        );
    }

    private function _getDomainNameFilter()
    {
        if (null === $this->_panelSupport) {
            $this->_panelSupport = new SpamFilter_PanelSupport_Plesk;
        }

        return (version_compare($this->_panelSupport->getVersion(), '10.2', '<') ? 'domain-name' : 'name');
    }

    private function _getScalarByXpath(DomDocument $dom, $xpath)
    {
        $data = new DOMXPath($dom);
        $nodes = $data->query($xpath);

        return ((0 < $nodes->length) ? (string) $nodes->item(0)->nodeValue : false);
    }

}
