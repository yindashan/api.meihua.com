<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Relation_stream extends MY_Controller {

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
        $this->load->library('offclient');

        $this->load->model('Tweet_model');

        //if ('detail' == $this->uri->segment(2)) {
        /*
        if (in_array($this->uri->segment(2), array('detail','detail_v2'))) {
            if (!isset($this->request_array['token'])) {
                $this->_set_token_check(false);
            }
        }
         */
	}


    /**
     * 广场关注列表
     */
    function get() 
    {
        $this->load->model('message_queue_model');
        $this->load->model('Zan_model');
        $this->load->model('short_url_model');

        //后端统一控制
        $this->request_array['rn'] = TWEET_COMMUNITY_LIST_COUNT;
        $request = $this->request_array;
        $response = $this->response_array;

        $result_arr = array();
        $res_content = array();

        if(!isset($request['type']) || !isset($request['uid'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $type = $request['type'];
        $uid = $request['uid'];

        $rn = $request['rn'];

        $tweets = $this->message_queue_model->get_user_message($uid);
        log_message('error', 'tweets:'.json_encode($tweets));
        $count = 0;
        if(false === $tweets) {
            $response['errno'] = MYSQL_ERR_SELECT;
            log_message('error', __METHOD__ . ' get user message error, uid['.$uid.'] errno['.$response['errno'].']');
            goto end;
        } elseif(!empty($tweets)) {
            if($type == 'new') {
                //下拉刷新取最新帖子
                foreach($tweets as $tid) {
                    //过滤已删除的帖子
                    $fields = array('is_del');
                    //$fields_arr = $this->Tweet_model->get_tweet_fields($tid, array('is_del'));
                    
                    //todo 暂时先从库里读，后续改为从redis里读
                    $fields_arr = $this->Tweet_model->get_tweet($tid, array('is_del'));
                    if(is_null($fields_arr)) {
                        /*
                        $response['errno'] = MYSQL_ERR_SELECT;
                        log_message('error', __METHOD__ . ' get tweet error, tid['.$tid.'] errno[' . $response['errno'] .']');
                        goto end;
                         */
                        continue;
                    }
                    log_message('error', 'fields_arr:'.json_encode($fields_arr));
                    $is_del = isset($fields_arr['is_del']) ? $fields_arr['is_del'] : 0;
                    if(intval($is_del) === 1) {
                        continue;
                    }

                    $tid_list[] = $tid;
                    if(count($tid_list) == $rn) break;
                }
                $this->msclient->clear_red_by_uid($uid, 2, 0);
            }elseif($type == 'next') {
                //上拉获取更多帖子
                $last_tid = $request['last_tid'];
                $pass_flag = true;
                foreach($tweets as $key => $tid) {
                    if ($last_tid == $tid) {
                        $pass_flag = false;
                        continue;
                    }
                    if ($pass_flag) {
                        continue;
                    }
                    //过滤已删除的帖子
                    $fields = array('is_del');
                    //$fields_arr = $this->Tweet_model->get_tweet_fields($tid, array('is_del'));
                    
                    //todo 暂时先从库里读，后续改为从redis里
                    $fields_arr = $this->Tweet_model->get_tweet($tid, array('is_del'));
                    if(is_null($fields_arr)) {
                        /*
                        $response['errno'] = MYSQL_ERR_SELECT;
                        log_message('error', __METHOD__ . ' get tweet error, tid['.$tid.'] errno[' . $response['errno'] .']');
                        goto end;
                         */
                        continue;
                    }
                    $is_del = isset($fields_arr['is_del']) ? $fields_arr['is_del'] : 0;
                    if(intval($is_del) === 1) {
                        continue;
                    }
                    $tid_list[] = $tid;
                    if(count($tid_list) == $rn) break;
                }
            }
            if(!empty($tid_list)) {
                //获取点赞标识
                $zan_dict = $this->Zan_model->get_tid_dianzan_dict($uid, $tid_list);

                foreach($tid_list as $k => $tid) {
                    $content = $this->get_tweet_detail($tid);
                    $praise_flag = $zan_dict[$tid];
                    $content['praise']['flag'] = $praise_flag;

                    //拼接转发url
                    $forward_url = TWEET_DETAIL_LANDING_PAGE . "?tid=" . $tid;
                    //$content['forward']['url'] = $this->short_url_model->generate_url($forward_url);
                    $content['forward']['url'] = $forward_url;
                    
                    $res_content[] = $content;
                    if(isset($request['industry'])) {
                        $result_arr['industry'] = $request['industry'];
                    }
                }
            }
            $response['data'] = array(
                'content' => $res_content,
                'type' => $type,
            );
        }
        end:
            $this->renderJson($response['errno'], $response['data']);
    }



}
