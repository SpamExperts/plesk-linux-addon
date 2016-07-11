<?php
/*
*************************************************************************
*                                                                       *
* ProSpamFilter                                                        	*
* Bridge between Webhosting panels & SpamExperts filtering	   	*
*                                                                       *
* Copyright (c) 2010-2011 SpamExperts B.V. All Rights Reserved,         *
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
* @author    $Author$
* @copyright Copyright (c) 2011, SpamExperts B.V., All rights Reserved. (http://www.spamexperts.com)
* @license   Closed Source
* @version   3.0
* @link      https://my.spamexperts.com/kb/34/Addons
* @since     3.0
*/

class SpamFilter_PanelSupport_Plesk
{
    /**
     * @param Plesk_Driver $_api API object
     */
    var $_api;

    /**
     * @var SpamFilter_Logger $_logger object
     */
    protected $_logger;

    /**
     * @param $_config object
     */
    public $_config;

    /**
     * @param $_options object
     */
    var $_options;

    protected $_adminPassword;

    /**
     * @var Plesk_Driver_Ip
     */
    private $ipDriver;

    /**
     * Verifies whether is possible to use this class on this server and sets up API communication.
     *
     * @throws Exception in case it is not possible
     *
     * @param array $options
     *
     * @return SpamFilter_PanelSupport_Plesk
     * @see    SpamFilter_Configuration
     *
     * @access public
     */
    public function __construct($options = array(), Plesk_Driver_Ip $ipDriver = null)
    {
        $this->_logger = Zend_Registry::get('logger');
        if (!file_exists("/usr/local/psa/") && !file_exists($_ENV['plesk_dir'])) {
            $this->_logger->crit("Wrong Panelsupport library loaded. This is not Plesk.");
            throw new Exception("Wrong Panelsupport library loaded");
        }
        
        $this->_options = $options;

        if (isset($options['altconfig'])) {
            $this->_logger->info("Loading alternative configuration.");
            new SpamFilter_Configuration($this->_options['altconfig']);
        } else {
            $this->_logger->info("Loading default configuration.");
            new SpamFilter_Configuration(CFG_PATH . DS . 'settings.conf');
        }

        if (!Zend_Registry::isRegistered('general_config')) {
            $this->_logger->crit("Config not loaded, please check the configuration file");
            $this->_options['skipapi'] = true; // Skip api at this point
        }

        $this->loadConfig();

        $this->_api = new Plesk_Driver();

        if (! $ipDriver) {
            $ipDriver = new Plesk_Driver_Ip();
        }

        $this->ipDriver = $ipDriver;
    }

    /**
     * Checks whether the API is available
     *
     *
     * @return bool Status
     *
     * @access public
     */
    public function apiAvailable()
    {
        $this->_logger->debug("Checking if Plesk API is available ...");
        $isAvailable = $this->_api->getSupportedProtocols() ? true : false;
        $this->_logger->debug("Plesk API ".($isAvailable ? '' : 'NOT ')."available.");

        return $isAvailable;
    }

    /**
     * Check whether the minimum version is met
     *
     * @return bool True/False
     *
     * @access public
     * @see    getVersion()
     */
    public function minVerCheck()
    {
        $this->_logger->debug("Doing version check.");
        $ver = $this->getVersion();

        if (!empty($ver)) {
            $this->_logger->debug("Checking if '$ver' matches...");
            if (version_compare($ver, '9.5.0', '>=') == 1) {
                $this->_logger->debug("Version OK ($ver)!.");

                return true;
            }
        } else {
            $this->_logger->debug("Current version number is empty.");

            return false;
        }
        $this->_logger->err("Version not OK! (is: '{$ver}').");

        return false;
    }

    /**
     * Get the version of the used control panel
     *
     * @return string|bool Version|Status failed
     *
     * @access public
     */
    public function getVersion()
    {
        if (file_exists(PLESK_DIR ."version")) {          
            $fc      = file_get_contents(PLESK_DIR . "version");         
            $x       = explode(" ", $fc);
            $version = $x[0];

            if (!empty($version)) {
                Zend_Registry::get('logger')->debug("Versioncheck: '{$version}'.");

                return $version;
            }
        }

        $this->_logger->err("Version retrieval failed.");

        return false;
    }

    /**
     * Get the level of the current user.
     *
     * @return string|bool Level |Status failed
     *
     * @access public
     */
    public function getUserLevel()
    {
        /**
         * Skip deep checks for CLI sessions made by the root user
         */
        if ('cli' == PHP_SAPI) {
            $processUser = posix_getpwuid(posix_geteuid());
            $processUsername = !empty($processUser['name']) ? $processUser['name'] : '';
            if ('root' == $processUsername) {
                return 'role_admin';
            }
        }

        /** @var $logger SpamFilter_Logger */
        $logger = Zend_Registry::get('logger');

        $role = 'role_enduser';       
        $logger->debug("Checking for user level");

        if (class_exists('pm_Session')){
            $client = pm_Session::getClient();
            if($client->isAdmin()){
                $logger->debug("role_admin");  
                return 'role_admin';
            } elseif($client->isReseller()){
                $logger->debug("role_reseller");  
                return 'role_reseller';
            } elseif($client->isClient()){
                $logger->debug("role_client");  
                return 'role_client';
            }       
        }

        if (isset($GLOBALS["session"]) && ($GLOBALS["session"] instanceof Session)) {
            if ($GLOBALS["session"]->isAdmin()) {
                $role = 'role_admin';
            } elseif ($GLOBALS["session"]->isReseller()) {
                $role = 'role_reseller';
            } elseif ($GLOBALS["session"]->isClient()) {
                $role = 'role_client';
            } elseif ($GLOBALS["session"]->isDomLevelUsr()) {
                $role = 'role_enduser';
            } elseif ($GLOBALS["session"]->isMailLevelUsr()) {
                $role = 'role_emailuser';
            } else {
                $role = 'role_serviceuser';
            }
        } elseif (!empty($_SESSION['auth']['isAuthenticatedAsRoot'])) {
            /** @see https://trac.spamexperts.com/ticket/20760 */
            $role = 'role_admin';
        } 
        
        $logger->debug("User is '{$role}'");

        return $role;
    }

