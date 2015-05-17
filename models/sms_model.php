<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


class Sms_model extends CI_Model { 

    private $table_name = 'ci_user_sms';

    public function __construct(){
        parent::__construct();

    } 


    function add($request) {

        $result = $this->db->insert($this->table_name, $request);
        //log_message('error', $this->db->last_query());
        if($result && $this->db->affected_rows() > 0) {
            $sid = $this->db->insert_id(); 
            return $sid;
        }
        return false;

    }

    function get_user_sms_count($mobile, $ctime_yday) {
        
        $this->db->select('sid');
        $where = array(
            'mobile' => $mobile,
            'ctime_yday' => $ctime_yday,
        );
        $this->db->where($where);
        $result = $this->db->get($this->table_name);
        //log_message('error', $this->db->last_query());
        if($result && $result->num_rows >= 0) {
            return $result->num_rows();
        }
        return false;
    }

    function get_user_latest_sms($mobile, $ctime_yday) {
        
        $this->db->select('ctime');
        $where = array(
            'mobile' => $mobile,
            'ctime_yday' => $ctime_yday,
        );
        $this->db->where($where);
        $this->db->order_by('ctime', 'desc');
        $this->db->limit(1);
        $result = $this->db->get($this->table_name);
        if($result && $result->num_rows > 0) {
            return $result->row_array();
        }
        if($result->num_rows == 0) {
            return null;
        }
        return false;
    }

    function get_info_by_verifycode($mobile, $verifycode, $fields = '*') {
    
        $this->db->select($fields);
        $this->db->where('mobile', $mobile);
        $this->db->where('verifycode', $verifycode);
        $this->db->where('valid', 1);
        $this->db->order_by('ctime', 'desc');
        $this->db->limit(1);
        $result = $this->db->get($this->table_name);
        log_message('error', $this->db->last_query());

        if($result && $result->num_rows > 0) {
            return $result->row_array(); 
        }
        if($result->num_rows == 0) {
            return null;
        }
        return false;
    }

}
