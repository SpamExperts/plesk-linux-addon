<?php

/*
 *************************************************************************
 *                                                                       *
 * ProSpamFilter                                                         *
 * Bridge between Webhosting panels & SpamExperts filtering				 *
 *                                                                       *
 * Copyright (c) 2010-2013 SpamExperts B.V. All Rights Reserved,         *
 *                                                                       *
 *************************************************************************
 *                                                                       *
 * Email: support@spamexperts.com                                        *
 * Website: htttp://www.spamexperts.com                                  *
 *                                                                       *
 *************************************************************************
 *                                                                       *
 * This software is furnished under a license and may be used and copied *
 * only in accordance with the terms of such license and with the        *
 * inclusion of the above copyright notice. No title to and ownership    *
 * of the software is hereby transferred.                                *
 *                                                                       *
 * You may not reverse engineer, decompile or disassemble this software  *
 * product or software product license.                                  *
 *                                                                       *
 * SpamExperts may terminate this license if you don't comply with any   *
 * of the terms and conditions set forth in our end user                 *
 * license agreement (EULA). In such event, licensee agrees to return    *
 * licensor or destroy all copies of software upon termination of the    *
 * license.                                                              *
 *                                                                       *
 * Please see the EULA file for the full End User License Agreement.     *
 *                                                                       *
 *************************************************************************
 * @category  SpamExperts
 * @package   ProSpamFilter
 * @author    "Dmitry Lomakin" <dmitry@spamexperts.com>
 * @copyright Copyright (c) 2013, SpamExperts B.V., All rights Reserved. (http://www.spamexperts.com)
 * @license   Closed Source
 * @version   3.0
 * @link      https://my.spamexperts.com/kb/34/Addons
 */

abstract class SpamFilter_Domain_Strategy_Abstract
{

    /**
     * Spampanel API client instance getter
     *
     * @access protected
     *
     * @return SpamFilter_ResellerAPI
     */
    protected function _getSpampanelApiClient()
    {
        return new SpamFilter_ResellerAPI;
    }

    /**
     * Log writer getter
     *
     * @access protected
     *
     * @throws RuntimeException
     *
     * @return SpamFilter_Logger
     */
    protected function _getLogger()
    {
        if (!Zend_Registry::isRegistered('logger')) {
            throw new RuntimeException("The 'logger' module is not registered in the application environment");
        }

        return Zend_Registry::get('logger');
    }

}