    public function getAliasDomains($domain)
    {
        $dapi = new Plesk_Driver_Aliases();

        return $dapi->getAliasbyDomain($domain);
    }

    public function getDomains($params)
    {
        if (!empty($params['username'])) {
            $username = $params['username'];
        }
        if (!empty($params['order'])){
            $order = $params['order'];
        }

        $dapi = new Plesk_Driver_Domain();
        $dapi->setPanelSupport($this);

        if (empty($username)) {
            $username = SpamFilter_Core::getUsername();
        }

        switch (strtolower($this->getUserLevel())) {
            case 'role_admin':
                $this->_logger->debug("Domainlist for ADMIN requested");
                $domains = $dapi->getAllDomains();

                break;

            case 'role_reseller':
                $this->_logger->debug("Domainlist for RESELLER requested");
                $domains = $dapi->getResellerDomains($username);

                break;

            case 'role_client':
                $this->_logger->debug("Domainlist for CLIENT requested");
                // Plesk Enduser level + Valid username given.
                $domains = $dapi->getDomainsbyUser($username);

                break;

            case 'role_enduser':
                $this->_logger->debug("Domainlist for ENDUSER requested");
                // Plesk Enduser level + Valid username given.
                $domains = $dapi->getDomainsbyDomain($username);

                break;

            case 'role_serviceuser':
                $domains = $dapi->getDomainsbyClient($GLOBALS["session"]->getUser()->getId());

                break;

            default:
                // Resellers are restricted to their own/their customer's domains
                $this->_logger->debug("Domainlist for OTHER requested");
                $domains = $dapi->getDomainsbyUser($username);

                break;

        }

        $this->loadConfig();
        
        $result = array();
        if (isset($domains) && is_array($domains)) {
            $doAliasesExtraction = (null !== $this->_config && isset($this->_config->handle_extra_domains)
                && 0 < $this->_config->handle_extra_domains);

            $domainsBySiteId = $allAliasesByDomain = array();
            if ($doAliasesExtraction) {
                /** We need to deocde domainname before we want use it as filter
                 * @see https://trac.spamexperts.com/ticket/24399 
                 */
                $convertedDomains = array();
                $IDNA = new IDNA_Convert();
                foreach($domains as $domain){
                    $convertedDomains[] = $IDNA->encode($domain);
                }

                $pleskAPI = new Plesk_Driver;
                $siteIdsByDomainData = $pleskAPI->doRequest(array(
                    'site' => array(
                        'get' => array(
                            'filter'  => array(
                                'name' => $convertedDomains,
                            ),
                            'dataset' => array(
                                'hosting' => '',
                            ),
                        ),
                    ),
                ), Plesk_Driver_Domain_Extractor_V10::PROTOCOL_VERSION);

                if (!empty($siteIdsByDomainData['site']['get']['result'])) {
                    if (!empty($siteIdsByDomainData['site']['get']['result']['id'])) { // if only one row in result not an array of rows
                        $domainsBySiteId[$siteIdsByDomainData['site']['get']['result']['id']]
                            = $siteIdsByDomainData['site']['get']['result']['data']['gen_info']['ascii-name'];
                    } else {
                        foreach ($siteIdsByDomainData['site']['get']['result'] as $siteInfo) {
                            if (is_array($siteInfo)
                                && isset($siteInfo['id'], $siteInfo['data']['gen_info']['ascii-name'])) {
                                $domainsBySiteId[$siteInfo['id']] = $siteInfo['data']['gen_info']['ascii-name'];
                            }
                        }
                    }
                }

                if (!empty($siteIdsByDomainData)) {
                    $allAliasesData = $pleskAPI->doRequest(array(
                        'site-alias' => array(
                            'get' => array(
                                'filter' => array(
                                    'site-id' => array_keys($domainsBySiteId),
                                ),
                            ),
                        ),
                    ), Plesk_Driver_Domain_Extractor_V10::PROTOCOL_VERSION);

                    $aliasesResult = !empty($allAliasesData['site-alias']['get']['result']) ? $allAliasesData['site-alias']['get']['result'] : array();

                    if (!empty($aliasesResult['info']['site-id'])) {
                        $siteId = (!empty($aliasesResult['info']['site-id']) ? (int) $aliasesResult['info']['site-id'] : 0);

                        if ($siteId && isset($domainsBySiteId[$siteId])) {
                            if (!isset($allAliasesByDomain[$domainsBySiteId[$siteId]])) {
                                $allAliasesByDomain[$domainsBySiteId[$siteId]] = array();
                            }

                            $allAliasesByDomain[$domainsBySiteId[$siteId]][] = $aliasesResult['info']['ascii-name'];
                        }
                    } elseif (!empty($aliasesResult) && is_array($aliasesResult)) {
                        foreach ($aliasesResult as $aliasInfo) {
                            $siteId = (!empty($aliasInfo['info']['site-id']) ? (int) $aliasInfo['info']['site-id'] : 0);
                            if ($siteId && isset($domainsBySiteId[$siteId])) {
                                if (!isset($allAliasesByDomain[$domainsBySiteId[$siteId]])) {
                                    $allAliasesByDomain[$domainsBySiteId[$siteId]] = array();
                                }

                                $allAliasesByDomain[$domainsBySiteId[$siteId]][] = $aliasInfo['info']['ascii-name'];
                            }
                        }
                    }
                }
            }

            foreach ($domains as $domain) {
                $this->_logger->debug("Adding {$domain} as ACCOUNT domain");

                // Append domain as normal domain to the list
                $c = count($result) + 1;

                $result[$c]['domain'] = $domain;
                $result[$c]['type']   = 'account';
                $result[$c]['extra']  = false;

                if ($doAliasesExtraction) {
                    $this->_logger->debug("Checking for aliases for '{$domain}' ");
                    $aliases = !empty($allAliasesByDomain[$domain]) ? $allAliasesByDomain[$domain] : array();

                    if (isset($aliases) && (count($aliases) > 0)) {
                        $this->_logger->debug("Domain '{$domain}' has aliases ");
                        // One or more aliases found for $domain
                        foreach ($aliases as $alias) {
                            $this->_logger->debug("Adding {$alias} as ALIAS domain for {$domain}");
                            $c                          = count($result) + 1;
                            $result[$c]['domain']       = $alias;
                            $result[$c]['type']         = 'alias';
                            $result[$c]['owner_domain'] = $domain;
                            $result[$c]['extra']        = true;
                            $result[$c]['user']         = null; // @todo: Do we really need this?
                        }
                    } else {
                        $this->_logger->debug("Domain '{$domain}' does not have aliases");
                    }
                }
            }
        }
        if(isset($order)){
            if ('asc' == $order) {
                ksort($result);
            } else {
                krsort($result);
            }
        }
        if (isset($order)) {
            if ('asc' == $order) {
                ksort($result);
            } else {
                krsort($result);
            }
        }

        return $result;
    }

