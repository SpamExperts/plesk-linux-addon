<?php

class SpamFilter_Forms_AdminConfig extends Twitter_Form
{
    protected $_paneltype;

    public function __construct($options = null)
    {
		$this->addElementPrefixPath('SpamFilter', 'SpamFilter/');
		parent::__construct($options);
		$this->setMethod('post')->setName('configForm');

		$config           = Zend_Registry::get('general_config');
        $translate        = Zend_Registry::get('translator');
		$this->_paneltype = SpamFilter_Core::getPanelType();
		$pn               = ucfirst(strtolower( $this->_paneltype )); //@TODO: Fixme
		///
		// Text Fields
		///
		$spampanel_url = new Zend_Form_Element_Text('spampanel_url');
		$spampanel_url->setLabel($translate->_('AntiSpam API URL'))
		          ->setRequired( true )
				  ->addFilter( new SpamFilter_Filter_UrlSlashes() )
		          ->addValidator('NotEmpty')
		          ->addValidator( new SpamFilter_Validate_Url() )
		          ->setValue( $config->spampanel_url )
				  ->setAttrib('class', 'span3')
				  ->setAttrib('title', $translate->_('AntiSpam API URL'))
		          ->setAttrib('data-content', $translate->_("This is the URL you use to login to your AntiSpam Web Interface. Please prepend the URL with http:// or https://"));
		          //->setDescription("This is the URL you use to login to Spampanel. Please prepend the URL with http:// or https://");
		$this->addElement( $spampanel_url );

		// API CONFIG
		$apihost = new Zend_Form_Element_Text('apihost');
		$apihost->setLabel($translate->_('API hostname'))
		          ->setRequired(true)
		          ->addValidator('NotEmpty')
                  ->addValidator( new SpamFilter_Validate_Hostname() )
                  ->setValue( $config->apihost )
				  ->setAttrib('class', 'span3')
				  ->setAttrib('title', $translate->_('API hostname'))
		          ->setAttrib('data-content', $translate->_("This is the hostname of the first antispam server, usually the same as the AntiSpam Web Interface URL unless you're using a CNAME for that."));
		          //->setDescription("This is the hostname of the first antispam server, usually the same as the Spampanel URL unless you're using a CNAME for that.");
		$this->addElement( $apihost );

		$apiuser = new Zend_Form_Element_Text('apiuser');
		$apiuser->setLabel($translate->_('API username'))
		          ->setRequired(true)
		          ->addValidator('NotEmpty')
       		      	  ->setValue( $config->apiuser )
				  ->setAttrib('class', 'span3')
				  ->setAttrib('title', $translate->_('API username'))
		          ->setAttrib('data-content', $translate->_("This is the name of the user that is being used to communicate with the spamfilter.") . "<span class='label label-warning'>" . $translate->_('You can only change this at the migration page.') . "</label>");
		          //->setDescription("This is the reseller user to log in with. We recommend you to create a separate user, but you can use your admin credentials too.");

		if( isset($config->apiuser) && !empty($config->apiuser))
		{
			// Lock the username field if it is not empty. Also make sure this cannot be changed
			$apiuser->addValidator(new Zend_Validate_Identical($config->apiuser))
			->setAttrib('readonly',true)
					->setAttrib('class', "span3 readonly");
		}

		$this->addElement( $apiuser );

		$apipass = new Zend_Form_Element_Password('apipass');
		$apipass->setLabel($translate->_('API password'))
		          ->setRequired(true)
		          ->addValidator('NotEmpty')
				  ->setAttrib('autocomplete', 'off')
				  ->setAttrib('class', 'span3')
				  ->setAttrib('title', $translate->_('API password'))
		          ->setAttrib('data-content', $translate->_("This is the password from the user that is being used to communicate with the spamfilter. Can be left empty once it has been validated."));
		          //->setDescription("This is the password from the reseller that is being used to login. Can be left empty once it has been validated.");
		$this->addElement( $apipass );

		if( (!empty($_POST)) )
		{
			if(empty($_POST['apipass']))
			{
				// Argh this is just dirty. But it saves us from having to enter the password over and over again.
				$_POST['apipass'] = $config->apipass;
			}
			$apipass->addValidator( new SpamFilter_Validate_ApiCredentials($_POST['apihost'], $_POST['apiuser'], $_POST['apipass'], $_POST['ssl_enabled']) );
		}

		////
		// Virtual MX records
		////
		$mx1 = new Zend_Form_Element_Text('mx1');
		$mx1->setLabel($translate->_('Primary MX'))
		          ->setRequired(true)
		          ->addValidator('NotEmpty')
			  ->setValue( $config->mx1 )
					->setAttrib('class', 'span3')
					->setAttrib('title', $translate->_('Primary MX'))
					->setAttrib('data-content', $translate->_("This is for the first (virtual) MX record. It can be either your cluster's first server or an other DNS name if you're using Round Robin DNS."));
		          //->setDescription("This is for the first (virtual) MX record. It can be either your cluster's first server or an other DNS name if you're using Round Robin DNS.");
		$this->addElement( $mx1 );

		$mx2 = new Zend_Form_Element_Text('mx2');
		$mx2->setLabel($translate->_('Secondary MX'))
			->setValue( $config->mx2 )
					->setAttrib('class', 'span3')
					->setAttrib('title', $translate->_('Secondary MX'))
					->setAttrib('data-content', $translate->_("This is for the second (virtual) MX record. It can be either your cluster's second server or an other DNS name if you're using Round Robin DNS."));
		        //->setDescription("This is for the second (virtual) MX record. It can be either your cluster's first server or an other DNS name if you're using Round Robin DNS.");
		$this->addElement( $mx2 );

		$mx3 = new Zend_Form_Element_Text('mx3');
		$mx3->setLabel($translate->_('Tertiary MX'))
			->setValue( $config->mx3 )
				->setAttrib('class', 'span3')
				->setAttrib('title', $translate->_('Tertiary MX'))
				->setAttrib('data-content', $translate->_("This is for the third (virtual) MX record. It can be either your cluster's third server or an other DNS name if you're using Round Robin DNS."));
			//->setDescription("This is for the third (virtual) MX record. It can be either your cluster's first server or an other DNS name if you're using Round Robin DNS.");
		$this->addElement( $mx3 );

		$mx4 = new Zend_Form_Element_Text('mx4');
		$mx4->setLabel($translate->_('Quaternary MX'))
				->setValue( $config->mx4 )
					->setAttrib('class', 'span3')
					->setAttrib('title', $translate->_('Quaternary MX'))
					->setAttrib('data-content', $translate->_("This is for the fourth (virtual) MX record. It can be either your cluster's third server or another DNS name if you're using Round Robin DNS."));
				//->setDescription("This is for the fourth (virtual) MX record. It can be either your cluster's first server or an other DNS name if you're using Round Robin DNS.");
		$this->addElement( $mx4 );

        // Language Select
        $languages = array(
            'en' 	=> "English",
            'da' 	=> "Dansk/Danmark",
            'de'	=> "Deutsch/Deutschland",
            'el' 	=> "Ελληνικά/Ελλάδα",
            'es' 	=> "Español/España",
            'fr' 	=> "Français/France",
            'hu' 	=> "Magyar/Magyarország",
            'it'    => "Italiano/Italia",
            'ja'    => "日本語",
            'nl'    => "Nederlands/Nederland",
            'pl'    => "Polski",
            'pt'    => "Português/Portugal",
            'pt_BR' => "Português/Brasil",
            'ru'    => "Русский/Россия",
            'tr'    => "Türkçe/Türkiye",
        );

        $compiledTransationsDirectory = BASE_PATH.DIRECTORY_SEPARATOR."translations".DIRECTORY_SEPARATOR."addons".DIRECTORY_SEPARATOR."compiled";

        // remove languages that don't have translations available
        foreach ($languages as $language => $name) {
            if ("en" != $language && ! is_dir($compiledTransationsDirectory.DIRECTORY_SEPARATOR.$language)) {
                unset($languages[$language]);
            }
        }

        $language = new Zend_Form_Element_Select('language');
        $language->setLabel($translate->_("Language"))
            ->setRequired( true )
            ->setMultiOptions($languages)
            ->setValue( $config->language )
            ->setAttrib('class', 'span3')
            ->setAttrib('title', $translate->_('Language'))
            ->setAttrib('data-content', $translate->_("Choose a language for the SpamFilter plugin."));
        //->setDescription("Choose a language for the SpamFilter plugin.");
        $this->addElement( $language );

		///
		// CHECKBOXES
		///
		$ssl_enabled = new Zend_Form_Element_Checkbox('ssl_enabled');
		$ssl_enabled->setLabel(sprintf($translate->_("Enable SSL for API requests to the spamfilter and %s"), "{$pn}"))
  			    ->setChecked( $config->ssl_enabled )
				->setAttrib('title', $translate->_('SSL'))
			    ->setAttrib('data-content', sprintf($translate->_("Use SSL to communicate with the Spamcluster API and %s."), "{$pn}"));
			    //->setDescription("Use SSL to communicate with the Spamcluster API and {$pn}.");

		if ( !SpamFilter_Core::selfCheck( false ) ) // Disable SSL support since it isn't available.
		{
			$ssl_enabled->setValue( 0 );
			$ssl_enabled->setChecked( 0 );
			$ssl_enabled->setAttrib('disable',true);
			$ssl_enabled->setAttrib('title', $translate->_('SSL'));
			$ssl_enabled->setAttrib('data-content', sprintf($translate->_("OpenSSL is not available in %s. This feature will be <strong>disabled</strong> and all communication with the API's is being done in plaintext!"), "{$pn}"));
			//$ssl_enabled->setDescription("OpenSSL is not available. This feature will be disabled and all communication with the API's is being done in plaintext!");
		}
		$this->addElement( $ssl_enabled ); // Add it later.

		$auto_update = new Zend_Form_Element_Checkbox('auto_update');
		$auto_update->setLabel($translate->_('Enable automatic updates'))
				->setChecked( $config->auto_update )
				->setAttrib('title', $translate->_('Enable automatic updates'))
				->setAttrib('data-content', $translate->_("Automatically install new updates if they are released. They are being checked once a day and being ran by cron."));
				//->setDescription("Automatically install new updates if they are released. They are being checked once a day and being ran by cron.");
		$this->addElement( $auto_update );

		$auto_add_domain = new Zend_Form_Element_Checkbox('auto_add_domain');
		$auto_add_domain->setLabel($translate->_('Automatically add domains to the SpamFilter'))
				->setChecked( $config->auto_add_domain )
						->setAttrib('title', $translate->_('Automatically add domains to the Spamfilter'))
  	                    ->setAttrib('data-content', $translate->_("Automatically add the local domain to the spamfilter when it is added to this server."));
  			    	//->setDescription("Automatically add the local domain to the spamfilter when it is added to this server.");
		$this->addElement( $auto_add_domain );

		$auto_del_domain = new Zend_Form_Element_Checkbox('auto_del_domain');
		$auto_del_domain->setLabel($translate->_('Automatically delete domains from the SpamFilter'))
				->setChecked( $config->auto_del_domain )
							->setAttrib('title', $translate->_('Automatically delete domains from the SpamFilter'))
  	                        ->setAttrib('data-content', $translate->_("Automatically remove the local domain from the spamfilter when it is removed from this server."));
  			    	//->setDescription("Automatically remove the local domain from the spamfilter when it is removed from this server.");
		$this->addElement( $auto_del_domain );

		$provision_dns = new Zend_Form_Element_Checkbox('provision_dns');
		$provision_dns->setLabel($translate->_('Automatically change the MX records for domains'))
			      ->setChecked( $config->provision_dns )
							->setAttrib('title', $translate->_('Automatically change the MX records for domains'))
							->setAttrib('data-content', $translate->_("Automatically change the MX records for the domains to the virtual MX records when they are being added to the panel or when using bulk protect."));
  			      //->setDescription("Automatically change the MX records for the domains to the virtual MX records when they are being added to the panel or when using bulk protect.");
		$this->addElement( $provision_dns );

		$set_contact = new Zend_Form_Element_Checkbox('set_contact');
		$set_contact->setLabel($translate->_('Configure the email address for this domain'))
			      ->setChecked( $config->set_contact )
						 ->setAttrib('title', $translate->_('Configure the email address for this domain'))
   	                      ->setAttrib('data-content', $translate->_("Automatically configure the email address for the domain owner. This email is being used to send out the protection reports and for the lost password feature"));
		              //->setDescription("Automatically configure the email address for the domain owner. This email is being used to send out the protection reports and for the lost password feature");
		$this->addElement( $set_contact );

		$handle_extra_domains = new Zend_Form_Element_Checkbox('handle_extra_domains');
		$handle_extra_domains->setChecked( $config->handle_extra_domains );
		

		$add_extra_alias = new Zend_Form_Element_Checkbox('add_extra_alias');
		$add_extra_alias->setChecked( $config->add_extra_alias );

		$handle_extra_domains->setLabel($translate->_('Process aliases and sub-domains'))
							  ->setAttrib('title', $translate->_('Process aliases and sub-domains'))
							  ->setAttrib('data-content', $translate->_("Also process aliases and sub-domains. The behaviour is controlled by other options."));
		$add_extra_alias->setLabel($translate->_('Add aliases and sub-domains as an alias instead of a normal domain. '))
						->setAttrib('title', $translate->_('Add aliases and sub-domains as an alias instead of a normal domain.'))
						->setAttrib('data-content', $translate->_("When adding aliases or sub-domains, treat them as an alias for the root domain they belong to."));
		$this->addElement( $handle_extra_domains );
		$this->addElement( $add_extra_alias );

		$use_existing_mx = new Zend_Form_Element_Checkbox('use_existing_mx');
		$use_existing_mx->setLabel($translate->_('Use existing MX records as routes in the spamfilter.'))
			      ->setChecked( $config->use_existing_mx )
				  ->setAttrib('title', $translate->_('Use existing MX records as routes in the spamfilter.'))
			      ->setAttrib('data-content', $translate->_("Use the existing MX records as destination hosts in the spamfilter. Useful if they point to a different server than this one."));
                  //->setDescription("Use the existing MX records as destination hosts in the spamfilter. Useful if they point to a different server then this one.");
		$this->addElement( $use_existing_mx );

		$handle_only_localdomains = new Zend_Form_Element_Checkbox('handle_only_localdomains');
		$handle_only_localdomains->setLabel($translate->_('Do not protect remote domains'))
				  ->setChecked( $config->handle_only_localdomains )
				  ->setAttrib('title', $translate->_('Do not protect remote domains'))
				  ->setAttrib('data-content', $translate->_("Skip domains if they are set to 'remote'. This usually happens when a different server is handling the incoming email for the domain"));
				  //->setDescription("Skip domains if they are listed in the remotedomains file. This usually happens when a different server is handling the incoming email for the domain");
		$this->addElement( $handle_only_localdomains );

		$redirectback = new Zend_Form_Element_Checkbox('redirectback');
		$redirectback->setLabel(sprintf($translate->_("Redirect back to %s upon logout"), "{$pn}"))
					  ->setChecked( $config->redirectback )
					  ->setAttrib('title', sprintf($translate->_("Redirect back to %s upon logout"), "{$pn}"))
					  ->setAttrib('data-content', sprintf($translate->_("Redirect the user back to %s if they logout from the antispam control panel."), "{$pn}"));
					//->setDescription("Redirect the user back to the controlpanel if they logout from the Antispam control panel.");
		$this->addElement( $redirectback );

		// Turn this in to an option then.
		$add_domain_loginfail = new Zend_Form_Element_Checkbox('add_domain_loginfail');
		$add_domain_loginfail->setLabel($translate->_('Add the domain to the spamfilter during login if it does not exist'))
				->setChecked( $config->add_domain_loginfail )
							->setAttrib('title', $translate->_('Add the domain to the spamfilter during login if it does not exist'))
  	                        ->setAttrib('data-content', sprintf($translate->_("If the domain does not exist in the spamfilter while trying to log in trough %s, it will try adding it."), "{$pn}"));
						  //->setDescription("If the domain does not exist in the spamfilter, it will add try adding it.");
		$this->addElement( $add_domain_loginfail );

		//
		$bulk_force_change = new Zend_Form_Element_Checkbox('bulk_force_change');
		$bulk_force_change->setLabel($translate->_('Force changing route & MX records, even if the domain exists.'))
						  ->setChecked( $config->bulk_force_change )
						  ->setAttrib('title', $translate->_('Force changing route &amp; MX records, even if the domain exists.'))
  	                      ->setAttrib('data-content', $translate->_("This will force the destination server / MX records to be set if the domain already exists."));
						   //->setDescription("This will force the destination server / MX records to be set if the domain already exists.");
		$this->addElement( $bulk_force_change );

        $use_ip_address_as_destination_routes = new Zend_Form_Element_Checkbox('use_ip_address_as_destination_routes');
        $use_ip_address_as_destination_routes->setLabel($translate->_('Use IP as destination route instead of domain'))
            ->setChecked((0 < $config->use_ip_address_as_destination_routes))
            ->setAttrib('title', $translate->_('Use IP as destination route instead of domain'))
            ->setAttrib('data-content', $translate->_("Force using of IP as destination route instead of hostname."));
        $this->addElement($use_ip_address_as_destination_routes);

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Save Settings');
		$submit->setAttrib('class', 'btn btn-primary');
		$this->addElement( $submit );

		$this->setAttrib('enctype', 'multipart/form-data');
		$this->setAttrib('horizontal', 1);
	}

    public function isValid($data)
    {
        $valid = parent::isValid($data);

        foreach ($this->getElements() as $element) {
            if ($element->hasErrors())
			{
                $oldClass = $element->getAttrib('class');
                if (!empty($oldClass)) {
                    $element->setAttrib('class', $oldClass . ' inputerror');
                } else {
                    $element->setAttrib('class', 'inputerror');
                }
            }
        }
        return $valid;
    }
}
