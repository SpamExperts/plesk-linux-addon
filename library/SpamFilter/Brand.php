<?php
/*
*************************************************************************
*                                                                       *
* ProSpamFilter                                                         *
* Bridge between Webhosting panels & SpamExperts filtering		*
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
*/

/*
* SpamFilter Branding
*
* This class provides branding options for the addon (as long as the panel supports it)
*
* @class     SpamFilter_Brand
* @category  SpamExperts
* @package   ProSpamFilter
* @author    $Author$
* @copyright Copyright (c) 2011, SpamExperts B.V., All rights Reserved. (http://www.spamexperts.com)
* @license   Closed Source
* @version   3.0
* @link      https://my.spamexperts.com/kb/34/Addons
* @since     2.0
*/
class SpamFilter_Brand
{
    const ICON_PATH_PLESK = '/usr/local/psa/admin/htdocs/images/custom_buttons/prospamfilter.png';

	private $_configData;
	private $_configFile;
	private $_productList;

	public function __construct( )
	{
        if(!CFG_PATH) {
			throw new Exception("Unable to load branding configuration without a provided config path");
		}

		$this->_configFile = CFG_PATH . DS . 'branding.conf';
		if( !is_readable( $this->_configFile ) ) {
			Zend_Registry::get('logger')->err("[Brand] Cannot read my configfile. ({$this->_configFile})");
			return false;
		}

		Zend_Registry::get('logger')->debug("[Branding] Retrieving configuration from '{$this->_configFile}'");
		$this->_configData = $this->_getIniContent( $this->_configFile );

		if( $this->_configData ) {
			Zend_Registry::get('logger')->err("[Brand] Branding configuration loaded.");
			Zend_Registry::set('branding', $this->_configData ); // Optional, no real need for it though.
			return true;
		}

		Zend_Registry::get('logger')->err("[Brand] Unable to obtain branding configuration.");
		return false;
	}

    public function hasBrandingData()
    {
        return $this->_configData ? true : false;
    }

	private function _getIniContent( $fileName )
	{
		// Used to obtain the normal ini content
		try
		{
			Zend_Registry::get('logger')->debug("[Brand] Loading branding config from '{$fileName}'");
			$config = new Zend_Config_Ini( $fileName );
		}
		catch(Zend_Config_Exception $e)
		{
			Zend_Registry::get('logger')->crit("[Brand] Failed to load the INI config. ({$e->getMessage()})");
			return false;
		}

		// Check if it is set.
		if( $config )
		{
			Zend_Registry::get('logger')->debug("[Brand] Data is set, saving to registry");
			return $config;
		}

		Zend_Registry::get('logger')->err("[Brand] Loading branding configuration has failed.");
		return false;
	}

	/**
	 * Retrieve the current brand
	 * @return string Current brand used
	 *
	 * @access public
	 */
	public function getBrandUsed()
	{
        $hasWhitelabel = $this->hasWhitelabel();
		if ( (isset($this->_configData->brandname)) && (!empty($this->_configData->brandname)) && $hasWhitelabel)
		{
			Zend_Registry::get('logger')->debug("[Brand] Brand is set, returning value '{$this->_configData->brandname}'...");
			return $this->_configData->brandname;
		}

		// Fallback
		Zend_Registry::get('logger')->debug("[Brand] No local brand configured (or WL disabled), falling back to default.");
		return $this->getDefaultBrandname();
	}