    /**
     * @see https://trac.spamexperts.com/ticket/24283
     * 
     * @param boolean $useAdminWrapper - set true if password should be decrypted.
     * @return string administrator password - returns false on failure
     */
    public function getAdminPassword($useAdminWrapper = false)
    {
        if (isset($this->_adminPassword) && (!empty($this->_adminPassword))) {
            Zend_Registry::get('logger')->debug("Returning admin password from class var");

            return $this->_adminPassword;
        }

        $index = 'storage_adminpass';
        if (Zend_Registry::isRegistered($index) && $useAdminWrapper) {
            $admin_pass = Zend_Registry::get($index);
            $this->_logger->debug("Returning admin password from registry.");

            return $admin_pass;
        }
        
        // Way to gather admin pass on Windows OS
        if(SpamFilter_Core::isWindows()){
           $output = exec ('"'. PLESK_DIR . 'admin' . DS . 'bin' . DS . 'plesksrvclient.exe" -get -nogui | more');
           $x = explode(': ', $output);
           $pass = trim($x[1]);
           Zend_Registry::set($index, $pass);

           return $pass;
        }
        
        // New approach required since Plesk 10.2 (@TODO: Test with Plesk 9.5)
        if($useAdminWrapper){
            $pass = shell_exec('/usr/local/psa/bin/admin --show-password');
            if ($pass !== false && (!empty($pass))) {
                Zend_Registry::get('logger')->debug("Returning admin password from admin binary retrieval");
                $thePassword = trim($pass);
                Zend_Registry::set($index, $thePassword);

                return $thePassword;
            }
        }
        
        if (file_exists('/etc/psa/.psa.shadow') && is_readable('/etc/psa/.psa.shadow')) {
            Zend_Registry::get('logger')->debug("Returning admin password");

            return trim(file_get_contents('/etc/psa/.psa.shadow'));
        }

        return false;
    }

    /**
     * validateOwnership
     * Check whether we are allowed to operate on this domain
     *
    @TODO	This function is a candidate for being moved to central location
     *
     * @static
     *
     * @param string $domain Domain to check
     *
     * @return bool true/false
     * @access public
     * @see    SpamFilter_Core::getUsername()
     */
    public function validateOwnership($domain)
    {
        //check if this is a domain or an alias
        //in case of alias, check ownership for main domain

        $aapi = new Plesk_Driver_Aliases();
        $alias = $aapi->getAliasbyName($domain);
        if (!empty($alias)) {
            $this->_logger->debug("Retrieveing main domain for alias '{$domain}'.");

            $dapi = new Plesk_Driver_Domain();
            $domain = $dapi->getDomainbyId(array_keys($alias)[0]);

            if (empty($domain)) {
                $this->_logger->debug("Couldn't retrieve main domain for alias '{$domain}'.");

                return false;
            } else {
                $domain = $domain[0];
            }
        }

        //first check
        $domainId = $this->getDomainID($domain);

        $user = SpamFilter_Core::getUsername();
        $this->_logger->debug("Checking access to '{$domain}' for '{$user}'");
        if (empty($user)) {
            $this->_logger->debug("Rejecting domain access to unset user '{$user}'");

            return false;
        }

        if (in_array(strtolower($user), array('admin', 'root'))) {
            $this->_logger->debug("Granting domain access to '{$user}', as this is the main admin.");

            return true;
        }
        
        // validate reseller 
        $reseller = pm_Session::getClient();
        $clientName = mb_strtolower($this->getDomainUser($domain), 'UTF-8');

        if ($reseller->isReseller()) {

            if (class_exists('pm_Client')) {
                $client = pm_Client::getByLogin($clientName);
                $clientID = $client->getId();
                if( !empty($clientID) && ($clientID == $reseller->getId()) ){
                    return true;
                }
            }
            $capi = new Plesk_Driver_Client();
            $data = $capi->getclientbyUser($clientName);
            if (isset($data['client']['get']['result']['data']['gen_info']['owner-id']) && $reseller->getId() == $data['client']['get']['result']['data']['gen_info']['owner-id']){
                return true;
            }
        }
        
        // Check owner
        $isValidUser = mb_strtolower($this->getDomainUser($domain), 'UTF-8') == mb_strtolower($user, 'UTF-8');

        return $isValidUser;
    }

    /**
     * Retrieve the username associated with the domainname
     *
     * @param string $domain Domain to check
     *
     * @return string Username
     * @access public
     */
    public function getDomainUser($domain)
    {
        $this->_logger->debug("getDomainUser is being called for '{$domain}'");

        $dapi = new Plesk_Driver_Domain();
        $data = $dapi->getDomainByDomain($domain, true);
        if (isset($data) && isset ($data['webspace']['get']['result']['data'])) {
            // Sometimes API returns owner-login at first request, so we want to return it
            if(isset($data['webspace']['get']['result']['data']['gen_info']['owner-login'])){
                $this->_logger->debug("Username gathered from owner-login. Returning it...");
                return $data['webspace']['get']['result']['data']['gen_info']['owner-login'];
            } 
            // If there's no login, we need to get owner-id then we get user login using it
            $userid = $data['webspace']['get']['result']['data']['gen_info']['owner-id'];
            
        } else if(isset($data['site']['get']['result']['data'])){
            $userid = $dapi->getOwnerIdByDomain($domain);
        }
        
        if (!empty($userid)){
            $capi = new Plesk_Driver_Client();
            $cdata = $capi->getClientByID($userid);
            if(isset($cdata['client']['get']['result']['data']['gen_info']['login'])){
                $user = $cdata['client']['get']['result']['data']['gen_info']['login'];
            } else { // maybe it is reseller?
                $rapi = new Plesk_Driver_Reseller();
                $rdata = $rapi->getResellerbyId($userid);
                if(isset($rdata['reseller']['get']['result']['data']['gen-info']['login'])){
                    $user = $rdata['reseller']['get']['result']['data']['gen-info']['login'];
                }
            }
            
            // Sometimes api returns error code 1013 (User do not exists). If API call don't return username, and reseller name we should try another way.              
            if(empty($user)){
                $cData = pm_Client::getByClientId($userid);
                $user = $cData->getProperty('login');
            }

            // The username is apparnatly set :-)
            $this->_logger->debug("Username is set, so returning it.");
            return $user;
        }

        $this->_logger->debug("Username is not set / is unknown.");

        return false;
    }

