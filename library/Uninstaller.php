<?php

class Uninstaller
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
     * @var SpamFilter_Logger
     */
    protected $logger;

    /**
     * @var Output_OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $resetMx;

    public function __construct(Installer_InstallPaths $paths, Filesystem_AbstractFilesystem $filesystem, Output_OutputInterface $output, $resetMx = false)
    {
        $this->output = $output;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
        $this->logger = Zend_Registry::get('logger');
        $this->resetMx = $resetMx;
    }

    public function uninstall()
    {
        try {
            $this->doUninstall();
        } catch (Exception $exception) {
            $this->output->error($exception->getMessage());
            $this->logger->debug($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
        }
    }

    private function doUninstall()
    {
        $this->outputStartMessage();
        $this->checkRequirementsAreMet();
        $this->resetMXs();
        $this->removeAddonFromPlesk();
        $this->filesystem->removeDirectory($this->paths->config);
        $this->filesystem->removeDirectory($this->paths->destination);
        $this->removeUpdateCronjob();
        $this->output->write("\n\n***** We're sad to see you go, but ProSpamFilter has now been uninstalled from your system! *****\n\n");
    }

    private function outputStartMessage()
    {
        $this->output->write("*** Uninstallation Process ***");
        $this->output->write("This application will UNINSTALL your ProSpamFilter with full configuration.");
        $this->output->write('');

        $file = __DIR__.'/../application/version.txt';
        $version = file_get_contents($file);
        $this->output->write("This system will uninstall ProSpamFilter v{$version}");
    }

    private function checkRequirementsAreMet()
    {
        if(! file_exists($this->paths->destination)) {
            throw new Exception("ProSpamFilter is not installed");
        }

        $this->checkUserIsRoot();

        if (! is_cli()) {
            throw new Exception("This program can only be ran from CLI");
        }

        // Additional check for resetMX action.
        if($this->resetMx){
            if(! class_exists("SpamFilter_Hooks") || ! class_exists("SpamFilter_PanelSupport_Plesk")){
                throw new Exception("ERROR: Required files are missing! Uninstaller cannot reset mx records!");
            }

            if(! function_exists("mb_strtolower")){
                throw new Exception("Multibyte Extension is required to proceed this action! Please install Multibyte Extenstion.");
            }
        }
    }

    private function resetMXs()
    {
        if (! $this->resetMx) {
            return;
        }

        $this->output->info("Resetting domains MX records. Please wait... ");
        $hooks = new SpamFilter_Hooks;
        $failures = array();

        $dapi = new Plesk_Driver_Domain();
        $domains = $dapi->getAllDomains();

        if(empty($domains)){
            $this->output->ok("There are no domains. Skipping this step.");
            return;
        }

        foreach ($domains as $domain){
            $this->output->info("Resetting: " . $domain['domain']);
            $result = $hooks->DelDomain(trim($domain['domain']), true, true);

            //If domain isn't added to SpamFilter then it isn't error
            if($result['status'] != 1 && $result['reason'] != 'NO_SUCH_DOMAIN'){
                $failures[] = array('domain' => $domain['domain'], 'reason' => $result['reason']);
            }
        }

        if(empty($failures)) {
            $this->output->ok("MXs records was successfully reset!");
        } else {
            $this->output->error("MX records reset failed for domains:");

            foreach($failures as $fail){
                $this->output->write("  " . $fail['domain'] . " - " . $fail['reason']);
            }
        }

        $this->output->ok("Done");
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

    private function removeAddonFromPlesk()
    {
        $panel = new SpamFilter_PanelSupport_Plesk();
        $this->filesystem->removeDirectory($this->paths->plesk . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter" . DS . 'images');
        @unlink($this->paths->plesk . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter" . DS . 'index.php');
        $this->filesystem->removeDirectory($this->paths->plesk . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter"); // delete symlink for windows
        @unlink($this->paths->plesk . "admin" . DS . "htdocs" . DS . "modules" . DS . "prospamfilter");

        // Remove icon
        @unlink($this->paths->plesk . "admin" . DS . "htdocs" . DS . "images" . DS . "custom_buttons" . DS . "prospamfilter.gif");
        @unlink($this->paths->plesk . "admin" . DS . "htdocs" . DS . "images" . DS . "custom_buttons" . DS . "prospamfilter.png");

        // Remove hooks
        @unlink($this->paths->plesk . "admin" . DS . "plib" . DS . "registry" . DS . "EventListener" . DS . "prospamfilter.php");

        //Remove prospamfilter.xml from DiskSecurity
        @unlink($this->paths->plesk . 'etc' . DS . 'DiskSecurity' . DS . 'prospamfilter.xml');

        $phpSymlink = '/usr/local/bin/prospamfilter_php';

        if (is_link($phpSymlink)) {
            unlink($phpSymlink);
        }

        // Remove our custom buttons
        $pass = $panel->getAdminPassword();
        $port = $panel->getMySQLPort();

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
    }

    private function removeUpdateCronjob()
    {
        $this->output->info("Removing cronjob...");
        @unlink("/etc/cron.d/prospamfilter");
        $this->output->ok("Done");

        $this->output->info("Reloading cron...");
        $cron = touch("/etc/crontab"); //Ha! Sneaky way of reloading which is univeral at CentOS/Debian :-)
        if ($cron) {
            $this->output->ok("Done");
        } else {
            $this->output->error("FAILED!");
            $this->output->error("** Unable to reload cron **");
            $this->output->error("--> Please rotate the cron daemon to make sure the cronjob is not longer being executed.");
            sleep(10);
        }
    }
}