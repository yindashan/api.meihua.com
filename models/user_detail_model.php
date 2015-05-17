<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once dirname(__FILE__).'/../libraries/RedisProxy.php';

/**
 *  用户详情信息model
 */
class User_detail_model extends CI_Model {

    private $table_name = 'ci_user_detail';
	function __construct()
	{
        parent::__construct();
        $this->_redis = RedisProxy::get_instance('cache_redis');
	}

    /**
     * 是否是单边关注 
     *
     * @param uid user_id
     * @return false:错误 NULL:空 arr:row 
     */
    function get_info_by_uid($uid, $fields = '*') {
        $this->db->select($fields);
        $this->db->where('uid', $uid);
        $result = $this->db->get($this->table_name);
        // 获取数据库信息失败
        if (false === $result) {
            return false;
        }
        // 查询无结果
        if (0 === $result->num_rows) {
            return NULL;
        }

        $arr_res = $result->result_array()[0];

        $redis_key = USER_DETAIL_PREFIX.$uid;

        if ($this->_redis) {
            $ret = $this->_redis->hset($redis_key, $arr_res);
            if (false === $ret) {
                log_message('error', __FILE__.':'.__LINE__.' update user_detail redis error, uid='.$uid);
                return $arr_res;
            }
        }

        return $arr_res;

    }

    function add($query) {
        $result = $this->db->insert($this->table_name, $query);
        if (false === $result) {
            return false;
        }
        if (0 < $this->db->affected_rows()) {
            return true;
        }
        return NULL;
    }
}