    /**
     * Retrieve all unique domains
     *
     * @TODO     This function is a candidate for being moved to central location
     *
     * @param array $domains
     *
     * @return array of unique domains f
     *
     * @access   public
     */
    public function getUniqueDomains($domains)
    {
        // Return a unique array, since the OWNER= can also contain USER=
        if (is_array($domains)) {
            $unique = $domain_names = array();
            foreach ($domains as $domain) {
                if (!empty($domain['domain']) && !in_array($domain['domain'], $domain_names)) {
                    $unique[]       = $domain;
                    $domain_names[] = $domain['domain'];
                }
            }

            return $unique;
        }

        return false;
    }

    public function createBulkProtectResponse($domain, $reason, $reasonStatus = "error", $rawResult)
    {
        return array(
            "domain" => $domain,
            "counts" => array(
                "ok"      => 0,
                "failed"  => 0,
                "normal"  => 0,
                "parked"  => 0,
                "addon"   => 0,
                "subdomain"   => 0,
                "skipped" => 1,
                "updated" => 0,
            ),
            "reason" => $reason,
            "reason_status" => $reasonStatus,
            'rawresult' => $rawResult,
            "time_start" => $_SERVER['REQUEST_TIME'],
            "time_execute" => time() - $_SERVER['REQUEST_TIME'],
        );
    }

    /**
     * Protect all domains on the server according to the configuration
     *
     * @TODO    This function is a candidate for being moved to central location
     *
     * @return array containing result data
     *
     * @param array $params
     *
     * @access  public
     * @see     getLocalAccounts()
     * @see     IsRemoteDomain()
     * @see     _setupProgressBar()
     * @see     SpamFilter_Panel_Account
     * @see     SpamFilter_Hooks
     * @see     SpamFilter_Panel_ProtectWhm // REPLACE ME
     * @see     getParkedDomains()
     * @see     getAddonDomains()
     */
    public function bulkProtect($params)
    {
        //set current user
        $params['user']     = $this->_config->apiuser;
        $params['password'] = $this->_config->apipass;

        $domain  = $params['domain'];
        $hook    = new SpamFilter_Hooks;
        $protect = new SpamFilter_Panel_ProtectPlesk($hook, $this->_api);
        $protect->setDomain($domain);
        $accountInstance
            = new SpamFilter_Panel_Account($params, !$this->_config->handle_only_localdomains, $this->_api);
        $protect->setAccount($accountInstance);

        switch ($params['type']) {
            case 'account':
            case 'domain':
                $protect->domainProtectHandler($this->getDestination($domain, 'normal domain'));
                break;

            case 'parked':
                $protect->parkedDomainProtectHandler($domain, $this->getDestination($domain, 'parked alias'));
                break;

            case 'addon':
            case 'alias':
                $protect->addonDomainProtectHandler($domain, $this->getDestination($domain, 'parked addon'));
                break;

            default:
                break;
        }

        return $protect->getResult();
    }

    public function getDomainID($domain)
    {       
        /**
         * Decode IDN domain
         * @see https://trac.spamexperts.com/ticket/16755
         */
        $domain_id = false;
        $idn = new IDNA_Convert;
        if (0 === stripos($domain, 'xn--')) {
            $domain = $idn->decode($domain);
        }

        $this->_logger->debug("Requesting domainid for '{$domain}'..");
        $index = 'storage_domainid_' . $domain;
        if (Zend_Registry::isRegistered($index)) {
            $domain_id = Zend_Registry::get($index);
            $this->_logger->debug("Returning domainid for '{$domain}' ({$domain_id}) from registry.");

            return $domain_id;
        }
        
        $dapi = new Plesk_Driver_Domain();   
        $this->_logger->debug("Requesting domainid for '{$domain}'.. via Plesk API");
        $data = $dapi->getDomainByDomain($domain);
        // try to get it faster
        if(!empty($data['webspace']['get']['result']['id'])){
            return $data['webspace']['get']['result']['id'];
        } else {
            // no domain found.
            $data= array();
        }
        
        if (is_array($data)) {
            foreach ($data as $did => $eachDomainFoundPunycoded) {
                if (in_array($domain, array($eachDomainFoundPunycoded, $idn->decode($eachDomainFoundPunycoded)))) {
                    $domain_id = (int) $did;
                    if (!empty($domain_id)) {
                        // The domain id is apparantly set :-)
                        $this->_logger->debug("Domain ID is set ({$domain_id}), so returning it (Real domain).");
                        Zend_Registry::set($index, $domain_id);

                        return $domain_id;
                    }
                }
            }
        }

        /**
         * For Plesk API v1.6.3.2+ we should try to check sites
         */
        if (version_compare($this->getVersion(), '10.2', '>=')) {
            $pleskAPI = new Plesk_Driver;
            $apiResponseXML = $pleskAPI->doRequest(array(
                 'site' => array(
                     'get' => array(
                         'filter'  => array(
                             'name' => $domain,
                         ),
                         'dataset' => array(
                             'hosting' => '',
                         ),
                     ),
                 ),
            ), Plesk_Driver_Domain_Extractor_V10::PROTOCOL_VERSION, true);

            try {
                $xmlResponse = new SimpleXMLElement($apiResponseXML);
                if (isset($xmlResponse->site->get->result->status) &&
                    'ok' == strtolower((string) $xmlResponse->site->get->result->status) &&
                    isset($xmlResponse->site->get->result->id)) {
                    $this->_logger->debug("Domain ID is set ({$xmlResponse->site->get->result->id}), so returning it.");
                    return (int) (string) $xmlResponse->site->get->result->id;
                }
            } catch (Exception $e) {
                $this->_logger->debug("Exception caught durin gathering domain ID for domain '$domain' ". $e->getMessage());
            }
        }

        // maybe its an alias?
        $dapi = new Plesk_Driver_Aliases();
        $data = $dapi->getAliasbyName($domain);

        if (is_array($data)) {
            foreach ($data as $did => $eachDomainFoundPunycoded) {
                if (in_array($domain, array($eachDomainFoundPunycoded, $idn->decode($eachDomainFoundPunycoded)))) {
                    $domain_id = (int) $did;
                    if (!empty($domain_id)) {
                        // The domain id is apparantly set :-)
                        $this->_logger->debug("Domain ID is set ({$domain_id}), so returning it (Alias domain).");
                        Zend_Registry::set($index, $domain_id);

                        return $domain_id;
                    }
                }
            }
        }

        $this->_logger->debug("Domain id for '{$domain}' is not set / is unknown.");

        return false;
    }


