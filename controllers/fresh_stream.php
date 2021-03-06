<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fresh_stream extends MY_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->model('fresh_stream_model');
	}

    private function _fill_tweet_info($tids) {
        $this->load->model('tweet_model');
        $result = array();
        foreach ($tids as $tid) {
            //$ret = $this->tweet_model->get_tweet_info($tid); 
            $ret = $this->get_tweet_detail($tid); 
            if ($ret /*&& 0 == intval($ret['is_del'])*/) {
                $ret['imgs'] = $ret['imgs'][0];
                $result[] = $ret;  
            }
        }
        return $result;
    }

    public function get_new() {
        $request = $this->request_array;
        $rn = isset($this->request_array['rn']) ? intval($this->request_array['rn']) : 10;

        $type = null;
        $tids = null;
        if (isset($request['first_id'])) {
            $idx = $this->fresh_stream_model->index($request['first_id']);
            if (false === $idx) {
                $this->renderJson(REDIS_ERR_OP);
                return;
            }
            if ($idx < 0) {
                $tids = $this->fresh_stream_model->get_by_page(0, $rn); 
                $type = 'new';
            } else {
                $tids = $this->fresh_stream_model->get_newer($idx, $rn); 
                $type = 'append';
            }
        } else {
            $tids = $this->fresh_stream_model->get_by_page(0, $rn);  
            $type = 'new';
        }
        if (false == $tids) {
            $this->renderJson(REDIS_ERR_OP); 
            return;
        }
        if (0 == count($tids)) {
            $this->renderJson(STATUS_OK, 
                              array('content' => array(), 'type' => $type, 'choose_type' => 2)); 
            return;
        }
        $content = $this->_fill_tweet_info($tids);
        $this->renderJson(STATUS_OK, array('content' => $content, 'type' => $type, 'choose_type' => 2 ));
    }

    public function get_old() {
        $request = $this->request_array;
        if (!isset($request['last_id'])) {
            $this->renderJson(STATUS_ERR_REQUEST); 
            return;
        }
        $last_id = $request['last_id']; 
        $rn = isset($this->request_array['rn']) ? intval($this->request_array['rn']) : 10;

        //$type = 'append';
        $type = 'next';
        $idx = $this->fresh_stream_model->index($last_id);
        if (false === $idx) {
            $this->renderJson(REDIS_ERR_OP);
            return;
        }
        if ($idx < 0) {
            $tids = array(); 
        } else {
            $tids = $this->fresh_stream_model->get_older($idx, $rn); 
        }
        if (false == $tids) {
            $this->renderJson(REDIS_ERR_OP); 
            return;
        }
        if (0 == count($tids)) {
            $this->renderJson(STATUS_OK, 
                              array('content' => array(), 'type' => $type, 'choose_type' => 2)); 
            return;
        }
        $content = $this->_fill_tweet_info($tids);
        $this->renderJson(STATUS_OK, array('content' => $content, 'type' => $type, 'choose_type' => 2));
    }

}
