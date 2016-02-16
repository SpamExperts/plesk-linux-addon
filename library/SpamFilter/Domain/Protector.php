<?php

/*************************************************************************
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

class SpamFilter_Domain_Protector
{

    /**
     * Domain instance container
     *
     * @access protected
     * @var SpamFilter_Domain_Abstract
     */
    protected $_domain;

    /**
     * @access protected
     * @var bool
     */
    protected $_protectOnlyLocalDomains = false;

    /**
     * Class constructor.
     * The input domain should not be punycoded
     *
     * @access public
     *
     * @param SpamFilter_Domain_Abstract $domain
     * @param bool                       $protectOnlyLocalDomains
     *
     * @return SpamFilter_Domain_Protector
     *
     */
    public function __construct(SpamFilter_Domain_Abstract $domain, $protectOnlyLocalDomains = false)
    {
        $this->_domain = $domain;
        $this->_protectOnlyLocalDomains = !!$protectOnlyLocalDomains;
    }

    /**
     * Protect the domain
     *
     * @access public
     *
     * @return void
     */
    public function protect()
    {
        $protector = $this->_domain->getProtectionStrategy();

        $protector->registerPreProtect(array($this, 'domainRemotenessCheck'));

        $protector->protect();
    }

    /**
     * Unprotect the domain
     *
     * @access public
     *
     * @return void
     */
    public function unprotect()
    {
        $this->_domain->getUnprotectionStrategy()->unprotect();
    }

    /**
     * Toggle domain protection status
     *
     * @access public
     *
     * @return void
     */
    public function toggleProtection()
    {
        if ($this->_domain->isProtected()) {
            $this->_domain->getUnprotectionStrategy()->unprotect();
        } else {
            $this->_domain->getProtectionStrategy()->protect();
        }
    }

    /**
     * A pre-check for local/remote domains
     *
     * @access public
     * @return void
     * @throws RuntimeException
     */
    public function domainRemotenessCheck()
    {
        if ($this->_domain->isRemote() && $this->_protectOnlyLocalDomains) {
            throw new RuntimeException(sprintf(
                "The domain '%s' is remote and we don't protect such domains according to the plugin's settings",
                $this->_domain));
        }
    }

}