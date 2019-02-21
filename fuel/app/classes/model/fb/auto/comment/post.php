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
class Model_Fb_Auto_Comment_Post extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'admin_id',
        'fb_account_id',
        'fb_auto_comment_id',
        'content',
        'status',
        'created',
        'updated',
        'fb_post_id',
        'time_start'
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
     * Get posts
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_posts()
    {
        $data = array();
        $fbAccounts = Model_Fb_Auto_Setting::get_all(array(
            'type' => 1
        ));
        if (!empty($fbAccounts)) {
            foreach ($fbAccounts as $acc) {
                $token = $acc['token'];
                $posts = Lib\AutoFB::getHomePosts($token);
                if (!empty($posts)) {
                    foreach ($posts as $p) {
                        $tmp = array(
                            'post_id' => !empty($p['id']) ? $p['id'] : '',
                            'message' => !empty($p['message']) ? $p['message'] : '',
                            'picture' => !empty($p['picture']) ? $p['picture'] : '',
                            'name' => !empty($p['name']) ? $p['name'] : '',
                            'token' => $token,
                            'fb_account_id' => $acc['id'],
                            'is_like' => 0,
                            'type' => !empty($acc['reaction_type']) ? $acc['reaction_type'] : 'LIKE',
                            'created' => time()
                        );
                        $data[] = $tmp;
                    } 
                }
            }
            if (!empty($data)) {
                self::batchInsert('fb_auto_like_feed', $data, array(
                    'post_id' => DB::expr('VALUES(post_id)'),
                    'message' => DB::expr('VALUES(message)'),
                    'picture' => DB::expr('VALUES(picture)'),
                    'name' => DB::expr('VALUES(name)'),
                    'token' => DB::expr('VALUES(token)'),
                    'fb_account_id' => DB::expr('VALUES(fb_account_id)'),
                ));
            }
        }
    }
    
    /**
     * Auto like posts
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function auto_like()
    {
        $addUpdateData = array();
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name.'.is_like', 0)
            ->group_by('fb_account_id')
        ;
        
        // Get data
        $data = $query->execute()->as_array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $postId = $val['post_id'];
                $token = $val['token'];
                $type = $val['type'];
                $reaction = Lib\AutoFB::autoReaction($postId, $token, $type);
                if (!empty($reaction['success'])) {
                    $tmp = array(
                        'is_like' => 1,
                        'post_id' => $postId,
                        'fb_account_id' => $val['fb_account_id']
                    );
                    $addUpdateData[] = $tmp;
                }
            }
            if (!empty($addUpdateData)) {
                self::batchInsert('fb_auto_like_feed', $addUpdateData, array(
                    'post_id' => DB::expr('VALUES(post_id)'),
                    'fb_account_id' => DB::expr('VALUES(fb_account_id)'),
                    'is_like' => DB::expr('VALUES(is_like)'),
                ));
            }
        }
        return $data;
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
                self::$_table_name.'.*',
                array('fb_pages.fb_id', 'fb_account_fb_id'),
                array('fb_pages.name', 'fb_account_name')
            )
            ->from(self::$_table_name)
            ->join('fb_pages', 'LEFT')
            ->on('fb_pages.id', '=', self::$_table_name.'.fb_account_id')
        ;
                        
        // Filter
        if (isset($param['disable']) && $param['disable'] != '') {
            $disable = !empty($param['disable']) ? 1 : 0;
            $query->where(self::$_table_name.'.disable', $disable);
        }
        if (!empty($param['fb_auto_comment_id'])) {
            $query->where(self::$_table_name.'.fb_auto_comment_id', $param['fb_auto_comment_id']);
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
                array('fb_pages.page_token', 'token')
            )
            ->from(self::$_table_name)
            ->join('fb_pages')
            ->on('fb_pages.id', '=', self::$_table_name.'.fb_account_id')
            ->where(self::$_table_name.'.status', 0)
            ->where(self::$_table_name.'.time_start', '<=', $time)
        ;
        
        // Get data
        $data = $query->execute()->as_array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $postId = $val['fb_post_id'];
                $token = $val['token'];
                $content = $val['content'];
                $result = Lib\AutoFB::autoComment($postId, $token, $content);
                if (!empty($result['id'])) {
                    $tmp = array(
                        'status' => 1,
                        'id' => $val['id']
                    );
                } else {
                    $tmp = array(
                        'status' => 2,
                        'id' => $val['id']
                    );
                }
                $addUpdateData[] = $tmp;
            }
            if (!empty($addUpdateData)) {
                self::batchInsert(self::$_table_name, $addUpdateData, array(
                    'id' => DB::expr('VALUES(id)'),
                    'status' => DB::expr('VALUES(status)')
                ));
            }
            return $addUpdateData;
        }
        return true;
    }
    
    /**
     * Auto comment posts
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function set_page_comment()
    {
        $autoComments = Model_Fb_Auto_Comment::get_all(array(
            'type' => 2
        ));
        if (!empty($autoComments)) {
            $fbAccount = Model_Fb_Account::find('first', array(
                'where' => array(
                    'is_live' => 1
                )
            ));
            if (!empty($fbAccount['token'])) {
                $token = $fbAccount['token'];
                $addUpdateData = array();
                $fbPages = Model_Fb_Page::get_all(array(
                    'disable' => 0,
                    'limit' => 50,
                    'page' => 1,
                    'random' => true
                ));
                if (empty($fbPages)) {
                    return false;
                }
                foreach ($autoComments as $ac) {
                    $pageId = $ac['fb_id'];
                    $time = time();
                    $timeStart = $time;
                    $timeRepeat = $ac['time_repeat'];
                    $totalComment = $ac['total_comment'];
                    $getPost = Lib\AutoFB::getPostByPageId($pageId, $token, 1);
                    $fbPostId = !empty($getPost[0]['id']) ? $getPost[0]['id'] : '';
                    $content = explode("\n", $ac['content']);
                    $count = 1;
                    foreach ($fbPages as $val) {
                        if ($count > $totalComment) {
                            break;
                        }
                        $timeStart += $timeRepeat;
                        $addUpdateData[] = array(
                            'fb_account_id' => $val['id'],
                            'content' => $content[array_rand($content)],
                            'fb_auto_comment_id' => $ac['id'],
                            'admin_id' => $ac['admin_id'],
                            'created' => $time,
                            'updated' => $time,
                            'fb_post_id' => $fbPostId,
                            'time_start' => $timeStart
                        );
                        $count++;
                    }
                }
                if (!empty($addUpdateData)) {
                    self::batchInsert('fb_auto_comment_posts', $addUpdateData, array());
                }
            }
        }
        return true;
    }
}
