<?php

require_once dirname(__FILE__).'/../libraries/RedisProxy.php';

class Cache_Model extends CI_Model {

    /*
    const TWEET_PREFIX = 'tweet_';
    const USER_PREFIX = 'user_';
    const USER_INDUSTRY_PREFIX = 'user_industry_';
    const USER_EXT_PREFIX = 'userext_';
    const TWEET_CACHE_SECONDS = 172800;
    const USER_EXT_CACHE_SECONDS = 172800;
     */

    private $_redis;

    function __construct() {
        parent::__construct();
        $this->_redis = RedisProxy::get_instance('cache_redis');
    }

    function get_user_info($uid, $fields) {

        if (false === $this->_redis) {
            goto mysql; 
        }
        $redis_key = USER_PREFIX.$uid;
        if ('*' === $fields) {
            $ret = $this->_redis->hgetall($redis_key); 
        } else {
            $ret = $this->_redis->hget($redis_key, $fields);    
        }
        if (false === $ret) {
            goto mysql; 
        }
        return $ret;
    mysql:
        $this->load->model('user_model');    
        $this->load->model('user_detail_model');    
        if (is_array($fields)) {
            $fields_str = join(',', $fields); 
        } else {
            $fields_str = $fields;
        }
        $ret = $this->user_model->get_user_info($uid, $fields_str);
        if (false === $ret) {
            return false; 
        } else if (NULL === $ret) {
            if (is_array($fields)) {
                $res = array();
                foreach ($fields as $f) {
                    $res[$f] = null; 
                }
                return $res; 
            } else {
                return NULL; 
            } 
        } else {
            $user_more_ret = $this->user_detail_model->get_user_more_info($uid);
            if ($user_more_ret) {
                $ret = $ret + $user_more_ret; 
            }
            if ('*' === $fields || is_array($fields)) {
                return $ret;
            } else {
                return $ret[$fields]; 
            }
        }
    }

    function get_user_detail_info($uid, $fields, $avatar_type = 2) {
        $user_detail_info = false;

        // get mysql failed
        if (false === $this->_redis) {
            goto mysql; 
        }
        $redis_key = USER_DETAIL_PREFIX.$uid;
        if ('*' === $fields) {
            $user_detail_info = $this->_redis->hgetall($redis_key); 
        } else {
            $user_detail_info = $this->_redis->hget($redis_key, $fields);    
        }
        if (!$user_detail_info) {
            goto mysql; 
        }

        goto user_detail_end;

    mysql:
        $str_fields = "";

        $this->load->model('User_detail_model');
        $user_detail_info = $this->User_detail_model->get_info_by_uid($uid, $str_fields);
        if (!$user_detail_info) {
            return false;
        }

    user_detail_end:
        switch ($avatar_type) {
        case 0:
            $arr_img_info = json_decode($user_detail_info['avatar'], true);
            if ($arr_img_info && isset($arr_img_info['s']) && isset($arr_img_info['s']['url'])) {
                $user_detail_info['avatar'] = $arr_img_info['s']['url'];
            } else  {
                $user_detail_info['avatar'] = "";
            }
            break;
        case 1:
            $arr_img_info = json_decode($user_detail_info['avatar'], true);
            if ($arr_img_info && isset($arr_img_info['n']) && isset($arr_img_info['n']['url'])) {
                $user_detail_info['avatar'] = $arr_img_info['n']['url'];
            } else {
                $user_detail_info['avatar'] ="";
            }
            break;
        }

        return $user_detail_info;
    }

    function get_user_industry ($uid) {
        if (false === $this->_redis) {
            goto mysql; 
        } 

        $redis_key_industry = USER_INDUSTRY_PREFIX . $uid;  
        $ret = $this->_redis->zrange($redis_key_industry, 0, -1);
        if(false === $ret) {
            goto mysql;
        }
        //test
        if(is_null($ret)) {
            goto mysql;
        }
        if(empty($ret)) {
            goto mysql;
        }
        $ret_str = implode(',', $ret);
        return $ret_str;
        //return $ret;
        mysql:
            $this->load->model('User_industry_model');
        $ret = $this->User_industry_model->get_user_industry($uid);

        if (!$ret) {
            return ''; 
        }
        foreach($ret as $indus_ret) {
            $indus[] = $indus_ret['industry_id'];
        }
        $ret_str = implode(',', $indus);
        return $ret_str;

    }

