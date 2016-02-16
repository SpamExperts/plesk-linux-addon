<?php

/*
*************************************************************************
*                                                                       *
* ProSpamFilter                                                        	*
* Bridge between Webhosting panels & SpamExperts filtering	   	*
*                                                                       *
* Copyright (c) 2010-2012 SpamExperts B.V. All Rights Reserved,         *
*                                                                       *
*************************************************************************
*                                                                       *
* Email: support@spamexperts.com                                        *
* Website: htttp://www.spamexperts.com                                  *
*                                                                       *
*************************************************************************
*                                                                       *
* This software is furnished under a license and may be used and copied *
* only in accordance with the  terms of such license and with the       *
* inclusion of the above copyright notice. No title to and ownership    *
* of the software is  hereby  transferred.                              *
*                                                                       *
* You may not reverse engineer, decompile or disassemble this software  *
* product or software product license.                                  *
*                                                                       *
* SpamExperts may terminate this license if you don't comply with any   *
* of the terms and conditions set forth in our end user                 *
* license agreement (EULA). In such event, licensee agrees to return    *
* licensor  or destroy  all copies of software upon termination of the  *
* license.                                                              *
*                                                                       *
* Please see the EULA file for the full End User License Agreement.     *
*                                                                       *
*************************************************************************
* @category  SpamExperts
* @package   ProSpamFilter
* @author    Dmitry Lomakin <dmitry@spamexperts.com>
* @copyright Copyright (c) 2012, SpamExperts B.V., All rights Reserved. (http://www.spamexperts.com)
* @license   Closed Source
* @version   3.0
* @link      https://my.spamexperts.com/kb/34/Addons
* @since     3.0
*/

abstract class Plesk_Driver_Domain_Extractor_Abstract
{
    /**
     * Plesk API client instance
     *
     * @access protected
     * @var Plesk_Driver
     */
    protected $_api;

    /**
     * Class constructor
     *
     * @access public
     *
     * @param Plesk_Driver $apiClientInstance
     *
     * @return Plesk_Driver_Domain_Extractor_Abstract
     */
    public function __construct(Plesk_Driver $apiClientInstance)
    {
        $this->_api = $apiClientInstance;
    }

    /**
     * Wrapper method for Plesk_Driver::doRequest()
     *
     * @access protected
     *
     * @param array $requestData
     *
     * @return bool|mixed
     */
    protected function _request(array $requestData)
    {
        return $this->_api->doRequest($requestData, $this->_getProtocolVersion(), true);
    }

    /**
     * Methods for sorted list of domains retrieval
     *
     * @access public
     *
     * @param string $filter
     * @param bool   $unfiltered
     * @param        $topLevelOnly
     *
     * @return array
     */
    public function getSortedDomains($filter = '', $unfiltered = false, $topLevelOnly)
    {
        $domains = $this->_getDomains($filter, $unfiltered, $topLevelOnly);

        if (!$unfiltered) {
            natcasesort($domains);
        }
        
        return $domains;
    }

    /**
     * This method transforms API response in XML format into a PHP array
     * array(domain_id => domain_ascii_name)
     *
     * @access protected
     *
     * @param string $xml
     *
     * @return array
     */
    protected function _xmlToDomains($xml)
    {
        $result = array();

        $dom = new DomDocument("1.0", "UTF-8");
        if (!@$dom->loadXML($xml)) {
            return $result;
        }

        unset($xml);

        $xpath      = new DOMXPath($dom);
        $xpathQuery = "//result[./id]";

        foreach ($xpath->query($xpathQuery) as $node) {
            $domainName = '';

            /** @var $node DOMElement */

            $asciiNameNodes = $node->getElementsByTagName('ascii-name');
            if ($asciiNameNodes->length) {
                $domainName = (string)$asciiNameNodes->item(0)->nodeValue;
            }

            // Fallback way to get a domain's name
            // This is actual for subdomains extraction
            if (empty($domainName)) {
                $nameNodes = $node->getElementsByTagName('name');
                if ($nameNodes->length) {
                    $domainName = (string)$nameNodes->item(0)->nodeValue;
                }
            }

            if (!empty($domainName)) {
                $result[(int)$node->getElementsByTagName('id')->item(0)->nodeValue] = $domainName;
            }
        }

        return $result;
    }

    /**
     * XML-> PHP Array converter, required for backward compatibility
     *
     * @access protected
     *
     * @param string $xml
     *
     * @return mixed
     */
    protected function _xmlAsArray($xml)
    {
        $xml  = simplexml_load_string($xml);
        $json = json_encode($xml);

        return json_decode($json, true);
    }

    abstract protected function _getProtocolVersion();

    abstract protected function _getDomains($filter = '', $unfiltered = false, $topLevelOnly = false);
}
