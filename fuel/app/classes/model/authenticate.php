<?php

/**
 * Any query in Model Authenticate.
 *
 * @package Model
 * @version 1.0
 * @author Le Tuan Tu
 * @copyright Oceanize INC
 */
class Model_Authenticate extends Model_Abstract {

    protected static $_properties = array(
        'id',
        'user_id',
        'token',
        'expire_date',
        'regist_type',
        'created',
        'vip_type'
    );
    
    protected static $_observers = array(
        'Orm\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => false,
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events' => array('before_update'),
            'mysql_timestamp' => false,
        ),
    );
    
    protected static $_table_name = 'authenticates';
    
    /**
     * Check token.
     *
     * @author Le Tuan Tu
     * @param array $param Input data.	 
     * @return bool|array Returns the boolean or the array.	
     */
    public static function check_token() {
        $param = array(
            'user_id' => \Lib\Util::authUserId(),
            'token' => \Lib\Util::authToken(),
        );
        $query = DB::select(
                        'id', 'user_id', 'vip_type', 'token', 'expire_date', 'regist_type', 'created', DB::expr("UNIX_TIMESTAMP() AS systime")
                )
                ->from(self::$_table_name)
                ->where('user_id', '=', $param['user_id'])
                ->where('token', '=', $param['token'])
                ->limit(1);
        
        $data = $query->execute()->as_array();
        $data = !empty($data[0]) ? $data[0] : array();
        
        if (empty($data)) {
            self::errorNotExist('token');
            return false;
        }
        if ($data['expire_date'] < $data['systime']) {
            self::errorOther(self::ERROR_CODE_AUTH_ERROR, 'token');
            return false;
        }
        
        return $data;
    }

    /**
     * Addupdate info for authenticates.
     *
     * @author diennvt
     * @param array $param Input data.
     * @return string Returns the string of token.
     */
    public static function addupdate($param) {
        if (empty($param['user_id']) || empty($param['regist_type'])) {
            self::errorParamInvalid('user_id_or_regist_type');
            return false;
        }
        $token = '';
        $vipType = 0;
        $query = DB::select(
                    'id', 
                    'user_id', 
                    'token', 
                    'expire_date', 
                    'regist_type', 
                    'created', 
                    'vip_type',
                    DB::expr("UNIX_TIMESTAMP() AS systime")
                )
                ->from(self::$_table_name)
                ->where('user_id', '=', $param['user_id'])
                ->where('regist_type', '=', $param['regist_type'])
                ->limit(1);
        $authenticate = $query->execute()->offsetGet(0);   
        if ($param['regist_type'] == 'admin') {
            $admin = Model_Admin::find($param['user_id']);
            $vipType = !empty($admin['type']) ? $admin['type'] : 0;
        }
        if (empty($authenticate['id'])) {
            \LogLib::info('Create new token', __METHOD__, $param);
            $token = \Lib\Str::generate_token_for_api();
            $auth = new self;
            $auth->set('user_id', $param['user_id']);
            $auth->set('regist_type', $param['regist_type']);
            $auth->set('token', $token);
            $auth->set('vip_type', $vipType);
            $auth->set('expire_date', \Config::get('api_token_expire'));
            if (!$auth->create()) {
                \LogLib::warning('Can not create token', __METHOD__, $param);
            }
        } else {
            $auth = new self($authenticate, false);
            $auth->set('expire_date', \Config::get('api_token_expire'));
            $auth->set('vip_type', $vipType);
            $token = $authenticate['token'];
            if ($authenticate['expire_date'] < $authenticate['systime']) {
                \LogLib::info('Update new token', __METHOD__, $param);
                $token = \Lib\Str::generate_token_for_api();
                $auth->set('token', $token);
            }
            if (!$auth->update()) {
                \LogLib::warning('Can not update token', __METHOD__, $param);
            }
        }
        return $token;
    }
}
