<?php

use Fuel\Core\DB;

/**
 * Any query in Model Version
 *
 * @package Model
 * @created 2017-10-22
 * @version 1.0
 * @author AnhMH
 */
class Model_Fb_Account extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'email',
        'password',
        'token',
        'name',
        'avatar',
        'disable',
        'created',
        'updated',
        'admin_id',
        'fb_id',
        'is_live'
    );

    protected static $_observers = array(
        'Orm\Observer_CreatedAt' => array(
            'events'          => array('before_insert'),
            'mysql_timestamp' => false,
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events'          => array('before_update'),
            'mysql_timestamp' => false,
        ),
    );

    /** @var array $_table_name name of table */
    protected static $_table_name = 'fb_accounts';
    
    /**
     * Add update info
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_update($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : 0;
        $id = !empty($param['id']) ? $param['id'] : 0;
        $time = time();
        $self = array();
        $new = false;
        
        // Check if exist User
        if (!empty($id)) {
            $self = self::find($id);
            if (empty($self)) {
                self::errorNotExist('fb_account_id');
                return false;
            }
        } else {
            $self = self::find('first', array(
                'where' => array(
                    'token' => $param['token']
                )
            ));
            if (empty($self)) {
                $self = new self;
                $new = true;
            }
        }
        
        // Set data
        $self->set('admin_id', $adminId);
        if (!empty($param['email'])) {
            $self->set('email', $param['email']);
        }
        if (!empty($param['password'])) {
            $self->set('password', $param['password']);
        }
        if (!empty($param['token'])) {
            $self->set('token', $param['token']);
        }
        if (!empty($param['name'])) {
            $self->set('name', $param['name']);
        }
        if (!empty($param['avatar'])) {
            $self->set('avatar', $param['avatar']);
        }
        if (!empty($param['fb_id'])) {
            $self->set('fb_id', $param['fb_id']);
        }
        if (isset($param['disable'])) {
            $self->set('disable', $param['disable']);
        }
        if ($new) {
            $self->set('created', $time);
            $self->set('updated', $time);
        } else {
            $self->set('updated', $time);
        }
        
        // Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
            }
            return $self->id;
        }
        return false;
    }
    
    /**
     * Get list
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_list($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
        ;
                        
        // Filter
        if (isset($param['disable']) && $param['disable'] != '') {
            $disable = !empty($param['disable']) ? 1 : 0;
            $query->where(self::$_table_name.'.disable', $disable);
        }
        
        // Pagination
        if (!empty($param['page']) && $param['limit']) {
            $offset = ($param['page'] - 1) * $param['limit'];
            $query->limit($param['limit'])->offset($offset);
        }
        
        // Sort
        if (!empty($param['sort'])) {
            if (!self::checkSort($param['sort'])) {
                self::errorParamInvalid('sort');
                return false;
            }

            $sortExplode = explode('-', $param['sort']);
            if ($sortExplode[0] == 'created') {
                $sortExplode[0] = self::$_table_name . '.created';
            }
            $query->order_by($sortExplode[0], $sortExplode[1]);
        } else {
            $query->order_by(self::$_table_name . '.created', 'DESC');
        }
        
        // Get data
        $data = $query->execute()->as_array();
        $total = !empty($data) ? DB::count_last_query(self::$slave_db) : 0;
        
        return array(
            'total' => $total,
            'data' => $data
        );
    }
    
    /**
     * Get all
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_all($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
        ;
                        
        // Filter
        if (isset($param['disable']) && $param['disable'] != '') {
            $disable = !empty($param['disable']) ? 1 : 0;
            $query->where(self::$_table_name.'.disable', $disable);
        }
        
        // Pagination
        if (!empty($param['page']) && $param['limit']) {
            $offset = ($param['page'] - 1) * $param['limit'];
            $query->limit($param['limit'])->offset($offset);
        }
        
        // Sort
        if (!empty($param['sort'])) {
            if (!self::checkSort($param['sort'])) {
                self::errorParamInvalid('sort');
                return false;
            }

            $sortExplode = explode('-', $param['sort']);
            if ($sortExplode[0] == 'created') {
                $sortExplode[0] = self::$_table_name . '.created';
            }
            $query->order_by($sortExplode[0], $sortExplode[1]);
        } else {
            $query->order_by(self::$_table_name . '.created', 'DESC');
        }
        
        // Get data
        $data = $query->execute()->as_array();
        
        return $data;
    }
    
    /**
     * Get token url
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_token_url($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        $url = '';
        
        $username = !empty($param['username']) ? $param['username'] : '';
        $password = !empty($param['password']) ? $param['password'] : '';
        $type = !empty($param['app']) ? $param['app'] : 'android';
        
        $url = Lib\AutoFB::getTokenUrl($username, $password, $type);
        
        return $url;
    }
    
    /**
     * Add token
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_token($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : 0;
        $token = !empty($param['token']) ? $param['token'] : '';
        $time = time();
        $self = array();
        
        // Check if exist User
        $self = self::find('first', array(
            'where' => array(
                'token' => $token
            )
        ));
        if (!empty($self)) {
            self::errorDuplicate('token');
            return false;
        }
        $self = new self;
        
        $profile = \Lib\AutoFB::getProfile($token);
        if (!empty($profile['error'])) {
            self::errorOther(self::ERROR_CODE_OTHER_1, 'Token lỗi');
            return false;
        }
        $param['fb_id'] = !empty($profile['id']) ? $profile['id'] : '';
        $param['name'] = !empty($profile['name']) ? $profile['name'] : '';
        $param['email'] = !empty($profile['email']) ? $profile['email'] : '';
        
        // Set data
        $self->set('admin_id', $adminId);
        $self->set('created', $time);
        $self->set('updated', $time);
        $self->set('is_live', 1);
        if (!empty($param['email'])) {
            $self->set('email', $param['email']);
        }
        if (!empty($param['password'])) {
            $self->set('password', $param['password']);
        }
        if (!empty($param['token'])) {
            $self->set('token', $param['token']);
        }
        if (!empty($param['name'])) {
            $self->set('name', $param['name']);
        }
        if (!empty($param['avatar'])) {
            $self->set('avatar', $param['avatar']);
        }
        if (!empty($param['fb_id'])) {
            $self->set('fb_id', $param['fb_id']);
        }
        if (isset($param['disable'])) {
            $self->set('disable', $param['disable']);
        }
        
        // Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
            }
            return $self->id;
        }
        return false;
    }
    
    /**
     * Enable/Disable
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function disable($param)
    {
        $ids = !empty($param['id']) ? $param['id'] : '';
        $table = self::$_table_name;
        $cond = '';
        if (!empty($param['id'])) {
            $cond .= "id IN ({$param['id']})";
        }
        
//        $sql = "DELETE FROM {$table} WHERE {$cond}";
        $sql = "UPDATE {$table} SET disable = 1 WHERE {$cond}";
        return DB::query($sql)->execute();
    }
    
    /**
     * Get uid from url
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_uid_from_url($param)
    {
        $url = !empty($param['url']) ? $param['url'] : '';
        $uId = Lib\AutoFB::getUIDfromUrl($url);
        
        if (empty($uId)) {
            self::errorNotExist('uid');
            return false;
        }
        
        return $uId;
    }
    
    /**
     * Check token live
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function check_live($param)
    {
        $ids = !empty($param['id']) ? explode(',', $param['id']) : 0;
        $addUpdateData = array();
        
        if (empty($ids)) {
            self::errorNotExist('fb_account_id');
            return false;
        }
        
        // Query
        $query = DB::select(
                self::$_table_name.'.token',
                self::$_table_name.'.id'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name.'.id', 'IN', $ids)
        ;
        
        $data = $query->execute()->as_array();
        
        if (!empty($data)) {
            foreach ($data as $val) {
                $check = Lib\AutoFB::getProfile($val['token']);
                $addUpdateData[] = array(
                    'id' => $val['id'],
                    'is_live' => !empty($check['id']) ? 1 : 0,
                    'updated' => time()
                );
            }
            if (!empty($addUpdateData)) {
                self::batchInsert(self::$_table_name, $addUpdateData, array(
                    'id' => DB::expr('VALUES(id)'),
                    'is_live' => DB::expr('VALUES(is_live)'),
                    'updated' => DB::expr('VALUES(updated)')
                ));
            }
        }
    }
}
