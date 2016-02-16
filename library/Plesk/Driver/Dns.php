<?php
class Plesk_Driver_Dns extends Plesk_Driver
{
	// Getters
	private function _getData( $filter = null ) 
	{
		$request_data['dns']['get_rec']['filter'] = $filter;
		return parent::doRequest( $request_data );
	}

	public function getZoneForDomainId( $domain_id )
	{
		return $this->_getdata( array('domain_id' => $domain_id) );
	}

	public function getDnsRecordById( $id )
	{
		return $this->_getdata( array('id' => $id) );
	}

	public function getZones()
	{
		return $this->_getdata( );
	}

	// Setters
	public function addRecord( $domain_id, $type, $host = '', $value )
	{
		$data['dns']['add_rec']['domain_id'] 	= $domain_id;
		$data['dns']['add_rec']['type'] 		= $type;
		$data['dns']['add_rec']['host'] 		= $host;
		$data['dns']['add_rec']['value'] 		= $value;
		return parent::doRequest( $data );
	}

	public function addMxRecord($domain_id, $value, $prio)
	{
		$data['dns']['add_rec']['domain_id'] 	= $domain_id;
		$data['dns']['add_rec']['type'] 		= 'MX';
		$data['dns']['add_rec']['host'] 		= null;
		$data['dns']['add_rec']['value'] 		= $value;
		$data['dns']['add_rec']['opt'] 			= $prio;
		return parent::doRequest( $data );
	}

	// Deleters
	public function deleteRecord( $id )
	{
		$data['dns']['del_rec']['filter']['id'] = $id;
		return parent::doRequest( $data );
	}

	public function deleteRecordForDomain( $id )
	{
		$data['dns']['del_rec']['filter']['domain_id'] = $id;
		return parent::doRequest( $data );
	}
}