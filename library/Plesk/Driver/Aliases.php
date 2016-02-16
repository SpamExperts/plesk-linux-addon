<?php

class Plesk_Driver_Aliases extends Plesk_Driver
{
	public function getAllAliases()
    {
		return $this->_retrieveAliases();
	}

	public function getAliasbyId( $alias_id )
	{
		return $this->_retrieveAliases( array('id' => $alias_id ) );
	}

	public function getAliasbyName( $alias )
	{
        $return = array();

        $panelSupport = new SpamFilter_PanelSupport_Plesk;

        if (version_compare($panelSupport->getVersion(), '10.2', '<')) {
            $request_data['domain_alias']['get']['filter']['name'] = $alias;
            $data = parent::doRequest( $request_data, '1.6.0.2' );
            if (isset($data) && is_array($data) && isset($data['domain_alias']['get']['result'])) {
                if (isset($data['domain_alias']['get']['result']['info']['ascii-name'])) {
                    // Single entry
                    $return[$data['domain_alias']['get']['result']['info']['domain_id']] =
                        $data['domain_alias']['get']['result']['info']['ascii-name'];
                } elseif (isset($data['domain_alias']['get']['result'][0])) {
                    // Multiple entries
                    foreach($data['domain_alias']['get']['result'] as $result) {
                        $return[$result['info']['domain_id']] = $result['info']['ascii-name'];
                    }
                }
            }
        } else {
		    $return = $this->_retrieveAliases(array(
                'name' => $alias,
            ));
        }

        return $return;
	}

	public function getAliasbyDomainId( $domain_id )
	{
		return $this->_retrieveAliases( array('site-id' => $domain_id ) );
	}

	public function getAliasbyDomain( $domain )
	{
        $return = array();

        $panelSupport = new SpamFilter_PanelSupport_Plesk;

        if (version_compare($panelSupport->getVersion(), '10.2', '<')) {
            $request_data['domain_alias']['get']['filter']['domain_name'] = $domain;
            $data = parent::doRequest( $request_data, '1.6.0.2' );
            if (isset($data) && is_array($data) && isset($data['domain_alias']['get']['result'])) {
                if (isset($data['domain_alias']['get']['result']['info']['ascii-name'])) {
                    // Single entry
                    $return[] = $data['domain_alias']['get']['result']['info']['ascii-name'];
                } elseif (isset($data['domain_alias']['get']['result'][0])) {
                    // Multiple entries
                    foreach($data['domain_alias']['get']['result'] as $result) {
                        $return[] = $result['info']['ascii-name'];
                    }
                }
            }
        } else {
		    $return = $this->_retrieveAliases(array(
                'site-name' => $domain,
            ));
        }

        return $return;
	}

	/*
		This is the actual feature that makes the request, and re-formats the data.
	*/
    private function _retrieveAliases($filter = '')
    {
        $return = array();

        $request_data['site-alias']['get']['filter'] = $filter;
        $data                                        = parent::doRequest(
            $request_data,
            '1.6.3.2'
        );

        if (isset($data) && is_array($data) && isset($data['site-alias']['get']['result'])) {
            if (isset($data['site-alias']['get']['result']['info']['ascii-name'],
                    $data['site-alias']['get']['result']['info']['site-id'])) {
                // Single entry
                $return[$data['site-alias']['get']['result']['info']['site-id']]
                    = $data['site-alias']['get']['result']['info']['ascii-name'];
            } elseif (isset($data['site-alias']['get']['result'][0])) {
                // Multiple entries
                foreach ($data['site-alias']['get']['result'] as $result) {
                    $aliasId = (!empty($result['info']['domain_id'])
                        ? $result['info']['domain_id'] : $result['info']['site-id'] * 1000000 + $result['id']);

                    $return[$aliasId] = $result['info']['ascii-name'];
                }
            }
        }

        return $return;
    }
}
