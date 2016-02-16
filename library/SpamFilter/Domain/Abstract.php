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

abstract class SpamFilter_Domain_Abstract
{

    /**
     * Domain container
     *
     * @access protected
     * @var string
     */
    protected $_domain;

    /**
     * Class constructor.
     * The input domain should not be punycoded
     *
     * @access public
     *
     * @param string $domain
     *
     * @return SpamFilter_Domain_Abstract
     *
     * @throws InvalidArgumentException
     */
    public function __construct($domain)
    {
        /** @noinspection PhpUndefinedClassInspection */
        if (Zend_Validate::is($domain, 'Hostname', array(Zend_Validate_Hostname::ALLOW_DNS, true, true))) {
            throw new InvalidArgumentException(
                sprintf("The domains '%s' is invalid", htmlspecialchars($domain, ENT_QUOTES, 'UTF-8')));
        }

        $this->_domain = $domain;
    }

    /**
     * Check whether the domain is protected
     *
     * @access public
     *
     * @return bool
     */
    public function isProtected()
    {
        $result = false;


        return $result;
    }

    /**
     * Check whether the domain is a remote domain
     *
     * @access public
     *
     * @return bool
     */
    public function isRemote()
    {
        $result = false;


        return $result;
    }

    /**
     * Retrieves the destination for the provided domain
     *
     * @access public
     *
     * @param bool $useExistingMxRecords
     *
     * @return string
     */
    public function getDestination($useExistingMxRecords = true)
    {
        /** @noinspection PhpUndefinedClassInspection */
        $destination = SpamFilter_Core::GetServerName();

        if ($useExistingMxRecords) {
            $mxr = $this->_getMxRecords();

            if (empty($mxr)) {
                return $destination;
            }

            // Check if the domain's MX records are already pointing to the filter cluster.
            $filteringClusterHostnames = SpamFilter_DNS::getFilteringClusterHostnames();
            $intersect = array_intersect($mxr, $filteringClusterHostnames);

            if (!empty($intersect)) {
                $this->_getLogger()->warn(
                    "Current MX records for '{$this->_domain}' seem to be pointing to the filtering cluster already. Falling back to the server's hostname."
                );

                return $destination;
            } else {
                $destination = join(',', $mxr); // Glue them with a ,
            }
        }

        return $destination;
    }

    /**
     * Get the domain's MX records ordered by priority
     *
     * @access protected
     *
     * @return array
     */
    protected function _getMxRecords()
    {
        $records = array();
        $mxRecordsExist = false;

        if (checkdnsrr($this->_domain . '.', 'MX')) {
            $mxRecordsExist = getmxrr($this->_domain, $mxHosts, $mxWeights);
        }

        if (false === $mxRecordsExist) {
            $records[] = $this->_domain;
        } elseif (isset($mxHosts, $mxWeights) && is_array($mxHosts) && is_array($mxWeights)) {
            asort($mxWeights, SORT_NUMERIC);
            foreach ($mxWeights as $i => $weight) {
                if (!empty($mxHosts[$i])) {
                    $records[] = $mxHosts[$i];
                }
            }
        }

        return $records;
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
        /** @noinspection PhpUndefinedClassInspection */
        if (!Zend_Registry::isRegistered('logger')) {
            throw new RuntimeException("The 'logger' module is not registered in the application environment");
        }

        /** @noinspection PhpUndefinedClassInspection */
        return Zend_Registry::get('logger');
    }

    public function __toString()
    {
        return $this->_domain;
    }

    /**
     * Protection strategy getter method
     *
     * @access public
     *
     * @return SpamFilter_Domain_Strategy_Protection_Abstract
     */
    abstract public function getProtectionStrategy();

    /**
     * Unprotection strategy getter method
     *
     * @access public
     *
     * @return SpamFilter_Domain_Strategy_Unprotection_Abstract
     */
    abstract public function getUnprotectionStrategy();

}