    /**
     * Retrieve MX record content for a given domain
     *
     * @param string $domain Domain to retrieve MX content from
     *
     * @return array of MX record content
     *
     * @access private
     */
    public function GetMXRecordContent($domain)
    {
        $this->_logger->debug("Get MX record contents for '{$domain}'");
        $records = array();

        $domain_id = $this->getDomainID($domain);
        if (false !== $domain_id) {
            $dnsapi = new Plesk_Driver_Dns();
            $zone   = $dnsapi->getZoneForDomainId($domain_id);
            if (isset($zone)) {
                $this->_logger->debug("Zone is set for '{$domain}'.");
                if (is_array($zone) && isset($zone['dns']['get_rec']['result'])) {
                    foreach ($zone['dns']['get_rec']['result'] as $record) {
                        $this->_logger->debug("The zone's record: " . print_r($record, true));

                        if (isset($record['data']['type']) && $record['data']['type'] == "MX") {
                            $records[] = trim($record['data']['value'], '.');
                        }
                    }

                    //Return data
                    $this->_logger->debug("Returning MX records for '{$domain}': " . serialize($records));

                    return $records;
                }
                $this->_logger->err("Unable to obtain zone data for '{$domain}'");

                return false;
            }
        }

        $this->_logger->err("Unable to obtain get MX records due to missing Domain ID for '{$domain}'");

        return false;
    }

    /**
     * Retrieves the destination for the provided domain
     *
     * @param string $domain Domain to lookup destination for
     * @param string $source Source used in logging.
     *
     * @TODO    This function is a candidate for being moved to central location
     *
     * @return string Value of destination host
     *
     * @access  public
     * @see     getMXRecordContent()
     */
    public function getDestination($domain, $source = '')
    {
        if (!empty($source)) {
            $this->_logger->info("GetDestination: Domaintype = '{$source}'");
        }

        if (empty($domain)) {
            $this->_logger->debug("GetDestination: Empty domain provided to lookup.");

            return null;
        }
        $this->_logger->info("Requesting current set destinations for '{$domain}'");

        $config = Zend_Registry::get('general_config');

        if ($config->use_existing_mx) {
            $this->_logger->debug("Requested to use existing MX records from '{$domain}' as route. Retrieving them");
            // Users want to use existing MX records as destinations (routes). Retrieve them!
            $mxr = $this->GetMXRecordContent($domain);
            if ((empty($mxr)) || ($mxr === false)) {
                $this->_logger->debug("Destination retrieval for domain '{$domain}' has FAILED.");
                $destination = null;

                return $destination;
            }
            $this->_logger->debug("Current MX records for '{$domain}' have been retrieved.");

            // Build me an array with MX records!
            $my_rr[] = $config->mx1;

            if (!empty($config->mx2)) {
                $my_rr[] = $config->mx2;
            }

            if (!empty($config->mx3)) {
                $my_rr[] = $config->mx3;
            }

            if (!empty($config->mx4)) {
                $my_rr[] = $config->mx4;
            }

            $myRRCount = count($my_rr);
            $foundRR   = 0;

            // Check if they aren't already pointing to the filter cluster.
            foreach ($mxr as $r) {
                // $r = record
                $this->_logger->debug("MX record value for '{$domain}': '{$r}'");
                if (in_array($r, $my_rr)) {
                    $foundRR++;
                    // Record $r exists in array $my_rr
                }
            }

            $c1 = count($mxr);
            if ($c1 > $myRRCount) {
                $this->_logger->debug("More records found ({$c1}) than configured ({$myRRCount}).");
            }

            if ($foundRR == $myRRCount) {
                // We have found the same records as stated in "my records".
                $this->_logger->debug(
                    "Current set MX records for '{$domain}' seems to be pointing to the filtering cluster already. Falling back. ({$foundRR} vs {$myRRCount})"
                );
                $destination = null;

                return $destination;
            } else {
                $this->_logger->debug(
                    "I found {$foundRR} current records but the ones to set are {$myRRCount}, falling back to server hostname to deliver email on."
                );
            }

            $this->_logger->debug("Merging current MX records for '{$domain}'");
            $destination = implode(',', $mxr); // Glue them with a ,
        } else {
            $this->_logger->debug("GetDestination: Default destination (this server)");
            // Use the default destination (aka: server hostname)
            $destination = null;
        }
        $this->_logger->debug("Returning destination: '{$destination}' for domain '{$domain}'");

        return $destination;
    }

    /**
     * isInFilter
     * Check whether the domain added to the filter.
     *
     * @TODO    This function is a candidate for being moved to central location
     *
     * @param string $domain Domain to check
     *
     * @return bool true/false
     * @access  public
     */
    public function isInFilter($domain)
    {
        if (SpamFilter_Domain::exists($domain)) {
            $this->_logger->debug("[isInFilter] The domain '{$domain}' is in the filter.");

            return true;
        }

        $this->_logger->debug("[isInFilter] The domain '{$domain}' is NOT in the filter, or request failed.");

        return false;
    }

