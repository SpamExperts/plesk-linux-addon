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
* @category  SpamExperts
* @package   ProSpamFilter
* @author    $Author$
* @copyright Copyright (c) 2011, SpamExperts B.V., All rights Reserved. (http://www.spamexperts.com)
* @license   Closed Source
* @version   3.0
* @link      https://my.spamexperts.com/kb/34/Addons
* @since     3.0
*/

class Plesk_Driver
{
	public function getSupportedProtocols()
	{
		$request = array(
			'server' => array(
				'get_protos'  => ''
			),
		);

		$response = $this->doRequest($request);

		if (is_array($response)
			&& isset($response['server'])
			&& isset($response['server']['get_protos'])
			&& isset($response['server']['get_protos']['result'])
			&& isset($response['server']['get_protos']['result']['status'])
			&& $response['server']['get_protos']['result']['status'] == 'ok'
		) {
			return $response['server']['get_protos']['result']['protos']['proto'];
		}

		return array();
	}

	public function doRequest( $data, $version='1.6.0.2', $returnXml = false )
	{
        /** @var $logger SpamFilter_Logger */
        $logger = Zend_Registry::get('logger');

		// Init Plesk
        /** @var $plesk SpamFilter_PanelSupport_Plesk */
		$plesk = new SpamFilter_PanelSupport();
		$adminpass = $plesk->getAdminPassword(true);

        if (empty($adminpass)) {
			$logger->err("[Plesk] Unable to obtain admin password, unable to do request.");
			return false;
		}

		if (empty($version) && !($data instanceof Plesk_Api_Request_Body)) {
			$logger->err("[Plesk] Version is mandatory.");
			return false;
		}

		// Format the data so Plesk understands it.
        if ($data instanceof Plesk_Api_Request_Body) {
            $xml = (string) $data;
        } else {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<packet version="' . $version . '">';
            $xml .= $this->array_to_xml( $data );
            $xml .= '</packet>';
        }

		$logger->debug("[Plesk] XML: " . $xml );

		$config = new stdClass;
		$config->apiuser = 'admin';
		$config->apipass = $adminpass;

        /**
         * @see https://trac.spamexperts.com/ticket/25134
         */
		$apiHostname = gethostname();
        if (empty($apiHostname)) {
            $apiHostname = 'localhost';
        }

		$content = SpamFilter_HTTP::getContent("https://{$apiHostname}:8443/enterprise/control/agent.php", $config, $adminpass, $xml);

		$data = $this->xml_to_array( $content );

		if ( (isset($data['system'])) && (isset($data['system']['status'])) && $data['system']['status'] == 'error' )
		{
			if(isset($data['system']['errcode']))
			{
				$error_code = $data['system']['errcode'];
			}

			if(isset($data['system']['errtext']))
			{
				$error_text = $data['system']['errtext'];
			}

			if(isset($error_code) && isset($error_text))
			{
				$logger->err('[Plesk] Request failed with errorcode ' . $error_code . ' stating: "' . $error_text . '"');
				if(isset($data['output'])) {
					$logger->debug('[Plesk] Error details: '  . $data['output'] );
				}
			}
			return false;
		}

		$logger->debug( Zend_Json::encode( $data ) );

		// No error, returning raw data (for now, so its a @TODO to rework output data)
		return ($returnXml ? $content : $data);
	}

	private function xml_to_array( $xmlstring )
	{
		$xml = simplexml_load_string( $xmlstring );
		$json = json_encode( $xml );
		return json_decode( $json, true );
	}

	private function array_to_xml($array, $parent = null)
	{
		// @TODO: Simplify this code

	    $xml = '';
	    # Navigates through all the elements of the array
	    foreach ($array as $key => $value)
	    {
		if (is_int($key)) {
		    $xml .= "<$parent>\n";
		}

		# If the current value is an array, a recursive call will be made
		if (is_array($value))
		{
		     # If the child elements of the array are ordered with a numeric index,
		     # the recursive call must be made passing the current index, which will
		     # be used as tag name for all of them.
		    if (array_key_exists(0, $value))
		    {
		        $xml .= $this->array_to_xml($value, $key);
		    } else {
		        if (is_int($key))
			{
		            # Make the recursive call ignoring the current index because it's numeric
		            $xml .= $this->array_to_xml($value);
		        } else {
		            # Create the tag and make the recursive call
		            $xml .= "<$key>" . $this->array_to_xml($value) . "</$key>\n";
		        }
		    }
		} else if (is_null($value)) {
		    # If you have a null element in your array, the tag must be empty
		    $xml .= "<$key/>\n";
		} else {
		    if (is_int($key))
		    {
		        #
		         # In this case the tag doesn't need to be printed, it has already
		         # been opened in the beggining of the foreach loop and will be
		         # closed in the end of the loop.
		         #
		        $xml .= "$value";
		    } else {
		        # Inserts the tag name and its value
		        $xml .= "<$key>$value</$key>\n";
		    }
		}
		if (is_int($key))
		{
		    $xml .= "</$parent>";
		}
	    }
	    return $xml;
	}
}
