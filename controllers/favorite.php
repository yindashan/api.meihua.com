<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Favorite extends MY_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model('Favorite_model');
        $this->load->model('Cache_model');
        $this->load->model('Tweet_model');
        $this->load->library('msclient');
        $this->load->library('offclient');
    }

    function add() {
        $request = $this->request_array;
        $response = $this->response_array;

        if(!isset($request['uid']) || !isset($request['tid'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $uid = intval($request['uid']);
        $tid = intval($request['tid']);

        if (empty($uid) || empty($tid)) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $ret = $this->Favorite_model->add($uid, $tid);
        if (!$ret) {
            $response['errno'] = MYSQL_ERR_INSERT;
            log_message('error', __METHOD__ . ' favorite add error, uid['.$uid.'] tid['.$tid.'] errno[' . $response['errno'] .']');
            goto end;
        }

        /*todo
        if ($ret > 0) {
            $this->Cache_model->zan_add($tid, $uid);
            $ret = $this->Tweet_model->get_tweet_info($tid);
            $this->msclient->send_system_msg($uid, ms\ActionType::PRAISE, $ret['uid'], $tid);
            $this->offclient->send_event($tid, offhub\EventType::ZAN);
        }
         */
        end:
        $this->renderJson($response['errno'], $response['data']);
    }

    function cancel() {
        $request = $this->request_array;
        $response = $this->response_array;
        if(!isset($request['uid']) || !isset($request['tid'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $uid = @$request['uid'];
        $tid = @$request['tid'];
        if (empty($uid) || empty($tid)) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $ret = $this->Favorite_model->remove($uid, $tid);
        if (false === $ret) {
            $response['errno'] = MYSQL_ERR_DELETE;
            log_message('error', __METHOD__ . ' favorite cancel error, uid['.$uid.'] tid['.$tid.'] errno[' . $response['errno'] .']');
            goto end;
        }

        /*todo
        if ($ret) {
            $this->Cache_model->zan_cancel($tid, $uid);
            $this->offclient->send_event($tid, offhub\EventType::ZAN_CANCEL);
        }
         */
        end:
        $this->renderJson($response['errno'], $response['data']);
    }

    function get_user_favorite() {
    
        $this->request_array['rn'] = TWEET_COMMUNITY_LIST_COUNT;
        $request = $this->request_array;
        $response = $this->response_array;

        $uid = $request['uid'];
        $rn = $request['rn'];

        //获取作品ID列表
        if(isset($request['type']) && $request['type'] == 'next') {
            $fid = $request['last_fid'];
            $res_tid = $this->Favorite_model->get_next_favorite_list_by_uid($uid, $fid, $rn);
        }else {
            $res_tid = $this->Favorite_model->get_favorite_list_by_uid($uid, $rn);
        }
        if(empty($res_tid)) {
            goto end;
        }
        foreach($res_tid as $tids) {
            $tid = $tids['tid'];
            $res = $this->get_tweet_detail($tid);
            $res['fid'] = $tids['fid'];
            if (count($res['imgs']) > 0) {
                $res['imgs'] = $res['imgs'][0];
            }

            $res_content[] = $res;
        }

        $response['data']['content'] = $res_content;

        end:
        $this->renderJson($response['errno'], $response['data']);

    }
}



/* End of file favorite.php */
/* Location: ./application/controllers/favorite.php */
