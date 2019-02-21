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
        'admin_id',
        'fb_id',
        'type',
        'title',
        'content',
        'time_start',
        'time_end',
        'total_comment',
        'created',
        'updated',
        'time_repeat'
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
    protected static $_table_name = 'fb_auto_comments';
    
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
        $type = !empty($param['type']) ? $param['type'] : 1;
        $totalComment = !empty($param['total_comment']) ? $param['total_comment'] : 5;
        $content = !empty($param['content']) ? $param['content'] : '';
        $timeRepeat = !empty($param['time_repeat']) ? $param['time_repeat'] : 5*60;
        $fbPostId = !empty($param['fb_postid']) ? $param['fb_postid'] : '';
        $time = time();
        $self = array();
        $new = false;
        
        if (!empty($id)) {
            $self = self::find($id);
            if (empty($self)) {
                self::errorNotExist('id');
                return false;
            }
        } else {
            $self = new self;
            $new = true;
        }
        
        // Set data
        $self->set('admin_id', $adminId);
        $self->set('updated', $time);
        $self->set('type', $type);
        $self->set('total_comment', $totalComment);
        $self->set('content', $content);
        $self->set('fb_id', $fbPostId);
        if (isset($param['time_start'])) {
            $self->set('time_start', $param['time_start']);
        }
        if (isset($param['time_end'])) {
            $self->set('time_end', $param['time_end']);
        }
        if (!empty($param['title'])) {
            $self->set('title', $param['title']);
        }
        if (!empty($param['time_repeat'])) {
            $self->set('time_repeat', $param['time_repeat']);
        }
        if ($new) {
            $self->set('created', $time);
        }
        
        // Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
            }
            if (!empty($param['add_comment_post'])) {
                $content = explode("\n", $content);
                $fbPages = Model_Fb_Page::get_all(array(
                    'disable' => 0,
                    'limit' => $totalComment,
                    'page' => 1
                ));
                if (!empty($fbPages)) {
                    $posts = array();
                    $timeStart = $time;
                    if ($type == 2) { //auto page
                        $fbAccount = Model_Fb_Account::find('first', array(
                            'where' => array(
                                'is_live' => 1
                            )
                        ));
                        if (!empty($fbAccount['token'])) {
                            $getPost = Lib\AutoFB::getPostByPageId($fbPostId, $fbAccount['token'], 1);
                            $fbPostId = !empty($getPost[0]['id']) ? $getPost[0]['id'] : '';
                        }
                    }
                    foreach ($fbPages as $val) {
                        $timeStart += $timeRepeat;
                        $posts[] = array(
                            'fb_account_id' => $val['id'],
                            'content' => $content[array_rand($content)],
                            'fb_auto_comment_id' => $self->id,
                            'admin_id' => $adminId,
                            'created' => $time,
                            'updated' => $time,
                            'fb_post_id' => $fbPostId,
                            'time_start' => $timeStart
                        );
                    }
                    if (!empty($posts)) {
                        self::batchInsert('fb_auto_comment_posts', $posts, array());
                    }
                }
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
        if (!empty($param['type'])) {
            $query->where(self::$_table_name.'.type', $param['type']);
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
        $total = 0;//!empty($data) ? DB::count_last_query(self::$slave_db) : 0;
        
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
        if (!empty($param['type'])) {
            $query->where(self::$_table_name.'.type', $param['type']);
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
        
        $sql = "DELETE FROM {$table} WHERE {$cond}; DELETE FROM fb_auto_comment_posts WHERE fb_auto_comment_id IN ({$param['id']});";
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
        
        $data['posts'] = Model_Fb_Auto_Comment_Post::get_all(array(
            'fb_auto_comment_id' => $id
        ));
        
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
    
    /**
     * Add update info
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_update_multi($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : 0;
        $time = time();
        $addUpdateData = array();
        $fbAccounts = !empty($param['fb_account_id']) ? explode(',', $param['fb_account_id']) : '';
        $type = !empty($param['type']) ? $param['type'] : '';
        $fbId = !empty($param['fb_postid']) ? $param['fb_postid'] : '';
        $content = !empty($param['content']) ? $param['content'] : '';
        $isRepeat = !empty($param['is_repeat']) ? $param['is_repeat'] : 0;
        $timeRepeat = !empty($param['time_repeat']) ? $param['time_repeat'] : 0;
        
        if (!empty($fbAccounts)) {
            foreach ($fbAccounts as $v) {
                $addUpdateData[] = array(
                    'admin_id' => $adminId,
                    'fb_account_id' => $v,
                    'type' => $type,
                    'fb_id' => $fbId,
                    'content' => $content,
                    'is_repeat' => $isRepeat,
                    'time_repeat' => $timeRepeat,
                    'created' => $time,
                    'updated' => $time
                );
            }
            if (!empty($addUpdateData)) {
                self::batchInsert(self::$_table_name, $addUpdateData, array(
                    'fb_account_id' => DB::expr('VALUES(fb_account_id)'),
                    'fb_id' => DB::expr('VALUES(fb_id)'),
                    'type' => DB::expr('VALUES(type)'),
                    'content' => DB::expr('VALUES(content)'),
                    'is_repeat' => DB::expr('VALUES(is_repeat)'),
                    'time_repeat' => DB::expr('VALUES(time_repeat)'),
                    'updated' => DB::expr('VALUES(updated)'),
                ));
            }
        }
        return true;
    }
}
