<?php

namespace Bus;

/**
 * List Order
 *
 * @package Bus
 * @created 2018-10-01
 * @version 1.0
 * @author AnhMH
 */
class Orders_List extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array();

    /** @var array $_length Length of fields */
    protected $_length = array();

    /** @var array $_email_format field email */
    protected $_email_format = array(
        
    );

    /**
     * Call function get_list() from model Order
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_Order::get_list($data);
            return $this->result(\Model_Order::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
