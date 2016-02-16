<?php

ini_set('apc.cache_by_default', 0); // Plesk bug workaround

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Content-Type: text/html; charset=UTF-8");

$isWindows = stripos(PHP_OS, 'win') === 0;

if(isset($_ENV['ProgramFiles']) && $isWindows){
    include($_ENV['ProgramFiles'] . DIRECTORY_SEPARATOR . "SpamExperts" . DIRECTORY_SEPARATOR . "Professional Spam Filter" . DIRECTORY_SEPARATOR . "application" . DIRECTORY_SEPARATOR . "bootstrap.php");
} else {
    include('/usr/local/prospamfilter/application/bootstrap.php');
}

// Determine access level
$panel = new SpamFilter_PanelSupport();
$accesslevel = strtolower( $panel->getUserLevel() );

// NEW PART
if( isset($_GET) && isset($_GET['q']) )
{
        $_SERVER['REQUEST_URI'] = $_GET['q'];
}

// We also do MVC
Zend_Layout::startMvc(array(
    'layoutPath' => BASE_PATH . DS . 'application' . DS . 'views' . DS . 'layouts',
    'layout' => 'default',
));

// Initialize the MVC component (actually only View+Controller)
$front = Zend_Controller_Front::getInstance();
$front->setControllerDirectory( BASE_PATH . DS . 'application' . DS . 'controllers' );

if (!empty( $accesslevel ) )
{
	switch( $accesslevel )
	{
		case 'role_reseller':
			$front->setDefaultControllerName( 'reseller' );
			$front->setDefaultAction( 'listdomains' );
			break;

		case 'role_enduser':
		case 'role_client':
			$front->setDefaultControllerName( 'domain' );
			break;

		case 'role_emailuser':
			$front->setDefaultControllerName( 'email' );
			break;

        case 'role_serviceuser':
            throw new InvalidArgumentException('Current user role is not supported.');

		default:
			$front->setDefaultAction( 'index' );
			break;
	}
}
$front->setParam('useDefaultControllerAlways', true);
$front->dispatch();
exit();