    /**
     * Retrieve all server IP's
     *
     * @return array of IP addresses
     *
     * @access public
     */
    public function getIpAddresses()
    {
        // IP handling is done directly at the API Driver
        $pleskipapi = new Plesk_Driver_Ip();

        return $pleskipapi->getServerIps();
    }

    /**
     * Retrieve email address linked to domain
     *
     * @param $data array with domain value
     *
     * @return string|bool Domain email address or 'false' if not set.
     *
     * @access public
     */
    public function getDomainContact($data)
    {
        $domain = $data['domain'];

        $this->_logger->debug("Requesting domain contact for '{$domain}'");
        
        // We need to obtain the CLIENT based on the domain, and work from there on.
        $dapi = new Plesk_Driver_Domain();
        $owner_id = $dapi->getOwnerIdByDomain($domain);

        if (!empty($owner_id)) {
            // Get owner based on owner_id
            $capi = new Plesk_Driver_Client();
            $email = $capi->getEmailByClientId($owner_id);  
            if(empty($email)){                         
                $rapi = new Plesk_Driver_Reseller();
                $email = $rapi->getEmailByResellerId($owner_id);
            }
            if(empty($email) && $owner_id == 1){                         
                $sapi = new Plesk_Driver_Server();
                $email = $sapi->getAdminEmail();
            }
            if (isset($email) && (!empty($email))) { 
                return $email;
            }
        }

        $this->_logger->debug("Email is not set / is unknown.");

        return false;
    }


    ///////////////////////////////////////
    /**
     * Remove all MX records for the given domain
     *
     * @param string $domain domain to remove MX records for
     *
     * @return bool Status
     *
     * @access private
     * @see    GetMXRecordContent()
     */
    private function removeMXRecords($domain)
    {
        $this->_logger->debug("Removing MX records for '{$domain}'");
        $domain_id = $this->getDomainID($domain);
        if ($domain_id !== false) {
            $this->_logger->debug("Removing all MX records from '{$domain}' ({$domain_id}) ");
            $pdns = new Plesk_Driver_Dns();

            $zone = $pdns->getZoneForDomainId($domain_id);
            if (!empty($zone)) {
                if (is_array($zone) && isset($zone['dns']['get_rec']['result'])) {
                    foreach ($zone['dns']['get_rec']['result'] as $record) {
                        if (isset($record['data']['type']) && $record['data']['type'] == "MX") {
                            $record_value = $record['data']['value'];
                            $record_id    = $record['id'];

                            $this->_logger->debug("Removing MX '{$record_value}' ({$record_id})..");
                            $status = $pdns->deleteRecord($record_id);
                            $this->_logger->debug("Removing all MX resulted in: " . serialize($status));
                        }
                    }
                    $this->_logger->debug("MX removal completed");
                    return true;
                    
                } else {
                    $this->_logger->err("No Zone retrieved for {$domain}");
                }
            }
        } else {
            $this->_logger->err("No domain ID retrieved for {$domain}");
        }

        $this->_logger->debug("MX removal could not complete, domain ID is not set");

        return false;
    }


    //v2
    public function removeForeignMXRecords($domain)
    {
        $this->_logger->debug("Removing foreign MX records for '{$domain}'");

        $domain_id = $this->getDomainID($domain);
        if ($domain_id !== false) {
            $this->_logger->debug("Removing all MX records from '{$domain} ({$domain_id}) ");
            $pdns = new Plesk_Driver_Dns();

            $zone = $pdns->getZoneForDomainId($domain_id);
            if (isset($zone)) {
                if (is_array($zone) && isset($zone['dns']['get_rec']['result'])) {
                    $to_set_records = array();
                    if (!empty($this->_config->mx1)) {
                        $to_set_records['10'] = (string) $this->_config->mx1 . ".";
                    }
                    if (!empty($this->_config->mx2)) {
                        $to_set_records['20'] = (string) $this->_config->mx2 . ".";
                    }
                    if (!empty($this->_config->mx3)) {
                        $to_set_records['30'] = (string) $this->_config->mx3 . ".";
                    }
                    if (!empty($this->_config->mx4)) {
                        $to_set_records['40'] = (string) $this->_config->mx4 . ".";
                    }

                    foreach ($zone['dns']['get_rec']['result'] as $record) {
                        if (isset($record['data']['type']) && $record['data']['type'] == "MX") {
                            $this->_logger->debug("Foreign MX removal eval:" . serialize($record));

                            $record_value = $record['data']['value'];
                            $record_id    = $record['id'];

                            if (!in_array($record_value, $to_set_records)) {
                                $this->_logger->debug("Removing MX '{$record_value}' ({$record_id})..");
                                $status = $pdns->deleteRecord($record_id);
                                $this->_logger->debug("Removing MX resulted in: " . serialize($status));
                            } else {
                                $this->_logger->debug(
                                    "Leaving MX record {$record_id} (value: {$record_value}) untouched."
                                );
                            }
                        }
                    }
                    $this->_logger->debug("Foreign MX removal completed");

                    return true;
                }
            } else {
                $this->_logger->err("No Zone retrieved for {$domain}");
            }
        } else {
            $this->_logger->err("No domain ID retrieved for {$domain}");
        }

        $this->_logger->debug("Foreign MX removal could not complete, domain ID is not set");

        return false;
    }


    /**
     * AddMXRecord
     * Create MX record
     *
     * @param string $domain   Domain to create record for
     * @param string $priority Which priority to add a record for
     * @param string $server   Which server to set as MX record destination.
     *
     * @return bool Status
     *
     * @access public
     */
    public function addMxRecord($domain, $priority, $server)
    {
        $domain_id = $this->getDomainID($domain);
        if ($domain_id !== false) {
            $this->_logger->debug("Adding MX record to '{$domain} ({$domain_id}): {$server}..");
            $pdns   = new Plesk_Driver_Dns();
            $status = $pdns->addMxRecord(
                $domain_id,
                $server,
                (int) $priority
            );
            if (isset($status) && isset($status['dns']['add_rec']['result']['status'])
                && ($status['dns']['add_rec']['result']['status'] == 'ok')
            ) {
                $this->_logger->debug("Adding MX record ({$server}) has been completed succesfully.");

                return true;
            }
        }
        $this->_logger->err("Adding MX record ({$server}) has NOT been completed succesfully.");

        return false;
    }

