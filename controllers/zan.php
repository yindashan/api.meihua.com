<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Zan extends MY_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model('Zan_model');
        $this->load->model('Cache_model');
        $this->load->model('Tweet_model');
        $this->load->library('msclient');
        $this->load->library('offclient');
    }

    function test() {
        $imgs = array(
            array(
                'img' => array(
                    'small' => array(
                        'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                        'width' => 200,
                        'height' => 200,
                    ),
                    'middle' => array(
                        'url' => '',
                        'width' => 350,
                        'height' => 350,
                    ),
                    'big' => array(
                        'url' => '',
                        'width' => 400,
                        'height' => 400,
                    ),
                ),
                'content' => '图片1描述',
            ),
            array(
                'img' => array(
                    'small' => array(
                        'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                        'width' => 200,
                        'height' => 200,
                    ),
                    'middle' => array(
                        'url' => '',
                        'width' => 350,
                        'height' => 350,
                    ),
                    'big' => array(
                        'url' => '',
                        'width' => 400,
                        'height' => 400,
                    ),
                ),
                'content' => '图片2描述',
            ),
        );
        echo json_encode($imgs);exit;
    
    }
    function add() {
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
        $ret_user_info = $this->Cache_model->get_user_info($uid, 'sname');
        if ($ret_user_info) {
            $sname = $ret_user_info['sname']; 
        } else {
            $sname = '';
        }

        $ret_tweet_info = $this->Tweet_model->get_tweet_info($tid);
        if($ret_tweet_info) {
            $owneruid = $ret_tweet_info['uid'];
        }else {
            $owneruid = 0;
        }
        $ret = $this->Zan_model->add($uid, $tid, $sname, $owneruid);
        if (!$ret) {
            $response['errno'] = MYSQL_ERR_INSERT;
            log_message('error', __METHOD__ . ' zan add error, uid['.$uid.'] tid['.$tid.'] errno[' . $response['errno'] .']');
            goto end;
        }

        if ($ret > 0) {
            $this->Cache_model->zan_add($tid, $uid);
            $ret = $this->Tweet_model->get_tweet_info($tid);
            $this->msclient->send_system_msg($uid, ms\ActionType::PRAISE, $ret['uid'], $tid);
            $this->offclient->send_event($tid, offhub\EventType::ZAN);
        }
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
        $ret = $this->Zan_model->remove($uid, $tid);
        if (false === $ret) {
            $response['errno'] = MYSQL_ERR_DELETE;
            log_message('error', __METHOD__ . ' zan cancel error, uid['.$uid.'] tid['.$tid.'] errno[' . $response['errno'] .']');
            goto end;
        }
        if ($ret) {
            $this->Cache_model->zan_cancel($tid, $uid);
            $this->offclient->send_event($tid, offhub\EventType::ZAN_CANCEL);
        }
        end:
        $this->renderJson($response['errno'], $response['data']);
    }

    function get_praised_list() {
    
        $request = $this->request_array;
        $response = $this->response_array;
        if(!isset($request['uid'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $owneruid = $request['uid'];
        $tid = $request['tid'];
        if (empty($owneruid)) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __METHOD__ . ' request error, errno[' . $response['errno'] .']');
            goto end;
        }
        $ret_tid = $this->Zan_model->get_praised_list_by_uid($owneruid);
        if (false === $ret_tid) {
            $response['errno'] = MYSQL_ERR_SELECT;
            log_message('error', __METHOD__ . ' get praised list error, uid['.$owneruid.'] errno[' . $response['errno'] .']');
            goto end;
        }
        if ($ret_tid) {
            foreach($ret_tid as $ret) {
                $tid = $ret['tid'];

                $res = $this->get_tweet_detail($tid);
                $res['praisednum'] = $ret['praisednum'];

                $res_content[] = $res;
            }
        }

        $response['data']['content'] = $res_content;

        end:
        $this->renderJson($response['errno'], $response['data']);
    }
}



/* End of file zan.php */
/* Location: ./application/controllers/zan.php */
