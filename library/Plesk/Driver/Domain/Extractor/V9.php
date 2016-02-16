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

class Plesk_Driver_Domain_Extractor_V9 extends Plesk_Driver_Domain_Extractor_Abstract
{
    const PROTOCOL_VERSION = '1.6.0.2';
    const ENTITY = 'domain';

    /**
     * Plesk_Driver_Domain_Extractor_Abstract::getProtocolVersion() implementation
     *
     * @access protected
     * @return string
     */
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

        $xml = $this->_request(array(
            'domain' => array(
                'get' => array(
                    'filter' => $filter,
                    'dataset' => array(
                        'hosting' => '',
                    ),
                ),
            ),
        ));

        $result += $this->_xmlToDomains($xml);

        return $result;
    }
}