    /**
     * Setup the DNS for the provided domain
     *
     * @param $data array containing data to use
     *
     * @return bool Status
     *
     * @access public
     * @see    removeMXRecords()
     */
    public function SetupDNS($data)
    {
        $domain = $data['domain'];
        if (!isset($domain)) {
            $this->_logger->err("Request to provision DNS but no domain supplied");

            return false;
        }
        $this->_logger->debug("DNS setup requested for '{$domain}'");

        $records = $data['records'];
        if (!isset($records)) {
            $this->_logger->err("Request to provision DNS for '{$domain}' but no records supplied");

            return false;
        }

        // Remove all current MX records
        if (!$this->removeMXRecords($domain)) {
            $this->_logger->err("Removal of existing MX records for '{$domain}' has failed");
            return false;
        } else {
            $this->_logger->debug("Removal of existing MX records for '{$domain}' has completed succesfully.");
            $this->_logger->debug("Prepare for inserting PSF MX records for: '{$domain}'");
            // Setup DNS for $domain using array $records (key = priority);
            foreach ($records as $prio => $value) {
                if (!empty($value)) {
                    $this->addMxRecord($domain, $prio, $value);
                } else {
                    $this->_logger->debug("Skipping one DNS record because of empty value.");
                }
            }
        }

        return true;
    }

    /**
     * Collection domains getter
     *
     *
     * @param bool $informer
     *
     * @return array of domais
     *
     * @access public
     */
    public function getCollectionDomains($informer = false)
    {
        $collectionDomains = SpamFilter_Panel_Cache::get('collectiondomains');

        if ($informer) {

            /** @var $sessionManager stdClass */
            $sessionManager                            = new SpamFilter_Session_Namespace;
            $sessionManager->bulkprotectinformer       = 'Getting list of domains...';
            $sessionManager->bulkprotectinformerstatus = 'run';
        }

        if (!$collectionDomains) {

            // Get all domains
            $local_accounts = $this->getDomains(array());

            if (is_array($local_accounts) && !empty($local_accounts)) {
                foreach ($local_accounts as $account) {
                    $accountInstance = new SpamFilter_Panel_Account($account,
                        !$this->_config->handle_only_localdomains, $this->_api);
                    $user            = $accountInstance->getUser();

                    if (empty($account['type'])) {
                        $account['type'] = 'account';
                    }

                    switch (strtolower($account['type'])) {
                        case 'alias':
                            $collectionDomains[] = array(
                                'name'         => $accountInstance->getDomain(),
                                'type'         => 'addon',
                                'owner_domain' => $account['owner_domain'],
                                'user'         => $user,
                            );

                            break;

                        default:
                            $collectionDomains[] = array(
                                'name' => $accountInstance->getDomain(),
                                'type' => 'domain',
                                'user' => $user,
                            );

                            break;
                    }
                }

                $this->_logger->info("Collection has completed.");
            } else {
                $this->_logger->info("Nothing to protect.");

                if ($informer && isset($sessionManager)) {
                    $sessionManager->bulkprotectinformer = 'Nothing to protect.';
                }
            }

            if (!$collectionDomains) {
                $collectionDomains = array();
            }

            // Filter duplicates from the result
            // @see https://trac.spamexperts.com/software/ticket/14697
            $collectionDomains = self::multidimArrayUnique($collectionDomains, 'name');

            // Refresh domains cache
            SpamFilter_Panel_Cache::set('collectiondomains', $collectionDomains);
        }

        if ($informer && isset($sessionManager)) {
            $sessionManager->bulkprotectinformerstatus = 'protecting';
            if (0 < count($collectionDomains)) {
                $sessionManager->bulkprotectinformer
                    = 'List of domains has completed. Starting the process of protecting...';
            }
        }

        return $collectionDomains;
    }

    /**
     * Method for array uniqualization. Can be applied for multidimensional
     * arrays only. Does comparison based on string representation on entry
     *
     * @static
     * @access public
     *
     * @param array  $array
     * @param string $uniqueField
     *
     * @return array
     */
    static final public function multidimArrayUnique(array $array, $uniqueField)
    {
        $addedValues = $result = array();

        foreach ($array as $entry) {
            if (!empty($entry[$uniqueField]) && empty($addedValues[$entry[$uniqueField]])) {
                $addedValues[$entry[$uniqueField]] = 1;
                $result[]                          = $entry;
            }
        }

        return $result;
    }

    /**
     * Migrate all domains to a diffent user
     *
     * @param $params Array of parameters
     *
     * @return bool status
     *
     * @access public
     */
    public function migrateDomainsTo($params)
    {
        if (!is_array($params)) {
            $this->_logger->err("Unable to migrate due to incorrect request.");

            return false;
        }

        if (!isset($params['username'])) {
            $this->_logger->err("Unable to migrate due to missing username.");

            return false;
        }

        if (!isset($params['password'])) {
            $this->_logger->err("Unable to migrate due to missing password.");

            return false;
        }

        $username = $params['username'];
        $password = $params['password'];
        $this->_logger->info("Going to migrate all domains to {$username}.");

        // Retrieve all domains
        $collectionDomains = $this->getCollectionDomains();
        $domains           = array();
        foreach ($collectionDomains as $collectionDomain) {
            $domains[] = $collectionDomain['name'];
        }

        if (count($domains) > 0) {
            $this->_logger->info("Starting migration.");
            $hook       = new SpamFilter_Hooks;
            $freelimit  = $hook->GetFreeLimit($username, $password);
            $is_success = false;
            if ('unlimit' == $freelimit || $freelimit - count($domains) >= 0) {
                $limitdomain = null;
                $notmoved    = array();
                $moved       = array();
                $rejected    = array();
                while (!empty($domains)) {
                    $domain_chunk = array_splice($domains, 0, 25);
                    $response     = $hook->MigrateDomain($domain_chunk, $username, $password);
                    $limitdomain  = (!empty($response['result']['limit']) && is_null($limitdomain))
                        ? $response['result']['limit'] : 0;
                    if (!empty($response['result']['notmoved'])) {
                        $notmoved = array_merge($notmoved, (array) $response['result']['notmoved']);
                    }
                    if (!empty($response['result']['rejected'])) {
                        $rejected = array_merge($rejected, (array) $response['result']['rejected']);
                    }
                    if (!empty($response['result']['moved'])) {
                        $moved = array_merge($moved, (array) $response['result']['moved']);
                    }
                }
                $this->_logger->info("Migration completed.");
                $messages = array();
                if (count($notmoved) > 0 || count($rejected) > 0) {
                    if (count($notmoved) > 0) {
                        $messages[] = array('message' => 'Domains have not been migrated: ' . join(', ', $notmoved),
                                            'status'  => 'error');
                    }
                    if (count($rejected) > 0) {
                        $messages[] = array('message'   =>
                                            "You don't own some of the listed domains so they aren't moved: " . join(
                                                ', ', $rejected
                                            ), 'status' => 'error');
                    }
                    if (count($moved) > 0) {
                        $messages[] = array('message' => "Domains have been migrated: " . join(', ', $moved),
                                            'status'  => 'success');
                        $is_success = true;
                    }
                } else {
                    $messages[] = array('message' => 'Domains have been migrated to new user.', 'status' => 'success');
                    $is_success = true;
                }

                return array('is_success' => $is_success, 'messages' => $messages);
            } else {
                $errMsg = "Migration failed. The new user's domains limit is lower than required for the migration.";
                $this->_logger->err($errMsg);
                $messages[] = array ('message' => $errMsg, 'status' => 'error');

                return array ('is_success' => $is_success, 'messages' => $messages);
            }
        }
        $this->_logger->err("Migration failed.");

        return false;
    }

