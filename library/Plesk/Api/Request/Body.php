<?php

/*
*************************************************************************
*                                                                       *
* ProSpamFilter                                                         *
* Bridge between Webhosting panels & SpamExperts filtering		*
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
* @author    "Dmitry Lomakin" <dmitry@spamexperts.com>
* @copyright Copyright (c) 2013, SpamExperts B.V., All rights Reserved. (http://www.spamexperts.com)
* @license   Closed Source
* @version   3.0
* @link      https://my.spamexperts.com/kb/34/Addons
* @since     3.0
*/

class Plesk_Api_Request_Body
{
    private $_version = Plesk_Driver_Domain_Extractor_V10::PROTOCOL_VERSION;

    private $_module;

    private $_action;

    private $_filters = array();

    private $_datasets = array();

    public function __construct($module, $action, array $filters = array(), array $datasets = array())
    {
        $this->_module = $module;
        $this->_action = $action;
        $this->_filters = $filters;
        $this->_datasets = $datasets;
    }

    public function __toString()
    {
        $result = '<?xml version="1.0" encoding="utf-8"?>'
                . '<packet version="' . $this->_version . '">'
                . "<{$this->_module}><{$this->_action}>"
                . '<filter>';

        foreach ($this->_filters as $name => $value) {
            $result .= "<{$name}>{$value}</{$name}>";
        }

        $result .= '</filter><dataset>';

        foreach ($this->_datasets as $dataset) {
            $result .= "<{$dataset}/>";
        }

        $result .= '</dataset>'
                 . "</{$this->_action}></{$this->_module}></packet>";

        return $result;
    }
}