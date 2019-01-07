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
class Model_Fb_Auto_Comment extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'fb_id', // id bai viet
        'fb_account_id',
        'content',
        'is_repeat',
        'is_comment',
        'time_repeat',
        'created',
        'admin_id',
        'total_comment',
        'type',
        'updated'
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
    protected static $_table_name = 'fb_auto_comment_posts';
    
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
                self::errorNotExist('fb_auto_comment_id');
                return false;
            }
        } else {
            $check = self::find('first', array(
                'where' => array(
                    'fb_account_id' => $param['fb_account_id'],
                    'fb_id' => $param['fb_postid']
                )
            ));
            if (!empty($check)) {
                self::errorDuplicate('fb_id');
                return false;
            }
            $self = new self;
            $new = true;
        }
        
        // Set data
        $self->set('admin_id', $adminId);
        $self->set('updated', $time);
        if (!empty($param['fb_postid'])) {
            $self->set('fb_id', $param['fb_postid']);
        }
        if (!empty($param['fb_account_id'])) {
            $self->set('fb_account_id', $param['fb_account_id']);
        }
        if (isset($param['is_repeat'])) {
            $self->set('is_repeat', $param['is_repeat']);
        }
        if (!empty($param['time_repeat'])) {
            $self->set('time_repeat', $param['time_repeat']);
        }
        if (!empty($param['content'])) {
            $self->set('content', $param['content']);
        }
        if (!empty($param['type'])) {
            $self->set('type', $param['type']);
        }
        if ($new) {
            $self->set('created', $time);
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
                self::$_table_name.'.*',
                array('fb_accounts.fb_id', 'fb_account_fb_id'),
                array('fb_accounts.name', 'fb_account_name')
            )
            ->from(self::$_table_name)
            ->join('fb_accounts')
            ->on('fb_accounts.id', '=', self::$_table_name.'.fb_account_id')
        ;
        
        // Filter
        if (!empty($param['fb_account_id'])) {
            $query->where(self::$_table_name.'.fb_account_id', $param['fb_account_id']);
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
            $query->order_by(self::$_table_name . '.id', 'DESC');
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
        
        $sql = "DELETE FROM {$table} WHERE {$cond}";
        return DB::query($sql)->execute();
    }
    
    /**
     * Get detail
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_detail($param)
    {
        $id = !empty($param['id']) ? $param['id'] : 0;
        $data = self::find($id);
        if (empty($data)) {
            self::errorNotExist('auto_comment_id');
            return false;
        }
        
        return $data;
    }
    
    /**
     * Auto comment posts
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function auto_comment()
    {
        $addUpdateData = array();
        $time = time();
        // Query
        $query = DB::select(
                self::$_table_name.'.*',
                'fb_accounts.token'
            )
            ->from(self::$_table_name)
            ->join('fb_accounts')
            ->on('fb_accounts.id', '=', self::$_table_name.'.fb_account_id')
            ->where_open()
                ->where(self::$_table_name.'.is_comment', 0)
                ->or_where(self::$_table_name.'.is_comment', 'is', null)
                ->or_where_open()
                    ->where(self::$_table_name.'.is_comment', 1)
                    ->where(self::$_table_name.'.is_repeat', 1)
                    ->where(DB::expr(self::$_table_name.".updated + ".self::$_table_name.".time_repeat"), '<=', $time)
                ->or_where_close()
            ->where_close()
        ;
        
        // Get data
        $data = $query->execute()->as_array();
        
        if (!empty($data)) {
            foreach ($data as $val) {
                $token = $val['token'];
                $content = explode("\n", $val['content']);
                $message = $content[array_rand($content)];
                $totalComment = !empty($val['total_comment']) ? $val['total_comment'] : 0;
                $postId = $val['fb_id'];
                
                if ($val['type'] == 2) { // page
                    $postData = Lib\AutoFB::getPostByUserId($postId, $token, 1);
                    $postId = !empty($postData[0]['id']) ? $postData[0]['id'] : '';
                } elseif ($val['type'] == 3) { // profile
                    $postData = Lib\AutoFB::getPostByUserId($postId, $token, 1);
                    $postId = !empty($postData[0]['id']) ? $postData[0]['id'] : '';
                }
                if (empty($postId)) {
                    continue;
                }
                $auto = Lib\AutoFB::autoComment($postId, $token, $message);
                if (!empty($auto['success']) || !empty($auto['id'])) {
                    $tmp = array(
                        'is_comment' => 1,
                        'id' => $val['id'],
                        'updated' => $time,
                        'total_comment' => $totalComment + 1
                    );
                    $addUpdateData[] = $tmp;
                }
            }
            if (!empty($addUpdateData)) {
                self::batchInsert(self::$_table_name, $addUpdateData, array(
                    'id' => DB::expr('VALUES(id)'),
                    'is_comment' => DB::expr('VALUES(is_comment)'),
                    'updated' => DB::expr('VALUES(updated)'),
                    'total_comment' => DB::expr('VALUES(total_comment)')
                ));
            }
        }
        return $data;
    }
}