	/**
	 * Check whether the cluster has a whitelabel license.
	 *
	 * @return bool Whitelabel licence enabled/disabled
	 *
	 * @access public
	 */
	public function hasWhitelabel()
	{
        if (empty($this->_productList)) {
            // Do an API call
            $api = new SpamFilter_ResellerAPI();
            if(!$api) {
                // Unable to check
                Zend_Registry::get('logger')->err("[Brand] Unable to check for whitelabel without API access.");
                return false;
            } else {
				// Do get_products
                $this->_productList = $api->productslist()->get( array() );
            }
        }
        if (empty($this->_productList)) {
            // Unable to retrieve
            Zend_Registry::get('logger')->err("[Brand] Unable to retrieve API methods. Maybe 'api/productslist' is disabled?");
            return false;
        }
		// Methods received, check if we have the allowed methods.
        if (is_array($this->_productList) &&
			(!isset($this->_productList['reason']) || $this->_productList['reason'] != 'API_REQUEST_FAILED')) {
            if(in_array('whitelabel', $this->_productList)) {
				Zend_Registry::get('logger')->debug("[Brand] Whitelabel is enabled.");
				return true;
			}
		}

        if (PHP_SAPI !== 'cli' && is_array($this->_productList) &&
            isset($this->_productList['reason']) && $this->_productList['reason'] == 'API_REQUEST_FAILED') {
            $message = array(
                'message' => "Unable to communicate with the Spamfilter API. Please check the configuration and if the problem persists contact your administrator or service provider.",
                'status' => 'error'
            );
			$messageQueue = new SpamFilter_Controller_Action_Helper_FlashMessenger();
            $messageQueue->addMessage($message);
        }

		// Did not find it, so not enabled.
		Zend_Registry::get('logger')->debug("[Brand] Whitelabel has NOT been enabled (or return value not correct).");
		return false;
	}


	/**
	 * Retrieve the default brandname
	 *
	 * @return string Brandname
	 *
	 * @access public
	 */
	static public function getDefaultBrandname()
	{
		return "Professional Spam Filter";
	}

	/**
	 * Retrieve the default brand icon
	 *
	 * @return string Base64 encoded icon
	 *
	 * @access public
	 */
	static public function getDefaultIcon()
	{
		return "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";
	}

	/**
	 * Retrieve current set brand icon (if the panel supports it)
	 *
	 * @return string Base64 encoded icon
	 *
	 * @access public
	 */
	public function getBrandIcon($force = false)
	{
                $wl = true;
		if(!$force){
                    // We don't want to execute this method while is forced to get icon
                    $wl = $this->hasWhitelabel();
                }
                if ( (isset($this->_configData->brandicon)) && (!empty($this->_configData->brandicon)) && $wl )
		{
			Zend_Registry::get('logger')->debug("[Brand] Brand icon is set, returning...");
			return $this->_configData->brandicon;
		}

		// Default value.
		Zend_Registry::get('logger')->debug("[Brand] getBrandIcon returns default value");
		return $this->getDefaultIcon();

	}

	/**
	 * Set the brandname
	 *
	 * @param string $brandname Brandname to set
	 *
	 * @see getPanelType()
	 * @see hasWhitelabel()
	 * @see setBrandName()
	 *
	 * @return bool Status
	 *
	 * @access public
	 */
	public function updateBrandname( $brandname = null )
	{
		if(!empty($brandname))
		{
			Zend_Registry::get('logger')->info("[Brand] Updating brandname to '{$brandname}'");
		}

		// Perform panel specific actions (if required) to rebrand the addon.
		if(empty($brandname))
		{
			// get it!
			$brandname = $this->getBrandUsed();
			Zend_Registry::get('logger')->info("[Brand] Updating brandname to '{$brandname}'");
		}
		$paneltype = strtolower( SpamFilter_Core::getPanelType() );

		if( ($brandname != $this->getDefaultBrandname() ) && (!$this->hasWhitelabel()) )
		{
			Zend_Registry::get('logger')->err("[Brand] Cannot update brand to '{$brandname}': No whitelabel license available.");
			return false;
		}

		// Write to config
		if (! $this->updateOption('brandname', $brandname) )
		{
			Zend_Registry::get('logger')->err("[Brand] Cannot save branding change to configuration file.");
			return false;
		}

		Zend_Registry::get('logger')->info("[Brand] Asking {$paneltype} to update brand to: '{$brandname}'");

        // For other panels we do not have to make an exception
        $panel = new SpamFilter_PanelSupport( );

        return $panel->setBrandname( $brandname );
	}

