<?php


class Installer_Installer
{
    /**
     * @var Filesystem_AbstractFilesystem
     */
    protected $filesystem;

    /**
     * @var Installer_InstallPaths
     */
    protected $paths;

    /**
     * @var SpamFilter_PanelSupport_Plesk
     */
    protected $panelSupport;

    /**
     * @var SpamFilter_Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @var Output_OutputInterface
     */
    protected $output;

    public function __construct(Installer_InstallPaths $paths, Filesystem_AbstractFilesystem $filesystem, Output_OutputInterface $output)
    {
        $this->output = $output;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
        $this->logger = Zend_Registry::get('logger');
        $this->findCurrentVersionAndInitPanelSupport();
    }

    public function install()
    {
        try {
            $this->doInstall();
        } catch (Exception $exception) {
            $this->output->error($exception->getMessage());
            $this->logger->debug($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
        }
    }

    private function doInstall()
    {
        $this->outputInstallStartMessage();

        $this->checkRequirementsAreMet();
        $this->checkUserIsRoot();
        $this->checkOldVersionIsUninstalled();

        if (!$this->panelSupport->minVerCheck()) {
            $this->output->error("The currently used version of your controlpanel doesn't match minimum requirement.");
            exit(1);
        }

        $this->copyFilesToDestination();
        $this->changeDestinationPermissions();

        $this->setupConfigDirectory();

        $this->symlinkImagesToWebDir();
        $this->symlinkFrontendToWebDir();
        $this->symlinkImagesToPSFWebDir();
        $this->setupHooks();

        $this->setupBrand();

        $pass = trim(file_get_contents('/etc/psa/.psa.shadow'));
        $this->installCustomButtons($pass, $this->panelSupport->getMySQLPort());

        $this->setUpdateCronjob();
        $this->setupSuidPermissions();

        $this->output->info("Changing ownership to 'psaadm' for addon config files");
        system("chown psaadm:sw-cp-server " . $this->paths->config . " -R");

        $this->removeMigrationFiles($this->currentVersion);
        $this->removeSrcFolder();
        $this->removeInstallFileAndDisplaySuccessMessage();
    }

    private function setupConfigDirectory()
    {
        $this->output->info("Prepopulating config folder");
        $this->createConfigDirectory();

        $cfgdir = $this->paths->config;
        // Settings file (world readable, root writable)
        touch("{$cfgdir}" . DS . "settings.conf");
        $this->updateSettingsFilePermissions();
        // Create branding file
        touch("{$cfgdir}" . DS . "branding.conf");

        $this->generateConfigurationFile();

        $this->output->ok("Done");
    }

    private function generateConfigurationFile()
    {
        $file = $this->paths->config.DS."settings.conf";

        if ((!file_exists($file)) || (filesize($file) == 0)) {
            $this->output->info("Configuration file '" . $file . "' does not exist (or is empty).");
            $this->output->info("Generating default configuration file..");
            $cfg = new SpamFilter_Configuration($file);

            if ($cfg) {
                $this->output->info("Configuring initial settings..");
                $cfg->setInitial();
                $this->output->ok("Done!");
            } else {
                $this->output->error("Failed!");
            }
        } else {
            $this->output->info("Configuration file '" . $file . "' already exists.");
        }
    }

    private function findCurrentVersionAndInitPanelSupport()
    {
        $options = array('skipapi' => true);

        if (file_exists("/usr/local/prospamfilter2/application/version.txt")) {
            $this->logger->debug("[Install] PSF2 version file exists");
            $this->currentVersion = trim(file_get_contents("/usr/local/prospamfilter2/application/version.txt"));
            if (version_compare($this->currentVersion, '3.0.0', '<')) {
                $this->logger->debug("[Install] Version is below 3.0.0");
                if (file_exists('/etc/prospamfilter2/settings.conf')) {
                    $this->logger->debug("[Install] Changing config to old");
                    $options['altconfig'] = "/etc/prospamfilter2/settings.conf";
                }
            }
        } elseif (file_exists($this->paths->destination . DS . 'application' . DS . 'version.txt')) {
            $this->logger->debug("[Install] New version file found post 3.0.0");
            $this->currentVersion = trim(file_get_contents($this->paths->destination . DS . 'application' . DS . 'version.txt'));
        } else {
            $this->logger->debug("[Install] No version file found, must be new install.");
            $this->currentVersion = null; //no version set, must be an upgrade
        }

        $this->panelSupport = new SpamFilter_PanelSupport_Plesk($options);
    }

    private function setupBrand()
    {
        $brand = new SpamFilter_Brand();

        // Setup initial icon, but only if it does not exist already.
        $icon_content = base64_decode($brand->getBrandIcon(true));

        if (!file_exists(SpamFilter_Brand::ICON_PATH_PLESK)) {
            file_put_contents(SpamFilter_Brand::ICON_PATH_PLESK, $icon_content);
        }

        system("chown psaadm:sw-cp-server " . SpamFilter_Brand::ICON_PATH_PLESK);
    }

    private function outputInstallStartMessage()
    {
        $version = $this->getVersion();
        $this->output->info("This system will install Professional Spam Filter v{$version} for Plesk Linux");
    }

    private function getVersion()
    {
        return trim(file_get_contents($this->paths->base . DS . "application" . DS . "version.txt"));
    }

    private function checkRequirementsAreMet()
    {
        if (version_compare($this->panelSupport->getVersion(), '12.6', '>=')) {
            $this->output->error('This addon is not compatible with your Plesk version. Please install our Plesk extension instead.');
            exit(1);
        }

        // We *need* shell_exec.
        if (!function_exists('shell_exec')) {
            $this->output->error('shell_exec function is required for the installer to work.');
            exit(1);
        }

        try {
            $whoami = shell_exec('whoami');
        } catch (Exception $e) {
            $this->output->error(
                "Error checking current user (via whoami), do you have the command 'whoami' or did you disallow shell_exec?."
            );
            exit(1);
        }

        $atPath = trim(shell_exec('which at'));

        if (! $atPath) {
            $this->output->error("Package 'at' is missing and the setup can not continue as the package is required for proper functioning of the add-on. Install package 'at' to fix this problem and run the installer again.");
            exit(1);
        }

        // More detailed testing
        $selfcheck = SpamFilter_Core::selfCheck(false, array('skipapi' => true));
        $this->output->info("Running selfcheck...");
        if ($selfcheck['status'] != true) {
            if ($selfcheck['critital'] == true) {
                $this->output->error("Failed\nThere are some issues detected, of whom there are critical ones:");
            } else {
                $this->output->warn("Warning\nThere are some (potential) issues detected:");
            }

            foreach ($selfcheck['reason'] as $issue) {
                echo " * {$issue}\n";
            }

            // Wait a short wile.
            sleep(10);
        } else {
            $this->output->ok("Finished without errors");
        }
    }

    private function checkUserIsRoot()
    {
        $whoami = shell_exec('whoami');
        $whoami = trim($whoami);
        if ($whoami != "root") {
            $this->output->error("This can only be installed by 'root' (not: '$whoami').");
            exit(1);
        }
    }

    private function checkOldVersionIsUninstalled()
    {
        if (file_exists('/usr/local/prospamfilter/uninstall.sh')) {
            die("Please uninstall the old version first (using 'sh /usr/local/prospamfilter/uninstall.sh') before installing this version.");
        }
    }

    private function copyFilesToDestination()
    {
        $this->output->info("Copying files to destination.");

        // Cleanup destination folder.
        $this->output->info("Cleaning up old folder (" . $this->paths->destination . ")..");
        $this->filesystem->removeDirectory($this->paths->destination);
        $this->output->ok("Done");

        // Make new destination folder
        $this->output->info("Creating new folder (" . $this->paths->destination . ")..");
        if(!file_exists($this->paths->destination)){
            if (!mkdir($this->paths->destination, 0777, true)) {
                $this->output->error("Unable to create destination folder.");
                exit(1);
            }
        }
        $this->output->ok("Done");

        // Move all files to the new location.

        $this->output->info("Copying files from '" . $this->paths->base . "' to '" . $this->paths->destination . "'...");
        smartCopy($this->paths->base, $this->paths->destination);

        $this->output->ok("Done");

        return true;
    }

    private function changeDestinationPermissions()
    {
        $permissions = array(
            0754 => array(
                'bin/bulkprotect.php',
                'bin/uninstall.php',
            ),
            0755 => array(
                'bin/checkUpdate.php',
                'bin/installer/installer.sh',
                'bin/hook.php',
                'frontend/cpanel/psf.php',
                'frontend/whm/prospamfilter.php',
                'hooks/plesk.php',
            ),
        );

        foreach($permissions as $permission => $files) {
            foreach($files as $file) {
                @chmod($this->paths->destination . DS . $file, $permission);
            }
        }

        $this->output->info("Setting tmp folder permissions");
        // Chmod the tmp folder (and everything in it)
        system("chmod -R 1777 " . $this->paths->destination . DS . "tmp");
        $this->output->ok("Done");
    }

    private function symlinkImagesToWebDir()
    {
        $this->output->info("Symlinking Plesk images to webdir");
        $target = $this->paths->destination . DS . 'public';
        $link = $this->paths->plesk . 'admin' . DS . 'htdocs' . DS . 'modules' . DS . 'prospamfilter';
        $ret_val = $this->filesystem->symlinkDirectory($target, $link);
        $this->logger->info("[Install] Symlink (Plesk images -> webdir) returned with {$ret_val}");
    }

    private function symlinkFrontendToWebDir()
    {
        // Symlink frontend to PSA dir
        $this->output->info("Symlinking Plesk frontend to webdir");
        $target = $this->paths->destination . DS . 'frontend' . DS . 'index.php';
        $link = $this->paths->plesk . 'admin' . DS . 'htdocs' . DS . 'modules' . DS . 'prospamfilter' . DS . 'index.php';
        $ret_val = $this->filesystem->symlink($target, $link);
        $this->logger->info("[Install] Symlink (Plesk frontend -> webdir) returned with {$ret_val}");
    }

    private function symlinkImagesToPSFWebDir()
    {
        $this->output->info("Symlinking Plesk images to psf webdir");
        $target = $this->paths->plesk . 'admin' . DS . 'htdocs' . DS . 'modules' . DS . 'prospamfilter' . DS . 'images';
        $link = $this->paths->plesk . 'admin' . DS . 'htdocs' . DS . 'modules' . DS . 'prospamfilter' . DS . 'psf';
        $ret_val = $this->filesystem->symlinkDirectory($target, $link);
        $this->logger->info("[Install] Symlink (Plesk frontend -> webdir) returned with {$ret_val}");
    }

    private function setupHooks()
    {
        //Setup hooks
        $this->output->info("Setting up hooks.");
        $target = $this->paths->destination . DS . 'hooks' . DS . 'plesk.php';
        $link = $this->paths->plesk . 'admin' . DS . 'plib' . DS . 'registry' . DS . 'EventListener' . DS . 'prospamfilter.php';
        $ret_val = $this->filesystem->symlink($target, $link);
        $this->logger->info("[Install] Setting up hooks exited with {$ret_val}");
    }

    private function installCustomButtons($sqlPass, $sqlPort)
    {
        // Inject ourselves into the custom buttons

        // Install the Custom Buttons
        if (!function_exists('mysql_connect')) {
            $this->output->error("MySQL extension isn't loaded, skipping database-related tasks");
            return;
        }

        $mysql_connection = mysql_connect('localhost:' . $sqlPort, 'admin', $sqlPass);

        if (!empty($mysql_connection)) {
            if (!(@mysql_select_db('psa', $mysql_connection))) {
                die('ERROR SELECTING DB');
            }
        } else {
            die('ERROR CONNECTING TO MYSQL');
        }

        /**
         * Get changed brandname (in case of it was set)
         * @see https://trac.spamexperts.com/ticket/16804
         */
        $persistentBrandName = 'Professional Spam Filter';
        $res = mysql_query("SELECT DISTINCT `text` FROM `custom_buttons` WHERE url like '%prospamfilter%'");
        if (is_resource($res) && 1 == mysql_num_rows($res)) {
            $row = mysql_fetch_assoc($res);
            if (isset($row['text']) && $persistentBrandName <> $row['text']) {
                $persistentBrandName = $row['text'];
            }
        }

        // Clean ourselves up
        mysql_query("DELETE FROM `custom_buttons` WHERE url like '%prospamfilter%'");

        // Insert sidebar (for all levels)
        $query = "INSERT INTO `custom_buttons` (`id`, `sort_key`, `level`, `level_id`, `place`, `text`, `url`, `conhelp`, `options`, `file`) VALUES (0, 100, 1, 0, 'navigation', '" . addslashes($persistentBrandName) . "', '/modules/prospamfilter/', 'Manage your spamfiltered domains', 384, 'prospamfilter.png')";
        if (! mysql_query($query, $mysql_connection)) {
            echo mysql_error();
        }

        // Admin level
        $query = "INSERT INTO `custom_buttons` (`id`, `sort_key`, `level`, `level_id`, `place`, `text`, `url`, `conhelp`, `options`, `file`) VALUES (0, 100, 1, 0, 'admin', '" . addslashes($persistentBrandName) . "', '/modules/prospamfilter/', 'Manage your spamfiltered domains', 384, 'prospamfilter.png')";
        if (! mysql_query($query, $mysql_connection)) {
            echo mysql_error();
        }

        // Reseller level
        $query = "INSERT INTO `custom_buttons` (`id`, `sort_key`, `level`, `level_id`, `place`, `text`, `url`, `conhelp`, `options`, `file`) VALUES (0, 100, 1, 0, 'reseller', '" . addslashes($persistentBrandName) . "', '/modules/prospamfilter/', 'Manage your spamfiltered domains', 384, 'prospamfilter.png')";
        if (! mysql_query($query, $mysql_connection)) {
            echo mysql_error();
        }
    }

    private function setUpdateCronjob()
    {
        $cronfile = "/etc/cron.d/prospamfilter";
        // Create crontab entry
        $hour = mt_rand(20, 23); // Decide on update hour (between 20 and 23)
        $min = mt_rand(0, 59); // Decide on update minutes (0 to 59)

        /** @see https://trac.spamexperts.com/ticket/24064 */
        $dayOfWeek = mt_rand(0, 6);

        /*
        // Test code to make sure the updater runs in 5 minutes after installing
        $test_time 	= strtotime("+5 minutes");
        $hour 		= date('H', $test_time);
        $min 		= date('i', $test_time);
        */

        $data = "## Skip mailing this cron output to prevent numerous emails\n";
        $data .= "#MAILTO=\"\"\n";
        $data .= "\n";
        $data .= "#TZ=UTC\n";
        $data .= "\n";
        $data .= "#TZ=" . date_default_timezone_get() . "\n";
        $data .= "## Automatically update Professional Spam Filter (if enabled in the settings)\n";
        $data .= "{$min} {$hour} * * {$dayOfWeek} root /usr/local/prospamfilter/bin/checkUpdate.php\n";

        // Write entry to crontab file.
        file_put_contents($cronfile, $data);
        $this->output->ok("Done");

        // Reload cron since placing a file does not work. (Support #711724)
        $this->output->info("Reloading cron...");
        touch("/etc/cron.d/prospamfilter");
        $cron = touch("/etc/cron.d"); // just in case

        $retval = '';

        // Here be some additional reload checks
        if (file_exists('/etc/init.d/crond')) {
            $this->output->info("Reloading cron via daemon reload (crond)");
            // In some cases, touching it does not work, so reloading must.
            system('/etc/init.d/crond reload >/dev/null', $retval);
        } elseif (file_exists('/etc/init.d/cron')) {
            $this->output->info("Reloading cron via daemon reload (cron)");
            // In some cases, touching it does not work, so reloading must.
            system('/etc/init.d/cron reload >/dev/null', $retval);
        }
        $this->output->info("Cron reload returned value {$retval}");

        if ($cron) {
            $this->output->ok("Done");
        } else {
            $this->output->error("FAILED!");
            $this->output->warn("** Unable to reload cron **");
            $this->output->warn("--> Please rotate the cron daemon to make sure the cronjob is loaded properly.");
            sleep(10);
        }
    }

    private function setupSuidPermissions()
    {
        $this->output->info("Setting up permissions for SUID binaries...");

        if (file_exists(DEST_PATH . DS . "bin" . DS . "getconfig64")) {
            $this->output->info("64-bits...");
            chown(DEST_PATH . "/bin/getconfig64", "root");
            chgrp(DEST_PATH . "/bin/getconfig64", "root");
            system("chmod 755 " . DEST_PATH . "/bin/getconfig64");
            system("chmod +s " . DEST_PATH . "/bin/getconfig64");
        }

        if (file_exists(DEST_PATH . "/bin/getconfig")) {
            $this->output->info("32-bits...");
            chown(DEST_PATH . "/bin/getconfig", "root");
            chgrp(DEST_PATH . "/bin/getconfig", "root");
            system("chmod 755 " . DEST_PATH . "/bin/getconfig");
            system("chmod +s " . DEST_PATH . "/bin/getconfig");
        }

        $this->output->ok("Done");
    }

    private function removeMigrationFiles($version)
    {
        if (version_compare($version, '3.0.0', '>')) {
            if (file_exists('/usr/local/cpanel/whostmgr/docroot/cgi/addon_prospamfilter2.php')) {
                $this->output->info("Removing some traces from PSF2->PSF3 migration.");
                unlink("/usr/local/cpanel/whostmgr/docroot/cgi/addon_prospamfilter2.php");
            }
        }
    }

    private function removeSrcFolder()
    {
        $this->output->info("Removing temporary files");
        $this->filesystem->removeDirectory($this->paths->base);
        $this->output->ok("Done");
    }

    private function removeInstallFileAndDisplaySuccessMessage()
    {
        // Remove installer as we do not need it anymore.
        unlink($this->paths->destination . "/bin/install.php");
        $this->output->ok("\n\n***** Congratulations, Professional Spam Filter for Plesk Linux has been installed on your system! *****");
        $this->output->ok("If the addon is not configured yet, you should setup initial configuration in the admin part of the control panel before using its features.");
    }

    private function updateSettingsFilePermissions()
    {
        chown($this->paths->config . "/settings.conf", "root");
        chgrp($this->paths->config . "/settings.conf", "root");
        chmod($this->paths->config . "/settings.conf", 0660);
    }

    private function createConfigDirectory()
    {
        $directory = $this->paths->config;

        if (!file_exists($directory)) {
            @mkdir("{$directory}" . DS, 0755, true);
            if (!file_exists($directory)) {
                die("[ERROR] Unable to create config folder!");
            }
        }
    }
}