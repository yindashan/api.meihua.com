<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Info extends MY_Controller {
	function __construct() {
		parent::__construct();
    }
    function msgnum() {
        $request = $this->request_array;
        if (!isset($request['uid']) || empty($request['uid'])) {
            $this->renderJson(STATUS_ERR_REQUEST);  
            return;
        }
        $uid = intval($request['uid']);
        $sys_msg_num = $this->msclient->get_num($uid, 3);
        $friend_msg_num = $this->msclient->get_num($uid, 2);
        $ret = array();
        $ret['sys_msg'] = array('is_red'=> 0, 'num'=>$sys_msg_num);
        if ($sys_msg_num > 0) {
            $ret['sys_msg']['is_red'] = 1;
        }

        $ret['friend_msg'] = array('is_red'=> 0, 'num'=>$friend_msg_num);
        if ($friend_msg_num > 0) {
            $ret['sys_msg']['is_red'] = 1;
        }
        $this->renderJson(0, $ret);
    }
}
