<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class System_message extends MY_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model('System_message_model');
        //$this->load->model('Community_model');
        $this->load->model('Comment_model');
    }
    private function _trunc($str, $len) {
        if (mb_strlen($str, 'utf8') > $len) {
            return mb_substr($str, 0, $len - 3, 'utf8').'...';
        } else {
            return $str;
        }
    }
    private $trunc_len = 125;

    function get() {
        $request = $this->request_array;
        $uid = intval($request['uid']);
        $last_id = intval($request['last_id']);
        $type = $request['type'];
        $earliest_id = $this->System_message_model->get_earliest_msg_id($uid);
        log_message('error', 'earliest_id:'.json_encode($earliest_id));
        $earliest_id = $earliest_id[0]['sys_message_id'];

        $result = $this->System_message_model->get_system_msg($uid, $last_id, $type);
        log_message('error', 'sys_result:'.json_encode($result));
        $data = array();
        $msg_list = array();
        $has_more = 1;
        foreach ($result as $msg) {
            $action_type = intval($msg['action_type']);
            if ($action_type == 1) {//过滤私信
                continue;
            }
            $info = array();
            $info['sys_msg_id'] = intval($msg['sys_message_id']);
            $info['timestamp'] = intval($msg['ctime']);
            $info['ctime'] = $this->format_time($msg['ctime']);
            //$info['ctime'] = $this->format_time(time());
            if ($info['sys_msg_id'] == $earliest_id)
                $has_more = 0;
            $from_uid = $msg['from_uid'];
            $user_info = $this->get_user_by_uid($from_uid);
            $from_user = array();//TODO user 信息从缓存中获取
            $from_user['sname'] = $user_info['sname'];
            $from_user['avatar'] = $user_info['avatar'];
            $from_user['uid'] = $from_uid;
            $info['from_user'] = $from_user;
            $info['action_type'] = $action_type;
            $info['digest'] = '';
            $info['is_read'] = $msg['is_read'];
            $info['jump'] = '';
            $content_id = intval($msg['content_id']);
            if ($action_type == 0) {//@
                $info['action_content'] = '@提到了你';
                $info['tid'] = $content_id;
                $community = $this->Community_model->get_detail_by_tid($content_id);
                $digest = $this->_trunc($community['content'], $this->trunc_len);
                $info['digest'] = $digest;
                $info['jump'] = 'lanjing://tweet?tid='.$content_id;
            }

            if ($action_type == 2) {//回复贴子
                $info['action_content'] = '回复了你的讨论';
                $cid = $content_id;//评论id
                $comment = $this->Comment_model->get_detail_by_cid($cid);
                $info['tid'] = intval($comment['tid']);
                $digest = $this->_trunc($comment['content'], $this->trunc_len);
                $info['digest'] = $digest;
                $info['jump'] = 'lanjing://tweet?tid='.$info['tid'];
            }

            if ($action_type == 3) {//回复评论
                $info['action_content'] = '回复了你的评论';
                $cid = $content_id;//评论id
                $comment = $this->Comment_model->get_detail_by_cid($cid);
                $info['tid'] = intval($comment['tid']);
                $digest = $this->_trunc($comment['content'], $this->trunc_len);
                $info['digest'] = $digest;
                $info['jump'] = 'lanjing://tweet?tid='.$info['tid'];
            }

            if ($action_type == 5) {
                $info['action_content'] = '关注了你';
                $info['jump'] = 'lanjing://user?uid='.$from_uid;
            }

            if ($action_type == 6) {
                $info['action_content'] = '赞了你的讨论';
                $info['tid'] = $msg['content_id'];
                $community = $this->Community_model->get_detail_by_tid($content_id);
                $digest = $this->_trunc($community['content'], $this->trunc_len);
                $info['digest'] = $digest;
                $info['jump'] = 'lanjing://tweet?tid='.$info['tid'];
            }

            array_push($msg_list, $info);
            if ($msg['is_read'] == '0') {
                $this->msclient->set_read(intval($msg['sys_message_id']));//把这个贴子设置为已读
            }
        }
        $errno = 0;
        $data['msg_list'] = $msg_list;
        $data['has_more'] = $has_more;
        $data['type'] = $type;
        $this->renderJson($errno, $data);
        $this->msclient->clear_red_by_uid($uid, 6, 0);

    }

    function del() {
        $request = $this->request_array;
        $sys_msg_id = intval($request['sys_msg_id']);
        $errno = 0;

        if(!$this->msclient->set_delete($sys_msg_id)) {
            $errno = -1;
        }

        $this->renderJson($errno,array());
    }
}
