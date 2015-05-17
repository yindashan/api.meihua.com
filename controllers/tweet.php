<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tweet extends MY_Controller {

	/**
	 * 构造函数
	 *
	 * @return void
	 * @author
	 **/
	function __construct()
	{
		parent::__construct();

        $this->load->library('msclient');

        $this->load->model('tweet_model');
        //$this->load->model('user_model');
        //$this->load->model('Mis_tweet_model');
        //$this->load->model('cache_model');
        $this->load->model('Zan_model');
        //$this->load->model('short_url_model');

        /*
        if (in_array($this->uri->segment(2), array('detail','detail_v2'))) {
            if (!isset($this->request_array['token'])) {
                $this->_set_token_check(false);
            }
        }
         */
    }

    function get_tweet() {
        $request = $this->request_array;
        $tid = $request['tid'];
        $tweet = $this->tweet_model->get_tweet_info($tid);
        echo json_encode($tweet);exit;
    }
    /**
     * 获取作品详情数据
     */
    function detail() {
    
        $request = $this->request_array;
        $response = $this->response_array;
        log_message('error', 'req_detail:'.json_encode($request));
        $result_arr = array();

        $tid = $request['tid'];
        $uid = $request['uid'];


        //$result = $this->Community_model->get_detail_by_tid($tid);
        $result = $this->get_tweet_detail($tid);
        log_message('error', 'community_detail:'.json_encode($result));
        if(isset($result['is_del']) && ($result['is_del'] == 1)) {
            //$response['errno'] = ERR_TWEET_IS_DEL;
            //goto end;
        }
        log_message('error', 'result:'.json_encode($result));
        //$result_array = $result->result_array();
        $result_array = $result;

        if(!empty($result_array)) {

            //获取点赞人
            $praise_user_list = array();
            $praise_user = $this->Zan_model->get_user_list($tid, PRAISE_USER_COUNT);
            if($praise_user) {
                foreach($praise_user as $user) {
                    $praise_user_list[] = $user['username'];
                }
            }
            log_message('error', 'praise_user:'. json_encode($praise_user));
            $result['praise']['user'] = $praise_user_list; 

            /*
            //获取点赞标识
            log_message('error', '$this->_uid:'.$this->_uid);
            $zan_dict = $this->Zan_model->get_tid_dianzan_dict($uid, array($tid));
            log_message('error', 'zan_dict:'.json_encode($zan_dict));
            $praise_flag = $zan_dict[$tid];
            $result['praise']['flag'] = $praise_flag;
             */

            //拼接转发落地页链接
            //$forward_url = "http://app.lanjinger.com/wap/community/detail?tid=" . $tid;
            //$result['forward']['url'] = $this->short_url_model->generate_url($forward_url);
            //$result['forward']['url'] = $forward_url;

            //获取收藏标志
            //test
            $result['fav'] = 1;


            //封装整体数据
            //$result_arr['content'] = $result;
            $response['data']['content'] = $result;
            end:
            $this->renderJson($response['errno'], $response['data']);
        }
    }

    /**
     * 获取用户帖子列表
     *
     */
    function usertweet() {

        $request = $this->request_array;
        log_message('debug', 'usertweet_request:'.json_encode($request));
        $response = $this->response_array;

        if (!isset($request['uid'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [uid] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        $uid = $request['uid'];         // 用户id
        $rn = $request['rn'];           // 一页返回数量, 默认10条
        $type = isset($request['type']) ? $request['type'] : 'new'; // type = 'new'新页, 'next'翻页
        log_message('debug', 'usertweet_uid:'.$uid);

        //获取帖子ID列表
        if ('new' == $type) {
            // 首页
            $res_tid = $this->tweet_model->get_tid_list_by_uid($uid, $rn);
        } else if ('next' == $type) {
            // 翻页
            if (!isset($request['last_tid'])) {
                log_message('error', __FILE__.':'.__LINE__.' key [last_tid] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            $tid = $request['last_tid'];
            $res_tid = $this->tweet_model->get_next_tid_list_by_uid($uid, $tid, $rn);
        } else {
            log_message('error', __FILE__.':'.__LINE__.' key type['.$type.'] not valid.');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (false === $res_tid) {
            log_message('error', __FILE__.':'.__LINE__.' get user['.strval($uid).'] tid list failed.');
            $this->renderJson(STATUS_ERR_RESPONSE, json_encode($res_tid));
            return ;
        }
        log_message('debug', 'usertweet_tids:'.json_encode($res_tid));

        // 获取详情
        $res_content = array();
        foreach($res_tid as $item_tid) {
            $ret = $this->get_tweet_detail($item_tid['tid']);
            if (count($ret['imgs']) > 0) {
                $ret['imgs'] = $ret['imgs'][0];
            }
            if ($ret && 0 == intval($ret['is_del'])) {
                $res_content[] = $ret;  
            }   
        }

        $response['data'] = array(
            'content' => $res_content,
        );
        end:
            $this->renderJson($response['errno'], $response['data']);
    }

    private function tweet_new() {
        $request = $this->request_array;
        log_message('error', 'tweet_new_request:'.json_encode($request));
        $response = $this->response_array;
        $result_arr = array();
        if (!isset($request['uid'])) {
            $response['errno'] = STATUS_ERR_REQUEST; 
            goto end;
        }
        $uid = $request['uid'];

        //处理帖子内容中的url
        $content = $this->shorten_url($request['content']);

        //用户发表作品
        $data = array(
            'uid' => $uid,
            'img' => isset($request['imgs']) ? $request['imgs'] : "",    
            'content' => isset($request['content']) ? $request['content'] : "",        
            'tags' => isset($request['tags']) ? $request['tags'] : "",
            'type' => 2,    
            'f_catalog' => isset($request['f_catalog']) ? $request['f_catalog'] : "",
            's_catalog' => isset($request['s_catalog']) ? $request['s_catalog'] : "",
            'ctime' => time(),    
        );

        // 获取帖子id
        $tid = $this->uidclient->get_id();
        
        if (!$tid) {
            log_message('error', __FILE__.':'.__LINE__.' get uid error.');
            $response['errno'] = STATUS_ERR_UIDCLIENT;
            goto end;
        }
        log_message('debug', 'new tid='.strval($tid));

        //操作线上数据库
        $online_tid = $this->tweet_model->add($data);
        log_message('error', 'online_tid:'.$online_tid);
        if(!$online_tid) {
            $response['errno'] = MYSQL_ERR_INSERT;
            goto end;
        }

        $this->tweet_model->tweet_add($request['uid']);

        //更新最新帖子流
        $this->load->model('fresh_stream_model'); 
        $this->fresh_stream_model->push($online_tid);

        
        //请求离线模块
        $data['tid'] = $online_tid;
        $res = $this->offclient->SendNewPost($data);

        $result_arr = array('tid' => $online_tid);
        $response['data'] = $result_arr;
        end:
            $this->renderJson($response['errno'], $response['data']);
    }


    private function tweet_forward() {
        $request = $this->request_array;
        $response = $this->response_array;
        log_message('error', 'tweet_forward_request:'.json_encode($request));
        $result_arr = array();
        if (!isset($request['uid']) || !isset($request['parent_tid'])) {
            $response['errno'] = STATUS_ERR_REQUEST; 
            goto end;
        }
        $res = $this->Community_model->get_tweet($request['parent_tid'], 'origin_tid, catalog, industry');
        if (!$res) {
            $response['errno'] = STATUS_ERR_RESPONSE;
            goto end;
        }

        //用户转发讨论
        $data = array(
            'uid' => $request['uid'],
            'title' => isset($request['title']) ? $request['title'] : "",            
            'content' => isset($request['content']) ? $request['content'] : "",        
            'industry' => isset($request['industry']) ? $request['industry'] : $res['industry'],    
            'catalog' => isset($request['catalog']) ? $request['catalog'] : $res['catalog'],    
            'ctime' => time(),    
            'parent_tid' => $request['parent_tid'],    
            'origin_tid' => 0 == $res['origin_tid'] ? $request['parent_tid'] : $res['origin_tid'], 
        );  
        $online_tid = $this->Community_model->add($data);
        if(false === $online_tid) {
            $response['errno'] = MYSQL_ERR_INSERT; 
            goto end;
        }

        //更新cache中帖子转发数
        $this->cache_model->forward_add($request['parent_tid']);
        $this->cache_model->tweet_add($request['uid']);

        $data['tid'] = $online_tid;
        $res = $this->offclient->SendNewPost($data);
        $result_arr = array('tid' => $online_tid);

        end:
            $this->renderJson($response['errno'], $result_arr);
    }

    private function tweet_delete() {
        $request = $this->request_array;
        $response = $this->response_array;
        $result_arr = array();
        if (!isset($request['tid']) && !isset($request['uid'])) {
            $response['errno'] = STATUS_ERR_REQUEST; 
            goto end;
        }
        $tid = $request['tid'];
        $uid = $request['uid'];
        /*todo
        if($this->_uid !== $uid) {
            $response['errno'] = ERR_USER_NOT_VERIFIED;
            goto end;
        }
         */
        log_message('error', 'delete_tid:'.$tid);
        $data = array(
            'is_del' => 1,    
        );
        //更新库里is_del字段
        $res = $this->tweet_model->update_by_tid_uid($tid, $uid, $data);
        if (!$res) {
            $response['errno'] = MYSQL_ERR_UPDATE; 
            goto end;
        }
        $tweet = $this->tweet_model->get_tweet_info($tid);
        log_message('error', 'commuinty_tweet_del_tweet:'.json_encode($tweet));

        //设置redis帖子删除字段
        if(isset($tweet['is_del']) && $tweet['is_del'] == 0) {
            log_message('error', 'commuinty_tweet_del-----------------');
            $this->tweet_model->tweet_del($tweet['tid']);
        }
        //设置redis用户帖子数
        if ($tweet['uid']) {
            $this->tweet_model->tweet_cancel($tweet['uid']);
        }
        end:
            $this->renderJson($response['errno']);
    }

    /**
     * 用户作品相关
     */
    function operate() {
        $request = $this->request_array;
        log_message('error', json_encode($request));
        $type = $request['type'];
        if ('new' === $type) {
            $this->tweet_new();
        } elseif($type == 'forward') {
            $this->tweet_forward();
        } elseif($type == 'delete') {
            $this->tweet_delete();
        }
    }

}



/* End of file tweet.php */
/* Location: ./application/controllers/tweet.php */  