    /**
     * Check domain is remote
     *
     * @param string $domain
     *
     * @return bool
     */
    public function IsRemoteDomain($domain)
    {
        if (is_array($domain)) {
            $domain = $domain['domain'];
        }

        return ! $this->isLocalDomain($domain);
    }

    /**
     * Check if domain is a local domain.
     * Get the Plesk server IPs and resolve MX hostnames to IP addresses
     * and if the IP address of MX hostnames match the panel’s IP addresses
     * then it's safe to consider that the domain is local
     *
     * @param string $domain
     *
     * @return bool
     */
    private function isLocalDomain($domain)
    {
        $serverIps = $this->ipDriver->getServerIps();
        $mxRecords = $this->GetMXRecordContent($domain);

        if (! $mxRecords) {
            return false;
        }

        foreach ($mxRecords as $mxRecord) {
            $ip = gethostbyname($mxRecord);

            if (! in_array($ip, $serverIps)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve all domains for given username
     *
     * @param $data Array containing username entry
     *
     * @return array of domains for the provided user
     *
     * @access public
     * @see    listaccts()
     */
    public function getUsersDomains($data)
    {
        $username = $data['username'];
        $username = trim($username);
        $this->_logger->debug("Show all domains of reseller '{$username}'");

        $domains_user  = $this->getDomains(array('username' => $username, 'level' => 'user'));
        $domains_owner = $this->getDomains(array('username' => $username, 'level' => 'owner'));
        $localDomains  = array_merge($domains_user, $domains_owner);

        // Return a unique array, since the OWNER= can also contain USER=
        $unique = $this->getUniqueDomains($localDomains);

        $c = count($unique);
        $this->_logger->debug("Returning {$c} domains");

        return $unique;
    }

    /**
     * Sets the configured brand in the panel
     *
     * @access public
     *
     * @param array $aBrand Array of brand data
     *
     * @return bool Status
     */
    public function setBrand($aBrand)
    {
        // Inject ourselves into the custom buttons
        $pass = $this->getAdminPassword();

        // Install the Custom Buttons
        $port = $this->getMySQLPort();
        $mysql_connection = mysql_connect('localhost:' . $port, 'admin', $pass);

        if (!empty($mysql_connection)) {
            if (!(@mysql_select_db('psa', $mysql_connection))) {
                die('ERROR SELECTING DB');
            }
        } else {
            die('ERROR CONNECTING TO MYSQL');
        }

        mysql_query("UPDATE `psa`.`custom_buttons` SET `text` = '" . mysql_real_escape_string($aBrand['brandname'])
            . "' WHERE `url` LIKE '%prospamfilter%'");
        $icon_path = (SpamFilter_Core::isWindows()) ? PLESK_DIR . 'admin' . DS . 'htdocs' . DS . 'images' . DS . 'custom_buttons' . DS . 'prospamfilter.png' : SpamFilter_Brand::ICON_PATH_PLESK;

        return ((!empty($aBrand['brandicon']))
            ? (0 < file_put_contents($icon_path, base64_decode($aBrand['brandicon'])))
            : true);
    }
   
     /**
     * Gets mysql connection port
     *
     * @access public
     *
     * @return string port
     */
    public function getMySQLPort(){
        if(file_exists(PLESK_DIR . 'MySQL' . DS . 'Data' . DS . 'my.ini')){
            $handle = fopen(PLESK_DIR . 'MySQL' . DS . 'Data' . DS . 'my.ini', "r");
            while (!feof($handle)){
                $line = fgets($handle);
                if(strpos($line,'port=') !== false){
                    $x = explode('=',$line);
                    $port = trim($x[1]);
                    return $port;
                }
                
            }
        }
        // empty string if port is not found
        return '';       
    }    
    
     /**
     * Returns domains matched with filter
     *
     * @param $params [domains array, filter string]
     *
     * @return array
     *
     * @access public
     */        
    public function filterDomains($params){
            $filter = $params['filter'];
            $filteredDomains = array();
            foreach($params['domains'] as $domain){
                if(strpos($domain['domain'], $filter) !== FALSE){
                    $filteredDomains[] = $domain;
                }
            }
            return $filteredDomains;
    }
    
    /**
     *  @TODO - make this functionality possible for Plesk
     * 
     * @return boolean - for Plesk it should return true. It is implemented only for cPanel atm.
     */
    public function isEnabledForResellers(){
        return true;
    }

    /**
     * Load config
     *
     * @throws Zend_Controller_Plugin_Exception
     * @throws Zend_Exception
     */
    private function loadConfig()
    {
        // load & temp store configuration
        $this->_config = (Zend_Registry::isRegistered('general_config') ? Zend_Registry::get('general_config') : null);
    }
}