    function get_user_ext_info ($uid) {
        if (false === $this->_redis) {
            goto mysql; 
        } 
        $redis_key = USER_EXT_PREFIX.$uid;
        $redis_ret = $this->_redis->hgetall($redis_key);
        if (!$redis_ret 
            || !isset($redis_ret['follower_num']) 
            || !isset($redis_ret['followee_num'])
            || !isset($redis_ret['tweet_num'])) {
                goto mysql; 
            }
        return $redis_ret;
        mysql:
            $ext_info = array();
        $this->load->model('relation_model');
        $ret = $this->relation_model->get_follower_num($uid);
        if ($ret) {
            $ext_info['follower_num'] = $ret;
        } else {
            $ext_info['follower_num'] = 0; 
        }
        $ret = $this->relation_model->get_followee_num($uid);
        if ($ret) {
            $ext_info['followee_num'] = $ret; 
        } else {
            $ext_info['followee_num'] = 0; 
        }
/*        $this->load->model('Community_model');
$ret = $this->Community_model->get_tweet_num($uid);*/
        $ret = false;
        if ($ret) {
            $ext_info['tweet_num'] = $ret; 
        } else {
            $ext_info['tweet_num'] = 0; 
        }
        if ($this->_redis) {
            $ret = $this->_redis->hset($redis_key, $ext_info); 
            if (false === $ret) {
                log_message('error', 'update user_ext redis error, uid['.$uid.']'); 
            }
            $ret = $this->_redis->expire($redis_key, USER_EXT_CACHE_SECONDS);
            if (false === $ret) {
                log_message('error', 'set cache time error, uid['.$uid.']'); 
            }
        }
        return $ext_info;
    }


