<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


require_once dirname(__FILE__).'/../libraries/RedisProxy.php';

class Tweet_model extends CI_Model {
    const TWEET_CACHE_SECONDS = 172800;

    private $table_name = 'ci_tweet';
    private $_redis;

	function __construct()
	{
		parent::__construct();
        $this->_redis = RedisProxy::get_instance('db_redis');
	}

    /**
     * 发表帖子
     *
     * @param array 请求参数
     * @return bool 状态
     */
    function add($request) {

        $result = $this->db->insert($this->table_name, $request);
    //    echo $this->db->last_query();
        if($this->db->affected_rows() > 0) {
            $tid = $this->db->insert_id();
            return $tid;
        }
        return false;
    }

    /**
     * 获取帖子列表
     *
     * @param int limit 每页显示条数
     * @param int offset 偏移量
     * @return array 帖子列表
     */
    function get_list($limit, $offset, $condition = array()) {

        if(!empty($condition)) {
            foreach($condition as $key => $value) {
                
                $this->db->where($key, $value);
            }
        }
        $this->db->order_by('ctime', 'desc');

        $result = $this->db->get($this->table_name, $limit, $offset);
        if($result->num_rows > 0) {
            return $result->result_array();
        }
    }

    /**
     * 根据帖子id获取帖子列表，上拉刷新
     *
     * @param string tid 帖子id
     * @param int limit 每页显示条数
     * @param int offset 偏移量
     * @return array 帖子列表
     */
    function get_list_by_tid($tid, $limit, $offset, $condition = array()) {

        $this->db->select('*');
        $this->db->where('tid >', $tid);
        if(!empty($condition)) {
            foreach($condition as $key => $value) {
                
                $this->db->where($key, $value);
            }
        }
        $this->db->limit($limit, $offset);

        $result = $this->db->get($this->table_name); 
        if($result->num_rows > 0) { 
            return $result->result_array();
        }
    }

    /**
     * 获取用户帖子列表
     * 
     * @param string uid 用户id
     * @param int limit 每页显示条数
     * @param int offset 偏移量
     * @return array 帖子列表
     */
    function get_list_by_uid($uid, $limit, $offset) {
    
        $this->db->select('*');
        $this->db->where('uid', $uid);
        $this->db->limit($limit, $offset);

        $result = $this->db->get($this->table_name); 
        if($result->num_rows > 0) {
            return $result->result_array();
        }
    }

    /**
     * 获取用户帖子ID列表
     * 
     * @param string uid 用户id
     * @param int limit 每页显示条数
     * @return array 帖子ID列表
     */
    function get_tid_list_by_uid($uid, $limit) {
    
        $this->db->select('tid');
        $this->db->where('uid', $uid);
        $this->db->where('is_del', 0);
        $this->db->order_by('ctime', 'desc');
        $this->db->limit($limit);

        $result = $this->db->get($this->table_name); 
        if($result->num_rows > 0) {
            return $result->result_array();
        }
    }

    /**
     * 获取更多用户帖子ID列表
     * 
     * @param string uid 用户id
     * @param int limit 每页显示条数
     * @return array 帖子ID列表
     */
    function get_next_tid_list_by_uid($uid, $tid, $limit) {
    
        $this->db->select('tid');
        $this->db->where('uid', $uid);
        $this->db->where('tid <', $tid);
        $this->db->where('is_del', 0);
        $this->db->order_by('ctime', 'desc');
        $this->db->limit($limit);

        $result = $this->db->get($this->table_name); 
        if($result->num_rows > 0) {
            return $result->result_array();
        }

        return array();
    }
    function get_tweet($tid, $fields = '*') {
        $this->db->select($fields); 
        $this->db->from($this->table_name);
        $this->db->where('tid', $tid);
        $this->db->limit(1);
        $result = $this->db->get();
        log_message('error', $this->db->last_query());
        if (false === $result) {
            log_message('error', 'get_tweet error: msg['.$this->db->_error_message().']'); return false; 
        } else if (0 == $result->num_rows) {
            return null; 
        } else {
            return $result->result_array()[0]; 
        }
    }










    /**
     * 根据帖子id获取帖子详情
     *
     * @param int tid 帖子id
     * @return array 帖子详情
         */
        function get_detail_by_tid($tid) {

            $this->db->select('*');
            $this->db->from($this->table_name);
            $this->db->where('tid', $tid);

            $result = $this->db->get();
            if($result->num_rows > 0) {
            return $result->row_array();
        }
    }

    /**
     * 根据帖子id删除帖子
     *
     * @param string tid 帖子id
     * @return bool 状态
     */
    function remove_by_tid($tid) {
    
        $this->db->where('tid', $tid);
        $result = $this->db->delete($this->table_name); 
        return $result;
        if($result->num_rows > 0) {
            return $result;
        }
    }

    /**
     * 根据用户id删除帖子
     *
     * @param string 用户id
     * @return bool 状态
     */
    function remove_by_uid($uid) {
    
        $this->db->where('uid', $uid);
        $result = $this->db->delete($this->table_name); 
        if($result->num_rows > 0) {
            return $result;
        }
    }

