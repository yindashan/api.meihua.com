<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Comment extends MY_Controller {

	/**
	 * 评论模块构造函数
	 *
	 * @return void
	 * @author
	 **/
	function __construct()
	{
		parent::__construct();

        $this->load->model('Comment_model');
        $this->load->model('Tweet_model');
        $this->load->model('Cache_model');

        /*
        if (in_array($this->uri->segment(2), array('topicmt','topicmt_v2'))) {
            $this->_set_token_check(false);
            $this->_set_sign_check(false);
        }
         */
    }

    /**
     * 获取某条帖子所有评论
     */
	function tweetcmt()
    {
        //后端统一控制展现数量
        $this->request_array['rn'] = COMMENT_LIST_COUNT;

        $request = $this->request_array;
        $response = $this->response_array;

        $content = array();

        if(!isset($request['type']) || !isset($request['tid'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $type = $request['type'];
        $tid = $request['tid'];
        $rn = $request['rn'];
        if($type == 'new') {
            //下拉刷新取最新评论
            //$cid = $request['first_cid'];
            $result = $this->Comment_model->get_list_by_tid($tid, $rn);

        }elseif($type == 'next') {
            //上拉加载更多评论
            $cid = $request['last_cid'] ? $request['last_cid'] : 0;
            $result = $this->Comment_model->get_list_by_cid_tid($cid, $tid, $rn);
        }
        if($result) {
            $content = $result;
            foreach($content as $idx => $comment) {
                //格式化时间
                $content[$idx]['ctime'] = $this->format_time($comment['ctime']);

                //获取当前评论用户数据
                $uid = $comment['uid'];

                $ret = $this->Cache_model->get_user_detail_info($uid, array('sname', 'avatar'),0);
                if ($ret) {
                    $content[$idx]['sname'] = $ret['sname'];
                    $content[$idx]['avatar'] = $ret['avatar'];
                } else {
                    $content[$idx]['sname'] = '';
                    $content[$idx]['avatar'] = '';
                }

                //获取原评论信息和用户数据
                $reply_cid = $comment['reply_cid'];
                $reply_uid = $comment['reply_uid'];
                if($reply_cid && $reply_uid) {
                    $ret = $this->Cache_model->get_user_info($reply_uid, 'sname');
                    if ($ret) {
                        $content[$idx]['reply_sname'] = $ret;
                    }
                }
            }
        }else{
            goto end;
        }
        $response['data'] = array(
            'content' => $content,
            'type' => $type,    
        );
        end:
        $this->renderJson($response['errno'], $response['data']);
	}

    /**
     * 发表评论和回复评论
     */
    function newcmt() {
        $this->load->library('offclient');
        $request = $this->request_array;
        $response = $this->response_array;
        $errno = 0;
        $result_arr = array();

        $uid = @$request['uid'];
        $tid = @$request['tid'];
        $content = @$request['content'];

        $reply_uid = @$request['reply_uid'];
        $reply_cid = @$request['reply_cid'];
        if (empty($uid) || empty($tid) || empty($content)) {
            $response['errno'] = STATUS_ERR_REQUEST;
            goto end;
        }
        if (empty($reply_uid)) {
            $reply_uid = 0; 
        }
        if (empty($reply_cid)) {
            $reply_cid = 0; 
        }

        $data = array(
            'uid' => $uid,
            'tid' => $tid,
            'content' => $content,
            'ctime' => time(),    
            'reply_uid' => $reply_uid,
            'reply_cid' => $reply_cid,
        );
        $cid = $this->Comment_model->add($data);
        if (!$cid) {
            $response['errno'] = MYSQL_ERR_INSERT;
            log_message('error', __METHOD__ . ' new comment error, uid['.$uid.'] errno[' . $response['errno'] .']');
            $this->renderJson(MYSQL_ERR_INSERT);
            return;
        }
        //推送到消息中心
        $this->offclient->send_event($tid, offhub\EventType::COMMENT);
        $this->Cache_model->comment_add($tid);

        $ret = $this->Tweet_model->get_tweet_info($tid);
        if(!$ret) {
            $response['errno'] = MYSQL_ERR_SELECT;
            log_message('error', __METHOD__ . ' get_tweet_info error, uid['.$uid.'] cid['.$cid.'] errno[' . $response['errno'] .']');
            goto end;
        }
        $this->load->library('msclient');
        if ($ret) {
            if(!$reply_uid || ($ret['uid'] != $reply_uid)) {
                $this->msclient->send_system_msg($uid, ms\ActionType::COMMENT, $ret['uid'], $cid);
            }
        }
        if ($reply_uid) {
            $this->msclient->send_system_msg($uid, ms\ActionType::COMMENT_REPLY, $reply_uid, $cid); 
        }
        end:
        $this->renderJson($response['errno'], array('cid' => $cid));
    }

    public function delcmt() {
        $request = $this->request_array;
        $response = $this->response_array;
        $result_arr = array();
        if (!isset($request['cid']) && !isset($request['uid'])) {
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            $this->renderJson(STATUS_ERR_REQUEST);
            return;
        }
        $cid = $request['cid'];
        $uid = $request['uid'];

        $comment = $this->Comment_model->get_detail_by_cid($cid);
        if(false === $comment) {
            $this->renderJson(STATUS_ERR_REQUEST);
            return;
        }
        if (1 == intval($comment['is_del'])) {
            $this->renderJson(STATUS_OK);
            return;
        }
        /*
        if($this->_uid !== $comment['uid']) {
            log_message('error', __METHOD__ . ' user illegal, uid['.$this->_uid.'] cid['.$cid.'] errno[' . $response['errno'] .']');
            $this->renderJson(ERR_USER_ILLEGAL);
            return;
        }
         */
        $data = array(
            'is_del' => 1,
        );

        //更新库里is_del字段
        //test
        $this->_uid = $uid;

        $res = $this->Comment_model->update_by_cid_uid($cid, $this->_uid, $data);
        if (false === $res) {
            log_message('error', __METHOD__ . ' comment delete error, uid['.$this->_uid.'] cid['.$cid.'] errno[' . $response['errno'] .']');
            $this->renderJson(MYSQL_ERR_UPDATE);
            return;
        } else if (0 < $res) {
            //设置redis帖子评论数
            $this->Cache_model->comment_cancel($comment['tid']);
        }
        $this->renderJson(STATUS_OK);
           
    }
          
}


/* End of file comment.php */
/* Location: ./application/controllers/comment.php */
