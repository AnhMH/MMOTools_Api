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
class Model_Link extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'type',
        'url',
        'delay_time',
        'created',
        'updated',
        'disable',
        'name',
        'admin_id'
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
    protected static $_table_name = 'links';
    
    /**
     * Get all
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool Detail Slider or false if error
     */
    public static function get_all($param)
    {
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name.'.disable', 0)
        ;
        
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
        
        return array(
            'links' => $data,
            'config' => array(
                'min_time' => 30,
                'max_time' => 60
            )
        );
    }

    /**
     * List Slider
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool Detail Slider or false if error
     */
    public static function get_list($param)
    {
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
        ;
        
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
     * Add update info
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_update($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        $id = !empty($param['id']) ? $param['id'] : 0;
        $time = time();
        $self = array();
        $new = false;
        
        // Check if exist User
        if (!empty($id)) {
            $self = self::find($id);
            if (empty($self)) {
                self::errorNotExist('link_id');
                return false;
            }
        } else {
            $self = new self;
            $new = true;
        }
        
        // Set data
        $self->set('admin_id', $adminId);
        if (!empty($param['type'])) {
            $self->set('type', $param['type']);
        }
        if (!empty($param['name'])) {
            $self->set('name', $param['name']);
        }
        if (!empty($param['url'])) {
            $self->set('url', $param['url']);
        }
        if (!empty($param['delay_time'])) {
            $self->set('delay_time', $param['delay_time']);
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
     * Get detail
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array
     */
    public static function get_detail($param)
    {
        $data = array();
        
        $data = self::find($param['id']);
        
        return $data;
    }
    
    /**
     * Delete
     *
     * @author AnhMH
     * @param array $param Input data
     * @return Int|bool
     */
    public static function del($param)
    {
        $table = self::$_table_name;
        $cond = '';
        if (!empty($param['id'])) {
            $cond .= "id IN ({$param['id']})";
        }
        
        $sql = "DELETE FROM {$table} WHERE {$cond}";
        return DB::query($sql)->execute();
    }
    
    /**
     * Disable
     *
     * @author AnhMH
     * @param array $param Input data
     * @return Int|bool
     */
    public static function disable($param)
    {
        $table = self::$_table_name;
        $cond = '';
        $disable = !empty($param['disable']) ? 1 : 0;
        if (!empty($param['id'])) {
            $cond .= "id IN ({$param['id']})";
        }
        
        $sql = "UPDATE {$table} SET disable = {$disable} WHERE {$cond}";
        return DB::query($sql)->execute();
    }
}
