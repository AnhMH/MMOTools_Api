<?php

namespace Bus;

/**
 * Disable AutoComment
 *
 * @package Bus
 * @created 2018-10-01
 * @version 1.0
 * @author AnhMH
 */
class AutoComments_AddUpdateMulti extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array();

    /** @var array $_length Length of fields */
    protected $_length = array();

    /** @var array $_email_format field email */
    protected $_email_format = array(
        
    );

    /**
     * Call function disable() from model AutoComment
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_Fb_Auto_Comment::add_update_multi($data);
            return $this->result(\Model_Fb_Auto_Comment::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
