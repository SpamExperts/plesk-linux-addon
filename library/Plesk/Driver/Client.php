<?php

class Plesk_Driver_Client extends Plesk_Driver
{
    private function _getdata($filter = '')
    {
        $request_data['client']['get']['filter'] = $filter;
        $request_data['client']['get']['dataset']['gen_info'] = '';

        return parent::doRequest($request_data);
    }

    public function getClientbyId($client_id)
    {
        return $this->_getdata(array('id' => $client_id));
    }

    public function getClientsbyOwnerId($owner_id)
    {
        return $this->_getdata(array('owner-id' => $owner_id));
    }

    public function getclientbyUser($user)
    {
        return $this->_getdata(array('login' => $user));
    }

    public function getEmailByClientId($client_id)
    {
        $client = $this->getClientById($client_id);
        if (isset($client) && (!empty($client)) && (is_array($client))
            && (isset($client['client']['get']['result']['data']['gen_info']['email']))
        ) {
            return $client['client']['get']['result']['data']['gen_info']['email'];
        }
        // If feature is not suported in Power User mode try this
        if (class_exists('pm_Client') && 'cli' != PHP_SAPI) {
            $cData = pm_Client::getByClientId($client_id);
            $email = $cData->getProperty('email');
            if(isset($email)){
                return $email;
            }
        }
        

        return false;
    }
}
