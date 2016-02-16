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

class Plesk_Driver_Domain_Extractor_Factory
{
    /**
     * Factory method for the proper instance of extractor generation
     *
     * @static
     * @access public
     * @param string $panelVersion
     * @param Plesk_Driver $apiClientInstance
     * @return Plesk_Driver_Domain_Extractor_Abstract
     */
    final static public function createInstance($panelVersion, Plesk_Driver $apiClientInstance)
    {
        if (version_compare($panelVersion, '10.2', '<')) {
            return new Plesk_Driver_Domain_Extractor_V9($apiClientInstance);
        } else {
            return new Plesk_Driver_Domain_Extractor_V10($apiClientInstance);
        }
    }
}
