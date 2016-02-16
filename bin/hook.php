#!/usr/local/bin/prospamfilter_php -nq
<?php
/*
*************************************************************************
*                                                                       *
* ProSpamFilter                                                         *
* Bridge between Webhosting panels & SpamExperts filtering				*
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
* @since     2.0
*/
 
require_once dirname(__FILE__) . '/../application/bootstrap.php';

$paneltype = SpamFilter_Core::getPanelType();
$domain = $email = $newdomain = $user = '';
$domains = array();
$hook = new SpamFilter_Hooks;

if (!Zend_Registry::isRegistered('general_config')) {
    // Initialize the config if this is not set.
    $configuration = new SpamFilter_Configuration(CFG_FILE);
}
$config = Zend_Registry::get('general_config');

$data = argv2array( $argv );
$action = (isset($data['action']) ? $data['action'] : '');

// Get the domain to execute actions on.
$domain = $data['domain'];

switch($action)
{
    case "editdomain":
        $newdomain = $domain;
    break;

    case "addaddondomain";
    case "deladdondomain":
        $alias = $data['alias'];
    break;
}

//
// EXECUTE
//
$response = '';
switch( $action )
{
	case "adddomain":
        if (isset($domain)) {

            // Creation of domain
            if ((isset($mxtype) && ($mxtype <> "local")) && ($config->handle_only_localdomains)) {
                $response .= "\nNOT Adding '{$domain}' to the Antispam filter because the Mail Routing Settings have been set to '{$mxtype}' and remotedomain skipping is enabled";
            } else {
                if ($config->auto_add_domain) {
                    $response .= "\nAdding '{$domain}' to the Antispam filter...";
                    $status = $hook->AddDomain($domain);
                    if (!empty($status['reason'])) {
                        $response .= " {$status['reason']} ";
                    }
                } else {
                    $response .= "\nNOT Adding '{$domain}' to the Antispam filter, because adding domains is disabled in the settings.";
                }
            }
        }

        break;

	case "deldomain":
		// Deletion of domain
		if( $config->auto_del_domain )
		{
            $response .= "\n Preparing to delete from the Antispam filter";

            foreach ($domains as $domain) {
                $response .= "\nDeleting '{$domain}' from the Antispam filter...";
                $hook->DelDomain( $domain );
            }

            $status = array('status' => true);
		} else {
			$response .= "\nNOT deleting '{$domain}' from the Antispam filter, because removing domains is disabled in the settings.";
		}
		break;

    case "park":
    case "addaddondomain":
        if (empty($alias)) {
            Zend_Registry::get('logger')->debug("[Hook] Alias not supplied. Cannot proceed");
            return false;
        }

        if (!$config->handle_extra_domains) {
            return false;
        }

        if (!$config->auto_add_domain) {
            return false;
        }

        if ($config->add_extra_alias) {
            // Add as alias
            $response .= "\nAdding '{$alias}' as alias of '{$domain}' to the Antispam filter...";
            $status = $hook->AddAlias($domain, $alias);
        } else {
            // Add as normal domain.
            $response .= "\nAdding '{$alias}' to the Antispam filter...";
            $status = $hook->AddDomain($alias);
        }

        break;

	case "unpark":
	case "deladdondomain":
		if(empty($alias))
		{
			Zend_Registry::get('logger')->debug("[Hook] Alias not supplied. Cannot proceed");
			return false;
		}
		if(!$config->handle_extra_domains) { return false; }// Extra/Addon domains DISABLED in plugin
		if(!$config->auto_del_domain ) { return false; } // The admin said he did not want to have domains removed from the filter.
		if($config->add_extra_alias) // Add the domain as ALIAS for existing one
		{
			// Deletion of alias
			$response .= "\nDeleting '{$alias}' (alias from '{$domain}') from the Antispam filter...";
			$status = $hook->DelAlias( $domain, $alias );
		} else {
			// Deletion of normal domain.
			$response .= "\nDeleting '{$alias}' from the Antispam filter...";
			$status = $hook->DelDomain( $alias );
		}
		break;

	case "predelaccount":
		// account ($data['user'] will be removed)
		// We need to get all of the domains associated to this acct.
		$response .= "\nDeleting all domains of '{$user}' from the Antispam filter...\n";
                    $status = $hook->DeleteAccount( $user );
		break;

	case "delaccount":
		$response .= "\nDelete account";
		// account ($data['user'] will be removed)
		// We need to get all of the domains associated to this acct.
		break;

	case "editdomain":
		$response .= "\nEdit domain (DIRECTADMIN)";

		$response .= " Deleting '{$domain}' from the Antispam filter...";
		$status = $hook->DelDomain( $domain );

		$response .= " Adding '{$domain}' to the Antispam filter...";
		$status = $hook->AddDomain( $newdomain );
		break;

	case "savecontactinfo":
		// Change email address for the user. (cpanel)
		$status = $hook->setContact($domain, $email);
		if( $status )
		{
			$response .= "\nYour Antispam email address for domain '{$domain}' has been set to '{$email}'.";
		} else {
			$response .= "\nCould not set your antispam email address for domain '{$domain}'.";
		}

		break;

	case "setalwaysaccept":
	case "setmxcheck":
		if( isset($mxcheck) && (!empty($mxcheck)) )
		{
			$hook->setMailHandling($domain, $mxcheck);
		} else {
			Zend_Registry::get('logger')->err("[Hook] Unable to set mail handling with missing mxtype.");
		}
	break;

	case "postdomainadd":
		// Re-check the MX records and remove the ones that don't belong.
		// Currently only needed in Plesk
		Zend_Registry::get('logger')->info("[Hook] Doing postdomainadd for Plesk (Domain: {$domain}).");
                $status = $hook->AddDomain($domain);
                $logger->debug("[Hook] Post add domain finished and returned: " . print_r($status, true));
                if (!empty($status['reason'])) {
                    $logger->info("Hook AddDomain status: " . $status['reason']);
                }                
	break;

	default:
		$response .= "\nUnknown option";
		return false;
		break;
}

if (isset($status['status'])) {
    if (!$status['status']) {
        $response .= " Failed!\n";
    }
    echo $response;
}
