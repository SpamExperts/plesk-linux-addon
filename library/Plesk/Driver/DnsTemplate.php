<?php
class Plesk_Driver_DnsTemplate extends Plesk_Driver
{
	// Getters
	private function _getData( $filter = null ) 
	{
		$request_data['dns']['get_rec']['filter'] = $filter;
        $request_data['dns']['get_rec']['template'] = '';
		return parent::doRequest( $request_data );
	}

	public function getRecords()
	{
		return $this->_getdata( );
	}
    
    public function getRecordCount()
    {
        $records = $this->getRecords();
        return count( $records['dns']['get_rec']['result'] );
    }
    
	public function getMXRecords()
	{
		$records = $this->getRecords( );
        
        $return = array();
        
        foreach($records['dns']['get_rec']['result'] as $record) 
        {
            $record = $record['data'];
            if ( $record['type'] == 'MX' )
            {
                $return[] =$record;
            }
        }
        return $return;
	}    
}