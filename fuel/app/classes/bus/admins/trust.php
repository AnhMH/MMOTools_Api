<?php

namespace Bus;

/**
 * Confirm Admin
 *
 * @package Bus
 * @created 2018-10-01
 * @version 1.0
 * @author AnhMH
 */
class Admins_Trust extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array(
        'id'
    );

    /** @var array $_length Length of fields */
    protected $_length = array();

    /** @var array $_email_format field email */
    protected $_email_format = array(
        
    );

    /**
     * Call function disable() from model Admin
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_Admin::is_trust($data);
            return $this->result(\Model_Admin::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
