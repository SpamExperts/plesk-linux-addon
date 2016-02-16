<?php
class Plesk_Driver_Ip extends Plesk_Driver
{
	// Getters
	public function getServerIps()
	{
		$request_data['ip']['get'] = '';
		$ips = parent::doRequest( $request_data );

		$result = array();
		if( isset($ips) && isset($ips['ip']['get']['result']['addresses']) ) 
		{
			if( isset( $ips['ip']['get']['result']['addresses']['ip_info']['ip_address'] ) ) 
			{
				// One entry
				$ip = $ips['ip']['get']['result']['addresses']['ip_info']['ip_address'];				
				$result[] = $ip;
			} else {
				$count = count($ips['ip']['get']['result']['addresses']['ip_info']);				
				foreach( $ips['ip']['get']['result']['addresses']['ip_info'] as $key => $ip )
				{					
					$result[] = $ip['ip_address'];
				}
			}	
		}
		return $result;
	}
}
