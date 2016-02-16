<?php

class Plesk_Driver_Reseller extends Plesk_Driver
{
    private function _getdata($filter = '')
    {
        $request_data['reseller']['get']['filter'] = $filter;
        $request_data['reseller']['get']['dataset']['gen-info'] = '';

        return parent::doRequest($request_data);
    }

    public function getResellerbyId($reseller_id)
    {
        return $this->_getdata(array('id' => $reseller_id));
    }

    public function getEmailByResellerId($reseller_id)
    {
        $reseller = $this->getResellerbyId($reseller_id);
        if (isset($reseller) && (!empty($reseller)) && (is_array($reseller))
            && (isset($reseller['reseller']['get']['result']['data']['gen-info']['email']))
        ) {
            return $reseller['reseller']['get']['result']['data']['gen-info']['email'];
        }

        return false;
    }
}
