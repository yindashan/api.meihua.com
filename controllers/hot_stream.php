<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hot_stream extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	}

    public function get() {
        $this->load->model('hot_stream_model');
        $pn = isset($this->request_array['pn']) ? intval($this->request_array['pn']) : 0;
        $rn = isset($this->request_array['rn']) ? intval($this->request_array['rn']) : 20;
        $ret = $this->hot_stream_model->get_by_page($pn, $rn);
        if (false === $ret) {
            $this->renderJson(REDIS_ERR_OP); 
            return;
        }
        if (0 == count($ret)) {
            $this->renderJson(STATUS_OK, array('content' => array())); 
            return;
        }
        $this->load->model('tweet_model');
        $result = array();
        foreach ($ret as $tid) {
            //$ret = $this->tweet_model->get_tweet_info($tid); 
            $ret = $this->get_tweet_detail($tid);
            $ret['imgs'] = $ret['imgs'][0];
            if ($ret /*&& 0 == intval($ret['is_del'])*/) {
                $result[] = $ret;  
            }
        }
        $this->renderJson(STATUS_OK, array('content' => $result, 'type' => 'new', 'choose_type' => 1));
    }

}
