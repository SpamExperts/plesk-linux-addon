<?php

class Plesk_Driver_Server extends Plesk_Driver {

    private function _getAdmindata()
    {
        $request_data['server']['get']['admin'] = '';

        return parent::doRequest($request_data);
    }

    public function getAdminEmail()
    {
        $admin = $this->_getAdminData();
        if (isset($admin) && (!empty($admin)) && (is_array($admin))
            && (isset($admin['server']['get']['result']['admin']['admin_email']))
        ) {
            return $admin['server']['get']['result']['admin']['admin_email'];
        }

        return false;
    }
}
