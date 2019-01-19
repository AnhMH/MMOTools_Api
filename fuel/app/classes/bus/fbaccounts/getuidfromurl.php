<?php

namespace Bus;

/**
 * Get token url
 *
 * @package Bus
 * @created 2018-10-01
 * @version 1.0
 * @author AnhMH
 */
class FBAccounts_GetUIDFromUrl extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array(
        'url'
    );

    /** @var array $_length Length of fields */
    protected $_length = array();

    /** @var array $_email_format field email */
    protected $_email_format = array(
        
    );

    /**
     * Call function disable() from model FBAccount
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_Fb_Account::get_uid_from_url($data);
            return $this->result(\Model_Fb_Account::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