    /**
     * 更新帖子
     *
     * @param array 请求参数
     * @return bool 状态
     */
    //function update_by_tid($tid, $uid, $data) {
    function update_by_tid($tid, $data) {

        $this->db->where('tid', $tid);
        //$this->db->where('uid', $uid);
        $result = $this->db->update($this->table_name, $data); 
        log_message('error', 'update_result:'.var_export($result, true));
        if($this->db->affected_rows() > 0) {
            return $result;
        }
        return true;
    }

    /**
     * 更新帖子
     *
     * @param array 请求参数
     * @return bool 状态
     */
    function update_by_tid_uid($tid, $uid, $data) {

        $this->db->where('tid', $tid);
        $this->db->where('uid', $uid);
        $result = $this->db->update($this->table_name, $data); 
        log_message('error', 'update_result:'.var_export($result, true));
        if($this->db->affected_rows() > 0) {
            return $result;
        }
        return true;
    }
    function get_forward_num($tid) {
        $this->db->from($this->table_name); 
        $this->db->where('origin_tid', $tid);
        return $this->db->count_all_results();
    }

    function get_tweet_num($uid) {
        $this->db->where('uid', $uid); 
        $this->db->where('is_del', 0);
        $this->db->from($this->table_name);
        return $this->db->count_all_results();
    }

    /**
     * Redis操作
     */
    function get_tweet_info($tid) {

        goto mysql;
        if (false === $this->_redis) {
            goto mysql; 
        }   
        $redis_key = TWEET_PREFIX.$tid;
        $redis_ret = $this->_redis->hgetall($redis_key); 
        if (!$redis_ret || !isset($redis_ret['tid'])) {
            goto mysql;
        }   
        return $redis_ret;
        mysql:
        $tweet = array();
        $ret = $this->get_tweet($tid);
        if (!$ret) {
            return $ret; 
        }   

        //处理帖子基础信息
        $tweet['tid'] = $ret['tid'];
        $tweet['uid'] = $ret['uid'];
        $tweet['type'] = $ret['type'];
        $tweet['f_catalog'] = $ret['f_catalog'];
        $tweet['s_catalog'] = $ret['s_catalog'];
        $tweet['content'] = $ret['content'];
        $tweet['img'] = $ret['img'];
        $tweet['tags'] = $ret['tags'];
        $tweet['is_del'] = $ret['is_del'];
        $tweet['ctime'] = $ret['ctime'];

        // 获取用户信息
        $this->load->model('Cache_model');
        $user_detail_info = $this->Cache_model->get_user_detail_info($ret['uid'], '*');
        $tweet['avatar'] = isset($user_detail_info['avatar']) ? $user_detail_info['avatar'] : "";
        $tweet['sname'] = isset($user_detail_info['sname']) ? $user_detail_info['sname'] : "";
        $tweet['ukind'] = isset($user_detail_info['ukind']) ? $user_detail_info['ukind'] : 0;

        // 处理点赞
        $this->load->model('Zan_model');
        $zan_num = $this->Zan_model->get_count_by_tid($tid);
        if (false === $zan_num) {
            $zan_num = 0;
        }
        $tweet['zan_num'] = $zan_num;

        // 处理评论
        $this->load->model('Comment_model');
        $comment_num = $this->Comment_model->get_comment_num($tid);
        if (false === $comment_num) {
            $comment_num = 0;
        }
        $tweet['comment_num'] = $comment_num;

        /*
        // 处理转发
        $forward_num = $this->get_forward_num($tid);
        if (false === $forward_num) {
            $forward_num = 0;
        }
        $tweet['forward_num'] = $forward_num;
         */

        if ($this->_redis && NULL === $redis_ret) {
            $ret = $this->_redis->hset($redis_key, $tweet);
            if (false === $ret) {
                log_message('update tweet redis error, tid['.$tid.']');
            }
            $ret = $this->_redis->expire($redis_key, TWEET_CACHE_SECONDS);
            if (false === $ret) {
                log_message('set cache time error, tid['.$tid.']');
            }
        }
        return $tweet;
    }

    function get_tweet_fields($tid, $fields) {
        if (false === $this->_redis) {
            goto mysql;
        }
        $redis_key = TWEET_PREFIX.$tid;
        $ret = $this->_redis->hget($redis_key, $fields);
            log_message('error', 'ret_hget'.json_encode($ret));
        if (!$ret) {
            goto mysql;
        }
        return $ret;
        mysql:
        $tweet = array();
        $ret = $this->get_tweet($tid, $fields);
        if (!$ret) {
            return $ret;
        }
        return $ret;
    }

    function tweet_add($uid) {
        if ($this->_redis) {
            return $this->_redis->hincrby(USER_EXT_PREFIX.$uid, 'tweet_num', 1);
        }
        return false;
    }

    function tweet_cancel($uid) {
        if ($this->_redis) {
            $tweet_num = $this->_redis->hget(USER_EXT_PREFIX.$uid, 'tweet_num');
            if($tweet_num === 0) {
                return 0;
            }
            return $this->_redis->hincrby(USER_EXT_PREFIX.$uid, 'tweet_num', -1);
        }
        return false;
    }

    function tweet_del($tid) {
        if ($this->_redis) {
            $data['is_del'] = 1;
            return $this->_redis->hset(TWEET_PREFIX.$tid, $data);
        }
        return false;
    }

}