	/**
	 * Set the brandname and icon in one go
	 *
	 * @param array $brandingData Brandname to set
	 *
	 * @see getPanelType()
	 * @see setBrand()
	 *
	 * @return bool Status
	 *
	 * @access public
	 */
	public function updateBranding( $brandingData )
	{
		Zend_Registry::get('logger')->info("[Brand] Updating complete branding ({$brandingData['brandname']}) including icon");
		// Write to config
		if (! $this->updateOption('brandname', trim($brandingData['brandname']) ) )
		{
			Zend_Registry::get('logger')->err("[Brand] Cannot save branding change (brandname) to configuration file.");
			return false;
		}

		if (! $this->updateOption('brandicon', trim($brandingData['brandicon'])) )
		{
			Zend_Registry::get('logger')->err("[Brand] Cannot save branding change (icon) to configuration file.");
			return false;
		}

		$paneltype = strtolower( SpamFilter_Core::getPanelType() );
		switch ($paneltype)
		{
			case "plesk":
				// cPanel/WHM is one exception we have to make.
				$panel = new SpamFilter_PanelSupport( 'plesk' );
			break;

			default:
				// For other panels we do not have to make an exception
				$panel = new SpamFilter_PanelSupport( );
			break;
		}

		// Two calls to rule them all
		if (isset($panel)) {
			Zend_Registry::get('logger')->info("[Brand] Pushing request to Panel.");
			Zend_Registry::get('logger')->debug("[Brand] Setting Brandname: {$brandingData['brandname']}.");
			Zend_Registry::get('logger')->debug("[Brand] Setting BrandIcon: {$brandingData['brandicon']}.");

            /** @var $panel SpamFilter_PanelSupport_Plesk */
			return $panel->setBrand(array(
                'brandname' => $brandingData['brandname'],
                'brandicon' => $brandingData['brandicon'],
            ));
		}

		Zend_Registry::get('logger')->info("[Brand] Updating branding has failed.");
                return false;
	}

/**
 * updateOption
 * Updates one specific option in the configuration
 *
 * @param $key Key to update
 * @param $value Value to set for specified key
 *
 * @return Zend_Config_Ini object|False in case it failed
 *
 * @todo Remove this piece of code and make a centralized config writer option of it.
 *
 * @access private
 * @see WriteConfig()
 */
	private function updateOption($key, $value)
	{
		Zend_Registry::get('logger')->debug("[Brand] Updating '{$key}'");
		$config = $this->_configData;
		if( (isset($config)) && (is_object($config)) )
		{
			try {
				$x = $config->toArray();
			} catch (Exception $e) {
				Zend_Registry::get('logger')->err("[Brand] Updating '{$key}' has failed.");
				return false;
			}
			// Direct key
			$x[$key] = $value;
			Zend_Registry::get('logger')->info("[Brand] Key '{$key}' has been updated.");
			return $this->WriteConfig( $x );
		}
		Zend_Registry::get('logger')->err("[Brand] Updating '$key' has failed.");
		return false;
	}

/**
 * WriteConfig
 * Write the full configuration file
 *
 * @param $cfgData Array of configuration data*
 *
 * @return bool Status code
 *
 * @todo Remove this piece of code and make a centralized config writer option of it.
 *
 * @access private
 */
	private function WriteConfig( $cfgData )
	{
		if(is_array($cfgData))
		{
			// Generate a clean config
			$config = new Zend_Config(array(), true);

			// Generate new values
			foreach ($cfgData as $key => $value)
			{
				$config->$key = $value;
			}
			// Write values to the INI file
			$writer = new Zend_Config_Writer_Ini(
							array(
								'config' => $config,
								'filename' => $this->_configFile
								)
							    );
			// Lets write.
			$writer->write( );

			// Write config to variable
			$this->_configData = $config;

			// Write config to registry
			Zend_Registry::set( 'branding_config', $config );


			// All done.
			return true;
		}
		return false;
	}

    public function hasAPIAccess() {
        if (!empty($this->_productList['reason'])) {
            return !in_array($this->_productList['reason'], array(
                "API_REQUEST_FAILED",
				"INVALID_API_CREDENTIALS",
                "API_USER_INACTIVE",
                "API_ACCESS_DISABLED",
                "API_IP_ACCESS_ERROR",
            ));
        }
        return true;
    }
}
