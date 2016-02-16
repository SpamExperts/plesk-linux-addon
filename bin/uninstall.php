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
	// Include requires
	require_once(realpath(dirname(__FILE__) . '/../') . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'bootstrap.php');
        
        if(SpamFilter_Core::isWindows()){
                define('DEST_PATH', BASE_PATH);
        } else {
		define('DEST_PATH', '/usr/local/prospamfilter' );
	}
	// Lets go!
	$uninstaller = new Uninstaller($argv);
	$uninstaller->run();

class Uninstaller
{
	/**
         * takes argv[1] parameter as confirmation for resetMX
         * 
         * @var type string
         * @visibility private
         * 
         */         
        private $confirmation;
        
        public function __construct($argv){
            if(!empty($argv) && isset($argv[1])){
                $this->confirmation = $argv[1];
            } else {
                $this->confirmation = '';
            }
        }
    
        /**
	 * Execute sanity checks to see if we can run
	 *
	 *
	 * @return bool Status
	 *
	 */        
        private function sanity_checks()
	{
		if(!file_exists( DEST_PATH ))
		{
			die("ProSpamFilter is not installed");
		}
                if(!SpamFilter_Core::isWindows()){
                    $whoami = trim(shell_exec('whoami'));
                    if ($whoami != "root")
                    {
                            die("This can only be done by root.");
                    }

                    if (!is_cli())
                    {
                            die("This program can only be ran from CLI");
                    }
                }
                // Additional check for resetMX action.
                if(trim(strtolower($this->confirmation)) == '--resetmx'){
                    if(!class_exists("SpamFilter_Hooks") || !class_exists("SpamFilter_PanelSupport")){
                        die("\nERROR: Required files are missing! Uninstaller cannot reset mx records!\n");
                    }
                    if(!function_exists("mb_strtolower")){
                        die("\nMultibyte Extension is required to proceed this action! Please install Multibyte Extenstion.\n");
                    }
                }

		return true;
	}

