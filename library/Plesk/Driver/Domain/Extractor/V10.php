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

class Plesk_Driver_Domain_Extractor_V10 extends Plesk_Driver_Domain_Extractor_Abstract
{
    const PROTOCOL_VERSION = '1.6.3.2';

    protected function _getProtocolVersion()
    {
        return self::PROTOCOL_VERSION;
    }

    /**
     * Methods for domains retrieval
     *
     * @access public
     *
     * @param string $filter
     * @param bool   $unfiltered
     * @param bool   $topLevelOnly
     *
     * @return array
     */
    public function _getDomains($filter = '', $unfiltered = false, $topLevelOnly = false)
    {
        $result = array();

        $xml = $this->_request(
            array(
                 'webspace' => array(
                     'get' => array(
                         'filter'  => $filter,
                         'dataset' => array(
                             'hosting' => '',
                         ),
                     ),
                 ),
            )
        );

        // If given domain is not webspace we will try find data for domain (site) 
        $check = $this->_xmlAsArray($xml);
        if (!isset($check['webspace']['get']['result']['errcode'])) {
            if ($unfiltered) {
                $result = array_merge($result, $this->_xmlAsArray($xml));
            } else {
                $result = array_merge($result, $this->_xmlToDomains($xml));
            }
            // @see https://trac.spamexperts.com/software/ticket/15766#comment:32
            if ($topLevelOnly) {
                return $result;
            }

            // We have to use another filter for sites retrieval in case of the filter isn't empty
            // @see https://trac.spamexperts.com/software/ticket/15846#comment:12
            if (!empty($filter) && !empty($result)) {
                $filter = array(
                    'parent-name' => array(),
                );
                foreach ($result as $webspaceName) {
                    $filter['parent-name'][] = $webspaceName;
                }
            }
        }
        // We should retrieve extra-domains only in case of there are owner domains exists
        // This is especially actual for non-admin accounts

        $xml = $this->_request(
            array(
                'site' => array(
                    'get' => array(
                        'filter'  => $filter,
                        'dataset' => array(
                            'hosting' => '',
                        ),
                    ),
                ),
            )
        );
        
        if ($this->_xmlAsArray($xml)) {
            if ($unfiltered) {
                $result = array_merge($result, $this->_xmlAsArray($xml));
            } else {
                $result = array_merge($result, $this->_xmlToDomains($xml));
            }
        }
        return $result;
    }
}
