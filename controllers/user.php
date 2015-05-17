<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller {

    const NO_FOLLOW = 0;
    const ONE_WAY_FOLLOW = 1;
    const MUTUAL_FOLLOW = 2;
    const R_ONE_WAY_FOLLOW = 3;
	/**
	 * 构造函数
	 *
	 * @return void
	 * @author
	 **/
	function __construct()
	{
		parent::__construct();

        $this->load->model('relation_model');
        $this->load->model('user_model');
        $this->load->model('user_detail_model');
        //$this->config->load('user', TRUE);
	}

    function is_valified() {
        $request = $this->request_array;
        $response = $this->response_array;
        $errno = 0;
        $result_arr = array();

        if(!isset($request['uid'])) {
            $this->renderJson(STATUS_ERR_REQUEST);
            return;
        }

        $ret = $this->get_user_info_by_uid($request['uid'], array('ukind_verify')); 
        if (!$ret) {
            $this->renderJson(STATUS_ERR_RESPONSE);
            return;
        }

        $this->renderJson(STATUS_OK, array('is_valified' => intval($ret['ukind_verify'])));
    }

	function get_info()
    {
        $request = $this->request_array;
        $response = $this->response_array;
        $arr_result = array();
        $arr_select_column = array();
        $own_info = false;

        // 1. judge uid exist
        if (!isset($request['uid'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __FILE__.":".__LINE__.' get_info doesn\'t have [uid].');
            goto end;
        }
        $uid = $request['uid'];
        
        // 2. judge se_id exist
        if (!isset($request['se_id'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            log_message('error', __FILE.":".__LINE__." get_info doesn't have [se_id]");
            goto end;
        }
        $se_id = $request['se_id'];

        // 3. judge own_info or others_info
        if ($se_id == $uid) {
            $own_info = true;
        }

        // 4. get user_info
        $user_info_res = $this->get_user_detail_by_uid($se_id);
        if (!$user_info_res) {
            $response['errno'] = STATUS_ERR_RESPONSE;
            log_message('error', __FILE__.":".__LINE__." get_info: get_user_info_by_uid failed.");
            goto end;
        }

        log_message('debug', __FILE__.':'.__LINE__
            ." get_info user ".strval($se_id).' info: '.json_encode($user_info_res));

        // 5. get others info
        if ($own_info) {
            // my_info: get follower, fans, approval
            // follower and followee num
            $user_ext_info = $this->cache_model->get_user_ext_info($uid);
            if (false === $user_ext_info) {
                log_message('error', __FILE__.":".__LINE__." get_info: get_user_ext_info failed.");
                $user_ext_info = array(
                    'follower_num'  => 0,
                    'followee_num'  => 0
                );
            }
            $user_info_res = array_merge($user_info_res, $user_ext_info);

            // TODO: approval num
            $approval_num = 0;
            $user_info_res['approval_num'] = $approval_num;
        } else {
            // others_info: get follower status
            $follow_type = $this->relation_model->get_relation_info($uid, $se_id);
            if (is_null($follow_type)) {
                $follow_type = array(
                    'follow_type'   => 0,
                );
            } else {
                if ($uid < $se_id) {
                    $a_follow_b = $follow_type['a_follow_b'] != 0;
                    $b_follow_a = $follow_type['b_follow_a'] != 0;
                } else {
                    $a_follow_b = $follow_type['b_follow_a'] != 0;
                    $b_follow_a = $follow_type['a_follow_b'] != 0;
                }
                if (!$a_follow_b) {
                    $follow_type = NO_FOLLOW;
                } else {
                    if (!$b_follow_a) {
                        $follow_type = ONE_WAY_FOLLOW;
                    } else {
                        $follow_type = MUTUAL_FOLLOW;
                    }
                }
                $follow_type = array(
                    'follow_type'   => $follow_type,
                );
            }
            $user_info_res = array_merge($user_info_res, $follow_type);
        }

        $response['data'] = $user_info_res;

        end:
            $this->renderJson($response['errno'], $response['data']);
    }

    function create_user() {
        $request = $this->request_array;
        $response = $this->response_array;
        $errno = 0;
        $result_arr = array();

        $result = $this->user_model->create_user($request);
        $response = $result;

        if (!$result) {
            return $this->renderJson(0, "null!");
        } else {
            return $this->renderJson(0, $result);
        }
    }

    function test_model() {
        $request = $this->request_array;
        $response = $this->response_array;
        $errno = 0;
        $result_arr = array();
        
        $result = $this->user_model->delete_by_uid(46);
        $response = $result;
        if (false === $result) {
            return $this->renderJson(0, "null!");
        } else {
            return $this->renderJson(0, $result);
        }
    }

    function register_user() {
        $request = $this->request_array;
        $response = $this->response_array;

        // check keys
        if (!isset($request['umobile'])) {
            log_message('error', __FILE__,':'.__LINE__.' key [umobile] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['password'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [password] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['sname'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [sname] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['province'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [province] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['city'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [city] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['avatar'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [avatar] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['intro'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [intro] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['school'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [school] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }

        $umobile    = $request['umobile'];
        $password   = $request['password'];
        $sname  = $request['sname'];
        $province   = $request['province'];
        $city   = $request['city'];
        $avatar = $request['avatar'];
        $intro  = $request['intro'];
        $school = $request['school'];
        $timestamp  = time();
    
        // update ci_user
        $arr_user_input = array(
            'pass_word' => $password,
            'pass_mark' => "",
            'umobile'   => $umobile,
            'login_type'    => 0,
            'oauth_name'    => NULL,
            'oauth_key'     => NULL,
            'create_time'   => $timestamp,
        );
        $uid = $this->user_model->get_uid_by_phone($umobile);
        if (false === $uid) {
            log_message('error', __FILE__.':'.__LINE__.' get_uid_by_phone error.');
            $this->renderJson(MYSQL_ERR_CONNECT);
            return ;
        }
        if (NULL !== $uid) {
            log_message('error', __FILE__.':'.__LINE__.' user exist, uid='.$uid.' phone='.$umobile);
            $this->renderJson(USER_EXIST);
            return ;
        }
        $uid = $this->user_model->add($arr_user_input);
        if (false === $uid) {
            log_message('error', __FILE__.':'.__LINE__.' add_user error.');
            $this->renderJson(MYSQL_ERR_CONNECT);
            return ;
        }
        if (NULL === $uid) {
            log_message('error', __FILE__.':'.__LINE__.' add_user not affect rows.');
            $this->renderJson(MYSQL_ERR_INSERT);
            return ;
        }
        
        // update ci_user_detail
        $arr_user_detail_input = array(
            'uid'   => $uid,
            'sname' => $sname,
            'avatar'    => $avatar,
            'province'  => $province,
            'city'      => $city,
            'intro'     => $intro,
            'school'    => $school,
        );
        $user_detail_res = $this->user_detail_model->add($arr_user_detail_input);
        if (false === $user_detail_res) {
            log_message('error', __FILE__.':'.__LINE__.' add_user_detail error.');
            $this->renderJson(MYSQL_ERR_CONNECT);
            return ;
        }
        if (NULL === $user_detail_res) {
            log_message('error', __FILE__.':'.__LINE__.' add_user_detail not affect rows.');
            $this->renderJson(MYSQL_ERR_INSERT);
            return ;
        }

        // set user_ext info
        $this->load->model('cache_model');
        $this->cache_model->get_user_ext_info($uid);

        // return info
        $arr_response = $this->user_detail_model->get_info_by_uid($uid);
        if (false === $arr_response) {
            log_message('error', __FILE__.':'.__LINE__.' user_detail get_info_by_uid error.');
            $this->renderJson(MYSQL_ERR_SELECT);
            return ;
        }

        //TODO: return which avatar, need adjusted

        $response['errno'] = 0;
        $response['data'] = $arr_response;

        $this->renderJson($response['errno'], $response['data']);
    }

    function normal_login() {
        $request = $this->request_array;
        $response = $this->response_array;

        if (!isset($request['umobile'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [umobile] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['password'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [password] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        $umobile = $request['umobile'];
        $password = $request['password'];
        
        $result = $this->user_model->get_user_by_phone($umobile);
        if (false === $result) {
            log_message('error', __FILE__.':'.__LINE__.' get_user_by_phone error.');
            $this->renderJson(MYSQL_ERR_CONNECT);
            return ;
        }
        if (NULL === $result) {
            log_message('error', __FILE__.':'.__LINE__.' get_user_by_phone not found.');
            $this->renderJson(MYSQL_ERR_SELECT);
            return ;
        }

        // TODO: check password is signed right
        // TODO: check password is right
        $uid = $result['id'];
        $right_pass = $result['pass_word'];
        if ($password != $right_pass) {
            log_message('debug', __FILE__.':'.__LINE__.' password not correct.');
            $this->renderJson(USER_ERR_PASS);
            return ;
        }

        // return info
        $arr_response = $this->user_detail_model->get_info_by_uid($uid);
        if (false === $arr_response) {
            log_message('error', __FILE__.':'.__LINE__.' user_detail get_info_by_uid error.');
            $this->renderJson(MYSQL_ERR_SELECT);
            return ;
        }
        if (NULL === $arr_response) {
            log_message('error', __FILE__.':'.__LINE__.' user_detail '.strval($uid).' not exist!');
            $this->renderJson(MYSQL_ERR_SELECT);
            return ;
        }
        $response['errno'] = 0;
        $response['data'] = $arr_response;

        $this->renderJson($response['errno'], $response['data']);
    }

    function third_party_login() {
        $request = $this->request_array;
        $response = $this->response_array;

        if (!isset($request['oauth_name'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [oauth_name] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        if (!isset($request['oauth_json'])) {
            log_message('error', __FILE__.':'.__LINE__.' key [oauth_json] not exist!');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }
        $arr_oauth_json = json_decode($request['oauth_json']);
        if (!$arr_oauth_json) {
            log_message('error', __FILE__.':'.__LINE__.' json_decode error.');
            $this->renderJson(STATUS_ERR_REQUEST);
            return ;
        }

        // adapt oauth
        switch ($request['oauth_name']) {
        case 'qq':
            if (!isset($arr_oauth_json['nickname'])) {
                log_message('error', __FILE__.':'.__LINE__.' oauth key [nickname] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            if (!isset($arr_oauth_json['figureurl_qq_1'])) {
                log_message('error', __FILE__.':'.__LINE__.' oauth key [figureurl_qq_1] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            if (!isset($arr_oauth_json['figureurl_qq_2'])) {
                log_message('error', __FILE__.':'.__LINE__.' oauth key [figureurl_qq_2] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            $arr_img = array(
                'n' => array(
                    'url'   => $arr_oauth_json['figureurl_qq_2'],
                    'w'     => 100,
                    'h'     => 100,
                ),
                's' => array(
                    'url'   => $arr_oauth_json['figureurl_qq_1'],
                    'w'     => 40,
                    'h'     => 40,
                ),
            );
            $avatar = json_encode($arr_img);

            $arr_user_input = array(
                'oauth_name'    => 'qq',
                'oauth_key'     => 0,           // TODO: no key
                'login_type'    => 1,
                'create_time'   => time(),
            );
            $arr_user_detail_input = array(
                'sname'     => $arr_oauth_json['nickname'],
                'avatar'    => $avatar,
            );

            break;
        case 'weixin':
            if (!isset($arr_oauth_json['nickname'])) {
                log_message('error', __FILE__.':'.__LINE__.' oauth key [nickname] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            if (!isset($arr_oauth_json['headimgurl'])) {
                log_message('error', __FILE__.':'.__LINE__.' oauth key [headimgurl] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            if (!isset($arr_oauth_json['openid'])) {
                log_message('error', __FILE__.':'.__LINE__.' oauth key [openid] not exist!');
                $this->renderJson(STATUS_ERR_REQUEST);
                return ;
            }
            $arr_img = array(
                'n' => array(
                    'url'   => $arr_oauth_json['headimgurl'],
                ),
            );
            $avatar = json_encode($arr_img);

            $arr_user_input = array(
                'oauth_name'    => 'weixin',
                'oauth_key'     => $arr_oauth_json['openid'],
                'login_type'    => 1,
                'create_time'   => time(),
            );
            $arr_user_detail_input = array(
                'sname'     => $arr_oauth_json['nickname'],
                'avatar'    => $avatar,
            );
            break;
        default:
            log_message('error', __FILE__.':'.__LINE__.' unknown oauth_name '.$request['oauth_name']);
        }

        // check is a new user or not
        $is_new = false;
        $uid = $this->user_model->get_uid_by_oauth($arr_user_input['oauth_name'], $arr_user_input['oauth_key']); // TODO: no key
        if (false === $uid) {
            log_message('error', __FILE__.':'.__LINE__.' get_uid_by_oauth error.');
            $this->renderJson(MYSQL_ERR_CONNECT);
            return ;
        }
        if (NULL === $uid) {
            log_message('debug', __FILE__.':'.__LINE__.' it\'s a new user.');
            $is_new = true;
        }

        // a new user
        if ($is_new) {
            // get base info
            $uid = $this->user_model->add($arr_user_input);
            if (false === $uid) {
                log_message('error', __FILE__.':'.__LINE__.' add_user error.');
                $this->renderJson(MYSQL_ERR_CONNECT);
                return ;
            }
            if (NULL === $uid) {
                log_message('error', __FILE__.':'.__LINE__.' add_user not affect rows.');
                $this->renderJson(MYSQL_ERR_INSERT);
                return ;
            }
            // get detail
            $arr_user_detail_input['uid'] = $uid;
            $user_detail_res = $this->user_detail_model->add($arr_user_detail_input);
            if (false === $user_detail_res) {
                log_message('error', __FILE__.':'.__LINE__.' add_user_detail error, uid='.$uid);
                $this->renderJson(MYSQL_ERR_CONNECT);
                return ;
            }
            if (NULL === $user_detail_res) {
                log_message('error', __FILE__.':'.__LINE__.' add_user_detail not affect rows, uid='.$uid);
                $this->renderJson(MYSQL_ERR_INSERT);
                return ;
            }
        }

        // get detail info
        $arr_user_detail = $this->user_detail_model->get_user_info($uid);
        if (false === $arr_user_detail) {
            log_message('error', __FILE__.':'.__LINE__.' user_detail get_user_info error, uid='.$uid);
            $this->renderJson(MYSQL_ERR_CONNECT);
            return ;
        }
        if (NULL === $arr_user_detail) {
            log_message('error', __FILE__.':'.__LINE__.' user_detail not affect rows, uid='.$uid);
            $this->renderJson(MYSQL_ERR_INSERT);
            return ;
        }

        // return info
        $response['errno'] = 0;
        $response['data'] = $arr_user_detail;

        $this->renderJson($response['errno'], $response['data']);
    }
}