    function get_tweet_info($tid) {
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
            $this->load->model('Community_model');
        $tweet = array();
        $ret = $this->Community_model->get_tweet($tid);
        if (!$ret) {
            return $ret; 
        }
        //处理帖子基础信息
        $tweet['tid'] = $ret['tid'];
        $tweet['uid'] = $ret['uid'];
        $tweet['title'] = $ret['title'];
        $tweet['content'] = $ret['content'];
        $tweet['img'] = $ret['img'];
        $tweet['industry'] = $ret['industry'];
        $tweet['is_del'] = $ret['is_del'];
        $tweet['ctime'] = $ret['ctime'];
        $tweet['origin_tid'] = $ret['origin_tid'];

        //处理原帖数据
        /*
        $origin_tid = @$ret['origin_tid'];
        if (!empty($origin_tid)) {
            $origin_tweet = $this->Community_model->get_tweet($origin_tid);
            //echo 'origin_tweet'.json_encode($origin_tweet);
            //exit;
            if ($origin_tweet) {
                $tweet['origin_tid'] = $origin_tid;
                $tweet['origin_title'] = $origin_tweet['title'];
                $tweet['origin_content'] = $origin_tweet['content'];
                $tweet['origin_img'] = $origin_tweet['img'];
                $tweet['origin_uid'] = $origin_tweet['uid'];
                $tweet['origin_is_del'] = $origin_tweet['is_del'];
                $ret = $this->get_user_info($tweet['origin_uid'], 'sname');
                if (false !== $ret) {
                    $tweet['origin_uname'] = $ret; 
                }
            }
        }
         */

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

        // 处理转发
        $forward_num = $this->Community_model->get_forward_num($tid);
        if (false === $forward_num) {
            $forward_num = 0; 
        }
        $tweet['forward_num'] = $forward_num;

        if ($this->_redis && NULL === $redis_ret) {
            $ret = $this->_redis->hset($redis_key, $tweet); 
            if (false === $ret) {
                log_message('error', 'update tweet redis error, tid['.$tid.']'); 
            }
            $ret = $this->_redis->expire($redis_key, TWEET_CACHE_SECONDS);
            if (false === $ret) {
                log_message('error', 'set cache time error, tid['.$tid.']'); 
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
        if (!$ret) {
            goto mysql;
        }
        return $ret;
        mysql:
            $this->load->model('Community_model');
        $tweet = array();
        $ret = $this->Community_model->get_tweet($tid, $fields);
        if (!$ret) {
            return $ret; 
        }
        return $ret;
    }


    function zan_add($tid) {
        if ($this->_redis) {
            return $this->_redis->hincrby(TWEET_PREFIX.$tid, 'zan_num', 1); 
        }
        return false;
    } 

    function zan_cancel($tid) {
        if ($this->_redis) {
            $zan_num = $this->_redis->hincrby(TWEET_PREFIX.$tid, 'zan_num', -1);
            if($zan_num < 0) {
                return $this->_redis->hset(TWEET_PREFIX.$tid, array('zan_num' => 0)); 
            }else {
                return $zan_num;
            }
        }
        return false;
    }

    function comment_add($tid) {
        if ($this->_redis) {
            return $this->_redis->hincrby(TWEET_PREFIX.$tid, 'comment_num', 1); 
        }
        return false;
    }

    function comment_cancel($tid) {
        if ($this->_redis) {
            $comment_num = $this->_redis->hincrby(TWEET_PREFIX.$tid, 'comment_num', -1);
            if($comment_num < 0) {
                return $this->_redis->hset(TWEET_PREFIX.$tid, array('comment_num' => 0)); 
            }else {
                return $comment_num;
            }
        }
        return false;
    }
    function forward_add($tid) {
        if ($this->_redis) {
            return $this->_redis->hincrby(TWEET_PREFIX.$tid, 'forward_num', 1); 
        }
        return false;
    }

    function add_follow($follower_uid, $followee_uid) {
        if ($this->_redis) {
            $this->_redis->hincrby(USER_EXT_PREFIX.$follower_uid, 'followee_num', 1); 
            $this->_redis->hincrby(USER_EXT_PREFIX.$followee_uid, 'follower_num', 1);
        }    
    }

    function cancel_follow($follower_uid, $followee_uid) {
        if ($this->_redis) {
            $follower_num = $this->_redis->hincrby(USER_EXT_PREFIX.$followee_uid, 'follower_num', -1);
            if($follower_num < 0) {
                $this->_redis->hset(USER_EXT_PREFIX.$followee_uid, array('follower_num' => 0)); 
            }
            $followee_num = $this->_redis->hincrby(USER_EXT_PREFIX.$follower_uid, 'followee_num', -1);
            if($followee_num < 0) {
                $this->_redis->hset(USER_EXT_PREFIX.$follower_uid, array('followee_num' => 0));
            }
        }    
    }

    function tweet_add($uid) {
        if ($this->_redis) {
            return $this->_redis->hincrby(USER_EXT_PREFIX.$uid, 'tweet_num', 1); 
        } 
        return false;
    }

    function tweet_cancel($uid) {
        if ($this->_redis) {
            $tweet_num = $this->_redis->hincrby(USER_EXT_PREFIX.$uid, 'tweet_num', -1);
            if($tweet_num < 0) {
                return $this->_redis->hset(USER_EXT_PREFIX.$uid, array('tweet_num' => 0)); 
            } else {
                return $tweet_num;
            }
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

    function get_online_rec($uid) {
        if ($this->_redis) {
            return $this->_redis->get(K_ONLINE_REC.$uid); 
        } 
        return false;
    }

    function set_online_rec($uid, $result) {
        if ($this->_redis) {
            return $this->_redis->set(K_ONLINE_REC.$uid, json_encode($result), 60); 
        } 
        return false;
    }
}