	/**
	 * Execute panel specific settings
	 *
	 *
	 * @return bool Status
	 *
	 */
	private function panel_actions()
	{
		$paneltype = SpamFilter_Core::getPanelType();
		echo "Executing {$paneltype} specific commands...";
		switch( $paneltype )
		{
			case "PLESK":
                $panel = new SpamFilter_PanelSupport_Plesk();
                @rrmdir(PLESK_DIR . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter" . DS . 'images');
                unlink(PLESK_DIR . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter" . DS . 'index.php');
                @rrmdir(PLESK_DIR . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter"); // delete symlink for windows
                @unlink(PLESK_DIR . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter");
                                
				// Remove icon
				@unlink(PLESK_DIR . "admin" . DS . "htdocs" . DS . "images" . DS . "custom_buttons" . DS . "prospamfilter.gif");
				@unlink(PLESK_DIR . "admin" . DS . "htdocs" . DS . "images" . DS . "custom_buttons" . DS . "prospamfilter.png");

				// Remove hooks
				unlink(PLESK_DIR . "admin" . DS . "plib" . DS . "registry" . DS . "EventListener" . DS . "prospamfilter.php");
                                
                //Remove prospamfilter.xml from DiskSecurity
                @unlink(PLESK_DIR . 'etc' . DS . 'DiskSecurity' . DS . 'prospamfilter.xml');
                                
				// Remove our custom buttons
				$pass = $panel->getAdminPassword();
                $port = $panel->getMySQLPort();

				$plesk_version = @file_get_contents(PLESK_DIR . 'version');
				$version = (int) substr($plesk_version, 0, strpos($plesk_version, '.'));
				$front = ($version > 9 ? '../../..' : '');

				// Remove Custom Buttons
                $mysql_connection = mysql_connect('localhost:' . $port, 'admin', $pass);

				if(!empty($mysql_connection)){
					if(!(@mysql_select_db('psa', $mysql_connection))){
						die('ERROR SELECTING DB');
					}
				}else{
					die('ERROR CONNECTING TO MYSQL');
				}

				// Clean ourselves up
				$success = mysql_query("DELETE FROM `custom_buttons` WHERE url like '%prospamfilter%'");

                if(! $success){
                    echo mysql_error() . "\n";
                }

                mysql_close($mysql_connection);

				// @TODO: Remove custom events
			break;
		}
		echo "Done";
		return true;
	}

	/**
	 * remove_update_cronjob
	 * Remove the cronjob & reload crontab
	 *
	 *
	 * @return bool Status
	 *
	 */
	private function remove_update_cronjob()
	{
		echo "Removing cronjob...\n";
                if(SpamFilter_Core::isWindows()){
                    system('schtasks /delete /tn "SpamExperts Check Update Task" /f');
                } else {
                    @unlink("/etc/cron.d/prospamfilter");
                    echo "Done\n";

                    echo "Reloading cron...";
                    $cron = touch("/etc/crontab"); //Ha! Sneaky way of reloading which is univeral at CentOS/Debian :-)
                    if( $cron )
                    {
			echo "Done\n";
                    } else {
			echo "FAILED!\n";
			echo "** Unable to reload cron **\n";
			echo "--> Please rotate the cron daemon to make sure the cronjob is not longer being executed.\n";
			sleep(10);
                    }
                }

		return true;
	}

	/**
	 * Remove hook
	 *
	 * @param $file Filename to check
	 * @param $name Hook name
	 * @param $content Content to remove
	 *
	 * @return bool Status
	 */
	private function remove_hook($file, $name, $content)
	{
		echo "Removing hook {$name}...";
		// Override the content a bit. :-)
		$fc = array();
		if(file_exists($file))
		{
			// Append (potential problem, so check if it is ran by bash first)
			$lines = file( $file, FILE_SKIP_EMPTY_LINES );

			// Check if we aren't already installed. Adding the hook twice is useless.
			foreach($lines as $line)
			{
				$line = trim($line);
				if ( (stristr($line, $content)) || (substr($line,0,1) == '#') || (empty($line)) )
				{
					#echo "Skipping line: '{$line}'\n";
					// Skip this line
				} else {
					#echo "Not our line: '{$line}'\n";
					$fc[] = $line;
				}
			}

			// Check if the file we're appending to is also bash.
			if (!strstr($lines[0],"bash"))
			{
				// Not ok, possibly Perl? That won't work...
				echo "\n******* ERROR *******\n";
				echo "Cannot remove hook {$name}! File exists but is not bash.\n";
				echo "Please correct this manually  in file '{$file}'\n";
				return false;
			}
			elseif(count($fc)>0)
			{
				// More lines exist.
				echo "\n******* ERROR *******\n";
				echo "Cannot remove hook {$name}! More content discovered not belonging to us\n";
				echo "Please correct this manually in file '{$file}'\n";
				sleep(2.5);
				return false;
			} else {
				unlink($file);
				echo "Done\n";
				return true;
			}
		} else {
			echo "Not neccesery\n";
			return true;
		}
	}

	/**
	 * Unregister hooks from panel (if required)
	 *
	 *
	 * @return bool status
	 *
	 */
	private function unregister_hooks()
	{
		echo "Re-Registering hooks..\n";
		system("/usr/local/cpanel/bin/register_hooks");
		echo "\n....Done\n";
		return true;
	}

	/**
	 * Remove the configuration
	 *
	 *
	 * @return bool Status
	 */
	private function remove_configs()
	{
		echo "Removing config folder...";
                if(SpamFilter_Core::isWindows()){
                    rrmdir($_ENV['ProgramData']. DS ."SpamExperts");    
                } else {
                    rrmdir(CFG_PATH);
                }
		echo "Done\n";
		return true;
	}

	/**
	 * Start uninstallation process
	 *
	 *
	 * @return bool Status
	 */
	private function start_uninstall()
	{
		$version = file_get_contents(".." . DS . "application" . DS . "version.txt");
		echo "This system will uninstall ProSpamFilter v{$version}\n";
		return true;
	}

	/**
	 * Done..
	 *
	 *
	 * @return bool Status
	 */
	private function end_uninstall()
	{
		echo "\n\n***** We're sad to see you go, but ProSpamFilter has now been uninstalled from your system! *****\n\n";
		return true;
	}

	/**
	 * Remove the files
	 *
	 *
	 * @return void
	 */
	private function remove_files()
	{
            rrmdir( DEST_PATH );
	}

	/**
	 * Kickstart uninstall system
	 *
	 *
	 * @return void
	 */
	public function run()
	{
        $this->confirm();
        $this->start_uninstall();
        $this->sanity_checks();
        $this->resetMXs();
        $this->panel_actions(); //<-- Panel actions should be executed before we remove it :-)
        $this->remove_configs();
        $this->remove_files();
        $this->remove_update_cronjob();
        $this->end_uninstall();
	}

/**
 * confirm
 * Are you sure?
 *
 *
 * @return void
 */
	private function confirm()
	{
		// Confirm uninstallation just in
		echo "*** Uninstallation Process ***\n";
		echo "This application will UNINSTALL your ProSpamFilter with full configuration.\n";
		echo "\n";
	}
        
        private function resetMXs(){     
            if(trim(strtolower($this->confirmation)) == '--resetmx'){
                echo "\nResetting domains MX records. Please wait... \n";
                $hooks = new SpamFilter_Hooks;
                $_panel = new SpamFilter_PanelSupport();
                $failures = array();
                if (strtolower($_panel->_paneltype) == 'plesk'){ // we do not have acces to pm/Session so we can't check user role
                    $dapi = new Plesk_Driver_Domain();
                    $domains = $dapi->getAllDomains();
                    
                    if(empty($domains)){
                        echo "There are no domains. Skipping this step.\n";
                        return false;
                    }                    
                    foreach ($domains as $domain){
                        echo "Resetting: " . $domain . "\n";
                        $result = $hooks->DelDomain($domain, true, true);
                        if($result['status'] != 1 && $result['reason'] != 'NO_SUCH_DOMAIN'){//If domain isn't added to SpamFilter then it isn't error
                            $failures[] = array('domain' => $domain, 'reason' => $result['reason']);
                        }
                    }
                }
             
                if(empty($failures)){
                    echo "MXs records was successfully reset!\n";
                } else {
                    echo "MX records reset failed for domains:\n";
                    foreach($failures as $fail){
                        echo "  " . $fail['domain'] . " - " . $fail['reason'] . "\n";
                    }
                }    
                echo "Done\n";
                return true;
            }
            return false;
        }
}
