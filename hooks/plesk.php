<?php

ini_set('apc.cache_by_default', 0); // Plesk bug workaround
if (!defined('IS_PLESK')) {
    define('IS_PLESK', true);
}

if (!defined('BASE_PATH')) {
    if (isset($_ENV['plesk_dir'])) {
        define('BASE_PATH', $_ENV['ProgramFiles'] . DIRECTORY_SEPARATOR . 'SpamExperts'. DIRECTORY_SEPARATOR . 'Professional Spam Filter');   
    } else {
        define('BASE_PATH', '/usr/local/prospamfilter');
    }
}

class Prospamfilter implements EventListener
{
    /**
     * @param $object_type
     * @param $object_id
     * @param $action
     * @param $old_values
     * @param $new_values
     *
     * @return void
     */
    function handleEvent($object_type, $object_id, $action, $old_values, $new_values)
    {
        if (!in_array($action, array(
            'domain_create', 'domain_update', 'domain_delete',
            'domain_alias_create', 'domain_alias_delete',
            'site_create', 'site_update', 'site_delete',
            //'phys_hosting_create'
            //'domain_dns_update','site_dns_update'
        ))) {
            // We do not handle this type of action, so no need to sit in between!
            syslog(LOG_DEBUG, "PSF: Skipping handling of {$action}");
        } else {

            // Init here
            include(BASE_PATH . DIRECTORY_SEPARATOR . 'application'. DIRECTORY_SEPARATOR . 'bootstrap.php');

            if (!Zend_Registry::isRegistered('general_config')) {
                // Initialize the config if this is not set.
                Zend_Registry::set('general_config', new SpamFilter_Configuration(CFG_FILE));
            }

            $hook = new SpamFilter_Hooks;
            // Retrieve config from the registry
            $config = Zend_Registry::get('general_config');
            /** @var $logger SpamFilter_Logger */
            $logger = Zend_Registry::get('logger');

            if (isset($new_values['Domain Name'])) {
                $add_domain = $new_values['Domain Name'];
            }
            if (isset($new_values['Domain Alias Name'])) {
                $add_alias = $new_values['Domain Alias Name'];
            }

            if (isset($old_values['Domain Name'])) {
                $del_domain = $old_values['Domain Name'];
            }
            if (isset($old_values['Domain Alias Name'])) {
                $del_alias = $old_values['Domain Alias Name'];
            }

            switch ($action) {
                /* Domain */
                case "domain_create":
                case 'site_create': //plesk10
                    SpamFilter_Core::invalidateDomainsCaches();

                    if (!$config->auto_add_domain) {
                        $logger->info(
                            "NOT Adding '{$add_domain}' to the Antispam filter, because adding domains is disabled in the settings"
                        );

                        return false;
                    }

                    // Trigger delayed job to fix the MX records due to Plesk limitations
                    if(!SpamFilter_Core::isWindows()){
                        $command =  'echo php ' . BASE_PATH . DS . 'bin' . DS . 'hook.php action postdomainadd domain ' . $add_domain . ' | at now + 2 minutes';
                        putenv("SHELL=/bin/sh");
                        system($command, $retval);
                        $logger->debug("Post process hook ({$command}) status: " . $retval);
                    } else {
                        $logger->debug("Hook: Add domain '{$add_domain}'");
                        $status = $hook->AddDomain($add_domain);
                        $logger->debug("Hook: Add domain finished and returned: " . print_r($status, true));
                        if (!empty($status['reason'])) {
                            $logger->info("Hook AddDomain status: " . $status['reason']);
                        }
                    }
                    break;

                case "domain_update":
                case "site_update": // plesk10
    #			case "phys_hosting_create": // plesk10
                    // We need to verify the MX records because the ADD hook is executed BEFORE the zone is setup.
                    $logger->debug("Hook: Checking MX records for '{$add_domain}'");
                    $hook->psa_CheckMxRecords($add_domain);
                    break;

                case "domain_delete":
                case 'site_delete': //plesk10
                    SpamFilter_Core::invalidateDomainsCaches();

                    if (!$config->auto_del_domain) {
                        $logger->info(
                            "NOT removing '{$del_domain}' from the Antispam filter, because removing domains is disabled in the settings"
                        );

                        return false;
                    }
                    $logger->debug("Hook: Delete domain '{$del_domain}'");
                    $status = $hook->DelDomain($del_domain);
                    if (!empty($status['reason'])) {
                        $logger->info("Hook DelDomain status: " . $status['reason']);
                    }
                    break;

                /* Domain Alias */
                case "domain_alias_create":
                    SpamFilter_Core::invalidateDomainsCaches();

                    if (empty($add_alias)) {
                        $logger->debug("[Hook] Alias not supplied. Cannot proceed");

                        return false;
                    }

                    // Extra/Addon domains DISABLED in plugin
                    if (!$config->handle_extra_domains) {
                        $logger->debug("[Hook] Extra domain handling not enabled");

                        return false;
                    }

                    // The admin said he did not want to have domains added to the filter.
                    if (!$config->auto_add_domain) {
                        $logger->debug("[Hook] Domain provisioning has not been enabled");

                        return false;
                    }

                    // Ok, we can proceed.
                    if ($config->add_extra_alias) // Add the domain as ALIAS for existing one
                    {
                        // Add as alias

                        // Since Plesk does not provide us with information on the domainNAME it belongs to we need to resolve the ID into a domain.
                        $dapi        = new Plesk_Driver_Domain();
                        $domain_data = $dapi->getDomainbyId($new_values['Domain Id']);
                        $add_domain  = current(array_values($domain_data));
                        if (!isset($add_domain)) {
                            $logger->debug("[Hook] Parent Domain not supplied. Cannot proceed");

                            return false;
                        }

                        $logger->info("Adding '{$add_alias}' as alias of '{$add_domain}' to the Antispam filter...");
                        $status = $hook->AddAlias($add_domain, $add_alias);
                    } else {
                        // Add as normal domain.

                        // Check if we need to handle this, no mail = no handling required
                        /*
                        $plesk = new Plesk_Driver_Aliases();
                        $alias_info = $plesk->getAliasbyName( $add_alias );
                        if( !(bool)$alias_info['get']['result']['info']['pref']['mail']['mail'] ) {
                            $logger->debug("[Hook] The alias is configured not to receive email, therefor we cannot protect it");
                            return false;
                        }
                        */

                        $logger->info("Adding '{$add_alias}' to the Antispam filter (alias as real domain)...");
                        $status = $hook->AddDomain($add_alias);
                    }
                    break;

                case "domain_alias_delete":
                    SpamFilter_Core::invalidateDomainsCaches();

                    if (empty($del_alias)) {
                        $logger->debug("[Hook] Alias not supplied. Cannot proceed");

                        return false;
                    }
                    // Extra/Addon domains DISABLED in plugin
                    if (!$config->handle_extra_domains) {
                        $logger->debug("[Hook] Extra domain handling not enabled");

                        return false;
                    }

                    // The admin said he did not want to have domains added to the filter.
                    if (!$config->auto_add_domain) {
                        $logger->debug("[Hook] Domain provisioning has not been enabled");

                        return false;
                    }

                    // Ok, we can proceed.
                    if ($config->add_extra_alias) // Add the domain as ALIAS for existing one
                    {
                        // Deletion of alias

                        // Since Plesk does not provide us with information on the domainNAME it belongs to we need to resolve the ID into a domain.
                        $dapi        = new Plesk_Driver_Domain();
                        $domain_data = $dapi->getDomainbyId($old_values['Domain Id']);
                        $del_domain  = current(array_values($domain_data));
                        if (!isset($del_domain)) {
                            $logger->debug("[Hook] Parent Domain not supplied. Cannot proceed");

                            return false;
                        }

                        $logger->info("Deleting '{$del_alias}' (alias from '{$del_domain}') from the Antispam filter...");
                        $status = $hook->DelAlias($del_domain, $del_alias);
                    } else {
                        // Deletion of normal domain.
                        $logger->info("Deleting '{$del_alias}' from the Antispam filter (alias as real domain)...");
                        $status = $hook->DelDomain($del_alias);
                    }
                    break;

                //@TODO: Evaluate the need of implementing more events (http://download1.parallels.com/Plesk/PPP9/Doc/en-US/plesk-9.5-unix-mod-api/index.htm?fileName=38616.htm)
            }
        }
    }
}

$MyEventInstance = new Prospamfilter;
return $MyEventInstance;
